@extends('admin::layouts.admin')

@section('main')

    <!-- Page content -->
    <div id="page-content">

        <!-- Statistics Widgets Header -->
        @include('admin::includes.header-section', ['name'   => 'Create Investor' ])
        <!-- END Statistics Widgets Header -->

        <!-- Responsive Full Block -->
        <div class="row">
            <div class="col-xs-12">
                <admin-investor-save-component
                    :get-save-data-route="'{{ $data['routes']['create_form_data'] }}'">
                </admin-investor-save-component>

            </div>
        </div>
        <!-- END Responsive Full Block -->

    </div>
    <!-- END Page Content -->


@endsection
