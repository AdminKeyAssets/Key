@extends('admin::layouts.admin')

@section('main')

    <!-- Page content -->
    <div id="page-content">

        <!-- Statistics Widgets Header -->
    @include('admin::includes.header-section', ['name'   => $data['name'] ])
    <!-- END Statistics Widgets Header -->

        <!-- Responsive Full Block -->
        <div class="row">
            <div class="col-xs-12">
                <asset-view-page-form
                    :id="{{ $data['id'] }}"
                    :investor-view="{{true}}"
                    :is-admin="{{false}}"
                    :get-save-data-route="'{{ $data['routes']['create_form_data'] }}'">
                </asset-view-page-form>

            </div>
        </div>
        <!-- END Responsive Full Block -->

    </div>
    <!-- END Page Content -->


@endsection

<style>
    .header-section{
        text-align: center;
    }
</style>
