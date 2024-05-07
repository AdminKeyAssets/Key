<div class="row">
    <div class="col-sm-5 hidden-xs">
        <div class="dataTables_info" id="example-datatable_info" role="status" aria-live="polite">
            <strong>{{ ($data->currentPage() - 1) * $data->perPage() + 1 }}</strong>-<strong>{{ ($data->currentPage() - 1) * $data->perPage() + count($data) }}</strong>
            of <strong>{{ $data->total() }}</strong></div>
    </div>
    <div class="col-sm-7 col-xs-12 clearfix">
        <div class="dataTables_paginate paging_bootstrap" id="example-datatable_paginate">
            {{ $data->withQueryString()->links() }}
        </div>
    </div>
</div>
