@extends('admin::layouts.admin')

@section('main')
    <!-- Page content -->
    <div id="page-content">
        <!-- Statistics Widgets Header -->
        @include('admin::includes.header-section', ['name'   => 'Assets' ])
        <!-- END Statistics Widgets Header -->

        <!-- Responsive Full Block -->
        <div class="row">
            @can(getPermissionKey($moduleKey, 'create', true))
                <div class="col-md-6">
                    <a href="{{ route($moduleKey . '.create') }}" class="btn btn-primary"><i class="el-icon-plus"></i>
                        Create Asset</a>
                </div>
            @endcan

        </div>
        <br>
        <div class="block">

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
                            <th> City</th>
                            @if(Auth::guard('admin')->check())
                                <th> Investor</th>
                            @endif
                            <th> Asset Type / Size</th>
                            <th> Agreement Status</th>
                            <th> Next Installment</th>
                            <th> Next Rent</th>
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
                                        <a href="{{route($moduleKey . '.details', [ $item->id ])}}">{!! $item->project_name !!}</a>
                                    @else
                                        <a href="{{route($moduleKey . '.view', [ $item->id ])}}">{!! $item->project_name !!}</a>
                                    @endif
                                </td>
                                <td>{!! $item->city !!}</td>
                                @if(Auth::guard('admin')->check())
                                    <td>{!! $item->investor->name !!} {!! $item->investor->surname !!}</td>
                                @endif
                                <td>{!! $item->type !!} - {!! $item->area !!} sq.m</td>
                                <td>{!! $item->agreement_status !!}</td>
                                <td>{!! $item->agreement_status == 'Installments' && count($item->payments) && count($item->payments->where('status', 0)) ? $item->payments->where('status', 0)->first()->payment_date . ' - ' . number_format($item->payments->where('status', 0)->first()->left_amount,0,".",",") . ' ' . $item->payments->where('status', 0)->first()->currency : '' !!}</td>
                                <td>{!! $item->asset_status == 'Rented' && count($item->rentals) && count($item->rentals->where('status', 0)) ? $item->rentals->where('status', 0)->first()->payment_date . ' - ' . number_format($item->rentals->where('status', 0)->first()->left_amount,0,".",",") . ' ' . $item->rentals->where('status', 0)->first()->currency : '' !!}</td>

                                <td class="text-center">

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
                                    @can(getPermissionKey($moduleKey, 'delete', true))
                                        <delete-component
                                            :url="'{{ route($moduleKey . '.delete') }}'"
                                            :id="{{ $item->id }}"
                                        ></delete-component>
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
