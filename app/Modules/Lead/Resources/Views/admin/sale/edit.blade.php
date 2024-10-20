@extends('admin::layouts.admin')

@section('main')

    <!-- Page content -->
    <div id="page-content">

        <!-- Statistics Widgets Header -->
        @include('admin::includes.header-section', ['name'   => 'Edit Sale' ])
        <!-- END Statistics Widgets Header -->

        <!-- Responsive Full Block -->
        <div class="row">
            <div class="col-xs-12">
                @if(\Auth::user()->getRolesNameAttribute() == 'administrator')
                    <sale-page-form
                        :id="{{ $data['id'] }}"
                        :is-admin="{{true}}"
                        :can-complete="{{ true }}"
                        :get-save-data-route="'{{ $data['routes']['create_form_data'] }}'">
                    </sale-page-form>
                @else
                    <sale-page-form
                        :id="{{ $data['id'] }}"
                        :get-save-data-route="'{{ $data['routes']['create_form_data'] }}'">
                    </sale-page-form>
                @endif
            </div>
        </div>
        <!-- END Responsive Full Block -->

    </div>
    <!-- END Page Content -->

@endsection

