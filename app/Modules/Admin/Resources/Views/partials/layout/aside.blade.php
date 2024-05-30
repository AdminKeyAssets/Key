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

    @can ( getPermissionKey('asset', 'index', true))
        <li
            {!! strpos(request()->route()->getName(), 'asset.list') !== false ? ' class="active"' : '' !!}>
            <a href="{{route('asset.index')}}"><i class="el-icon-house sidebar-nav-icon"></i>
                <span class="sidebar-nav-mini-hide">Assets</span></a>
        </li>
    @endif

    @can ( getPermissionKey('asset', 'view', true))
        <li
            {!! strpos(request()->route()->getName(), 'asset.myasset') !== false ? ' class="active"' : '' !!}>
            <a href="{{route('asset.myassets')}}"><i class="el-icon-house sidebar-nav-icon"></i>
                <span class="sidebar-nav-mini-hide">My Assets</span></a>
        </li>
    @endif

    @can ( getPermissionKey('rental', 'index', true))
        <li
            {!! strpos(request()->route()->getName(), 'rental.list') !== false ? ' class="active"' : '' !!}>
            <a href="{{route('asset.lease.list')}}"><i class="el-icon-house sidebar-nav-icon"></i>
                <span class="sidebar-nav-mini-hide">Rentals</span></a>
        </li>
    @endif

</ul>
