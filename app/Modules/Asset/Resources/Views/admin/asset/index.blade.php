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
                            <th> Delivery Date</th>
                            <th> Area (m2)</th>
                            <th> Price</th>
                            <th width="10%" class="text-center">@lang('Action')</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($allData as $item)
                            <tr>
                                <td>{!! $item->name !!}</td>
                                <td>{!! $item->city !!}</td>
                                <td>{!! $item->delivery_date !!}</td>
                                <td>{!! $item->area !!}</td>
                                <td>{!! number_format($item->total_price,2,".",",") !!}</td>

                                <td class="text-center">
                                    @can(getPermissionKey($moduleKey, 'update', true))
                                        @include('admin::includes.actions.change',['route' => route($moduleKey . '.change', [ $item->id ])])
                                    @endcan
                                    @can(getPermissionKey('payment', 'index', true))
                                        @if(count($item->payments))
                                            @include('admin::includes.actions.payment',['route' => route($moduleKey . '.payments.list', [ $item->id ])])
                                        @else
                                            @include('admin::includes.actions.payment-disabled',['route' => route($moduleKey . '.payments.list', [ $item->id ])])
                                        @endif
                                    @endcan
                                    @can(getPermissionKey($moduleKey, 'view', true))
                                        @include('admin::includes.actions.view',['route' => route($moduleKey . '.view', [ $item->id ])])
                                    @endcan
                                    @can(getPermissionKey($moduleKey, 'update', true))
                                        @include('admin::includes.actions.edit',['route' => route($moduleKey . '.edit', [ $item->id ])])
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
