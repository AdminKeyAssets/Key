@extends('admin::layouts.admin')

@section('main')

    <!-- Page content -->
    <div id="page-content">

        <!-- Statistics Widgets Header -->
    @include('admin::includes.header-section', ['name'   => 'Create Lease' ])
    <!-- END Statistics Widgets Header -->
        <!-- Responsive Full Block -->
        <div class="row">
            <div class="col-xs-12">
                <a href="{{ route($moduleKey . '.lease.list', [$assetId]) }}" size="medium" class="btn btn-secondary">
                    <i class="el-icon-back"></i>
                </a>
                <lease-page-form
                :asset-id="{{$assetId}}"
                :get-save-data-route="'{{ route($moduleKey . '.lease.create_data', $assetId) }}'">
                </lease-page-form>

            </div>
        </div>
        <!-- END Responsive Full Block -->

    </div>
    <!-- END Page Content -->


@endsection

