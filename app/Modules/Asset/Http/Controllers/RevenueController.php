<?php

namespace App\Modules\Asset\Http\Controllers;

use App\Modules\Admin\Exports\RevenueExport;
use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Admin\Models\Country;
use App\Modules\Admin\Models\User\Admin;
use App\Modules\Admin\Models\User\Investor;
use App\Modules\Asset\Models\Asset;
use App\Modules\Asset\Models\RentalPaymentsHistory;
use App\Modules\Asset\Models\Tenant;
use App\Utilities\ServiceResponse;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Mockery\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
        $user = auth('investor')->user();

        if (!$user) {
            $user = auth('admin')->user();
        }

        $userId = $user->getAuthIdentifier();
        $managers = ['Asset Manager', 'AssetManager', 'Sales Manager', 'Sales manager', 'SalesManager'];

        if (Auth::guard('investor')->check()) {
            $paginatedAssets = Asset::where('investor_id', $userId)->orderByDesc('id');
            $allAssets = Asset::where('investor_id', $userId);
        } else if (in_array($user->getRolesNameAttribute(), $managers)) {
            $investors = Investor::where('admin_id', $userId)->pluck('id');
            $paginatedAssets = Asset::whereIn('investor_id', $investors)->orderByDesc('id');
            $allAssets = Asset::whereIn('investor_id', $investors);
        } else {
            $paginatedAssets = Asset::orderByDesc('id');
            $allAssets = Asset::orderByDesc('id');
        }

        // Apply filters based on the related entities
        if ($request->agreement_date) {
            $createdDates = explode(',', $request->agreement_date);
            if (isset($createdDates[0])) {
                $paginatedAssets->where(function ($query) use ($createdDates) {
                    $query->where('created_at', '>=', $createdDates[0])
                        ->orWhereHas('paymentsHistories', function ($q) use ($createdDates) {
                            $q->where('created_at', '>=', $createdDates[0]);
                        })
                        ->orWhereHas('rentalPaymentsHistories', function ($q) use ($createdDates) {
                            $q->where('created_at', '>=', $createdDates[0]);
                        })
                        ->orWhereHas('Investments', function ($q) use ($createdDates) {
                            $q->where('created_at', '>=', $createdDates[0]);
                        });
                });
                $allAssets->where(function ($query) use ($createdDates) {
                    $query->where('created_at', '>=', $createdDates[0])
                        ->orWhereHas('paymentsHistories', function ($q) use ($createdDates) {
                            $q->where('created_at', '>=', $createdDates[0]);
                        })
                        ->orWhereHas('rentalPaymentsHistories', function ($q) use ($createdDates) {
                            $q->where('created_at', '>=', $createdDates[0]);
                        })
                        ->orWhereHas('Investments', function ($q) use ($createdDates) {
                            $q->where('created_at', '>=', $createdDates[0]);
                        });
                });
            }
            if (isset($createdDates[1])) {
                $paginatedAssets->where(function ($query) use ($createdDates) {
                    $query->where('created_at', '<=', $createdDates[1])
                        ->orWhereHas('paymentsHistories', function ($q) use ($createdDates) {
                            $q->where('created_at', '<=', $createdDates[1]);
                        })
                        ->orWhereHas('rentalPaymentsHistories', function ($q) use ($createdDates) {
                            $q->where('created_at', '<=', $createdDates[1]);
                        })
                        ->orWhereHas('Investments', function ($q) use ($createdDates) {
                            $q->where('created_at', '<=', $createdDates[1]);
                        });
                });
                $allAssets->where(function ($query) use ($createdDates) {
                    $query->where('created_at', '<=', $createdDates[1])
                        ->orWhereHas('paymentsHistories', function ($q) use ($createdDates) {
                            $q->where('created_at', '<=', $createdDates[1]);
                        })
                        ->orWhereHas('rentalPaymentsHistories', function ($q) use ($createdDates) {
                            $q->where('created_at', '<=', $createdDates[1]);
                        })
                        ->orWhereHas('Investments', function ($q) use ($createdDates) {
                            $q->where('created_at', '<=', $createdDates[1]);
                        });
                });
            }
        }

        if ($request->investor && $request->investor != 'all') {
            $investorNamesArray = explode(' ', $request->investor);

            // The first part is the name
            $firstName = array_shift($investorNamesArray);

            // The remaining parts are the surname
            $surname = implode(' ', $investorNamesArray);

            $investorNamesArray = explode(' ', $request->investor);
            $investorUser = Investor::where('name', $firstName)
                ->where('surname', $surname)->first();

            if (isset($investorUser->id)) {
                $paginatedAssets->where(function ($query) use ($investorUser) {
                    $query->where('investor_id', '=', $investorUser->id);
                });
                $allAssets->where(function ($query) use ($investorUser) {
                    $query->where('investor_id', '=', $investorUser->id);
                });
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
                $paginatedAssets->where(function ($query) use ($createdDates) {
                    $query->where('created_at', '>=', $createdDates[0])
                        ->orWhereHas('paymentsHistories', function ($q) use ($createdDates) {
                            $q->where('created_at', '>=', $createdDates[0]);
                        })
                        ->orWhereHas('rentalPaymentsHistories', function ($q) use ($createdDates) {
                            $q->where('created_at', '>=', $createdDates[0]);
                        })
                        ->orWhereHas('Investments', function ($q) use ($createdDates) {
                            $q->where('created_at', '>=', $createdDates[0]);
                        });
                });
                $allAssets->where(function ($query) use ($createdDates) {
                    $query->where('created_at', '>=', $createdDates[0])
                        ->orWhereHas('paymentsHistories', function ($q) use ($createdDates) {
                            $q->where('created_at', '>=', $createdDates[0]);
                        })
                        ->orWhereHas('rentalPaymentsHistories', function ($q) use ($createdDates) {
                            $q->where('created_at', '>=', $createdDates[0]);
                        })
                        ->orWhereHas('Investments', function ($q) use ($createdDates) {
                            $q->where('created_at', '>=', $createdDates[0]);
                        });
                });
            }
            if (isset($createdDates[1])) {
                $paginatedAssets->where(function ($query) use ($createdDates) {
                    $query->where('created_at', '<=', $createdDates[1])
                        ->orWhereHas('paymentsHistories', function ($q) use ($createdDates) {
                            $q->where('created_at', '<=', $createdDates[1]);
                        })
                        ->orWhereHas('rentalPaymentsHistories', function ($q) use ($createdDates) {
                            $q->where('created_at', '<=', $createdDates[1]);
                        })
                        ->orWhereHas('Investments', function ($q) use ($createdDates) {
                            $q->where('created_at', '<=', $createdDates[1]);
                        });
                });
                $allAssets->where(function ($query) use ($createdDates) {
                    $query->where('created_at', '<=', $createdDates[1])
                        ->orWhereHas('paymentsHistories', function ($q) use ($createdDates) {
                            $q->where('created_at', '<=', $createdDates[1]);
                        })
                        ->orWhereHas('rentalPaymentsHistories', function ($q) use ($createdDates) {
                            $q->where('created_at', '<=', $createdDates[1]);
                        })
                        ->orWhereHas('Investments', function ($q) use ($createdDates) {
                            $q->where('created_at', '<=', $createdDates[1]);
                        });
                });
            }
        }

        $paginatedAssets = $paginatedAssets->paginate(25);
        $allAssets = $allAssets->get();

        $this->calculateRevenue($paginatedAssets, $allAssets);

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.index', $this->baseData);
    }

    /**
     * @param $paginatedAssets
     * @param $allAssets
     * @return void
     */
    private function calculateRevenue($paginatedAssets, $allAssets): void
    {
        // Initialize totals
        $totalRent = 0;
        $totalCapitalGain = 0;
        $totalInvestment = 0;
        $otherInvestment = 0;
        $totalPurchasePrice = 0;
        $totalCurrentValue = 0;

        // Calculate totals for all assets
        foreach ($allAssets as $asset) {
            $totalRent += $asset->rentalPaymentsHistories()->sum('amount');
            if ($asset->agreement_status === "Installments") {
                $totalInvestment += $asset->paymentsHistories()->sum('amount') + $asset->investments()->sum('amount');
            } else {
                $totalInvestment += $asset->total_price + $asset->investments()->sum('amount');
            }
            $totalCapitalGain += $asset->current_value - ($asset->total_price + $asset->investments()->sum('amount'));
            $otherInvestment += $asset->investments()->sum('amount');
            $totalPurchasePrice += $asset->total_price;
            $totalCurrentValue += $asset->current_value;
        }

        foreach ($paginatedAssets as $asset) {
            $asset->rent = $asset->rentalPaymentsHistories()->sum('amount');
            if ($asset->agreement_status === "Installments") {
                $asset->total_investment = $asset->paymentsHistories()->sum('amount') + $asset->investments()->sum('amount');
            } else {
                $asset->total_investment = $asset->total_price + $asset->investments()->sum('amount');
            }
            $asset->capital_gain = $asset->current_value - ($asset->total_price + $asset->investments()->sum('amount'));
            $asset->other_investment = $asset->investments()->sum('amount');
        }

        $this->baseData['allData'] = $paginatedAssets;
        $this->baseData['totals'] = [
            'total_rent' => $totalRent,
            'total_capital_gain' => $totalCapitalGain,
            'total_investment' => $totalInvestment,
            'other_investment' => $otherInvestment,
            'total_current_value' => $totalCurrentValue,
            'total_purchase_price' => $totalPurchasePrice,
        ];
    }

    /**
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function export(Request $request)
    {
        $filters = $request->only(['agreement_date']);
        return Excel::download(new RevenueExport($filters), 'revenues.xlsx');
    }

    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function view($id = '')
    {
        try {
            $this->baseData['routes']['create_form_data'] = route('asset.revenue_create_form_data');

            $this->baseData['id'] = $id;
            $asset = Asset::where('id', $id)->first();
            $this->baseData['name'] = $asset->project_name ?? '';

        } catch (\Exception $ex) {
            return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.view', ServiceResponse::error($ex->getMessage()));
        }

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.view', ServiceResponse::success($this->baseData));
    }

    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function investorView($id = '')
    {
        try {
            $this->baseData['routes']['create_form_data'] = route('asset.revenue_create_form_data');

            $this->baseData['id'] = $id;
            $this->baseData['name'] = Asset::where('id', $id)->first()->toArray()['project_name'];
        } catch (\Exception $ex) {
            return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.investor_view', ServiceResponse::error($ex->getMessage()));
        }

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.investor_view', ServiceResponse::success($this->baseData));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function createData(Request $request)
    {
        try {
            $this->baseData['routes'] = [
                'create_form_data' => route('asset.revenue_create_form_data'),
            ];

            if ($request->get('id')) {
                $asset = Asset::findOrFail($request->get('id'));

                $tenantData = [];

                if ($asset->tenant) {
                    $tenants = Tenant::where('asset_id', $asset->id)->orderByDesc('id')->get();
                    foreach ($tenants as $tenant) {
                        $tenantRentalPayments = RentalPaymentsHistory::where('tenant_id', $tenant->id)->orderByDesc('id');
                        $tenant = $tenant->toArray();
                        $tenant['rental_payments'] = $tenantRentalPayments->get();
                        $tenant['rental_payments_amount_sum'] = $tenantRentalPayments->sum('amount');
                        $tenantData[] = $tenant;
                    }
                }
                $investments = [];
                if ($asset->investments) {
                    $investments = $asset->investments()->orderByDesc('id')->get();
                }

                $this->baseData['tenants'] = $tenantData;
                $this->baseData['investments'] = $investments;
            }

        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return ServiceResponse::jsonNotification('', 200, $this->baseData);
    }

    public function filterOptions()
    {
        $this->baseData['investors'] = Investor::orderByDesc('id')->get();

        return ServiceResponse::jsonNotification(__('Filter role successfully'), 200, $this->baseData);
    }
}
