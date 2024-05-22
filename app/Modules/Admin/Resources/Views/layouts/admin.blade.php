@extends('admin::layouts.layout')

@section('content')
    <div id="page-wrapper">
        <div id="page-container" class="sidebar-partial sidebar-visible-lg sidebar-no-animations">

            <!-- Main Sidebar -->
            <div id="sidebar">
                <!-- Wrapper for scrolling functionality -->
                <div id="sidebar-scroll">
                    <!-- Sidebar Content -->
                    <div class="sidebar-content">
                        <!-- Brand -->
                        <a href="" class="sidebar-brand">
                            <i class="gi gi-flash"></i><span class="sidebar-nav-mini-hide"><strong>{{ config('admin.project_name') }}</strong></span>
                        </a>
                        <!-- END Brand -->

                        <!-- User Info -->
                        <div class="sidebar-section sidebar-user clearfix sidebar-nav-mini-hide">
                            <div class="sidebar-user-avatar">
                                <a href="{{ route('admin.profile.index') }}">
                                    <img src="{{ config('admin.user_avatar') }}" alt="avatar">
                                </a>
                            </div>
                            <div class="sidebar-user-name">{{ \Auth::user()->name }}</div>
                            <div class="sidebar-user-links">
                                <a href="{{ route('admin.profile.index') }}" data-toggle="tooltip" data-placement="bottom" title="პროფილი"><i class="el-icon-user"></i></a>
                                <a href="javascript:;" class="logout-link" data-toggle="tooltip" data-placement="bottom" title="გასვლა"><i class="el-icon-switch-button"></i></a>
                            </div>
                        </div>
                        <!-- END User Info -->

                    @include('admin::partials.layout.aside')

                    <!-- END Sidebar Navigation -->
                    </div>
                    <!-- END Sidebar Content -->
                </div>
                <!-- END Wrapper for scrolling functionality -->
            </div>
            <!-- END Main Sidebar -->

            <!-- Main Container -->
            <div id="main-container">
                <header class="navbar navbar-inverse">
                    <!-- Left Header Navigation -->
                    <ul class="nav navbar-nav-custom">
                        <!-- Main Sidebar Toggle Button -->
                        <li>
                            <a href="javascript:void(0)" onclick="toggleClass();this.blur();">
                                <i class="fa fa-bars fa-fw"></i>
                            </a>
                        </li>

                        <!-- END Main Sidebar Toggle Button -->
                    </ul>
                    <!-- END Left Header Navigation -->

                    <!-- Right Header Navigation -->
                    <ul class="nav navbar-nav-custom pull-right">
                        <!-- User Dropdown -->
                        <li class="dropdown">
                            <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="el-icon-more-outline"></i> <i class="fa fa-angle-down"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-custom dropdown-menu-right">
                                <li class="dropdown-header text-center">My Account</li>
                                <li>
                                    <a href="{{ route('admin.profile.index') }}">
                                        <i class="el-icon-user pull-right"></i>
                                        profile
                                    </a>
                                    <a href="javascript:;" class="logout-link"><i class="el-icon-switch-button pull-right"></i> logout</a>
                                    <form action="{{ route('admin.logout') }}" id="logout-form" method="post" style="display: none;">
                                        {{csrf_field()}}
                                        <button type="submit"></button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                        <!-- END User Dropdown -->
                    </ul>
                    <!-- END Right Header Navigation -->
                </header>
                <!-- END Header -->

                <div id="admin">

                    @yield('main')

                </div>

            <!-- Footer -->
                <footer class="clearfix">
                    <div class="pull-right">
                        Handcrafted with <i class="fa fa-heart text-danger"></i> by <a href="{{ config('admin.handcrafted_by_url') }}" target="_blank">{{ config('admin.handcrafted_by') }}</a>
                    </div>
                </footer>
                <!-- END Footer -->
            </div>
            <!-- END Main Container -->
        </div>
        <!-- END Page Container -->
    </div>
    <!-- END Page Wrapper -->

    <!-- Scroll to top link, initialized in js/app.js - scrollToTop() -->
    <a href="#" id="to-top"><i class="fa fa-angle-double-up"></i></a>
    <!-- END User Settings -->
@endsection

<script>
    function toggleClass() {
        var element = document.getElementById("sidebar");
        element.classList.toggle("sidebar-show");
    }
</script>
