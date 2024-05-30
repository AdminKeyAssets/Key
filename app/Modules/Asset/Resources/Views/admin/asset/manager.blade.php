@extends('admin::layouts.admin')

@section('main')

    <!-- Page content -->
    <div id="page-content">

        <!-- Statistics Widgets Header -->
    @include('admin::includes.header-section', ['name'   => 'Change Asset Manager' ])
    <!-- END Statistics Widgets Header -->

        <!-- Responsive Full Block -->
        <div class="row">
            <div class="col-xs-12">
                <a href="{{ route('asset.index') }}" size="medium" class="btn btn-secondary">
                    <i class="el-icon-back"></i>
                </a>

                <manager-page-form
                    :id="{{$assetId}}"
                    :manager-id="{{$managerId}}"
                    :managers="{{ json_encode($salesManagers) }}"
                    :change-manager-url="'{{ route($moduleKey . '.store_manager') }}'">
                </manager-page-form>
            </div>
        </div>
        <!-- END Responsive Full Block -->

    </div>
    <!-- END Page Content -->


@endsection

