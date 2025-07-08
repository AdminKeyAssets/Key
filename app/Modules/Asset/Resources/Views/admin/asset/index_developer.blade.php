@extends('admin::layouts.admin')

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
                    
                    // Check if the investor column should be hidden (no investors for any assets)
                    $hideInvestorColumn = hasEmptyColumn($allData, function($item) {
                        return $item->investors->count();
                    });
                    
                    // Check if other columns should be hidden
                    $hideTypeColumn = hasEmptyColumn($allData, function($item) {
                        return $item->type;
                    }) && hasEmptyColumn($allData, function($item) {
                        return $item->flat_number;
                    }) && hasEmptyColumn($allData, function($item) {
                        return $item->area;
                    });
                    
                    $hidePurchasePriceColumn = hasEmptyColumn($allData, function($item) {
                        return $item->total_price;
                    });
                    
                    $hidePaidColumn = hasEmptyColumn($allData, function($item) {
                        return $item->paid_formatted;
                    });
                    
                    $hideAgreementStatusColumn = hasEmptyColumn($allData, function($item) {
                        return $item->agreement_status;
                    });
                    
                    $hideNextInstallmentColumn = hasEmptyColumn($allData, function($item) {
                        return $item->agreement_status == 'Installments' && count($item->payments) > 0;
                    });
                    
                    $hideCurrentValueColumn = hasEmptyColumn($allData, function($item) {
                        return $item->current_value;
                    });
                    
                    $hideCapitalGainColumn = hasEmptyColumn($allData, function($item) {
                        return $item->current_value - $item->total_price;
                    });
                    
                    $hideManagerColumn = hasEmptyColumn($allData, function($item) {
                        return isset($item->investors->first()->admin);
                    });
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
                            <th> Asset Type / Size</th>
                            @endif
                            @if(!$hidePurchasePriceColumn)
                            <th> Purchase Price</th>
                            @endif
                            @if(!$hidePaidColumn)
                            <th> Paid</th>
                            @endif
                            @if(!$hideAgreementStatusColumn)
                            <th> Agreement Status</th>
                            @endif
                            @if(!$hideNextInstallmentColumn)
                            <th> Next Installment</th>
                            @endif
                            @if(!$hideCurrentValueColumn)
                            <th> Current Value</th>
                            @endif
                            @if(!$hideCapitalGainColumn)
                            <th> Capital Gain</th>
                            @endif
                            @if(!$hideManagerColumn)
                            <th> Manager</th>
                            @endif
                            <th width="10%" class="text-center">@lang('Action')</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($allData as $item)
                            <tr>
                                <td>
                                    <a href="{{route($moduleKey . '.details', [ $item->id ])}}">{!! $item->project_name !!}</a>
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
                                    @foreach($item->investors as $investor)
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
                                @if(!$hidePurchasePriceColumn)
                                <td>{!! number_format($item->total_price) !!}$</td>
                                @endif
                                @if(!$hidePaidColumn)
                                <td>
                                    {!! $item->paid_formatted !!}
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
                                @if(!$hideCurrentValueColumn)
                                <td>{!! number_format($item->current_value) !!}$</td>
                                @endif
                                @if(!$hideCapitalGainColumn)
                                <td>{!! number_format($item->current_value - $item->total_price) !!}$</td>
                                @endif
                                @if(!$hideManagerColumn)
                                <td>{!! $item->investors->first()->admin->name ?? '' !!} {!! $item->investors->first()->admin->surname ?? '' !!}</td>
                                @endif
                                <td class="text-center">
                                    @include('admin::includes.actions.developer-access',
                                    [
                                        'title' => 'Switch Developer Access to ',
                                        'route' => route($moduleKey . '.developer_access', [ $item->id ]),
                                        'developerAccess' => $item->developer_access
                                    ])
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

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
