<?php

namespace Tests\Feature\Infrastructure;

use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Platform\Entities\FileRequestEntity;
use App\Modules\Core\Platform\Models\File;
use App\Modules\Core\Platform\Models\FileAudit;
use App\Modules\Core\Platform\Models\FileVariant;
use App\Modules\Core\Platform\Repositories\FileRepository;
use App\Modules\Core\Platform\Services\FileService;
use App\Modules\Core\Platform\Services\ImageService;
use App\Modules\Core\Identity\Models\UserBranch;
use App\Modules\Core\Identity\Models\UserCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Test perilaku nyata FileService/ImageService dengan FileRepository konkret
 * (SQLite :memory:) -- melengkapi FileServiceTest/ImageServiceTest yang
 * sebelumnya hanya reflection stub.
 */
class FileServiceUploadFlowTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected Branch $branch;
    protected User $user;
    protected User $owner;
    protected FileService $fileService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create(['deleted_at' => 0]);
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);

        $this->user = User::factory()->create();
        $this->owner = User::factory()->create();

        UserCompany::query()->create([
            'user_uuid' => $this->user->uuid,
            'company_uuid' => $this->company->uuid,
            'is_default' => true,
        ]);
        UserBranch::query()->create([
            'user_uuid' => $this->user->uuid,
            'company_uuid' => $this->company->uuid,
            'branch_uuid' => $this->branch->uuid,
            'is_default' => true,
        ]);

        $this->actingAs($this->user);
        Auth::shouldUse('web');

        Storage::fake('local');
        $this->fileService = new FileService(new FileRepository(), 'local');
    }

    private function validEntity(array $overrides = []): FileRequestEntity
    {
        return FileRequestEntity::fromArray(array_merge([
            'branch_uuid'       => $this->branch->uuid,
            'parent_uuid'       => $this->owner->uuid,
            'parent_model'      => 'users',
            'feature'           => 'avatar',
            'context_type'      => 'profile',
            'original_filename' => 'foto.pdf',
            'mime_type'         => 'application/pdf',
            'file_content'      => "%PDF-1.4\n%%EOF",
            'file_size'         => strlen("%PDF-1.4\n%%EOF"),
            'uploaded_by_uuid'  => $this->user->uuid,
            'uploaded_ip'       => '127.0.0.1',
        ], $overrides));
    }

    public function test_upload_file_creates_file_variant_and_audit(): void
    {
        $file = $this->fileService->uploadFile($this->validEntity());

        $this->assertInstanceOf(File::class, $file);
        $this->assertSame('foto.pdf', $file->original_filename);
        $this->assertSame('application/pdf', $file->mime_type);
        $this->assertSame('document', $file->file_category);
        $this->assertSame(1, $file->version);
        $this->assertTrue((bool) $file->is_primary);
        $this->assertTrue((bool) $file->is_latest);

        Storage::disk('local')->assertExists('files/' . $file->file_path);

        $variants = FileVariant::query()->where('file_uuid', $file->uuid)->get();
        $this->assertCount(1, $variants);
        $this->assertSame('original', $variants->first()->variant_type);

        $audit = FileAudit::query()->where('file_uuid', $file->uuid)->first();
        $this->assertNotNull($audit);
        $this->assertSame('upload', $audit->action);
        $this->assertSame($this->user->uuid, $audit->actor_uuid);
    }

    public function test_upload_same_content_returns_existing_file_as_duplicate(): void
    {
        $first = $this->fileService->uploadFile($this->validEntity());
        $second = $this->fileService->uploadFile($this->validEntity([
            'original_filename' => 'duplikat.pdf',
        ]));

        // Kontrak service: konten sama -> kembalikan file existing, bukan row baru
        $this->assertSame($first->uuid, $second->uuid);
        $this->assertSame($first->uuid, $second->existing_file_uuid);
        $this->assertTrue((bool) $second->is_duplicate);
        $this->assertSame(1, File::query()->where('file_hash', $first->file_hash)->count());
    }

    public function test_upload_rejects_missing_parent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('parent_uuid tidak ditemukan.');

        $this->fileService->uploadFile($this->validEntity(['parent_uuid' => 'uuid-tidak-ada']));
    }

    public function test_upload_rejects_dangerous_extension(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Extension file berbahaya');

        $this->fileService->uploadFile($this->validEntity([
            'original_filename' => 'shell.php',
        ]));
    }

    public function test_upload_rejects_oversized_file(): void
    {
        $content = str_repeat('A', 2048);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Ukuran file melebihi batas maksimum.');

        $this->fileService->uploadFile($this->validEntity([
            'original_filename' => 'besar.pdf',
            'mime_type'         => 'application/pdf',
            'file_content'      => $content,
            'file_size'         => strlen($content),
            'options'           => ['max_size_bytes' => 1024],
        ]));
    }

    public function test_soft_delete_marks_file_and_writes_audit(): void
    {
        $file = $this->fileService->uploadFile($this->validEntity());

        $this->fileService->softDeleteFile(FileRequestEntity::fromArray([
            'file_uuid'  => $file->uuid,
            'actor_uuid' => $this->user->uuid,
            'actor_ip'   => '127.0.0.1',
            'reason'     => 'tidak dipakai',
        ]));

        $this->assertNull(app(FileRepository::class)->findByUUID($file->uuid));
        $this->assertNotNull(app(FileRepository::class)->findAnyByUUID($file->uuid));

        $audit = FileAudit::query()->where('file_uuid', $file->uuid)
            ->where('action', 'delete')->first();
        $this->assertNotNull($audit);
        $this->assertSame('tidak dipakai', $audit->note);
    }

    public function test_soft_delete_rejects_missing_file(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('File tidak ditemukan.');

        $this->fileService->softDeleteFile(FileRequestEntity::fromArray([
            'file_uuid'  => 'uuid-tidak-ada',
            'actor_uuid' => $this->user->uuid,
        ]));
    }

    public function test_restore_brings_back_soft_deleted_file(): void
    {
        $file = $this->fileService->uploadFile($this->validEntity());

        $this->fileService->softDeleteFile(FileRequestEntity::fromArray([
            'file_uuid'  => $file->uuid,
            'actor_uuid' => $this->user->uuid,
        ]));
        $this->assertNull(app(FileRepository::class)->findByUUID($file->uuid));

        $this->fileService->restoreFile(FileRequestEntity::fromArray([
            'file_uuid'  => $file->uuid,
            'actor_uuid' => $this->user->uuid,
        ]));

        $this->assertNotNull(app(FileRepository::class)->findByUUID($file->uuid));
    }

    public function test_verify_integrity_detects_missing_storage_file(): void
    {
        $file = $this->fileService->uploadFile($this->validEntity());

        Storage::disk('local')->delete('files/' . $file->file_path);

        $result = $this->fileService->verifyFileIntegrity(FileRequestEntity::fromArray([
            'file_uuid' => $file->uuid,
        ]));

        $this->assertSame(1, $result['missing_files']);
        $this->assertSame(0, $result['hash_mismatch_files']);
    }

    public function test_verify_integrity_repairs_hash_when_repair_mode_on(): void
    {
        $file = $this->fileService->uploadFile($this->validEntity());

        // Konten di storage diubah di belakang service -> hash mismatch
        Storage::disk('local')->put('files/' . $file->file_path, 'TAMPERED');

        $result = $this->fileService->verifyFileIntegrity(FileRequestEntity::fromArray([
            'file_uuid'   => $file->uuid,
            'repair_mode' => true,
        ]));

        $this->assertSame(1, $result['hash_mismatch_files']);
        $this->assertSame(1, $result['repaired_files']);
        $this->assertSame(hash('sha256', 'TAMPERED'), $file->fresh()->file_hash);
    }

    public function test_image_service_rejects_non_image_mime(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Mime type image tidak valid.');

        $imageService = new ImageService(
            app(\App\Modules\Core\Platform\Repositories\ImageRepositoryInterface::class),
            $this->fileService,
        );
        $imageService->storeImage($this->validEntity());
    }

    public function test_get_storage_usage_reports_branch_usage(): void
    {
        $file = $this->fileService->uploadFile($this->validEntity());

        $usage = $this->fileService->getStorageUsage(FileRequestEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
        ]));

        $this->assertGreaterThanOrEqual($file->file_size, (int) ($usage['total_size_bytes'] ?? 0));
    }
}
