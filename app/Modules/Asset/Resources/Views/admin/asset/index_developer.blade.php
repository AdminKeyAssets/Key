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

                <div class="table-responsive">
                    <table class="table table-vcenter table-striped">
                        <thead>
                        <tr>
                            <th> Name</th>
                            <th> Photo</th>
                            <th> City</th>
                            <th> Investor</th>
                            <th> Asset Type / Size</th>
                            <th> Purchase Price</th>
                            {{--                            <th> Paid</th>--}}
                            <th> Agreement Status</th>
                            <th> Next Installment</th>
                            <th> Current Value</th>
                            <th> Capital Gain</th>
                            <th> Manager</th>

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
                                <td>{!! number_format($item->total_price) !!}$</td>
                                {{--                                <td>{!! number_format(0) !!}$</td>--}}
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
                                <td>{!! number_format($item->current_value) !!}$</td>
                                <td>{!! number_format($item->current_value - $item->total_price) !!}$</td>
                                <td>{!! $item->investors->first()->admin->name !!} {!! $item->investors->first()->admin->surname !!}</td>
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
