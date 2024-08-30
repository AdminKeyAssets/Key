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
                        <span>Not registered yet? <span class="try-now" style="cursor: pointer" data-toggle="modal"
                                                        data-target="#registrationModal">Try Demo User</span></span>
                    </div>
                </div>
                <!-- END Login Form -->
            </div>
            <!-- END Login Block -->
        </div>
        <!-- END Login Container -->
        <!-- Registration Modal -->
        <div class="modal fade" id="registrationModal" tabindex="-1" role="dialog"
             aria-labelledby="registrationModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <!-- Custom Close Button -->
                        <button type="button" class="close custom-close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h5 class="modal-title text-center" id="registrationModalLabel">For getting demo user and password <br>please fill the data below</h5>
                    </div>
                    <div class="modal-body">
                        <!-- Registration Form -->
                        <form id="registration-form" method="POST" action="{{ route('lead.store.web') }}">
                            {{ csrf_field() }}
                            <div id="form-content">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" id="first_name" name="name"
                                           placeholder="First name">
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" id="last_name" name="surname"
                                           placeholder="Last name">
                                </div>
                                <div class="col-md-6">
                                    <input type="email" class="form-control" id="email" name="email"
                                           placeholder="Email">
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group phone">
                                        <span class="input-group-addon">
                                            <select id="phone_prefix" name="phone_prefix" class="form-control" style="width: auto; display: inline-block;">
                                                <!-- Prefixes will be dynamically added here -->
                                            </select>
                                        </span>
                                        <input type="text" class="form-control" id="phone" name="phone" placeholder="Phone">
                                    </div>
                                </div>
                            </div>

                            <!-- Demo Credentials -->
                            <div id="demo-credentials" class="credentials-boxes" style="display: none;">
                                <div class="credential-box col-md-5" style="margin-right: 15px">
                                    <label>User</label>
                                    <p id="demo-email"></p>
                                </div>
                                <div class="credential-box col-md-5">
                                    <label>Password</label>
                                    <p id="demo-password"></p>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary" id="submit-form">Submit</button>
                                <!-- New Login Button -->
                                <button type="button" id="login-button" class="btn btn-primary" style="display: none;">Login</button>
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
            // Fetch prefixes when the modal is shown
            $('#registrationModal').on('show.bs.modal', function () {
                $.ajax({
                    url: '/lead/prefixes',
                    method: 'GET',
                    success: function (response) {
                        let prefixSelect = $('#phone_prefix');
                        prefixSelect.empty(); // Clear existing options

                        response.forEach(function (item) {
                            prefixSelect.append(new Option(item.prefix, item.prefix));
                        });
                    },
                    error: function () {
                        alert('Failed to load phone prefixes.');
                    }
                });
            });

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

                        // Update the modal title
                        $('#registrationModalLabel').text(`Demo User Details`);

                        // Display the returned email and password
                        $('#demo-email').text(response.data.name); // Use 'email' to populate the login field
                        $('#demo-password').text(response.data.password);

                        // Show the credentials and login button
                        $('#demo-credentials').show();
                        $('#login-button').show();
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

            // Handle login button click
            $('#login-button').on('click', function () {
                // Fill in the login form with the demo user's credentials
                $('#login-email').val($('#demo-email').text());
                $('#login-password').val($('#demo-password').text());

                // Submit the login form
                $('#form-login').submit();
            });
        });
    </script>

    <style>
        /* Modal Styles */
        .modal-content {
            border-radius: 10px;
            padding: 20px;
        }

        .modal-header {
            border-bottom: none;
            position: relative;
        }

        .modal-header h5 {
            font-size: 24px;
            font-weight: bold;
        }

        .modal-header p {
            margin-bottom: 20px;
            font-size: 16px;
            color: #555;
        }

        /* Custom Close Button */
        .custom-close {
            opacity: 1;
            position: absolute;
            top: -30px;
            right: -30px;
            background-color: #007bff !important;
            border: none !important;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            color: #fff;
            font-size: 18px;
            line-height: 18px;
            text-align: center;
            cursor: pointer;
        }

        .form-control {
            border-radius: 5px;
            border: 1px solid #ccc;
            height: 45px;
            font-size: 14px;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            padding: 10px 20px;
            font-size: 14px;
        }

        .btn-secondary {
            padding: 10px 20px;
            font-size: 14px;
        }

        .modal-footer {
            border-top: none;
            text-align: center;
            padding-top: 20px;
        }

        .modal-footer .btn {
            width: 120px;
            font-weight: 600;
        }

        #form-content div {
            padding-bottom: 10px;
        }

        .credential-box p {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 20px;
        }

        .credential-box label {
            display: block;
            font-weight: bold !important;
            margin-bottom: 5px;
            font-size: 14px;
            width: 100%;
            text-align: center;
        }

        .credential-box p {
            font-size: 14px;
            word-break: break-word;
        }

        @media (max-width: 767px) {
            .modal-dialog {
                margin-top: 30%;
            }

            .credentials-boxes {
                flex-direction: column;
            }

            .credential-box {
                width: 100%;
                margin-bottom: 10px;
            }
        }

        #registrationModalLabel {
            font-size: 20px;
            font-weight: 500;
        }

        .input-group-addon {
            padding: 0;
        }

        .phone.input-group {
            display: flex;
            padding: 0 !important;
            border: none;
            border-radius: 5px !important;
        }
        .phone.input-group .input-group-addon{
            width: 50%;
            border-color: #ccc;
        }
        #phone_prefix{
            height: 100%;
            width: 100%;
            display: inline-block;
            border-color: #ccc;
        }

    </style>
@endsection
