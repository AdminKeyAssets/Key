<?php

namespace Tests\Feature\Smoke;

use App\Modules\Admin\Models\User\Admin;
use Tests\TestCase;

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

    /** @dataProvider adminPageProvider */
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
            'admin/dashboard',
            'admin/profile',
            'admin/users',
            'admin/users/filter-options',
            'admin/users/reminders',
            'admin/roles',
            'admin/investors',
            'admin/investors/create',
            'admin/investors/filter-options',
            'admin/developers',
            'admin/developers/create',
            'admin/developers/filter-options',
            'admin/news',
            'admin/news/create',
            'admin/news/filter-options',
            'assets/list',
            'assets/create',
            'assets/filter-options',
            'assets/names',
            'assets/available-managers',
            'assets/comments/unread',
            'assets/notifications/pending-payment',
            'assets/notifications/pending-rentals',
            'assets/revenues',
            'assets/revenues/filter-options',
            'assets/revenues/admin/filter-options',
            'lead/list',
            'lead/create',
            'lead/filter-options',
            'lead/prefixes',
            'sale/list',
            'sale/create',
            'sale/filter-options',
            'templates/list',
            'templates/create',
            'templates/filter',
        ];

        return array_combine($urls, array_map(fn ($url) => [$url], $urls));
    }
}
