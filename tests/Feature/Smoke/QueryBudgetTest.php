<?php

namespace Tests\Feature\Smoke;

use App\Modules\Admin\Models\User\Admin;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class QueryBudgetTest extends TestCase
{
    private Admin $administrator;

    protected function setUp(): void
    {
        parent::setUp();

        $administrator = Admin::whereHas('roles', fn ($q) => $q->where('name', 'administrator'))->first();

        if (! $administrator) {
            $this->markTestSkipped('No administrator account in the test database.');
        }

        $this->administrator = $administrator;
    }

    #[DataProvider('budgetProvider')]
    public function test_page_stays_within_its_query_budget(string $url, int $budget): void
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->actingAs($this->administrator, 'admin')->get($url);

        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(
            $budget,
            $count,
            sprintf(
                'GET %s ran %d queries, budget is %d. A relation is probably being lazy loaded inside a loop.',
                $url,
                $count,
                $budget
            )
        );
    }

    public static function budgetProvider(): array
    {
        return [
            'assets/list' => ['assets/list', 20],
            'assets/revenues' => ['assets/revenues', 40],
            'admin/investors' => ['admin/investors', 10],
            'lead/list' => ['lead/list', 10],
            'admin/news' => ['admin/news', 10],
        ];
    }
}
