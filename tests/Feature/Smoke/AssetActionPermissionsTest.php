<?php

namespace Tests\Feature\Smoke;

use App\Modules\Admin\Models\User\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AssetActionPermissionsTest extends TestCase
{
    use DatabaseTransactions;

    private const DELETE = '<delete-component';
    private const ARCHIVE = '<archive-asset-component';
    private const DEVELOPER_ACCESS = '<developer-access-component';

    private function adminWithRole(string $role): Admin
    {
        $admin = Admin::whereHas('roles', fn ($q) => $q->where('name', $role))->first();

        if (! $admin) {
            $this->markTestSkipped("No admin holding the {$role} role.");
        }

        return $admin;
    }

    private function assetListHtmlAs(Admin $admin): string
    {
        return $this->actingAs($admin, 'admin')->get('assets/list')->getContent();
    }

    private function revokeFromAdministrator(string $permission): void
    {
        Role::where('name', 'administrator')->firstOrFail()->revokePermissionTo($permission);
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_administrator_sees_every_asset_action(): void
    {
        $html = $this->assetListHtmlAs($this->adminWithRole('administrator'));

        $this->assertStringContainsString(self::DELETE, $html);
        $this->assertStringContainsString(self::ARCHIVE, $html);
        $this->assertStringContainsString(self::DEVELOPER_ACCESS, $html);
    }

    public function test_delete_permission_gates_the_delete_action(): void
    {
        $admin = $this->adminWithRole('administrator');

        $this->assertStringContainsString(self::DELETE, $this->assetListHtmlAs($admin));

        $this->revokeFromAdministrator('asset_delete');

        $html = $this->assetListHtmlAs($admin->fresh());

        $this->assertStringNotContainsString(self::DELETE, $html);
        $this->assertStringContainsString(self::ARCHIVE, $html);
    }

    public function test_update_permission_gates_the_archive_action(): void
    {
        $admin = $this->adminWithRole('administrator');

        $this->revokeFromAdministrator('asset_update');

        $html = $this->assetListHtmlAs($admin->fresh());

        $this->assertStringNotContainsString(self::ARCHIVE, $html);
        $this->assertStringContainsString(self::DELETE, $html);
    }

    public function test_developer_access_action_is_not_permission_gated(): void
    {
        $admin = $this->adminWithRole('administrator');

        $this->revokeFromAdministrator('asset_update');
        $this->revokeFromAdministrator('asset_delete');

        $this->assertStringContainsString(
            self::DEVELOPER_ACCESS,
            $this->assetListHtmlAs($admin->fresh()),
            'developer-access-component is gated only by Auth::guard("admin")->check(), '
            .'so it stays visible to any admin regardless of asset permissions.'
        );
    }

    public function test_asset_list_is_forbidden_without_the_list_permission(): void
    {
        $salesManager = $this->adminWithRole('Sales Manager');

        $this->assertFalse(
            $salesManager->hasPermissionTo('asset_list'),
            'Fixture assumption changed: Sales Manager now holds asset_list.'
        );

        $this->actingAs($salesManager, 'admin')->get('assets/list')->assertForbidden();
    }
}
