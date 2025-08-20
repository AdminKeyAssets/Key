@extends('admin::layouts.admin')

@section('main')

    <!-- Page content -->
    <div id="page-content">

        <!-- Statistics Widgets Header -->
        @include('admin::includes.header-section', ['name' => 'View News'])
        <!-- END Statistics Widgets Header -->

        <!-- Responsive Full Block -->
        <div class="row">
            <div class="col-xs-12">

                <admin-news-view-component
                    :news-id="{{ $data['id'] }}"
                    :user-id="{{ auth()->user()->getAuthIdentifier() }}"
                    :get-data-route="'{{ $data['routes']['create_form_data'] }}'"
                    :back-route="'{{ route('admin.news.index') }}'">
                </admin-news-view-component>

            </div>
        </div>
        <!-- END Responsive Full Block -->

    </div>
    <!-- END Page Content -->

@endsection
