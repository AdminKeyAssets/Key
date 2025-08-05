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
                @php
                    // Check if any columns have data to determine which columns to show
                    $hasProfilePic = false;
                    $hasRepresentative = false;
                    $hasCell = false;
                    $hasAssets = false;
                    
                    foreach($allData as $item) {
                        if(!empty($item->logo)) {
                            $hasProfilePic = true;
                        }
                        if(!empty($item->representative) || !empty($item->representative_position)) {
                            $hasRepresentative = true;
                        }
                        if(!empty($item->tel)) {
                            $hasCell = true;
                        }
                        if(auth()->user()->getRolesNameAttribute() == 'administrator' && count($item->assets) > 0) {
                            $hasAssets = true;
                        }
                    }
                @endphp
                <div class="table-responsive">
                    <table class="table table-vcenter table-striped table-developers">
                        <thead>
                        <tr>
                            <th>Developer Name</th>
                            @if($hasProfilePic)<th>Profile Pic</th>@endif
                            @if($hasRepresentative)<th>Representative</th>@endif
                            @if($hasCell)<th>Cell</th>@endif
                            @if(auth()->user()->getRolesNameAttribute() == 'administrator' && $hasAssets)
                                <th class="developer-assets"> Assets</th>
                            @endif
                            <th width="10%" class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($allData as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                @if($hasProfilePic)
                                <td>
                                    @if($item->logo)
                                        <img src="{{ $item->logo }}" alt="{{ $item->name }}" width="50">
                                    @endif
                                </td>
                                @endif
                                @if($hasRepresentative)
                                <td>{{ $item->representative }} @if($item->representative_position) - {{ $item->representative_position }} @endif</td>
                                @endif
                                @if($hasCell)
                                <td>{{ $item->tel }}</td>
                                @endif
                                @if(auth()->user()->getRolesNameAttribute() == 'administrator' && $hasAssets)
                                    @php
                                        $assetNameList = $item->assets->pluck('asset_name')->toArray();
                                    @endphp

                                    <td class="developer-assets">
                                        @foreach($assetNameList as $assetName)
                                            <a href="{{ url('/assets/list?asset=' . urlencode($assetName) . '&status=all') }}" style="cursor: pointer">
                                                {{ $assetName }}
                                            </a><br>
                                        @endforeach
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
