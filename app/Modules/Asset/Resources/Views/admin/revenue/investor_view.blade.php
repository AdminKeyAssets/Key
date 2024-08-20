@extends('admin::layouts.admin')

@section('main')

    <!-- Page content -->
    <div id="page-content" class="view view-asset">

        <!-- Statistics Widgets Header -->
        @include('admin::includes.header-section', ['name'   => $data['name'] ])
        <!-- END Statistics Widgets Header -->

        <!-- Responsive Full Block -->
        <div class="row">
            <div class="col-xs-12">
                <revenue-view-page-form
                    :id="{{ $data['id'] }}"
                    :user-id="{{ auth()->user()->getAuthIdentifier() }}"
                    :get-save-data-route="'{{ $data['routes']['create_form_data'] }}'">
                </revenue-view-page-form>
            </div>
        </div>
        <!-- END Responsive Full Block -->

    </div>
    <!-- END Page Content -->

@endsection

