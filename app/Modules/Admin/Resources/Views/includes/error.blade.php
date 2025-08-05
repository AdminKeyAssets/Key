@if(\Session::get('error'))
    <div class="form-group">
        <div class="alert alert-danger text-center">
            {{ \Session::get('error') }}
        </div>
    </div>
@endif
