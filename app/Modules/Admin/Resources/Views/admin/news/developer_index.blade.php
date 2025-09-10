@extends('admin::layouts.admin')

@section('main')
    <!-- Page content -->
    <div id="page-content">
        <!-- Statistics Widgets Header -->
        @include('admin::includes.header-section', ['name' => 'News Management'])
        <!-- END Statistics Widgets Header -->

        <!-- Responsive Full Block -->
        <div class="block">

            @include('admin::includes.success')

            <div class="row">
                <div class="col-md-6">
                    <a href="{{ route('developer.news.create_form') }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Create News
                    </a>
                </div>
            </div>
            <br>

            <div class="row">
                <admin-news-filter-component
                    :is-developer="{{ true }}">
                </admin-news-filter-component>
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
                            <th>Thumbnail</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Investors</th>
                            <th width="15%" class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($allData as $item)
                            <tr>
                                <td>
                                    @if($item->thumbnail)
                                        <image-modal thumbnail="{{ $item->thumbnail }}"
                                                   image-path="{{ $item->thumbnail }}"
                                                   :rounded="false"
                                                   :width="60"
                                                   :height="40"></image-modal>
                                    @else
                                        <div class="text-muted">No Image</div>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ \Illuminate\Support\Str::limit($item->title, 50) }}</strong>
                                    <br>
                                    <small class="text-muted">{{ \Illuminate\Support\Str::limit(strip_tags($item->content), 100) }}</small>
                                </td>
                                <td>
                                    @if($item->status == 'published')
                                        <span class="badge badge-success">Published</span>
                                    @else
                                        <span class="badge badge-warning">Draft</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->created_by_type === 'developer')
                                        <span class="badge badge-primary">You</span>
                                        <br><small class="text-muted">Created by Developer</small>
                                    @elseif($item->created_by_type === 'admin')
                                        <span class="badge badge-info">Admin</span>
                                        <br><small class="text-muted">{{ $item->created_by_name }}</small>
                                    @else
                                        <span class="badge badge-secondary">Manager</span>
                                        <br><small class="text-muted">{{ $item->created_by_name }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($item->investors && $item->investors->count() > 0)
                                        <span class="badge badge-info">{{ $item->investors->count() }} investor(s)</span>
                                        <br>
                                        <small class="text-muted">
                                            {{ $item->investors->take(2)->pluck('full_name')->join(', ') }}
                                            @if($item->investors->count() > 2)
                                                <br>and {{ $item->investors->count() - 2 }} more...
                                            @endif
                                        </small>
                                    @else
                                        <span class="text-muted">No investors</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @include('admin::includes.actions.view', ['route' => route('developer.news.view', $item->id)])
                                    
                                    {{-- Only show edit/delete for news created by developer --}}
                                    @if($item->created_by_type === 'developer')
                                        @include('admin::includes.actions.edit', ['route' => route('developer.news.edit', $item->id)])
                                        <delete-component
                                            :url="'{{ route('developer.news.delete') }}'"
                                            :id="{{ $item->id }}"
                                        ></delete-component>
                                    @else
                                        <span class="text-muted" title="This news was assigned to you by management">
                                            <i class="fa fa-eye-slash"></i> View Only
                                        </span>
                                    @endif
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

        <form action="{{ route('developer.news.delete', ['']) }}" method="GET" class="delete-item">
        </form>

    </div>
    <!-- END Page Content -->
@endsection

<style>
    th, td {
        text-align: center !important;
    }
    td:nth-child(2) {
        text-align: left !important;
    }
    td:nth-child(4) {
        text-align: center !important;
    }
    td:nth-child(5) {
        text-align: center !important;
    }
</style>
