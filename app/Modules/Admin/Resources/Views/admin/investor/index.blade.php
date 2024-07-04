@extends('admin::layouts.admin')

@section('main')
    <!-- Page content -->
    <div id="page-content">
        <!-- Statistics Widgets Header -->
        @include('admin::includes.header-section', ['name'   => 'Create Investor' ])
        <!-- END Statistics Widgets Header -->

        <!-- Responsive Full Block -->
        <div class="block">

            @include('admin::includes.success')

            <div class="row">
                @can(getPermissionKey($moduleKey, 'create', true))
                    <div class="col-md-6">
                        <a href="{{ route($baseRouteName . 'create_form') }}" class="btn btn-primary"><i
                                class="fa fa-plus"></i>Create Investor</a>
                    </div>
                @endcan

                @can(getPermissionKey($moduleKey, 'export', false))
                    <admin-investor-component>
                    </admin-investor-component>
                @endcan
            </div>
            <br>

            <div class="row">
                <admin-investor-filter-component>
                </admin-investor-filter-component>
            </div>
            <br>

            <!-- Responsive Full Content -->
            @if(count($allData) == 0)
                <br><h3 class="text-center">@lang('User Not Found')</h3><br>
            @else
                <div class="table-responsive">
                    <table class="table table-vcenter table-striped">
                        <thead>
                        <tr>
                            <th> Name</th>
                            <th> Profile Picture</th>
                            <th> Citizenship</th>
                            <th> Email</th>
                            <th> Phone</th>
                            <th> Assets</th>
                            <th> Asset Manager</th>
                            <th> Created At</th>
                            <th width="10%" class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($allData as $item)
                            <tr>
                                <td>{!! $item->name !!} {!! $item->surname !!}</td>
                                <td>
                                    @if($item->profile_picture)
                                        <image-modal thumbnail="{!! $item->profile_picture !!}"
                                                     image-path="{!! $item->profile_picture !!}"
                                                     :rounded="true"
                                                     :width="{{50}}"
                                                     :height="{{50}}"></image-modal>
                                    @endif
                                </td>
                                <td>{!! $item->citizenship !!}</td>
                                <td>{!! $item->email !!}</td>
                                <td>{!! $item->prefix !!}{!! $item->phone !!}</td>
                                <td>{!! count($item->assets) !!}</td>
                                <td>{!! $item->admin->name !!} {!! $item->admin->surname !!}</td>
                                <td>{!! $item->created_at->toDateString() !!}</td>
                                <td class="text-center">

                                    @can(getPermissionKey($moduleKey, 'update', true))
                                        @include('admin::includes.actions.edit',['route' => route($baseRouteName . 'edit', [ $item->id ])])
                                    @endcan
                                    @can(getPermissionKey($moduleKey, 'delete', true))
                                        <delete-component
                                            :url="'{{ route($baseRouteName . 'delete') }}'"
                                            :id="{{ $item->id }}"
                                        ></delete-component>
                                    @endcan

                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @include('admin::includes.paginate', ['data' => $allData ])

            <br>
            <!-- END Responsive Full Content -->
        </div>
        <!-- END Responsive Full Block -->

        <form action="{{route($baseRouteName . 'delete', [''])}}" method="GET" class="delete-item">
        </form>

    </div>
    <!-- END Page Content -->
@endsection

<style>
    th, td{
        text-align: center !important;
    }
</style>
