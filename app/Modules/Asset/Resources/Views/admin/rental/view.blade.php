@extends('admin::layouts.admin')

@section('main')

    <!-- Page content -->
    <div id="page-content">

        <!-- Statistics Widgets Header -->
    @include('admin::includes.header-section', ['name'   => 'View Rental' ])
    <!-- END Statistics Widgets Header -->

        <!-- Responsive Full Block -->
        <div class="row">
            <div class="col-xs-12">
                <a href="{{ route('asset.index') }}" size="medium" class="btn btn-secondary">
                    <i class="el-icon-back"></i>
                </a>

                <rental-view-page-form
                    :id="{{ $data['id'] }}"
                    :income="{{ $data['income'] }}"
                    :get-save-data-route="'{{ $data['routes']['create_form_data'] }}'">
                </rental-view-page-form>

            </div>
        </div>
        <!-- END Responsive Full Block -->

    </div>
    <!-- END Page Content -->


@endsection
