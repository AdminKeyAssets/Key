<?php

namespace App\Modules\Asset\Http\Controllers;

use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Admin\Models\User\Investor;
use App\Modules\Asset\Models\Asset;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class RevenueController extends BaseController
{

    /**
     * @var string
     */
    protected $baseModuleName = 'asset::';
    /**
     * @var string
     */
    public $viewFolderName = 'revenue';

    /**
     * @var string
     */
    public $baseName = 'revenue.';

    public function __construct()
    {
        parent::__construct();
        $this->baseData['moduleKey'] = 'revenue';
        $this->baseData['baseRouteName'] = $this->baseData['baseRouteName'] . '.' . $this->baseData['moduleKey'] . '.';
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $userId = $user->getAuthIdentifier();
        $managers = ['Asset Manager', 'AssetManager', 'Sales Manager', 'Sales manager', 'SalesManager'];

        if (in_array($user->getRolesNameAttribute(), $managers)) {
            $investors = Investor::where('admin_id', $userId)->pluck('id');
            $paginatedAssets = Asset::whereIn('investor_id', $investors)->orderByDesc('id');
            $allAssets = Asset::whereIn('investor_id', $investors);
        } else {
            $paginatedAssets = Asset::orderByDesc('id');
            $allAssets = Asset::orderByDesc('id');
        }

        if ($request->agreement_date) {
            $createdDates = explode(',', $request->agreement_date);
            if (isset($createdDates[0])) {
                $paginatedAssets->where('created_at', '>=', $createdDates[0]);
                $allAssets->where('created_at', '>=', $createdDates[0]);
            }
            if (isset($createdDates[1])) {
                $paginatedAssets->where('created_at', '<=', $createdDates[1]);
                $allAssets->where('created_at', '<=', $createdDates[1]);
            }
        }

        $paginatedAssets = $paginatedAssets->paginate(25);
        $allAssets = $allAssets->get();

        $this->calculateRevenue($paginatedAssets, $allAssets);

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.index', $this->baseData);
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function investorRevenues(Request $request)
    {
        $user = auth()->user();
        $userId = $user->getAuthIdentifier();

        // Fetch paginated assets
        $paginatedAssets = Asset::where('investor_id', $userId)->orderByDesc('id');

        // Fetch all assets for totals calculation
        $allAssets = Asset::where('investor_id', $userId);

        if ($request->agreement_date) {
            $createdDates = explode(',', $request->agreement_date);
            if (isset($createdDates[0])) {
                $paginatedAssets->where('created_at', '>=', $createdDates[0]);
                $allAssets->where('created_at', '>=', $createdDates[0]);
            }
            if (isset($createdDates[1])) {
                $paginatedAssets->where('created_at', '<=', $createdDates[1]);
                $allAssets->where('created_at', '<=', $createdDates[1]);
            }
        }

        $paginatedAssets = $paginatedAssets->paginate(25);
        $allAssets = $allAssets->get();

        $this->calculateRevenue($paginatedAssets, $allAssets);

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.index', $this->baseData);
    }

    private function calculateRevenue($paginatedAssets, $allAssets): void
    {
        // Initialize totals
        $totalRent = 0;
        $totalCapitalGain = 0;
        $totalInvestment = 0;

        // Calculate totals for all assets
        foreach ($allAssets as $asset) {
            $totalRent += $asset->rentals()->where('status', true)->sum('amount');
            $totalCapitalGain += $asset->current_value - $asset->total_price;
            $totalInvestment += $asset->payments()->where('status', true)->sum('amount');
        }

        foreach ($paginatedAssets as $asset) {
            $asset->rent = $asset->rentals()->where('status', true)->sum('amount');
            $asset->capital_gain = $asset->current_value - $asset->total_price;
            $asset->total_investment = $asset->payments()->where('status', true)->sum('amount');
        }

        $this->baseData['allData'] = $paginatedAssets;
        $this->baseData['totals'] = [
            'total_rent' => $totalRent,
            'total_capital_gain' => $totalCapitalGain,
            'total_investment' => $totalInvestment,
        ];
    }
}
