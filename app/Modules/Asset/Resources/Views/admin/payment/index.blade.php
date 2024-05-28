@extends('admin::layouts.admin')

@section('main')
    <!-- Page content -->
    <div id="page-content">
        <!-- Statistics Widgets Header -->
        @include('admin::includes.header-section', ['name'   => 'Payments' ])
        <!-- END Statistics Widgets Header -->

        <!-- Responsive Full Block -->
        <div class="row">
            @can(getPermissionKey($moduleKey, 'create', true))
                <div class="col-md-6">
                    <a href="{{ route($moduleKey . '.payments.create', [$assetId]) }}" class="btn btn-primary"><i
                            class="el-icon-plus"></i> Add Payment</a>
                </div>
            @endcan

        </div>
        <br>
        <div class="block">

            @include('admin::includes.success')

            <!-- Responsive Full Content -->
            @if(count($allData) == 0)
                <br><h3 class="text-center">@lang('Payment Not Found')</h3><br>
            @else
                <div class="table-responsive">
                    <table class="table table-vcenter table-striped">
                        <thead>
                        <tr>
                            <th> Month</th>
                            <th> Payment Date</th>
                            <th> Amount</th>
                            <th> Paid</th>
                            <th width="10%" class="text-center">@lang('admin.action')</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($allData as $item)
                            <tr>
                                <td>{!! $item->month !!}</td>
                                <td>{!! $item->payment_date !!}</td>
                                <td>{!! number_format($item->amount,2,".",",") !!}</td>
                                <td>
                                    @if($item->status)
                                        <i class="el-icon-check" style="color: green"></i>
                                    @else
                                        <i class="el-icon-close" style="color: red"></i>
                                    @endif
                                </td>

                                <td class="text-center">
                                    @can(getPermissionKey($moduleKey, 'view', true))
                                        @include('admin::includes.actions.view',['route' => route($moduleKey . '.payments.view', [$assetId, $item->id, ])])
                                    @endcan
                                    @can(getPermissionKey($moduleKey, 'update', true))
                                        @include('admin::includes.actions.edit',['route' => route($moduleKey . '.payments.edit', [$assetId, $item->id ])])
                                    @endcan
                                    @can(getPermissionKey($moduleKey, 'delete', true))
                                        <delete-component
                                            :url="'{{ route($moduleKey . '.payments.delete', $assetId) }}'"
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
