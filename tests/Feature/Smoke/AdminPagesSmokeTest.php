<?php

namespace Tests\Feature\Smoke;

use App\Modules\Admin\Models\User\Admin;
use Tests\TestCase;

/**
 * Characterization tests for the admin panel.
 *
 * These are deliberately shallow: they assert that every admin page still boots,
 * resolves its views and returns a successful response. They exist to catch
 * regressions during the Laravel 7 -> 13 upgrade, where the usual failure mode is
 * a removed framework API turning a working page into a 500.
 *
 * They run against the key_assets_test database (see phpunit.xml), which is a
 * clone of the development database, so the pages render with realistic data.
 */
class AdminPagesSmokeTest extends TestCase
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

    /**
     * @dataProvider adminPageProvider
     */
    public function test_admin_page_renders(string $url): void
    {
        $response = $this->actingAs($this->administrator, 'admin')->get($url);

        $this->assertTrue(
            $response->isSuccessful(),
            sprintf('GET %s returned %d, expected a successful response.', $url, $response->getStatusCode())
        );
    }

    public static function adminPageProvider(): array
    {
        $urls = [
            // Dashboard & account
            'admin/dashboard',
            'admin/profile',

            // Users, roles, permissions
            'admin/users',
            'admin/users/filter-options',
            'admin/users/reminders',
            'admin/roles',

            // Investors
            'admin/investors',
            'admin/investors/create',
            'admin/investors/filter-options',

            // Developers
            'admin/developers',
            'admin/developers/create',
            'admin/developers/filter-options',

            // News
            'admin/news',
            'admin/news/create',
            'admin/news/filter-options',

            // Assets
            'assets/list',
            'assets/create',
            'assets/filter-options',
            'assets/names',
            'assets/available-managers',
            'assets/comments/unread',
            'assets/notifications/pending-payment',
            'assets/notifications/pending-rentals',

            // Revenue
            'assets/revenues',
            'assets/revenues/filter-options',
            'assets/revenues/admin/filter-options',

            // Leads
            'lead/list',
            'lead/create',
            'lead/filter-options',
            'lead/prefixes',

            // Sales
            'sale/list',
            'sale/create',
            'sale/filter-options',

            // Email templates
            'templates/list',
            'templates/create',
            'templates/filter',
        ];

        return array_combine($urls, array_map(fn ($url) => [$url], $urls));
    }
}
