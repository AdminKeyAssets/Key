@extends('admin::layouts.admin')

@section('main')
    <!-- Page content -->
    <div id="page-content">
        <!-- Statistics Widgets Header -->
        @include('admin::includes.header-section', ['name' => $trans_text['index']])
        <!-- END Statistics Widgets Header -->

        <!-- Responsive Full Block -->
        <div class="block">

            @include('admin::includes.success')

            <div class="row">
                @can(getPermissionKey($moduleKey, 'create', true))
                    <div class="col-md-6">
                        <a href="{{ route($baseRouteName . 'create_form') }}" class="btn btn-primary">
                            <i class="fa fa-plus"></i> {{ $trans_text['create'] }}
                        </a>
                    </div>
                @endcan

                <div class="col-md-6">
                    <form method="GET" action="{{ route($baseRouteName . 'index') }}">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control"
                                   placeholder="{{ $trans_text['search'] }}"
                                   value="{{ request('search') }}">
                            <select name="status" class="form-control">
                                <option value="all">{{ $trans_text['all_status'] }}</option>
                                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>
                                    {{ $trans_text['published'] }}
                                </option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>
                                    {{ $trans_text['draft'] }}
                                </option>
                                <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>
                                    {{ $trans_text['archived'] }}
                                </option>
                            </select>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <br>

            <!-- Responsive Full Content -->
            @if(count($allData) == 0)
                <br><h3 class="text-center">@lang('No News Found')</h3><br>
            @else
                <div class="table-responsive">
                    <table class="table table-vcenter table-striped">
                        <thead>
                        <tr>
                            <th>{{ $trans_text['title'] }}</th>
                            <th>{{ $trans_text['status'] }}</th>
                            <th>{{ $trans_text['investors'] }}</th>
                            <th>{{ $trans_text['manager'] }}</th>
                            <th>{{ $trans_text['created_at'] }}</th>
                            <th width="15%" class="text-center">{{ $trans_text['actions'] }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($allData as $item)
                            <tr>
                                <td>
                                    <a href="{{ route($baseRouteName . 'view', [$item->id]) }}">
                                        <strong>
                                            {{ Str::limit($item->title, 50) }}
                                        </strong>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge
                                        @if($item->status == 'published') badge-success
                                        @elseif($item->status == 'draft') badge-warning
                                        @else badge-secondary
                                        @endif">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td>{{ Str::limit($item->investorsNames, 30) }}</td>
                                <td>{{ $item->admin ? $item->admin->name . ' ' . $item->admin->surname : '' }}</td>
                                <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                                <td class="text-center">
                                    <a href="{{ route($baseRouteName . 'view', [$item->id]) }}"
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="fa fa-eye"></i>
                                    </a>

                                    @can(getPermissionKey($moduleKey, 'update', true))
                                        @if(auth()->user()->getRolesNameAttribute() == 'administrator' || $item->admin_id == auth()->user()->getAuthIdentifier())
                                            @include('admin::includes.actions.edit', [
                                                'route' => route($baseRouteName . 'create_form', [$item->id])
                                            ])
                                        @endif
                                    @endcan

                                    @can(getPermissionKey($moduleKey, 'delete', true))
                                        @if(auth()->user()->getRolesNameAttribute() == 'administrator' || $item->admin_id == auth()->user()->getAuthIdentifier())
                                            <delete-component
                                                :url="'{{ route($baseRouteName . 'delete') }}'"
                                                :id="{{ $item->id }}">
                                            </delete-component>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @include('admin::includes.paginate', ['data' => $allData])

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
    th, td {
        text-align: center !important;
    }
</style>
