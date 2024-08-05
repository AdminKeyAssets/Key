@extends('admin::layouts.layout')

@section('content')
    {{--    <img src="{{ asset(config('admin.login_background_image')) }}" alt="Login Background" class="full-bg">--}}

    <div class="dk-container" style="background-image: url('{{ config('admin.login_background') }}')">
        <!-- Login Container -->
        <div id="login-container">
            <!-- Login Title -->

            <div class="login-title text-center" style="background-image: url('{{ config('admin.sidebar_background') }}')">
                <div  class="logo">
                    @foreach(config('admin.login_logo') as $logo)
                        <img src="{{ $logo['src'] }}" alt="" style="{{ $logo['style'] }}">
                    @endforeach
                </div>

                {{--                <h1><strong class="title-black">{{ config('admin.project_name') }} {{ config('admin.version') }}</strong></h1>--}}
            </div>

            <!-- END Login Title -->

            <!-- Login Block -->
            <div class="block push-bit login">
                <div>
                    <h1 class="title-black cms"><strong>CRM login</strong></h1>
                    <!-- Login Form -->
                    <form class="form-horizontal form-bordered form-control-borderless" id="form-login" method="POST"
                          action="{{ route('admin.investor_login') }}">
                        {{ csrf_field() }}
                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <div class="col-xs-12">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="gi gi-envelope"></i></span>
                                    <input type="text" id="login-email" name="email" value="{{ old('email') }}" class="form-control" placeholder="Email">
                                </div>

                                @if ($errors->has('email'))
                                    <span class="help-block"><strong>{{ $errors->first('email') }}</strong></span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <div class="col-xs-12">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="gi gi-asterisk"></i></span>
                                    <input type="password" id="login-password" name="password" class="form-control"
                                           placeholder="Password">
                                </div>
                                @if ($errors->has('password'))
                                    <span class="help-block"><strong>{{ $errors->first('password') }}</strong></span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group form-actions">
                            <div class="col-xs-4">
                                <label class="switch switch-primary" data-toggle="tooltip" title="დამიმახსოვრე">
                                    <input type="checkbox" id="login-remember-me" name="remember"{{ old('remember') ? ' checked' : '' }}>
                                    <span></span>
                                    <small style="font-size: 10px;font-weight: normal;">Remember Me</small>
                                </label>
                            </div>
                            <div class="col-xs-8 text-right">
                                <button type="submit"
                                        @if(config('admin.recaptcha.modules.login.status'))
                                            data-callback='onSubmit'
                                        data-sitekey="{{ config('admin.recaptcha.public_key') }}"
                                        @endif
                                        class="btn btn-sm btn-primary login @if(config('admin.recaptcha.modules.login.status')) g-recaptcha @endif">
                                    Enter
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
                <!-- END Login Form -->
            </div>
            <!-- END Login Block -->

        </div>
        <!-- END Login Container -->
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
