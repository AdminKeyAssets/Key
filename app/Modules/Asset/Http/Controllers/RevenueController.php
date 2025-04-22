<?php

namespace App\Modules\Asset\Http\Controllers;

use App\Modules\Admin\Exports\RevenueAssetValueExport;
use App\Modules\Admin\Exports\RevenueExport;
use App\Modules\Admin\Exports\RevenueInvestmentExport;
use App\Modules\Admin\Exports\RevenueRentalExport;
use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Admin\Models\Country;
use App\Modules\Admin\Models\User\Admin;
use App\Modules\Admin\Models\User\Investor;
use App\Modules\Asset\Models\Asset;
use App\Modules\Asset\Models\AssetAgreement;
use App\Modules\Asset\Models\CurrentValue;
use App\Modules\Asset\Models\Payment;
use App\Modules\Asset\Models\PaymentsHistory;
use App\Modules\Asset\Models\Rental;
use App\Modules\Asset\Models\RentalPaymentsHistory;
use App\Modules\Asset\Models\Tenant;
use App\Utilities\ServiceResponse;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $statusFilter = $request->status ?? 'active';

        $userId = $user->getAuthIdentifier();

        $managers = ['Asset Manager', 'AssetManager', 'Sales Manager', 'Sales manager', 'SalesManager'];

        if (in_array($user->getRolesNameAttribute(), $managers)) {
            $investors = Investor::where('admin_id', $userId)->pluck('id')->toArray();

            $paginatedAssets = Asset::where('admin_id', auth()->user()->getAuthIdentifier())->whereHas('investors', function ($query) use ($investors) {
                $query->whereIn('id', $investors);
            })->orderByDesc('id');

            $allAssets = Asset::where('admin_id', auth()->user()->getAuthIdentifier())->whereHas('investors', function ($query) use ($investors) {
                $query->whereIn('id', $investors);
            });
        } else {
            $paginatedAssets = Asset::where('status', 'completed')->orderByDesc('id');
            $allAssets = Asset::where('status', 'completed')->orderByDesc('id');
        }

        if ($request->asset && $request->asset != 'all') {
            $paginatedAssets->where('project_name', 'like', '%' . $request->asset . '%');
            $allAssets->where('project_name', 'like', '%' . $request->asset . '%');
        }


        if ($statusFilter !== 'all') {
            $paginatedAssets->where('sale_status', $statusFilter);
            $allAssets->where('sale_status', $statusFilter);
        }


        if ($request->manager && $request->manager != 'all') {
            $managerNamesArray = explode(' ', $request->manager);

            $managerFirstName = array_shift($managerNamesArray);

            $managerSurname = implode(' ', $managerNamesArray);

            $managerUser = Admin::where('name', $managerFirstName)
                ->where('surname', $managerSurname)->
                first();
            if (isset($managerUser->id)) {
                $paginatedAssets->where('admin_id', $managerUser->id);
                $allAssets->where('admin_id', $managerUser->id);
            }
        }


        if ($request->asset_type && $request->asset_type != 'all') {
            $paginatedAssets->where('type', $request->asset_type);
            $allAssets->where('type', $request->asset_type);
        }

        if ($request->asset_status && $request->asset_status != 'all') {
            $paginatedAssets->where('asset_status', $request->asset_status);
            $allAssets->where('asset_status', $request->asset_status);
        }

        if ($request->agreement_status && $request->agreement_status != 'all') {
            $paginatedAssets->where('agreement_status', $request->agreement_status);
            $allAssets->where('agreement_status', $request->agreement_status);
        }


        // Apply filters based on the related entities

        if ($request->agreement_date && !is_null($request->agreement_date) && $request->agreement_date !== 'null') {
            $createdDates = explode(',', $request->agreement_date);
            if (isset($createdDates[0])) {
                $paginatedAssets->where(function ($query) use ($createdDates) {
                    $query
                        ->orWhereHas('paymentsHistories', function ($q) use ($createdDates) {
                            $q->where('date', '>=', $createdDates[0]);
                        })
                        ->orWhereHas('rentalPaymentsHistories', function ($q) use ($createdDates) {
                            $q->where('date', '>=', $createdDates[0]);
                        })
                        ->orWhereHas('Investments', function ($q) use ($createdDates) {
                            $q->where('date', '>=', $createdDates[0]);
                        });
                });
                $allAssets->where(function ($query) use ($createdDates) {
                    $query->where('created_at', '>=', $createdDates[0])
                        ->orWhereHas('paymentsHistories', function ($q) use ($createdDates) {
                            $q->where('date', '>=', $createdDates[0]);
                        })
                        ->orWhereHas('rentalPaymentsHistories', function ($q) use ($createdDates) {
                            $q->where('date', '>=', $createdDates[0]);
                        })
                        ->orWhereHas('Investments', function ($q) use ($createdDates) {
                            $q->where('date', '>=', $createdDates[0]);
                        });
                });
            }
            if (isset($createdDates[1])) {
                $paginatedAssets->where(function ($query) use ($createdDates) {
                    $query->where('created_at', '<=', $createdDates[1])
                        ->orWhereHas('paymentsHistories', function ($q) use ($createdDates) {
                            $q->where('date', '<=', $createdDates[1]);
                        })
                        ->orWhereHas('rentalPaymentsHistories', function ($q) use ($createdDates) {
                            $q->where('date', '<=', $createdDates[1]);
                        })
                        ->orWhereHas('Investments', function ($q) use ($createdDates) {
                            $q->where('date', '<=', $createdDates[1]);
                        });
                });
                $allAssets->where(function ($query) use ($createdDates) {
                    $query->where('created_at', '<=', $createdDates[1])
                        ->orWhereHas('paymentsHistories', function ($q) use ($createdDates) {
                            $q->where('date', '<=', $createdDates[1]);
                        })
                        ->orWhereHas('rentalPaymentsHistories', function ($q) use ($createdDates) {
                            $q->where('date', '<=', $createdDates[1]);
                        })
                        ->orWhereHas('Investments', function ($q) use ($createdDates) {
                            $q->where('date', '<=', $createdDates[1]);
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
                $paginatedAssets->whereHas('investors', function ($query) use ($investorUser) {
                    $query->where('id', $investorUser->id);
                });
                $allAssets->whereHas('investors', function ($query) use ($investorUser) {
                    $query->where('id', $investorUser->id);
                });
            }
        }

        $paginatedAssets = $paginatedAssets->paginate(25);
        $allAssets = $allAssets->get();

        $this->calculateRevenue($paginatedAssets, $allAssets, isset($createdDates[0]) ? date('Y/m/d', strtotime($createdDates[0])) : null, isset($createdDates[1]) ? date('Y/m/d', strtotime($createdDates[1])) : null);

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

        $statusFilter = $request->status ?? 'active';

        // Fetch paginated assets
        $paginatedAssets = $user->assets()->where('status', 'completed')->orderByDesc('id');

        // Fetch all assets for totals calculation
        $allAssets = $user->assets()->orderByDesc('id');

        $activeQuery = $user->assets()->orderByDesc('id')->where('sale_status', 'active');

        if ($activeQuery->count() > 0) {
            $paginatedAssets->where('sale_status', $statusFilter);
            $allAssets->where('sale_status', $statusFilter);
        } else {
            $paginatedAssets->where('sale_status', 'sold');
            $allAssets->where('sale_status', 'sold');
        }

        if ($request->agreement_date && !is_null($request->agreement_date) && $request->agreement_date !== 'null') {
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

        $this->calculateRevenue($paginatedAssets, $allAssets, isset($createdDates[0]) ? date('Y/m/d', strtotime($createdDates[0])) : null, isset($createdDates[1]) ? date('Y/m/d', strtotime($createdDates[1])) : null);

        return view($this->baseModuleName . $this->baseAdminViewName . $this->viewFolderName . '.index', $this->baseData);
    }

    /**
     * @param $paginatedAssets
     * @param $allAssets
     * @return void
     */
    private function calculateRevenue($paginatedAssets, $allAssets, $startDate = null, $endDate = null): void
    {
        // 1. Eager load the relationships to avoid repeated queries
        $allAssets->load(['rentalPaymentsHistories', 'paymentsHistories', 'investments']);
        $paginatedAssets->load(['rentalPaymentsHistories', 'paymentsHistories', 'investments']);

        // 2. Initialize totals
        $totalRent = 0;
        $totalCapitalGain = 0;
        $totalInvestment = 0;
        $otherInvestment = 0;
        $totalPurchasePrice = 0;
        $totalCurrentValue = 0;
        $totalRenovationPrice = 0;
        $totalNetCashBalance = 0;
        $totalPaid = 0;

        // 3. Calculate totals for all assets
        foreach ($allAssets as $asset) {
            // Calculate sums once per asset
            $rent = $asset->rentalPaymentsHistories;
            $paid = $asset->paymentsHistories;
            $allInvestments = $asset->investments;
            $renovationInvestment = $asset->investments->where('status', 'Renovation');
            $renovationPayments = $asset->renovationPaymentsHistories();
            if ($startDate) {
                $rent = $rent->where('date', '>=', $startDate);
                $paid = $paid->where('date', '>=', $startDate);
                $allInvestments = $allInvestments->where('date', '>=', $startDate);
                $renovationInvestment = $renovationInvestment->where('date', '>=', $startDate);
                $renovationPayments = $renovationPayments->where('date', '>=', $startDate);
            }

            if ($endDate) {
                $rent = $rent->where('date', '<=', $endDate);
                $paid = $paid->where('date', '<=', $endDate);
                $allInvestments = $allInvestments->where('date', '<=', $endDate);
                $renovationInvestment = $renovationInvestment->where('date', '<=', $endDate);
                $renovationPayments = $renovationPayments->where('date', '<=', $startDate);
            }

            if ($asset->sale_status === 'sold') {
                $rent = $rent->where('date', '<=', $asset->sale_date);
            }

            $rent = $rent->sum('amount');
            $paid = $paid->sum('amount');
            $allInvestments = $allInvestments->sum('amount');
            $renovationInvestment = $renovationInvestment->sum('amount');
            $renovationPayments = $renovationPayments->sum('amount');

            $otherInvestments = $allInvestments - $renovationInvestment;  // everything that's not Renovation

            $renovationInvestment = $renovationInvestment + $renovationPayments;

            $totalRent += $rent;

            // Determine total investment based on agreement_status
            if ($asset->agreement_status === 'Installments') {
                $investmentAmount = $paid + $allInvestments;
            } else {
//                $paid = $asset->total_price;
                $investmentAmount = $asset->total_price + $allInvestments;
            }
            $totalInvestment += $investmentAmount + $renovationPayments;

            // Calculate capital gain
            if ($asset->sale_status !== 'sold') {
                $capitalGain = $asset->current_value - ($asset->total_price + $renovationInvestment);
            } else {
                $capitalGain = $asset->sale_price - ($asset->total_price + $renovationInvestment);
            }

            $totalCapitalGain += $capitalGain;

            // Other totals
            $otherInvestment += $otherInvestments;
            $totalRenovationPrice += $renovationInvestment;
            $totalPurchasePrice += $asset->total_price;
            $totalCurrentValue += $asset->current_value;
            $totalPaid += $paid;

            // Net cash balance
            if ($asset->sale_status !== 'sold') {
                $netCashBalance = $rent - $otherInvestments;
            } else {
                $netCashBalance = $asset->sale_price + $rent - $allInvestments;
            }
            $totalNetCashBalance += $netCashBalance;
        }

        // 4. Populate detailed fields on each paginated asset
        foreach ($paginatedAssets as $asset) {
            // Again, compute these sums once
            $rent = $asset->rentalPaymentsHistories;
            $paid = $asset->paymentsHistories;
            $allInvestments = $asset->investments;
            $renovationInvestment = $asset->investments->where('status', 'Renovation');
            $renovationPayments = $asset->renovationPaymentsHistories();
            if ($startDate) {
                $rent = $rent->where('date', '>=', $startDate);
                $paid = $paid->where('date', '>=', $startDate);
                $allInvestments = $allInvestments->where('date', '>=', $startDate);
                $renovationInvestment = $renovationInvestment->where('date', '>=', $startDate);
                $renovationPayments = $renovationPayments->where('date', '>=', $startDate);
            }
            if ($endDate) {
                $rent = $rent->where('date', '<=', $endDate);
                $paid = $paid->where('date', '<=', $endDate);
                $allInvestments = $allInvestments->where('date', '<=', $endDate);
                $renovationInvestment = $renovationInvestment->where('date', '<=', $endDate);
                $renovationPayments = $renovationPayments->where('date', '<=', $startDate);
            }

            if ($asset->sale_status === 'sold') {
                $rent = $rent->where('date', '<=', $asset->sale_date);
            }

            $rent = $rent->sum('amount');
            $paid = $paid->sum('amount');
            $allInvestments = $allInvestments->sum('amount');

            $renovationInvestment = $renovationInvestment->sum('amount');
            $renovationPayments = $renovationPayments->sum('amount');

            $otherInvestments = $allInvestments - $renovationInvestment;  // everything that's not Renovation
            $renovationInvestment = $renovationInvestment + $renovationPayments;

            // Set per-asset fields
            $asset->rent = $rent;


            if ($asset->agreement_status === 'Installments') {
                $asset->total_investment = $paid + $allInvestments + $renovationPayments;
                $asset->paid = $paid;
            } else {
                $asset->total_investment = $asset->total_price + $allInvestments + $renovationPayments;
                $asset->paid = $asset->total_price;
            }

            if ($asset->sale_status !== 'sold') {
                $asset->net_cache_balance = $asset->rent - $otherInvestments;
                $asset->capital_gain = $asset->current_value - ($asset->total_price + $renovationInvestment);
            } else {
                $asset->net_cache_balance = $asset->sale_price + $asset->rent - $asset->total_investment;
                $asset->capital_gain = $asset->sale_price - $asset->total_investment;
            }
            $asset->other_investment = $otherInvestments;
            $asset->renovation = $renovationInvestment;
        }

        // 5. Provide the results
        $this->baseData['allData'] = $paginatedAssets;
        $this->baseData['totals'] = [
            'total_rent' => $totalRent,
            'total_capital_gain' => $totalCapitalGain,
            'total_investment' => $totalInvestment,
            'other_investment' => $otherInvestment,
            'total_current_value' => $totalCurrentValue,
            'total_purchase_price' => $totalPurchasePrice,
            'total_renovation_price' => $totalRenovationPrice,
            'total_paid' => $totalPaid,
            'total_net_cash_balance' => $totalNetCashBalance,
        ];
    }

    /**
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function export(Request $request)
    {
        $filters = $request->only([
            'agreement_date',
            'investor',
            'status',
            'asset',
            'manager',
            'asset_status',
            'asset_type',
            'agreement_status',
        ]);
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

            $asset = Asset::where('id', $asset->id)->first();
            $investors = $asset->investors;
            $investorNames = [];
            foreach ($investors as $investor) {
                $investorNames[] = $investor->name . ' ' . $investor->surname;
            }
            $investorNames = implode(' / ', $investorNames);

            $this->baseData['extra'] = [
                'asset_name' => $asset->project_name,
                'asset_route' => route('asset.view', [$asset->id]),
                'investor_name' => $investorNames,
            ];

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
                        $tenantRentals = Rental::where('asset_id', $asset->id)->get();
                        $tenant = $tenant->toArray();
                        $tenant['rental_payments'] = $tenantRentalPayments->get();
                        $tenant['rentals'] = $tenantRentals;
                        $tenant['rental_payments_amount_sum'] = $tenantRentalPayments->sum('amount');
                        $tenantData[] = $tenant;
                    }
                }
                $investments = [];
                if ($asset->investments) {
                    $investments = $asset->investments()->orderByDesc('id')->get();
                }

                $currentValues = [];
                if ($asset->currentValues) {
                    $currentValues = CurrentValue::where('asset_id', $asset->id)->orderByDesc('id')->get();
                }
                $this->baseData['item'] = [];
                $this->baseData['item']['agreements'] = $this->baseData['item']['payments'] = $this->baseData['item']['payments_histories'] = $this->baseData['item']['files'] = [];

                if ($asset->sale_status === 'sold') {
                    $this->baseData['item'] = $asset;

                    $paid = $asset->paymentsHistories;
                    $rent = $asset->rentalPaymentsHistories;
                    $allInvestments = $asset->investments;
                    $renovationInvestment = $asset->investments->where('status', 'Renovation');
                    $renovationPayments = $asset->renovationPaymentsHistories();


                    $rent = $rent->sum('amount');
                    $allInvestments = $allInvestments->sum('amount');

                    $renovationInvestment = $renovationInvestment->sum('amount');
                    $renovationPayments = $renovationPayments->sum('amount');

                    $otherInvestments = $allInvestments - $renovationInvestment;  // everything that's not Renovation
                    $renovationInvestment = $renovationInvestment + $renovationPayments;

                    // Set per-asset fields
                    $this->baseData['item']['rent'] = $rent;

                    if ($asset->agreement_status === 'Installments') {
                        $paid = $paid->sum('amount');
                        $totalInvestments = $paid + $allInvestments + $renovationPayments;
                        $this->baseData['item']['paid'] = $paid;
                    } else {
                        $paid = $asset->total_price;
                        $totalInvestments = $asset->total_price + $allInvestments + $renovationPayments;
                        $this->baseData['item']['paid'] = $paid;
                    }
                    //                    dd([$asset->sale_price,$rent,$allInvestments]);

                    $this->baseData['item']['total_investment'] = $totalInvestments;
                    $this->baseData['item']['net_cash_balance'] = $asset->sale_price + $rent - $totalInvestments;

                    $this->baseData['item']['capital_gain'] = $asset->sale_price - $totalInvestments;

                    $this->baseData['item']['other_investment'] = $otherInvestments;
                    $this->baseData['item']['renovation'] = $renovationInvestment;

                    $paidPercent = fmod(($paid / $asset->total_price) * 100, 1) == 0 ? number_format(($paid / $asset->total_price) * 100, 0) : number_format(($paid / $asset->total_price) * 100, 2);
                    $investors = $asset->investors;
                    $investorNames = [];
                    foreach ($investors as $investor) {
                        $investorNames[] = $investor->name . ' ' . $investor->surname;
                    }
                    $investorNames = implode(' / ', $investorNames);

                    $this->baseData['item']['investorNames'] = $investorNames;

                    $this->baseData['item']['paid'] = number_format($paid, 0, ".", ",") . '$ - ' . $paidPercent . '%';

                    $this->baseData['item']['agreements'] = AssetAgreement::where('asset_id', $asset->id)->get();

                    $files = [];
                    if ($asset->attachments) {
                        foreach ($asset->attachments as $item) {
                            $files[] = [
                                'name' => $item->name,
                                'image' => $item->image,
                                'type' => substr($item->type, 0, 5) == 'image' ? 'image' : null
                            ];
                        }
                    }
                    $this->baseData['item']['files'] = $files;
                }

                $this->baseData['tenants'] = $tenantData;
                $this->baseData['investments'] = $investments;
                $this->baseData['current_values'] = $currentValues;
            }

        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        return ServiceResponse::jsonNotification('', 200, $this->baseData);
    }

    public function filterOptions()
    {
        $this->baseData['investors'] = [];
        if (\Auth::guard('admin')->check()) {
            if (auth()->user()->getRolesNameAttribute() == 'administrator') {
                $this->baseData['investors'] = Investor::orderBy('name')
                    ->orderBy('surname')
                    ->get();
                $this->baseData['managers'] = Admin::whereHas('roles', function ($query) {
                    $query->where('name', 'like', '%asset%manager%');
                })
                    ->orderBy('name')
                    ->orderBy('surname')
                    ->get();

                $this->baseData['types'] = Asset::select('type', DB::raw('MAX(id) as max_id'))
                    ->groupBy('type')
                    ->orderBy('type')
                    ->get();

                $this->baseData['statuses'] = Asset::select('asset_status', DB::raw('MAX(id) as max_id'))
                    ->groupBy('asset_status')
                    ->orderBy('asset_status')
                    ->get();

                $this->baseData['assets'] = Asset::select('project_name', DB::raw('MAX(id) as max_id'))
                    ->groupBy('project_name')
                    ->orderBy('project_name')
                    ->get();
            } else {
                $this->baseData['investors'] = Investor::where('admin_id', auth()->user()->getAuthIdentifier())
                    ->orderBy('name')
                    ->orderBy('surname')
                    ->get();
                $this->baseData['managers'] = [];

                $this->baseData['types'] = Asset::where('admin_id', auth()->user()->getAuthIdentifier())
                    ->select('type', DB::raw('MAX(id) as max_id'))
                    ->groupBy('type')
                    ->orderBy('type')
                    ->get();

                $this->baseData['statuses'] = Asset::where('admin_id', auth()->user()->getAuthIdentifier())
                    ->select('asset_status', DB::raw('MAX(id) as max_id'))
                    ->groupBy('asset_status')
                    ->orderBy('asset_status')
                    ->get();

                $this->baseData['assets'] = Asset::where('admin_id', auth()->user()->getAuthIdentifier())
                    ->select('project_name', DB::raw('MAX(id) as max_id'))
                    ->groupBy('project_name')
                    ->orderBy('project_name')
                    ->get();
            }
        }


        return ServiceResponse::jsonNotification(__('Filter role successfully'), 200, $this->baseData);
    }

    public function deleteRental(Request $request, $tenantId)
    {
        Tenant::where('id', $tenantId)->delete();
        RentalPaymentsHistory::where('tenant_id', $tenantId)->delete();

        return ServiceResponse::jsonNotification('Deleted successfully', 200, $this->baseData);
    }

    public function exportRentals(Request $request, $assetId)
    {
        return Excel::download(new RevenueRentalExport(['asset_id' => $assetId]), 'revenue_rentals.xlsx');
    }

    public function exportInvestments(Request $request, $assetId)
    {
        return Excel::download(new RevenueInvestmentExport(['asset_id' => $assetId]), 'revenue_investments.xlsx');

    }

    public function exportAssetValueHistory(Request $request, $assetId)
    {
        return Excel::download(new RevenueAssetValueExport(['asset_id' => $assetId]), 'asset_values.xlsx');

    }
}
