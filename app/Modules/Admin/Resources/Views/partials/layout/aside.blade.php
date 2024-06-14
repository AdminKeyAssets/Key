<ul class="sidebar-nav">
    @can ( getPermissionKey('user', 'index', true))
        <li
            {!! strpos(request()->route()->getName(), 'admin.user.') !== false ? ' class="active"' : '' !!}>
            <a href="{{route('admin.user.index')}}"><i class="el-icon-user sidebar-nav-icon"></i>
                <span class="sidebar-nav-mini-hide">Users</span></a>
        </li>
    @endif

    @can ( getPermissionKey('role', 'index', true))
        <li
            {!! strpos(request()->route()->getName(), 'admin.role.') !== false ? ' class="active"' : '' !!}>
            <a href="{{route('admin.role.index')}}"><i class="el-icon-thumb sidebar-nav-icon"></i>
                <span class="sidebar-nav-mini-hide">Roles</span></a>
        </li>
    @endif

    @can ( getPermissionKey('investor', 'index', true))
        <li
            {!! strpos(request()->route()->getName(), 'admin.investor.') !== false ? ' class="active"' : '' !!}>
            <a href="{{route('admin.investor.index')}}"><i class="el-icon-user sidebar-nav-icon"></i>
                <span class="sidebar-nav-mini-hide">Investors</span></a>
        </li>
    @endif

    @can ( getPermissionKey('asset', 'index', true))
        <li
            {!! strpos(request()->route()->getName(), 'asset.index') !== false ? ' class="active"' : '' !!}>
            <a href="{{route('asset.index')}}"><i class="el-icon-house sidebar-nav-icon"></i>
                <span class="sidebar-nav-mini-hide">Assets</span></a>
        </li>
    @endif
</ul>
