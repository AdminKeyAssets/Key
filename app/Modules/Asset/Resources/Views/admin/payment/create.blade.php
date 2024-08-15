@extends('admin::layouts.admin')

@section('main')

    <!-- Page content -->
    <div id="page-content">

        <!-- Statistics Widgets Header -->
    @include('admin::includes.header-section', ['name'   => 'Create Payment' ])
    <!-- END Statistics Widgets Header -->
        <!-- Responsive Full Block -->
        <div class="row">
            <div class="col-xs-12">

                <payment-page-form
                :asset-id="{{$assetId}}"
                :get-save-data-route="'{{ route($moduleKey . '.payments.create_data', $assetId) }}'">
                </payment-page-form>

            </div>
        </div>
        <!-- END Responsive Full Block -->

    </div>
    <!-- END Page Content -->


@endsection

