@extends('admin::layouts.admin')

@section('main')
    <!-- Page content -->
    <div id="page-content">
        <!-- Statistics Widgets Header -->
    @include('admin::includes.header-section', ['name'   => __($langFolderName . '.'.$moduleKey.'.index') ])
    <!-- END Statistics Widgets Header -->

        <!-- Responsive Full Block -->
        <div class="block">

        </div>
        <!-- END Responsive Full Block -->

    </div>
    <!-- END Page Content -->
@endsection
