@if(\Session::get('success'))
    <div class="form-group">
        <div class="alert alert-success text-center">
            {{ \Session::get('success') }}
        </div>
    </div>
@endif
