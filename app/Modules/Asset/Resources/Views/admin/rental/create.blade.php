@extends('admin::layouts.admin')

@section('main')

    <!-- Page content -->
    <div id="page-content">

        <!-- Statistics Widgets Header -->
    @include('admin::includes.header-section', ['name'   => 'Create Rental' ])
    <!-- END Statistics Widgets Header -->
        <!-- Responsive Full Block -->
        <div class="row">
            <div class="col-xs-12">
                <a href="{{ route($moduleKey . '.index') }}" size="medium" class="btn btn-secondary">
                    <i class="el-icon-back"></i>
                </a>
                <rental-page-form
                :get-save-data-route="'{{ route($moduleKey . '.rental.create_data', $assetId) }}'">
                </rental-page-form>

            </div>
        </div>
        <!-- END Responsive Full Block -->

    </div>
    <!-- END Page Content -->


@endsection

