@extends('admin::layouts.layout')

@section('content')
    <div class="login-container">
        <div id="login-box">
            <div class="login-logo">
                @foreach(config('admin.login_logo') as $logo)
                    <img src="{{ $logo['src'] }}" alt="Logo" >
                @endforeach
            </div>
            <form class="login-form" id="form-login" method="POST" action="{{ route('admin.investor_login') }}">
                {{ csrf_field() }}
                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                    <input type="text" id="login-email" name="email" value="{{ old('email') }}" class="form-control" placeholder="Email">
                    @if ($errors->has('email'))
                        <span class="help-block"><strong>{{ $errors->first('email') }}</strong></span>
                    @endif
                </div>
                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                    <input type="password" id="login-password" name="password" class="form-control" placeholder="Password">
                    @if ($errors->has('password'))
                        <span class="help-block"><strong>{{ $errors->first('password') }}</strong></span>
                    @endif
                </div>
                <div class="form-group form-actions">
                    <button type="submit" class="btn btn-primary btn-block">Sign in</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('footer_scripts')
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
        function onSubmit(token) {
            document.getElementById("form-login").submit();
        }
    </script>
@endsection

<style>
    body {
        background-color: #888888;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }

    .login-container {
        background-color: #333333;
        padding: 30px; /* Increased padding */
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        transform: scale(1.5); /* Scaling the container to make it 1.5x bigger */
    }

    #login-box {
        background-color: #4a4a4a;
        padding: 60px; /* Increased padding */
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
    }

    .login-logo img {
        display: block;
        margin: 0 auto 30px; /* Increased margin */
    }

    .login-form .form-control {
        background-color: #5a5a5a;
        color: #ffffff;
        border: 1px solid #666666;
        margin-bottom: 15px; /* Increased margin */
        padding: 15px; /* Increased padding */
        border-radius: 4px;
        font-size: 1.25em; /* Increased font size */
    }

    .login-form .form-control::placeholder {
        color: #cccccc;
    }

    .btn-primary {
        background-color: #1a8cff;
        border: none;
        padding: 15px; /* Increased padding */
        border-radius: 4px;
        color: #ffffff;
        font-size: 1.25em; /* Increased font size */
        cursor: pointer;
    }

    .btn-primary:hover {
        background-color: #0073e6;
    }
</style>
