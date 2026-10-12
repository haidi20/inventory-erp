<?php

namespace Tests\Feature\Infrastructure;

use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Identity\Models\UserBranch;
use App\Modules\Core\Identity\Models\UserCompany;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use Illuminate\Support\Facades\Broadcast;
use Tests\TestCase;

class ChannelAuthorizationTest extends TestCase
{
    private User $user;

    private Company $company;

    private Branch $branch;

    private Company $otherCompany;

    private Branch $otherBranch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
        $this->otherCompany = Company::factory()->create();
        $this->otherBranch = Branch::factory()->create(['company_uuid' => $this->otherCompany->uuid]);

        $this->user = User::factory()->create();

        UserCompany::create([
            'uuid' => (string) \Ramsey\Uuid\Uuid::uuid4(),
            'user_uuid' => $this->user->uuid,
            'company_uuid' => $this->company->uuid,
            'is_default' => 1,
        ]);

        UserBranch::create([
            'uuid' => (string) \Ramsey\Uuid\Uuid::uuid4(),
            'user_uuid' => $this->user->uuid,
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'is_default' => 1,
        ]);
    }

    public function test_user_belongs_to_company(): void
    {
        $this->assertTrue($this->user->belongsToCompany($this->company->uuid));
    }

    public function test_user_does_not_belong_to_other_company(): void
    {
        $this->assertFalse($this->user->belongsToCompany($this->otherCompany->uuid));
    }

    public function test_user_belongs_to_branch(): void
    {
        $this->assertTrue($this->user->belongsToBranch($this->branch->uuid));
    }

    public function test_user_does_not_belong_to_other_branch(): void
    {
        $this->assertFalse($this->user->belongsToBranch($this->otherBranch->uuid));
    }

    public function test_private_user_channel_authorizes_owner(): void
    {
        $result = Broadcast::auth($this->createRequest(
            'private-user.'.$this->user->uuid
        ));

        $this->assertNotNull($result);
    }

    public function test_private_user_channel_denies_other_user(): void
    {
        $otherUser = User::factory()->create();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException::class);
        Broadcast::auth($this->createRequest(
            'private-user.'.$otherUser->uuid
        ));
    }

    public function test_private_company_channel_authorizes_member(): void
    {
        $result = Broadcast::auth($this->createRequest(
            'private-company.'.$this->company->uuid
        ));

        $this->assertNotNull($result);
    }

    public function test_private_company_channel_denies_non_member(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException::class);
        Broadcast::auth($this->createRequest(
            'private-company.'.$this->otherCompany->uuid
        ));
    }

    public function test_private_branch_channel_authorizes_member(): void
    {
        $result = Broadcast::auth($this->createRequest(
            'private-branch.'.$this->branch->uuid
        ));

        $this->assertNotNull($result);
    }

    public function test_private_branch_channel_denies_non_member(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException::class);
        Broadcast::auth($this->createRequest(
            'private-branch.'.$this->otherBranch->uuid
        ));
    }

    private function createRequest(string $channelName): \Illuminate\Http\Request
    {
        $request = \Illuminate\Http\Request::create(
            '/broadcasting/auth',
            'POST',
            ['channel_name' => $channelName, 'socket_id' => '12345.67890']
        );
        $request->setUserResolver(fn () => $this->user);

        return $request;
    }
}
