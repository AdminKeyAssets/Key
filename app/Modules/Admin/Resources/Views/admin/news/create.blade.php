@extends('admin::layouts.admin')

@section('main')

    <!-- Page content -->
    <div id="page-content">

        <!-- Statistics Widgets Header -->
        @include('admin::includes.header-section', ['name' => 'Create News'])
        <!-- END Statistics Widgets Header -->

        <!-- Responsive Full Block -->
        <div class="row">
            <div class="col-xs-12">

                @if(\Auth::user()->getRolesNameAttribute() == 'administrator')
                    <admin-news-save-component
                        :is-admin="{{true}}"
                        :get-save-data-route="'{{ $data['routes']['create_form_data'] }}'"
                        :save-route="'{{ $data['routes']['save'] }}'"
                        :back-route="'{{ route('admin.news.index') }}'">
                    </admin-news-save-component>
                @elseif(\Auth::user()->getRolesNameAttribute() == 'developer')
                    <admin-news-save-component
                        :is-developer="{{true}}"
                        :get-save-data-route="'{{ $data['routes']['create_form_data'] }}'"
                        :save-route="'{{ $data['routes']['save'] }}'"
                        :back-route="'{{ route('admin.news.index') }}'">
                    </admin-news-save-component>
                @else
                    <admin-news-save-component
                        :get-save-data-route="'{{ $data['routes']['create_form_data'] }}'"
                        :save-route="'{{ $data['routes']['save'] }}'"
                        :back-route="'{{ route('admin.news.index') }}'">
                    </admin-news-save-component>
                @endif

            </div>
        </div>
        <!-- END Responsive Full Block -->

    </div>
    <!-- END Page Content -->

@endsection
