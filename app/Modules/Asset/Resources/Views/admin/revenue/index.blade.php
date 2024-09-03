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

            <div class="row">
                <revenue-export-component>
                </revenue-export-component>
            </div>
            <div class="row">
                <revenue-filter-component>
                </revenue-filter-component>
            </div>

            <!-- Responsive Full Content -->
            @if(count($allData) == 0)
                <br><h3 class="text-center">@lang('Revenue Not Found')</h3><br>
            @else
                <div class="table-responsive">
                    <table class="table table-vcenter table-striped">
                        <thead>
                        <tr>
                            <th> Name</th>
                            @if(Auth::guard('admin')->check())
                                <th> Investor</th>
                            @endif
                            <th> Purchase Date</th>
                            <th> Purchase Price</th>
                            <th> Total Investment ({!! number_format($totals['total_rent']) !!})</th>
                            <th> Current Value</th>
                            <th> Capital Gain ({!! number_format($totals['total_capital_gain']) !!})</th>
                            <th> Rent ({!! number_format($totals['total_rent'])  !!})</th>
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
                                @if(Auth::guard('admin')->check())
                                    <td>{!! $item->investor->name !!} {!! $item->investor->surname !!}</td>
                                @endif
                                <td>{!! $item->agreement_date !!}</td>
                                <td>{!! number_format($item->total_price,0,".",",") !!}</td>
                                <td>{!! number_format($item->total_investment,0,".",",") !!}</td>
                                <td>{!! number_format($item->current_value,0,".",",") !!}</td>
                                <td>{!! number_format($item->capital_gain,0,".",",") !!}</td>
                                <td>{!! number_format($item->rent,0,".",",") !!}</td>
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
