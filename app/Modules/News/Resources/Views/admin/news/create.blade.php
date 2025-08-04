@extends('admin::layouts.admin')

@section('main')

    <!-- Page content -->
    <div id="page-content">

        <!-- Statistics Widgets Header -->
        @include('admin::includes.header-section', ['name' => $data['trans_text']['create']])
        <!-- END Statistics Widgets Header -->

        <!-- Responsive Full Block -->
        <div class="row">
            <div class="col-xs-12">

                <admin-news-save-component
                    :news="{{ json_encode($data['news']) }}"
                    :get-save-data-route="'{{ $data['routes']['create_form_data'] }}'">
                </admin-news-save-component>

            </div>
        </div>
        <!-- END Responsive Full Block -->

    </div>
    <!-- END Page Content -->

@endsection
