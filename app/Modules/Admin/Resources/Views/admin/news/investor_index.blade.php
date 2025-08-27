@extends('admin::layouts.admin')

@section('main')
    <!-- Page content -->
    <div id="page-content">
        <!-- Statistics Widgets Header -->
        @include('admin::includes.header-section', ['name' => 'My News'])
        <!-- END Statistics Widgets Header -->

        <!-- Responsive Full Block -->
        <div class="block">

            @include('admin::includes.success')

            <div class="row">
                <div class="col-md-12">
                    <h4 class="text-center text-muted">News & Updates for You</h4>
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
                            <th>Thumbnail</th>
                            <th>Title</th>
                            <th>Status</th>
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
                                <td class="text-center">
                                    @include('admin::includes.actions.view', ['route' => route('investor.news.view', $item->id)])
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
</style>

<script>
    // Refresh notification count when investor visits news index
    document.addEventListener('DOMContentLoaded', function() {
        if (window.app && window.app.$root) {
            setTimeout(() => {
                window.app.$root.$emit('refresh-news-count');
            }, 1000);
        }
    });
</script>
