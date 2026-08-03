<?php

namespace Tests\Feature\Smoke;

use App\Modules\Admin\Models\User\Admin;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUploadSmokeTest extends TestCase
{
    public function test_audit_user_resolver_is_configured(): void
    {
        $this->assertIsString(
            config('audit.user.resolver'),
            'audit.user.resolver is unset. owen-it/laravel-auditing v14 reads the user '
            .'resolver from audit.user.resolver, not the v10 audit.resolver.user. When it '
            .'resolves to null every audited write fails with "Invalid UserResolver implementation".'
        );

        $this->assertTrue(
            class_exists(config('audit.user.resolver')),
            'audit.user.resolver points at a class that does not exist.'
        );
    }

    public function test_audit_guards_cover_the_applications_own_guards(): void
    {
        $guards = config('audit.user.guards');

        foreach (['admin', 'investor', 'developer'] as $guard) {
            $this->assertContains(
                $guard,
                $guards,
                "audit.user.guards omits the '{$guard}' guard, so audits recorded through it "
                .'have no user attached.'
            );
        }
    }

    public function test_admin_can_upload_an_image(): void
    {
        Storage::fake('public');

        $admin = Admin::whereHas('roles', fn ($q) => $q->where('name', 'administrator'))->first();

        if (! $admin) {
            $this->markTestSkipped('No administrator account in the test database.');
        }

        $response = $this->actingAs($admin, 'admin')->post('/admin/files/upload', [
            'file' => UploadedFile::fake()->image('upload-smoke.png', 40, 40),
        ]);

        $this->assertTrue(
            $response->isSuccessful(),
            sprintf('File upload returned %d, expected success.', $response->getStatusCode())
        );
    }
}
