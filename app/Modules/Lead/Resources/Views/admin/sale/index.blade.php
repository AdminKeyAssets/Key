@extends('admin::layouts.admin')

@section('main')
    <!-- Page content -->
    <div id="page-content">
        <!-- Statistics Widgets Header -->
        @include('admin::includes.header-section', ['name'   => 'Sales' ])
        <!-- END Statistics Widgets Header -->

        <!-- Responsive Full Block -->
        <div class="row">
            @can(getPermissionKey($moduleKey, 'create', true))
                <div class="col-md-6">
                    <a href="{{ route($moduleKey . '.create') }}" class="btn btn-primary"><i
                            class="el-icon-plus"></i> Add Sale</a>
                </div>
            @endcan

        </div>
        <br>


        <div class="block">

            @include('admin::includes.success')

            <!-- Responsive Full Content -->
            @if(count($allData) == 0)
                <br><h3 class="text-center">@lang('Sales Not Found')</h3><br>
            @else
                <div class="table-responsive">
                    <table class="table table-vcenter table-striped">
                        <thead>
                        <tr>
                            <th> Project</th>
                            <th> Investor</th>
                            <th> Type</th>
                            <th> Size (sq/m)</th>
                            <th> Price</th>
                            <th> Total Price</th>
                            <th> Agreement Status</th>
                            <th> Down Payment</th>
                            <th> Period</th>
                            <th width="10%" class="text-center">@lang('Action')</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($allData as $item)
                            <tr>

                                <td>{!! $item->project !!}</td>
                                <td>{!! $item->investor !!}</td>
                                <td>{!! $item->type !!}</td>
                                <td>{!! $item->size !!}</td>
                                <td>{!! $item->price !!} {!! $item->currency !!}</td>
                                <td>{!! $item->total_price !!} {!! $item->currency !!}</td>
                                <td>{!! $item->agreement_status !!}</td>
                                <td>{!! $item->down_payment !!}</td>
                                <td>{!! $item->period !!}</td>

                                <td class="text-center">
                                    @can(getPermissionKey($moduleKey, 'view', true))
                                        @include('admin::includes.actions.view',['route' => route($moduleKey . '.view',  [$item->id, ])])
                                    @endcan
                                    @can(getPermissionKey($moduleKey, 'update', true))
                                        @include('admin::includes.actions.edit',['route' => route($moduleKey . '.edit',  [$item->id, ])])
                                    @endcan
                                    @can(getPermissionKey($moduleKey, 'delete', true))
                                        <delete-component
                                            :url="'{{ route($moduleKey . '.delete', [$item->id ]) }}'"
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
