@extends('admin::layouts.admin')

@section('main')
    <!-- Page content -->
    <div id="page-content">
        <!-- Statistics Widgets Header -->
        @include('admin::includes.header-section', ['name' => 'Developer Management' ])
        <!-- END Statistics Widgets Header -->

        <!-- Responsive Full Block -->
        <div class="block">

            @include('admin::includes.success')

            <div class="row">
                @can(getPermissionKey($moduleKey, 'create', true))
                    <div class="col-md-6">
                        <a href="{{ route($baseRouteName . 'create_form') }}" class="btn btn-primary"><i
                                class="fa fa-plus"></i>Create Developer</a>
                    </div>
                @endcan
            </div>
            <br>

            <!-- Responsive Full Content -->
            @if(count($allData) == 0)
                <br><h3 class="text-center">@lang('Developer Not Found')</h3><br>
            @else
                <div class="table-responsive">
                    <table class="table table-vcenter table-striped">
                        <thead>
                        <tr>
                            <th>Developer Name</th>
                            <th>ID Code</th>
                            <th>Representative</th>
                            <th>Position</th>
                            <th>Tel</th>
                            <th>Logo</th>
                            <th>Username</th>
                            <th width="10%" class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($allData as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->id_code }}</td>
                                <td>{{ $item->representative }}</td>
                                <td>{{ $item->representative_position }}</td>
                                <td>{{ $item->tel }}</td>
                                <td>
                                    @if($item->logo)
                                        <img src="{{ $item->logo }}" alt="{{ $item->name }}" width="50">
                                    @else
                                        --
                                    @endif
                                </td>
                                <td>{{ $item->username }}</td>
                                <td class="text-center">
                                    @can(getPermissionKey($moduleKey, 'view', true))
                                        @include('admin::includes.actions.view',['route' => route($baseRouteName . 'view',  [$item->id, ])])
                                    @endcan
                                    @can(getPermissionKey($moduleKey, 'update', true))
                                        @include('admin::includes.actions.edit',['route' => route($baseRouteName . 'edit', [ $item->id ])])
                                    @endcan
                                    @can(getPermissionKey($moduleKey, 'delete', true))
                                        <delete-component
                                            :item-id="{{ $item->id }}"
                                            :item-name="'{{ $item->name }}'"
                                            :delete-route="'{{ route($baseRouteName . 'delete') }}'"
                                            :redirect-route="'{{ route($baseRouteName . 'index') }}'"
                                            :title="'Do you really want to delete {{ $item->name }}?'"
                                        >
                                        </delete-component>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
            <!-- END Responsive Full Content -->

            <div class="row">
                <div class="col-sm-6 col-xs-12">
                    @if(count($allData) != 0)
                        <div class="dataTables_info" id="example-datatable_info" role="status" aria-live="polite">
                            <div class="btn-group">
                                Showing {{ $allData->firstItem() }} to {{ $allData->lastItem() }}
                                of {{ $allData->total() }} entries
                            </div>
                        </div>
                    @endif
                </div>
                <div class="col-sm-6 col-xs-12">
                    <div class="dataTables_paginate paging_simple_numbers" id="example-datatable_paginate">
                        {{ $allData->links() }}
                    </div>
                </div>
            </div>

        </div>
        <!-- END Responsive Full Block -->
    </div>
    <!-- END Page Content -->
@endsection
