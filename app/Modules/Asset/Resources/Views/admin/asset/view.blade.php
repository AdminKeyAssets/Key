@extends('admin::layouts.admin')

@section('main')

    <!-- Page content -->
    <div id="page-content">

        <!-- Statistics Widgets Header -->
    @include('admin::includes.header-section', ['name'   => 'View Asset' ])
    <!-- END Statistics Widgets Header -->

        <!-- Responsive Full Block -->
        <div class="row">
            <div class="col-xs-12">
                @if(in_array(auth()->user()->getRolesNameAttribute(), ['Asset Manager', 'AssetManager', 'Sales Manager', 'SalesManager', 'Investor']))
                <a href="{{ route('asset.myassets') }}" size="medium" class="btn btn-secondary">
                    <i class="el-icon-back"></i>
                </a>
                @else
                    <a href="{{ route('asset.index') }}" size="medium" class="btn btn-secondary">
                        <i class="el-icon-back"></i>
                    </a>
                @endif
                <asset-view-page-form
                    :id="{{ $data['id'] }}"
                    :get-save-data-route="'{{ $data['routes']['create_form_data'] }}'">
                </asset-view-page-form>

            </div>
        </div>
        <!-- END Responsive Full Block -->

    </div>
    <!-- END Page Content -->


@endsection

