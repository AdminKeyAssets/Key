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
                @if(\Auth::user()->getRolesNameAttribute() == 'administrator')
                    <revenue-view-page-form
                        :id="{{ $data['id'] }}"
                        :is-admin="{{ true }}"
                        :user-id="{{ auth()->user()->getAuthIdentifier() }}"
                        :get-save-data-route="'{{ $data['routes']['create_form_data'] }}'">
                    </revenue-view-page-form>
                @else
                    <revenue-view-page-form
                        :id="{{ $data['id'] }}"
                        :user-id="{{ auth()->user()->getAuthIdentifier() }}"
                        :get-save-data-route="'{{ $data['routes']['create_form_data'] }}'">
                    </revenue-view-page-form>
                @endif
            </div>
        </div>
        <!-- END Responsive Full Block -->

    </div>
    <!-- END Page Content -->

@endsection

