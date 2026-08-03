<?php

namespace Tests\Feature\Smoke;

use App\Modules\Admin\Models\User\Admin;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExportsSmokeTest extends TestCase
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

    private function assertDownloads(string $url): void
    {
        $response = $this->actingAs($this->administrator, 'admin')->get($url);

        $this->assertTrue(
            $response->isSuccessful(),
            sprintf('GET %s returned %d, expected a successful download.', $url, $response->getStatusCode())
        );
    }

    /** @dataProvider listExportProvider */
    public function test_list_export_succeeds(string $url): void
    {
        $this->assertDownloads($url);
    }

    public static function listExportProvider(): array
    {
        $urls = [
            'assets/export',
            'assets/revenues/export',
            'admin/investors/export',
            'admin/users/export',
            'lead/export',
            'sale/export',
            'lead/export-import-sample',
            'lead/export-import-status-sample',
            'lead/export-import-manager-sample',
        ];

        return array_combine($urls, array_map(fn ($url) => [$url], $urls));
    }

    public function test_per_asset_exports_succeed(): void
    {
        $assetId = DB::table('assets')->min('id');

        if ($assetId === null) {
            $this->markTestSkipped('No assets in the test database.');
        }

        foreach ([
            "assets/{$assetId}/payments/export",
            "assets/{$assetId}/payments-history/export",
            "assets/{$assetId}/dept-statement/export",
            "assets/{$assetId}/rental/export",
            "assets/{$assetId}/rental-payments-history/export",
            "assets/{$assetId}/renovation/export",
            "assets/{$assetId}/renovation-payments-history/export",
            "assets/revenues/{$assetId}/asset-value-history/export",
            "assets/revenues/{$assetId}/investments/export",
            "assets/revenues/{$assetId}/rentals/export",
        ] as $url) {
            $this->assertDownloads($url);
        }
    }
}
