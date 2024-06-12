@extends('admin::layouts.admin')

@section('main')

    <!-- Page content -->
    <div id="page-content">

        <!-- Statistics Widgets Header -->
    @include('admin::includes.header-section', ['name'   => $data['trans_text']['create'] ])
    <!-- END Statistics Widgets Header -->

        <!-- Responsive Full Block -->
        <div class="row">
            <div class="col-xs-12">

                <admin-user-save-component
                    :user="{{ json_encode($data['user']) }}"
                    :get-save-data-route="'{{ $data['routes']['create_form_data'] }}'">
                </admin-user-save-component>

            </div>
        </div>
        <!-- END Responsive Full Block -->

    </div>
    <!-- END Page Content -->


@endsection

