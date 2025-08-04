@extends('admin::layouts.admin')

@section('main')

    <!-- Page content -->
    <div id="page-content">

        <!-- Statistics Widgets Header -->
        @include('admin::includes.header-section', ['name'   => 'View Lead' ])
        <!-- END Statistics Widgets Header -->

        <!-- Responsive Full Block -->
        <div class="row">
            <div class="col-xs-12">

                    <news-view-component
                        :id="{{ $data['news']->id }}"
                        :get-save-data-route="'{{ route($data['baseRouteName'] . 'getSaveData') }}'"
                    ></news-view-component>

            </div>
        </div>
        <!-- END Responsive Full Block -->

    </div>
    <!-- END Page Content -->


@endsection

