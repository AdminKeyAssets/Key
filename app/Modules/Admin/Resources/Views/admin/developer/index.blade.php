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
                            @if(auth()->user()->getRolesNameAttribute() == 'administrator')
                                <th class="developer-assets"> Assets</th>
                            @endif
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
                                    @endif
                                </td>
                                <td>{{ $item->username }}</td>
                                @if(auth()->user()->getRolesNameAttribute() == 'administrator')
                                    @php
                                    $assetNameList = $item->assets->pluck('asset_name')->toArray();
                                    @endphp
                                    <td class="developer-assets">
                                        <update-developer-asset
                                            :asset-name='@json($assetNameList)'
                                            :developer-id="{{ $item->id }}">
                                        </update-developer-asset>
                                    </td>
                                @endif
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

            @include('admin::includes.paginate', ['data' => $allData ])

        </div>
        <!-- END Responsive Full Block -->
    </div>
    <!-- END Page Content -->
@endsection
