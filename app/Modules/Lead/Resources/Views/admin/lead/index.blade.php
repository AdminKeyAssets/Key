@extends('admin::layouts.admin')

@section('main')
    <!-- Page content -->
    <div id="page-content">
        <!-- Statistics Widgets Header -->
        @include('admin::includes.header-section', ['name'   => 'Leads' ])
        <!-- END Statistics Widgets Header -->

        <!-- Responsive Full Block -->
        <div class="row">
            @can(getPermissionKey($moduleKey, 'create', true))
                <div class="col-md-6">
                    <a href="{{ route($moduleKey . '.create') }}" class="btn btn-primary"><i
                            class="el-icon-plus"></i> Add Lead</a>
                </div>
            @endcan

        </div>
        <br>
        <div class="row">
            <lead-filter-component>
            </lead-filter-component>
        </div>
        <br>
        <div class="block">

            @include('admin::includes.success')

            <!-- Responsive Full Content -->
            @if(count($allData) == 0)
                <br><h3 class="text-center">@lang('Leads Not Found')</h3><br>
            @else
                <div class="table-responsive">
                    <table class="table table-vcenter table-striped">
                        <thead>
                        <tr>
                            <th> Name</th>
                            <th> Surname</th>
                            <th> Email</th>
                            <th> Cell</th>
                            @if(auth()->user()->getRolesNameAttribute() == 'administrator')
                                <th> Sales Manager</th>
                            @endif
                            <th> Status</th>
                            <th> Created At</th>
                            <th width="10%" class="text-center">@lang('Action')</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($allData as $item)
                            <tr>
                                <td>
                                    <a href="{{route($moduleKey . '.view', [ $item->id ])}}">
                                        {!! $item->name !!}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{route($moduleKey . '.view', [ $item->id ])}}">
                                        {!! $item->surname !!}
                                    </a>
                                </td>
                                <td>{!! $item->email !!}</td>
                                <td>{!! $item->prefix !!}{!! $item->phone !!}</td>
                                @if(auth()->user()->getRolesNameAttribute() == 'administrator')
                                    <td>{!! $item->manager_name !!} {!! $item->manager_surname !!}</td>
                                @endif
                                <td>{!! $item->status !!}</td>
                                <td>{!! $item->created_at->toDateString() !!}</td>

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
