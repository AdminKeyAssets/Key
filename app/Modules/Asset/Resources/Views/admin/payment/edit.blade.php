@extends('admin::layouts.admin')

@section('main')

    <!-- Page content -->
    <div id="page-content">

        <!-- Statistics Widgets Header -->
    @include('admin::includes.header-section', ['name'   => 'Edit Payment' ])
    <!-- END Statistics Widgets Header -->

        <!-- Responsive Full Block -->
        <div class="row">
            <div class="col-xs-12">
                <a href="{{ route('asset.payments.list', [$data['assetId']]) }}" size="medium" class="btn btn-secondary">
                    <i class="el-icon-back"></i>
                </a>
                <payment-page-form
                    :id="{{ $data['id'] }}"
                    :get-save-data-route="'{{ $data['routes']['create_form_data'] }}'">
                </payment-page-form>

            </div>
        </div>
        <!-- END Responsive Full Block -->

    </div>
    <!-- END Page Content -->


@endsection

