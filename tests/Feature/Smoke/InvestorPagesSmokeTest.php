<?php

namespace Tests\Feature\Smoke;

use App\Modules\Admin\Models\User\Developer;
use App\Modules\Admin\Models\User\Investor;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InvestorPagesSmokeTest extends TestCase
{
    private function assertRendersAs($user, string $guard, string $url): void
    {
        $response = $this->actingAs($user, $guard)->get($url);

        $this->assertTrue(
            $response->isSuccessful(),
            sprintf('GET %s as %s returned %d, expected a successful response.', $url, $guard, $response->getStatusCode())
        );
    }

    private function investorWithAssets(): ?Investor
    {
        $investorId = DB::table('asset_investor')->min('investor_id');

        return $investorId === null ? null : Investor::find($investorId);
    }

    public function test_investor_portal_pages_render(): void
    {
        $investor = $this->investorWithAssets();

        if (! $investor) {
            $this->markTestSkipped('No investor linked to an asset in the test database.');
        }

        foreach ([
            'assets',
            'assets/investor/filter-options',
            'assets/revenues/investor/filter-options',
            'investor/profile',
            'investor/news',
            'investor/news/unread-count',
        ] as $url) {
            $this->assertRendersAs($investor, 'investor', $url);
        }
    }

    public function test_investor_asset_detail_pages_render(): void
    {
        $investor = $this->investorWithAssets();

        if (! $investor) {
            $this->markTestSkipped('No investor linked to an asset in the test database.');
        }

        $assetId = (int) DB::table('asset_investor')
            ->where('investor_id', $investor->id)
            ->min('asset_id');

        $this->assertRendersAs($investor, 'investor', "assets/details/{$assetId}");
        $this->assertRendersAs($investor, 'investor', "assets/revenues/details/{$assetId}");
    }

    public function test_developer_portal_pages_render(): void
    {
        $developer = Developer::query()->orderBy('id')->first();

        if (! $developer) {
            $this->markTestSkipped('No developers in the test database.');
        }

        foreach ([
            'assets',
            'assets/developer/filter-options',
            'developer/news',
            'developer/news/create',
            'developer/news/filter-options',
            'developer/managers',
        ] as $url) {
            $this->assertRendersAs($developer, 'developer', $url);
        }
    }
}
