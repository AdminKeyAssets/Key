@extends('admin::layouts.layout')

@section('content')
    <div class="dk-container investor-login-container">
        <div class="login-header">
            <span style="padding-right: 10px">
                <img src="{{ config('admin.burger') }}" width="24">
            </span>
            <span>
                <img src="{{ config('admin.header_logo') }}" width="110">
            </span>
        </div>
        <!-- Login Container -->
        <div id="login-container">
            <!-- Login Title -->

            <div class="login-title text-center"
                 style="background-image: url('{{ config('admin.sidebar_background') }}')">
                <div class="logo">
                    @foreach(config('admin.login_logo') as $logo)
                        <img src="{{ $logo['src'] }}" alt="" style="{{ $logo['style'] }}">
                    @endforeach
                </div>
            </div>

            <!-- END Login Title -->

            <!-- Login Block -->
            <div class="block push-bit login">
                <div>
                    <h1 class="title-black cms">
                        <img src="{{ config('admin.logo_c') }}" width="155"/>
                    </h1>
                    <!-- Login Form -->
                    <form class="form-horizontal form-bordered form-control-borderless" id="form-login" method="POST"
                          action="{{ route('admin.investor_login') }}">
                        {{ csrf_field() }}
                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <div class="col-xs-12">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="gi gi-envelope"></i></span>
                                    <input type="text" id="login-email" name="email" value="{{ old('email') }}"
                                           class="form-control" placeholder="Email">
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
                                <label class="switch switch-primary" data-toggle="tooltip" title="Remember Me">
                                    <input type="checkbox" id="login-remember-me"
                                           name="remember"{{ old('remember') ? ' checked' : '' }}>
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

                <!-- Modal Trigger -->
                <div class="not-registered-wrapper">
                    <div class="col-xs-12">
                        <span>Not registered yet? <span class="try-now" style="cursor: pointer" data-toggle="modal" data-target="#registrationModal">Try Demo User</span></span>
                    </div>
                </div>
                <!-- END Login Form -->
            </div>
            <!-- END Login Block -->
        </div>
        <!-- END Login Container -->
        <!-- Registration Modal -->
        <div class="modal fade" id="registrationModal" tabindex="-1" role="dialog" aria-labelledby="registrationModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="registrationModalLabel">For getting demo user and password please fill the data below</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Registration Form -->
                        <form id="registration-form" method="POST" action="{{ route('lead.store.web') }}">
                            {{ csrf_field() }}
                            <div id="form-content">
                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter Name">
                                </div>
                                <div class="form-group">
                                    <label for="surname">Surname</label>
                                    <input type="text" class="form-control" id="surname" name="surname" placeholder="Enter Surname">
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email">
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter Phone">
                                </div>
                            </div>

                            <!-- Demo Credentials -->
                            <div id="demo-credentials" class="alert alert-success" style="display: none;">
                                <p><strong>Email:</strong> <span id="demo-email"></span></p>
                                <p><strong>Password:</strong> <span id="demo-password"></span></p>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" id="submit-form">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- END Registration Modal -->
    </div>
@endsection

@section('footer_scripts')
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
        $(document).ready(function () {
            // Handle form submission via AJAX
            $('#registration-form').on('submit', function (e) {
                e.preventDefault();

                let formData = $(this).serialize();
                let formAction = $(this).attr('action');

                // Clear previous messages
                $('#demo-credentials').hide();
                $('#form-content').show(); // In case it was hidden before

                $.ajax({
                    url: formAction,
                    method: 'POST',
                    data: formData,
                    success: function (response) {
                        // Hide the form
                        $('#form-content').hide();
                        $('#submit-form').hide();
                        // Display the returned email and password
                        $('#demo-email').text(response.data.name);
                        $('#demo-password').text(response.data.password);
                        $('#demo-credentials').show();
                    },
                    error: function (response) {
                        let errors = response.responseJSON.errors;
                        let errorMessage = 'Registration failed. Please check the form for errors.';

                        if (errors) {
                            errorMessage = Object.values(errors).flat().join(' ');
                        }

                        // Optionally display error messages (you can improve this part)
                        alert(errorMessage);
                    }
                });
            });
        });
    </script>
@endsection
