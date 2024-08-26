@extends('admin::layouts.layout')
<?php
$user = auth('investor')->user();

if (!$user) {
    $user = auth('admin')->user();
}
?>
@section('content')
    <div id="admin">
        <div id="page-wrapper">
            <div id="page-container" class="sidebar-partial sidebar-visible-lg sidebar-no-animations">

                <!-- Main Sidebar -->
                <div id="sidebar" style="background-image: url('{{ config('admin.sidebar_background') }}')">
                    <!-- Wrapper for scrolling functionality -->
                    <div id="sidebar-scroll">
                        <!-- Sidebar Content -->
                        <div class="sidebar-content">
                            <!-- Brand -->
                            <a href="" class="sidebar-brand">
                                <img class="sidebar-logo" src="{{ config('admin.sidebar_logo') }}">
                            </a>
                            <!-- END Brand -->

                            <!-- User Info -->
                            <div class="sidebar-section sidebar-user clearfix sidebar-nav-mini-hide">
                                <div class="sidebar-user-avatar">
                                    @if(\Auth::guard('investor')->check())
                                        @if(!$user->is_demo)
                                            <a href="{{ !$user->is_demo ?? route('investor.profile.index') }}">
                                                @if( $user->profile_picture)
                                                    <img src="{{ $user->profile_picture }}" alt="avatar">
                                                @else
                                                    <img src="{{ config('admin.user_avatar') }}" alt="avatar">
                                                @endif
                                            </a>
                                        @else
                                            <span>
                                                @if( $user->profile_picture)
                                                    <img src="{{ $user->profile_picture }}" alt="avatar">
                                                @else
                                                    <img src="{{ config('admin.user_avatar') }}" alt="avatar">
                                                @endif
                                            </span>
                                        @endif
                                    @else
                                        <a href="{{ route('admin.profile.index') }}">
                                            @if( $user->profile_picture)
                                                <img src="{{ $user->profile_picture }}" alt="avatar">
                                            @else
                                                <img src="{{ config('admin.user_avatar') }}" alt="avatar">
                                            @endif
                                        </a>
                                    @endif
                                </div>
                                <div class="sidebar-user-name">{{ $user->name }}</div>
                                <div class="sidebar-user-links">
                                    @if(\Auth::guard('investor')->check())
                                        @if(!$user->is_demo)
                                            <a href="{{ route('investor.profile.index')}}"
                                               data-toggle="tooltip"
                                               data-placement="bottom" title="Profile"><i class="el-icon-user"></i></a>
                                        @endif
                                    @else
                                        <a href="{{ route('admin.profile.index')}}"
                                           data-toggle="tooltip"
                                           data-placement="bottom" title="Profile"><i class="el-icon-user"></i></a>
                                    @endif
                                    @if(\Auth::guard('admin')->check())
                                        <a href="{{ route('admin.logout') }}" class="logout-link" data-toggle="tooltip"
                                           data-placement="bottom" title="Log Out"><i class="el-icon-switch-button"></i></a>
                                    @elseif(\Auth::guard('investor')->check())
                                        <a href="{{route('investor.logout')}}" class="logout-link" data-toggle="tooltip"
                                           data-placement="bottom" title="Log Out"><i class="el-icon-switch-button"></i></a>
                                    @endif
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
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 4.5H3" stroke="white" stroke-width="1.5" stroke-linecap="round"
                                              stroke-linejoin="round"/>
                                        <path d="M12 9.5H3" stroke="white" stroke-width="1.5" stroke-linecap="round"
                                              stroke-linejoin="round"/>
                                        <path d="M21 14.5H3" stroke="white" stroke-width="1.5" stroke-linecap="round"
                                              stroke-linejoin="round"/>
                                        <path d="M21 19.5H3" stroke="white" stroke-width="1.5" stroke-linecap="round"
                                              stroke-linejoin="round"/>
                                    </svg>

                                </a>
                            </li>

                            <!-- END Main Sidebar Toggle Button -->
                        </ul>
                        <!-- END Left Header Navigation -->

                        <!-- Right Header Navigation -->
                        <ul class="nav navbar-nav-custom pull-right">
                            <!-- User Dropdown -->
                            <li>
                                <div id="header">
                                    @include('asset::admin.notifications.rentals')
                                    @include('asset::admin.notifications.payments')
                                    @include('asset::admin.comment.unread')
                                </div>
                            </li>
                            <!-- END User Dropdown -->
                        </ul>
                        <!-- END Right Header Navigation -->
                    </header>
                    <!-- END Header -->

                    {{--                    <div class="admin" style="background-image: url('{{ config('admin.auth_background') }}')">--}}
                    <div class="admin">
                        @yield('main')
                    </div>
                </div>
                <!-- END Main Container -->
            </div>
            <!-- END Page Container -->
        </div>
        <!-- END Page Wrapper -->
    </div>
@endsection

<script>
    function toggleClass() {
        var element = document.getElementById("sidebar");
        element.classList.toggle("sidebar-show");
    }
</script>
