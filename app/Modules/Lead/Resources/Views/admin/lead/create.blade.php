@extends('admin::layouts.admin')

@section('main')

    <!-- Page content -->
    <div id="page-content">

        <!-- Statistics Widgets Header -->
    @include('admin::includes.header-section', ['name'   => 'Add Lead' ])
    <!-- END Statistics Widgets Header -->
        <!-- Responsive Full Block -->
        <div class="row">
            <div class="col-xs-12">

                <lead-page-form
                :get-save-data-route="'{{ route($moduleKey . '.create_data') }}'">
                </lead-page-form>

            </div>
        </div>
        <!-- END Responsive Full Block -->

    </div>
    <!-- END Page Content -->


@endsection

