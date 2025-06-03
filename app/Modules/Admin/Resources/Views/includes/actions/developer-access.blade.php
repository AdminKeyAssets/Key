<el-tooltip
    content="{{ $developerAccess
        ? ($title . ' Disabled')
        : ($title . ' Active') }}"
    placement="top"
    effect="light"
>
    <a href="{{ $route }}" class="btn btn-switch {{ $developerAccess ? 'disabled-icon' : 'active-icon' }}">
        <i class="el-icon-switch-button"></i>
    </a>
</el-tooltip>
