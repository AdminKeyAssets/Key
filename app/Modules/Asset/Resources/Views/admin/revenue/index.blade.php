@extends('admin::layouts.admin')

@section('main')
    <!-- Page content -->
    <div id="page-content">
        <!-- Statistics Widgets Header -->
        @include('admin::includes.header-section', ['name'   => 'Revenue' ])
        <!-- END Statistics Widgets Header -->

        <!-- Responsive Full Block -->
        <div class="row">

        </div>

        <div class="block">

            @include('admin::includes.success')

            @if(\Auth::guard('admin')->check())
                <div class="row">
                    <revenue-export-component>
                    </revenue-export-component>
                </div>

                <div class="row">
                    <revenue-filter-component>
                    </revenue-filter-component>
                </div>
            @endif

            <!-- Responsive Full Content -->
            @if(count($allData) == 0)
                <br><h3 class="text-center">@lang('Revenue Not Found')</h3><br>
            @else
                <div class="table-responsive">
                    <table class="table table-vcenter table-striped">
                        <thead>
                        <tr>
                            <th> Name</th>
                            <th> Photo</th>
                            @if(Auth::guard('admin')->check())
                                <th> Investor</th>
                            @endif
                            <th> Purchase Date</th>
                            <th> Purchase Price <br>({!! number_format($totals['total_purchase_price']) !!}$)</th>
                            <th class="paid-col"> Paid <br>({!! number_format($totals['total_paid'])  !!}$)</th>
                            <th> Renovation <br>({!! number_format($totals['total_renovation_price']) !!}$)</th>
                            <th> Other Investment <br>({!! number_format($totals['other_investment']) !!}$)</th>
                            <th> Total Investment <br>({!! number_format($totals['total_investment']) !!}$)</th>
                            <th> Current Value <br>({!! number_format($totals['total_current_value']) !!}$)</th>
                            <th> Capital Gain <br>({!! number_format($totals['total_capital_gain']) !!}$)</th>
                            <th> Rent <br>({!! number_format($totals['total_rent'])  !!}$)</th>
                            <th> Net Cash Balance <br>({!! number_format($totals['total_net_cash_balance'])  !!}$)</th>
                            @if(!Auth::guard('investor')->check())
                                <th width="10%" class="text-center">@lang('Action')</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($allData as $item)
                            <tr>
                                <td>
                                    @if(Auth::guard('investor')->check())
                                        <a href="{{route('asset.revenue.details', [ $item->id ])}}">{!! $item->project_name !!}</a>
                                    @else
                                        <a href="{{route('asset.revenue.view', [ $item->id ])}}">{!! $item->project_name !!}</a>
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
                                @if(Auth::guard('admin')->check())
                                    <td>
                                        @foreach($item->investors as $investor)
                                            {!! $investor->name !!} {!! $investor->surname !!}
                                            @if(!$loop->last)
                                                ,<br>
                                            @endif
                                        @endforeach
                                    </td>
                                @endif
                                <td>{!! $item->agreement_date !!}</td>
                                <td>{!! number_format($item->total_price,0,".",",") !!}$</td>
                                <td class="paid-col">{!! number_format($item->paid,0,".",",") !!}$ - {{ fmod(($item->paid/$item->total_price)*100, 1) == 0 ? number_format(($item->paid/$item->total_price)*100, 0) : number_format(($item->paid/$item->total_price)*100, 2) }}%</td>
                                <td>{!! number_format($item->renovation,0,".",",") !!}$</td>
                                <td>{!! number_format($item->other_investment,0,".",",") !!}$</td>
                                <td>{!! number_format($item->total_investment,0,".",",") !!}$</td>
                                <td>{!! number_format($item->current_value,0,".",",") !!}$</td>
                                <td>{!! number_format($item->capital_gain,0,".",",") !!}$</td>
                                <td>{!! number_format($item->rent,0,".",",") !!}$</td>
                                <td>{!! number_format($item->net_cache_balance,0,".",",") !!}$</td>
                                <td>
                                    @can(getPermissionKey('investment', 'index', true))
                                        @include('admin::includes.actions.investment',['route' => route('asset.investment.index', [ $item->id ])])
                                    @endcan
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
