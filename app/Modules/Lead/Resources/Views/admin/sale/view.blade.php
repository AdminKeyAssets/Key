@extends('admin::layouts.admin')

@section('main')

    <!-- Page content -->
    <div id="page-content">

        <!-- Statistics Widgets Header -->
    @include('admin::includes.header-section', ['name'   => 'View Sale' ])
    <!-- END Statistics Widgets Header -->

        <!-- Responsive Full Block -->
        <div class="row">
            <div class="col-xs-12">

                <sale-view-page-form
                    :id="{{ $data['id'] }}"
                    :user-id="{{ auth()->user()->getAuthIdentifier() }}"
                    :get-save-data-route="'{{ $data['routes']['create_form_data'] }}'">
                </sale-view-page-form>

            </div>
        </div>
        <!-- END Responsive Full Block -->

    </div>
    <!-- END Page Content -->


@endsection
