<?php

namespace Tests\Unit\Entities\Core;

use App\Modules\Core\Platform\Entities\FileRequestEntity;
use PHPUnit\Framework\TestCase;

class FileRequestEntityTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated_from_an_array()
    {
        $data = [
            'file_uuid'           => 'file-uuid-1',
            'branch_uuid'         => 'branch-uuid-1',
            'parent_uuid'         => 'parent-uuid-1',
            'parent_model'        => 'users',
            'feature'             => 'avatar',
            'context_type'        => 'profile',
            'context_identifier'  => 'ctx-1',
            'original_filename'   => 'foto.png',
            'mime_type'           => 'image/png',
            'file_content'        => 'biner',
            'file_size'           => 5,
            'uploaded_by_uuid'    => 'user-uuid-1',
            'uploaded_ip'         => '127.0.0.1',
            'uploaded_by_role'    => 'admin',
            'reason'              => 'ubah foto profil',
            'variant'             => 'thumbnail',
            'disposition'         => 'inline',
            'delete_variants'     => true,
        ];

        $entity = FileRequestEntity::fromArray($data);

        $this->assertInstanceOf(FileRequestEntity::class, $entity);
        $this->assertSame($data['file_uuid'], $entity->file_uuid);
        $this->assertSame($data['branch_uuid'], $entity->branch_uuid);
        $this->assertSame($data['parent_uuid'], $entity->parent_uuid);
        $this->assertSame($data['parent_model'], $entity->parent_model);
        $this->assertSame($data['feature'], $entity->feature);
        $this->assertSame($data['context_type'], $entity->context_type);
        $this->assertSame($data['original_filename'], $entity->original_filename);
        $this->assertSame($data['mime_type'], $entity->mime_type);
        $this->assertSame($data['file_size'], $entity->file_size);
        $this->assertSame($data['uploaded_by_uuid'], $entity->uploaded_by_uuid);
        $this->assertSame($data['uploaded_ip'], $entity->uploaded_ip);
        $this->assertSame($data['uploaded_by_role'], $entity->uploaded_by_role);
        $this->assertSame($data['reason'], $entity->reason);
        $this->assertSame('thumbnail', $entity->variant);
        $this->assertSame('inline', $entity->disposition);
        $this->assertTrue($entity->delete_variants);
    }

    /** @test */
    public function it_applies_default_values_when_data_is_empty()
    {
        $entity = FileRequestEntity::fromArray([]);

        $this->assertNull($entity->file_uuid);
        $this->assertNull($entity->original_filename);
        $this->assertSame(0, $entity->file_size);
        $this->assertSame([], $entity->options);
        $this->assertSame('original', $entity->variant);
        $this->assertSame('attachment', $entity->disposition);
        $this->assertSame('full', $entity->backup_type);
        $this->assertSame('replace', $entity->restore_mode);
        $this->assertTrue($entity->verify_checksum);
        $this->assertFalse($entity->include_deleted);
        $this->assertFalse($entity->repair_mode);
        $this->assertFalse($entity->delete_variants);
    }

    /** @test */
    public function it_supports_legacy_alias_keys()
    {
        $entity = FileRequestEntity::fromArray([
            'uuid'     => 'legacy-uuid',
            'user_uuid' => 'user-1',
            'user_ip'  => '10.0.0.1',
        ]);

        $this->assertSame('legacy-uuid', $entity->file_uuid);
        $this->assertSame('user-1', $entity->uploaded_by_uuid);
        $this->assertSame('user-1', $entity->actor_uuid);
        $this->assertSame('10.0.0.1', $entity->uploaded_ip);
        $this->assertSame('10.0.0.1', $entity->actor_ip);
    }

    /** @test */
    public function it_casts_boolean_flags_from_strings()
    {
        $entity = FileRequestEntity::fromArray([
            'verify_checksum' => 'false',
            'repair_mode'     => '1',
        ]);

        $this->assertFalse($entity->verify_checksum);
        $this->assertTrue($entity->repair_mode);
    }

    /** @test */
    public function it_converts_to_array_roundtrip()
    {
        $data = [
            'file_uuid'         => 'f-1',
            'branch_uuid'       => 'b-1',
            'original_filename' => 'a.pdf',
            'mime_type'         => 'application/pdf',
            'file_content'      => '%PDF-1.4',
            'file_size'         => 9,
        ];

        $entity = FileRequestEntity::fromArray($data);
        $arr = $entity->toArray();

        $this->assertIsArray($arr);
        $this->assertSame('f-1', $arr['file_uuid']);
        $this->assertSame('b-1', $arr['branch_uuid']);
        $this->assertSame('a.pdf', $arr['original_filename']);
        $this->assertSame('application/pdf', $arr['mime_type']);
        $this->assertSame(9, $arr['file_size']);
        $this->assertSame('attachment', $arr['disposition']);
    }
}
