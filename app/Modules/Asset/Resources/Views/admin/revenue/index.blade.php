@extends('admin::layouts.admin')

@section('main')
    <!-- Page content -->
    <div id="page-content">
        <!-- Statistics Widgets Header -->
        @include('admin::includes.header-section', ['name'   => 'Payments' ])
        <!-- END Statistics Widgets Header -->

        <!-- Responsive Full Block -->
        <div class="row">

        </div>
        <br>

        <div class="row">
            <revenue-filter-component>
            </revenue-filter-component>
        </div>
        <br>
        <div class="block">

            @include('admin::includes.success')

            <!-- Responsive Full Content -->
            @if(count($allData) == 0)
                <br><h3 class="text-center">@lang('Revenue Not Found')</h3><br>
            @else
                <div class="table-responsive">
                    <table class="table table-vcenter table-striped">
                        <thead>
                        <tr>
                            <th> Project Name</th>
                            <th> City</th>
                            <th> Purchase Date</th>
                            <th> Total Price</th>
                            <th> Total Investment ({!! $totals['total_rent'] !!})</th>
                            <th> Current Value</th>
                            <th> Capital Gain ({!! $totals['total_capital_gain'] !!})</th>
                            <th> Rent ({!! $totals['total_rent'] !!})</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($allData as $item)
                            <tr>
                                <td>
                                    @if(Auth::guard('investor')->check())
                                        <a href="{{route('asset.details', [ $item->id ])}}">{!! $item->project_name !!}</a>
                                    @else
                                        <a href="{{route('asset.view', [ $item->id ])}}">{!! $item->project_name !!}</a>
                                    @endif
                                </td>
                                <td>{!! $item->city !!}</td>
                                <td>{!! $item->agreement_date !!}</td>
                                <td>{!! number_format($item->total_price,2,".",",") !!}</td>
                                <td>{!! number_format($item->total_investment,2,".",",") !!}</td>
                                <td>{!! number_format($item->current_value,2,".",",") !!}</td>
                                <td>{!! number_format($item->capital_gain,2,".",",") !!}</td>
                                <td>{!! number_format($item->rent,2,".",",") !!}</td>
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
    th, td{
        text-align: center !important;
    }
</style>
