@extends('admin::layouts.admin')

@php
// Helper function to preserve existing query parameters when sorting
function getUrlWithSortParams($sortBy, $currentSortBy, $currentSortOrder) {
    $params = request()->except(['sort_by', 'sort_order']);
    $params['sort_by'] = $sortBy;
    $params['sort_order'] = ($currentSortBy == $sortBy && $currentSortOrder == 'asc') ? 'desc' : 'asc';
    return request()->url() . '?' . http_build_query($params);
}
@endphp

@section('main')
    <!-- Page content -->
    <div id="page-content">
        <!-- Statistics Widgets Header -->
        @include('admin::includes.header-section', ['name'   => 'Assets' ])
        <!-- END Statistics Widgets Header -->

        <!-- Responsive Full Block -->

        <div class="block">

            <div class="row">
                @can(getPermissionKey($moduleKey, 'create', true))
                    <div class="col-md-6">
                        <a href="{{ route($moduleKey . '.create') }}" class="btn btn-primary"><i
                                class="el-icon-plus"></i>
                            Create Asset</a>
                    </div>
                @endcan

            </div>
            <div class="row">
                <asset-export-component>
                </asset-export-component>
            </div>
            @if(\Auth::guard('admin')->check())

                <div class="row">
                    @if(auth()->user()->getRolesNameAttribute() == 'administrator')
                        <asset-filter-component
                            is-admin="{{true}}">
                        </asset-filter-component>
                    @else
                        <asset-filter-component>
                        </asset-filter-component>
                    @endif
                </div>
            @elseif(\Auth::guard('investor')->check())
                <investor-asset-filter-component>
                </investor-asset-filter-component>
            @else
                <div class="row">
                    <developer-asset-filter-component>
                    </developer-asset-filter-component>
                </div>
            @endif

            @include('admin::includes.success')
            @include('admin::includes.error')

            <!-- Responsive Full Content -->
            @if(count($allData) == 0)
                <br><h3 class="text-center">@lang('Asset Not Found')</h3><br>
            @else
                @php
                    // Helper function to check if a column should be hidden (all rows empty)
                    function hasEmptyColumn($collection, $checkFunction) {
                        return $collection->every(function($item) use ($checkFunction) {
                            return empty($checkFunction($item));
                        });
                    }
                    
                    // Get the current sort field from the view data if available
                    $currentSortField = $sortField ?? request()->sort_by ?? 'id';

                    // Check if there are any rented assets
                    $showNextRent = $allData
                        ->filter(function($item) {
                            return $item->asset_status === 'Rented';
                        })
                        ->isNotEmpty();

                    // Check if we should hide the investor column
                    $hideInvestorColumn = false;
                    
                    // Scenario 1: Logged-in investor is the only investor for all assets
                    if (\Auth::guard('investor')->check()) {
                        $investorId = \Auth::guard('investor')->user()->id;
                        $hideInvestorColumn = $allData->every(function($item) use ($investorId) {
                            // Check if the asset has exactly one investor and it's the logged-in user
                            return $item->investors->count() === 1 && $item->investors->first()->id === $investorId;
                        });
                    }
                    
                    // Scenario 2: All assets have no investors or all investor entries would be empty after filtering
                    if (!$hideInvestorColumn && \Auth::guard('investor')->check()) {
                        $investorId = \Auth::guard('investor')->user()->id;
                        $hideInvestorColumn = hasEmptyColumn($allData, function($item) use ($investorId) {
                            return $item->investors->filter(function($investor) use ($investorId) {
                                return $investor->id !== $investorId;
                            })->count();
                        });
                    } else if (!$hideInvestorColumn) {
                        $hideInvestorColumn = hasEmptyColumn($allData, function($item) {
                            return $item->investors->count();
                        });
                    }
                    
                    // Check if we should hide other columns
                    $hideTypeColumn = hasEmptyColumn($allData, function($item) {
                        return $item->type;
                    }) && hasEmptyColumn($allData, function($item) {
                        return $item->flat_number;
                    }) && hasEmptyColumn($allData, function($item) {
                        return $item->area;
                    });
                    // Never hide the column if it's being sorted
                    if ($currentSortField === 'type') {
                        $hideTypeColumn = false;
                    }
                    
                    $hideAgreementStatusColumn = hasEmptyColumn($allData, function($item) {
                        return $item->agreement_status;
                    });
                    // Never hide the column if it's being sorted
                    if ($currentSortField === 'agreement_status') {
                        $hideAgreementStatusColumn = false;
                    }
                    
                    $hideNextInstallmentColumn = hasEmptyColumn($allData, function($item) {
                        return $item->agreement_status == 'Installments' && count($item->payments) > 0;
                    });
                    // Never hide the column if it's being sorted
                    if ($currentSortField === 'next_installment') {
                        $hideNextInstallmentColumn = false;
                    }
                    
                    $hideNextRenovationColumn = hasEmptyColumn($allData, function($item) {
                        return $item->renovation_status == 'In Progress' && count($item->renovationPayments) > 0;
                    });
                    // Never hide the column if it's being sorted
                    if ($currentSortField === 'next_renovation') {
                        $hideNextRenovationColumn = false;
                    }
                    
                    // Always show next rent column if it's being sorted
                    if ($currentSortField === 'next_rent') {
                        $showNextRent = true;
                    }
                @endphp
                <div class="table-responsive">
                    <table class="table table-vcenter table-striped">
                        <thead>
                        <tr>
                            <th> Name</th>
                            <th> Photo</th>
                            <th> City</th>
                            @if(!$hideInvestorColumn)
                            <th> Investor</th>
                            @endif
                            @if(!$hideTypeColumn)
                            <th>
                                <a href="{{ getUrlWithSortParams('type', request()->sort_by, request()->sort_order) }}" class="text-dark">
                                    Asset Type / Size
                                    @if(request()->sort_by == 'type')
                                        <i class="fa fa-sort-{{ request()->sort_order == 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fa fa-sort"></i>
                                    @endif
                                </a>
                            </th>
                            @endif
                            @if(!$hideAgreementStatusColumn)
                            <th>
                                <a href="{{ getUrlWithSortParams('agreement_status', request()->sort_by, request()->sort_order) }}" class="text-dark">
                                    Agreement Status
                                    @if(request()->sort_by == 'agreement_status')
                                        <i class="fa fa-sort-{{ request()->sort_order == 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fa fa-sort"></i>
                                    @endif
                                </a>
                            </th>
                            @endif
                            @if(!$hideNextInstallmentColumn)
                            <th>
                                <a href="{{ getUrlWithSortParams('next_installment', request()->sort_by, request()->sort_order) }}" class="text-dark">
                                    Next Installment
                                    @if(request()->sort_by == 'next_installment')
                                        <i class="fa fa-sort-{{ request()->sort_order == 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fa fa-sort"></i>
                                    @endif
                                </a>
                            </th>
                            @endif
                            @if($showNextRent && !Auth::guard('developer')->check())
                                <th>
                                    <a href="{{ getUrlWithSortParams('next_rent', request()->sort_by, request()->sort_order) }}" class="text-dark">
                                        Next Rent
                                        @if(request()->sort_by == 'next_rent')
                                            <i class="fa fa-sort-{{ request()->sort_order == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fa fa-sort"></i>
                                        @endif
                                    </a>
                                </th>
                            @endif
                            @if(!$hideNextRenovationColumn && !Auth::guard('developer')->check())
                                <th>
                                    <a href="{{ getUrlWithSortParams('next_renovation', request()->sort_by, request()->sort_order) }}" class="text-dark">
                                        Next Renovation
                                        @if(request()->sort_by == 'next_renovation')
                                            <i class="fa fa-sort-{{ request()->sort_order == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fa fa-sort"></i>
                                        @endif
                                    </a>
                                </th>
                            @endif
                            @if(!Auth::guard('investor')->check() && !Auth::guard('developer')->check())
                                <th width="10%" class="text-center">@lang('Action')</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($allData as $item)
                            <tr
                                @if(Auth::guard('admin')->check() && !$item->rentals->where('status', 0)->count() && $item->asset_status === 'Rented')
                                    class="completed-rent"
                                title="Please complete the rent or prolong the rents schedule"
                                @endif
                            >
                                <td>
                                    @if(Auth::guard('investor')->check() || Auth::guard('developer')->check())
                                        <a href="{{route($moduleKey . '.details', [ $item->id ])}}">{!! $item->project_name !!}</a>

                                    @else
                                        <a href="{{route($moduleKey . '.view', [ $item->id ])}}">{!! $item->project_name !!}</a>
                                    @endif
                                </td>
                                <td>
                                    @if($item->icon && !is_null($item->icon) && $item->icon !== 'null')
                                        <image-modal thumbnail="{!! $item->icon !!}"
                                                     image-path="{!! $item->icon !!}"
                                                     :rounded="true"
                                                     :width="{{50}}"
                                                     :height="{{50}}"></image-modal>
                                    @endif
                                </td>
                                <td>{!! $item->city !!}</td>
                                @if(!$hideInvestorColumn)
                                <td>
                                    @php
                                    // Filter out logged-in investor from the investors array
                                    $displayInvestors = $item->investors;
                                    if (\Auth::guard('investor')->check()) {
                                        $investorId = \Auth::guard('investor')->user()->id;
                                        $displayInvestors = $displayInvestors->filter(function($investor) use ($investorId) {
                                            return $investor->id !== $investorId;
                                        });
                                    }
                                    @endphp

                                    @foreach($displayInvestors as $investor)
                                        {!! $investor->name !!} {!! $investor->surname !!}
                                        @if(!$loop->last)
                                            /<br>
                                        @endif
                                    @endforeach
                                </td>
                                @endif
                                @if(!$hideTypeColumn)
                                <td>
                                    {!! $item->type !!}
                                    @if($item->flat_number)
                                        N{!! $item->flat_number !!} -
                                    @endif
                                    @if($item->area)
                                        {!! $item->area !!} sq.m
                                    @endif
                                </td>
                                @endif
                                @if(!$hideAgreementStatusColumn)
                                <td>{!! $item->agreement_status !!}</td>
                                @endif
                                @if(!$hideNextInstallmentColumn)
                                <td>
                                    @php
                                    // Check if we're filtering by payment_date
                                    $paymentDateFilter = request()->payment_date && request()->payment_date !== 'null' ?
                                        explode(',', request()->payment_date) : null;

                                    $paymentsQuery = $item->payments->where('status', 0);

                                    // Filter payments by payment_date if filter is active
                                    if ($paymentDateFilter) {
                                        $startDate = $paymentDateFilter[0] ?? null;
                                        $endDate = $paymentDateFilter[1] ?? null;

                                        if ($startDate && $endDate) {
                                            $paymentsQuery = $paymentsQuery->filter(function($payment) use ($startDate, $endDate) {
                                                $paymentDate = strtotime($payment->payment_date);
                                                return $paymentDate >= strtotime($startDate) && $paymentDate <= strtotime($endDate);
                                            });
                                        }
                                    }
                                    @endphp

                                    {!!
                                        $item->agreement_status == 'Installments'
                                        && count($item->payments)
                                        && count($paymentsQuery)
                                        ? (
                                            strtotime($paymentsQuery->first()->payment_date) < time()
                                            ?
                                                // Overdue: display formatted overdue date & sum of overdue left_amount
                                                \Carbon\Carbon::parse($paymentsQuery->first()->payment_date)->format('Y/m/d')
                                                . ' - ' . number_format(
                                                    $paymentsQuery
                                                        ->filter(function($payment) {
                                                            return strtotime($payment->payment_date) < time();
                                                        })->sum('left_amount'), 2, ".", ",") . '$'
                                            :
                                                // Not overdue: display the first record's payment_date and left_amount
                                                $paymentsQuery->first()->payment_date
                                                . ' - ' . number_format($paymentsQuery->first()->left_amount, 2, ".", ",") . '$'
                                          )
                                        : ''
                                    !!}
                                </td>
                                @endif

                                @if($showNextRent && !Auth::guard('developer')->check())
                                    <td>
                                        @php
                                        // Check if we're filtering by payment_date
                                        $paymentDateFilter = request()->payment_date && request()->payment_date !== 'null' ?
                                            explode(',', request()->payment_date) : null;

                                        $rentalsQuery = $item->rentals->where('status', 0);

                                        // Filter rentals by payment_date if filter is active
                                        if ($paymentDateFilter) {
                                            $startDate = $paymentDateFilter[0] ?? null;
                                            $endDate = $paymentDateFilter[1] ?? null;

                                            if ($startDate && $endDate) {
                                                $rentalsQuery = $rentalsQuery->filter(function($rental) use ($startDate, $endDate) {
                                                    $paymentDate = strtotime($rental->payment_date);
                                                    return $paymentDate >= strtotime($startDate) && $paymentDate <= strtotime($endDate);
                                                });
                                            }
                                        }
                                        @endphp

                                        {!!
                                            $item->asset_status == 'Rented'
                                            && count($item->rentals)
                                            && count($rentalsQuery)
                                            ? (
                                                strtotime($rentalsQuery->first()->payment_date) < time()
                                                ?
                                                    // Overdue: display formatted overdue date & sum of overdue left_amount
                                                    \Carbon\Carbon::parse($rentalsQuery->first()->payment_date)->format('Y/m/d')
                                                    . ' - ' . number_format(
                                                        $rentalsQuery
                                                            ->filter(function($rental) {
                                                                return strtotime($rental->payment_date) < time();
                                                            })->sum('left_amount'), 2, ".", ",") . '$'
                                                :
                                                    // Not overdue: display the first record's payment_date and left_amount
                                                    $rentalsQuery->first()->payment_date
                                                    . ' - ' . number_format($rentalsQuery->first()->left_amount, 2, ".", ",") . '$'
                                              )
                                            : ''
                                        !!}
                                    </td>
                                @endif
                                @if(!$hideNextRenovationColumn && !Auth::guard('developer')->check())
                                <td>
                                    @php
                                    // Check if we're filtering by payment_date
                                    $paymentDateFilter = request()->payment_date && request()->payment_date !== 'null' ?
                                        explode(',', request()->payment_date) : null;

                                    $renovationPaymentsQuery = $item->renovationPayments->where('status', 0);

                                    // Filter renovation payments by payment_date if filter is active
                                    if ($paymentDateFilter) {
                                        $startDate = $paymentDateFilter[0] ?? null;
                                        $endDate = $paymentDateFilter[1] ?? null;

                                        if ($startDate && $endDate) {
                                            $renovationPaymentsQuery = $renovationPaymentsQuery->filter(function($renovationPayment) use ($startDate, $endDate) {
                                                $paymentDate = strtotime($renovationPayment->payment_date);
                                                return $paymentDate >= strtotime($startDate) && $paymentDate <= strtotime($endDate);
                                            });
                                        }
                                    }
                                    @endphp

                                    {!!
                                    !Auth::guard('developer')->check()
                                        && $item->renovation_status == 'In Progress'
                                        && count($item->renovationPayments)
                                        && count($renovationPaymentsQuery)
                                        ? (
                                            strtotime($renovationPaymentsQuery->first()->payment_date) < time()
                                            ?
                                                // Overdue: display formatted overdue date & sum of overdue left_amount
                                                \Carbon\Carbon::parse($renovationPaymentsQuery->first()->payment_date)->format('Y/m/d')
                                                . ' - ' . number_format(
                                                    $renovationPaymentsQuery
                                                        ->filter(function($renovationPayments) {
                                                            return strtotime($renovationPayments->payment_date) < time();
                                                        })->sum('left_amount'), 2, ".", ",") . '$'
                                            :
                                                // Not overdue: display the first record's payment_date and left_amount
                                                $renovationPaymentsQuery->first()->payment_date
                                                . ' - ' . number_format($renovationPaymentsQuery->first()->left_amount, 2, ".", ",") . '$'
                                          )
                                        : ''
                                    !!}
                                </td>
                                @endif

                                <td class="text-center">
                                    @if($item->sale_status !== 'sold')
                                        @can(getPermissionKey('payment', 'index', true))
                                            @if($item->agreement_status == 'Installments')
                                                @include('admin::includes.actions.payment',['title' => 'Payments','route' => route($moduleKey . '.payments.list', [ $item->id ]), ])
                                            @endif
                                        @endcan
                                        @can(getPermissionKey($moduleKey, 'update', true))
                                            @include('admin::includes.actions.edit',['title' => 'Update','route' => route($moduleKey . '.edit', [ $item->id ])])
                                        @endcan
                                        @can(getPermissionKey('rental', 'index', true))
                                            @if($item->asset_status == 'Rented')
                                                @include('admin::includes.actions.rental',['title' => 'Rentals', 'route' => route($moduleKey . '.rental.index', [ $item->id ])])
                                            @endif
                                        @endcan
                                        @can(getPermissionKey('renovation', 'index', true))
                                            @if($item->renovation_status == 'In Progress')
                                                @include('admin::includes.actions.renovation',['title' => 'Renovation Payments', 'route' => route($moduleKey . '.renovation.index', [ $item->id ])])
                                            @endif
                                        @endcan
                                        @can(getPermissionKey($moduleKey, 'update', true))
                                            <register-purchase-component :asset-id="{{ $item->id }}">
                                            </register-purchase-component>
                                        @endcan
                                        @can(getPermissionKey('investment', 'index', true))
                                            @include('admin::includes.actions.investment',['route' => route('asset.investment.index', [ $item->id ])])
                                        @endcan
                                        @if(\Auth::guard('admin')->check())
                                            <developer-access-component
                                                :asset-id="{{ $item->id }}"
                                                :developer-access="{{ $item->developer_access ? 'true' : 'false' }}"
                                                title="Switch Developer Access to"
                                                route="{{ route($moduleKey . '.developer_access', [ $item->id ]) }}"
                                            ></developer-access-component>
                                        @endif
                                        @can(getPermissionKey($moduleKey, 'update', true))
                                            <archive-asset-component
                                                :asset-id="{{ $item->id }}"
                                                :is-archived="{{ $item->is_archived ? 'true' : 'false' }}"
                                            ></archive-asset-component>
                                        @endcan
                                        @can(getPermissionKey($moduleKey, 'delete', true))
                                            <delete-component
                                                :url="'{{ route($moduleKey . '.delete') }}'"
                                                :id="{{ $item->id }}"
                                            ></delete-component>
                                        @endcan
                                    @else
                                        Sold
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @php
                // Make sure pagination links maintain the sort parameters
                $allData->appends([
                    'sort_by' => $currentSortField,
                    'sort_order' => request()->sort_order ?? 'desc'
                ]);
            @endphp
            @include('admin::includes.paginate', ['data' => $allData ])

            <br>
            <!-- END Responsive Full Content -->
        </div>
        <!-- END Responsive Full Block -->

    </div>
    <!-- END Page Content -->
@endsection

<style>
    th, td {
        text-align: center !important;
    }
</style>
