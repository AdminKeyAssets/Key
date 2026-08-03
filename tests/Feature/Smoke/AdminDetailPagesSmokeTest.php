<?php

namespace Tests\Feature\Smoke;

use App\Modules\Admin\Models\User\Admin;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminDetailPagesSmokeTest extends TestCase
{
    private Admin $administrator;

    protected function setUp(): void
    {
        parent::setUp();

        $administrator = Admin::whereHas('roles', function ($query) {
            $query->where('name', 'administrator');
        })->first();

        if (! $administrator) {
            $this->markTestSkipped('No administrator account in the test database.');
        }

        $this->administrator = $administrator;
    }

    private function firstId(string $table): ?int
    {
        $id = DB::table($table)->min('id');

        return $id === null ? null : (int) $id;
    }

    private function assertRenders(string $url): void
    {
        $response = $this->actingAs($this->administrator, 'admin')->get($url);

        $this->assertTrue(
            $response->isSuccessful(),
            sprintf('GET %s returned %d, expected a successful response.', $url, $response->getStatusCode())
        );
    }

    public function test_asset_detail_pages_render(): void
    {
        $assetId = $this->firstId('assets');

        if ($assetId === null) {
            $this->markTestSkipped('No assets in the test database.');
        }

        foreach ([
            "assets/view/{$assetId}",
            "assets/edit/{$assetId}",
            "assets/{$assetId}/comments",
            "assets/{$assetId}/payments",
            "assets/{$assetId}/payments/create",
            "assets/{$assetId}/rental",
            "assets/{$assetId}/rental/create",
            "assets/{$assetId}/renovation",
            "assets/{$assetId}/renovation/create",
            "assets/{$assetId}/investment",
            "assets/{$assetId}/investment/create",
        ] as $url) {
            $this->assertRenders($url);
        }
    }

    public function test_revenue_detail_pages_render(): void
    {
        $assetId = $this->firstId('assets');

        if ($assetId === null) {
            $this->markTestSkipped('No assets in the test database.');
        }
        $this->assertRenders("assets/revenues/view/{$assetId}");
    }

    public function test_investor_detail_pages_render(): void
    {
        $investorId = $this->firstId('investors');

        if ($investorId === null) {
            $this->markTestSkipped('No investors in the test database.');
        }

        $this->assertRenders("admin/investors/view/{$investorId}");
        $this->assertRenders("admin/investors/edit/{$investorId}");
    }

    public function test_developer_detail_pages_render(): void
    {
        $developerId = $this->firstId('developers');

        if ($developerId === null) {
            $this->markTestSkipped('No developers in the test database.');
        }

        $this->assertRenders("admin/developers/view/{$developerId}");
        $this->assertRenders("admin/developers/edit/{$developerId}");
    }

    public function test_lead_detail_pages_render(): void
    {
        $leadId = $this->firstId('leads');

        if ($leadId === null) {
            $this->markTestSkipped('No leads in the test database.');
        }

        $this->assertRenders("lead/view/{$leadId}");
        $this->assertRenders("lead/edit/{$leadId}");
        $this->assertRenders("lead/{$leadId}/comments");
    }

    public function test_sale_detail_pages_render(): void
    {
        $saleId = $this->firstId('sales');

        if ($saleId === null) {
            $this->markTestSkipped('No sales in the test database.');
        }

        $this->assertRenders("sale/view/{$saleId}");
        $this->assertRenders("sale/edit/{$saleId}");
    }

    public function test_user_and_role_forms_render(): void
    {
        $adminId = $this->firstId('admins');
        $roleId = $this->firstId('roles');

        $this->assertRenders("admin/users/create/{$adminId}");
        $this->assertRenders("admin/roles/create/{$roleId}");
    }
}
