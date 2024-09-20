@extends('admin::layouts.admin')

@section('main')

    <!-- Page content -->
    <div id="page-content">

        <!-- Statistics Widgets Header -->
        @include('admin::includes.header-section', ['name'   => 'Add Sale' ])
        <!-- END Statistics Widgets Header -->
        <!-- Responsive Full Block -->
        <div class="row">
            <div class="col-xs-12">
                @if(\Auth::user()->getRolesNameAttribute() == 'administrator')
                    <sale-page-form
                        :can-complete="{{ true }}"
                        :get-save-data-route="'{{ route($moduleKey . '.create_data') }}'">
                    </sale-page-form>
                @else
                    <sale-page-form
                        :can-complete="{{ true }}"
                        :get-save-data-route="'{{ route($moduleKey . '.create_data') }}'">
                    </sale-page-form>
                @endif

            </div>
        </div>
        <!-- END Responsive Full Block -->

    </div>
    <!-- END Page Content -->

@endsection

