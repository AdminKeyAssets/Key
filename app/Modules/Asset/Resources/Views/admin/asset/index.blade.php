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

            @if(\Auth::guard('admin')->check())

                <div class="row">
                    <asset-export-component>
                    </asset-export-component>
                </div>

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
                    $showNextRent = $allData
                        ->filter(function($item) {
                            return $item->asset_status === 'Rented';
                        })
                        ->isNotEmpty();
                @endphp
                <div class="table-responsive">
                    <table class="table table-vcenter table-striped">
                        <thead>
                        <tr>
                            <th> Name</th>
                            <th> Photo</th>
                            <th> City</th>
                            {{--                            @if(Auth::guard('admin')->check())--}}
                            <th> Investor</th>
                            {{--                            @endif--}}
                            <th> Asset Type / Size</th>
                            <th> Agreement Status</th>
                            <th> Next Installment</th>
                            @if($showNextRent && !Auth::guard('developer')->check())
                                <th> Next Rent</th>
                            @endif
                            @if(!Auth::guard('developer')->check())
                                <th> Next Renovation</th>
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
                                {{--                                @if(Auth::guard('admin')->check())--}}
                                <td>
                                    @foreach($item->investors as $investor)
                                        {!! $investor->name !!} {!! $investor->surname !!}
                                        @if(!$loop->last)
                                            /<br>
                                        @endif
                                    @endforeach
                                </td>
                                {{--                                @endif--}}
                                <td>

                                    {!! $item->type !!}
                                    @if($item->flat_number)
                                        N{!! $item->flat_number !!} -
                                    @endif
                                    @if($item->area)
                                        {!! $item->area !!} sq.m
                                    @endif
                                </td>
                                <td>{!! $item->agreement_status !!}</td>

                                <td>
                                    {!!
                                        $item->agreement_status == 'Installments'
                                        && count($item->payments)
                                        && count($item->payments->where('status', 0))
                                        ? (
                                            strtotime($item->payments->where('status', 0)->first()->payment_date) < time()
                                            ?
                                                // Overdue: display formatted overdue date & sum of overdue left_amount
                                                \Carbon\Carbon::parse($item->payments->where('status', 0)->first()->payment_date)->format('Y/m/d')
                                                . ' - ' . number_format(
                                                    $item->payments->where('status', 0)
                                                        ->filter(function($payment) {
                                                            return strtotime($payment->payment_date) < time();
                                                        })->sum('left_amount'), 2, ".", ",") . '$'
                                            :
                                                // Not overdue: display the first record's payment_date and left_amount
                                                $item->payments->where('status', 0)->first()->payment_date
                                                . ' - ' . number_format($item->payments->where('status', 0)->first()->left_amount, 2, ".", ",") . '$'
                                          )
                                        : ''
                                    !!}
                                </td>

                                @if($showNextRent && !Auth::guard('developer')->check())
                                    <td>
                                        {!!
                                            $item->asset_status == 'Rented'
                                            && count($item->rentals)
                                            && count($item->rentals->where('status', 0))
                                            ? (
                                                strtotime($item->rentals->where('status', 0)->first()->payment_date) < time()
                                                ?
                                                    // Overdue: display formatted overdue date & sum of overdue left_amount
                                                    \Carbon\Carbon::parse($item->rentals->where('status', 0)->first()->payment_date)->format('Y/m/d')
                                                    . ' - ' . number_format(
                                                        $item->rentals->where('status', 0)
                                                            ->filter(function($rental) {
                                                                return strtotime($rental->payment_date) < time();
                                                            })->sum('left_amount'), 2, ".", ",") . '$'
                                                :
                                                    // Not overdue: display the first record's payment_date and left_amount
                                                    $item->rentals->where('status', 0)->first()->payment_date
                                                    . ' - ' . number_format($item->rentals->where('status', 0)->first()->left_amount, 2, ".", ",") . '$'
                                              )
                                            : ''
                                        !!}
                                    </td>
                                @endif
                                <td>
                                    {!!
                                    !Auth::guard('developer')->check()
                                        && $item->renovation_status == 'In Progress'
                                        && count($item->renovationPayments)
                                        && count($item->renovationPayments->where('status', 0))
                                        ? (
                                            strtotime($item->renovationPayments->where('status', 0)->first()->payment_date) < time()
                                            ?
                                                // Overdue: display formatted overdue date & sum of overdue left_amount
                                                \Carbon\Carbon::parse($item->renovationPayments->where('status', 0)->first()->payment_date)->format('Y/m/d')
                                                . ' - ' . number_format(
                                                    $item->renovationPayments->where('status', 0)
                                                        ->filter(function($renovationPayments) {
                                                            return strtotime($renovationPayments->payment_date) < time();
                                                        })->sum('left_amount'), 2, ".", ",") . '$'
                                            :
                                                // Not overdue: display the first record's payment_date and left_amount
                                                $item->renovationPayments->where('status', 0)->first()->payment_date
                                                . ' - ' . number_format($item->renovationPayments->where('status', 0)->first()->left_amount, 2, ".", ",") . '$'
                                          )
                                        : ''
                                    !!}
                                </td>


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
