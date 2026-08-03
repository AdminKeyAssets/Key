<?php

namespace Tests\Feature\Smoke;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GuestAccessSmokeTest extends TestCase
{
    public function test_root_redirects_to_the_investor_login_form(): void
    {
        $this->get('/')->assertRedirect(route('admin.investor_login_form'));
    }

    #[DataProvider('guestPageProvider')]
    public function test_guest_page_renders(string $url): void
    {
        $response = $this->get($url);

        $this->assertTrue(
            $response->isSuccessful(),
            sprintf('GET %s returned %d, expected a successful response.', $url, $response->getStatusCode())
        );
    }

    public static function guestPageProvider(): array
    {
        $urls = [
            'admin',
            'login',
        ];

        return array_combine($urls, array_map(fn ($url) => [$url], $urls));
    }

    #[DataProvider('protectedPageProvider')]
    public function test_protected_page_rejects_guests(string $url): void
    {
        $response = $this->get($url);

        $this->assertFalse(
            $response->isSuccessful(),
            sprintf('GET %s was reachable without authentication.', $url)
        );
    }

    public static function protectedPageProvider(): array
    {
        $urls = [
            'admin/dashboard',
            'admin/users',
            'admin/investors',
            'assets/list',
            'lead/list',
            'investor/profile',
            'developer/news',
        ];

        return array_combine($urls, array_map(fn ($url) => [$url], $urls));
    }
}
