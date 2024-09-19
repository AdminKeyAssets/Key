<div class="content-header">
    <div class="header-section {{ isset($extra) ? 'flex-header-row' : '' }}">
        <h1>{{ $name }}</h1>
        @if(isset($extra))
            <div class="asset-info">
                <div>
                    @if(isset($extra['asset_route']) && isset($extra['asset_name']))
                        <a target="_blank" href="{{$extra['asset_route']}}">
                            <i class="el-icon-house"></i> {{ $extra['asset_name'] }}
                        </a>
                    @elseif(isset($extra['asset_name']))
                        <i class="el-icon-house"></i> {{ $extra['asset_name'] }}
                    @endif
                </div>
                <div><i class="el-icon-user"></i> {{ $extra['investor_name'] }}</div>
                @if(isset($extra['asset_edit_route'])
                        || isset($extra['payments_route'])
                        || isset($extra['rentals_route']))
                    <div class="text-center" style="margin-top: 10px">
                        @if($extra['asset_edit_route'])
                            @can(getPermissionKey('asset', 'update', true))
                                @include('admin::includes.actions.edit',['title' => 'Update','route' => $extra['asset_edit_route']])
                            @endcan
                        @endif
                        @if(isset($extra['payments_route']))
                            @can(getPermissionKey('payment', 'index', true))
                                @include('admin::includes.actions.payment', ['title' => 'Payments', 'route' => $extra['payments_route']])
                            @endcan
                        @endif
                        @if(isset($extra['rentals_route']))
                            @can(getPermissionKey('rental', 'index', true))
                                @include('admin::includes.actions.rental', ['title' => 'Rentals', 'route' => $extra['rentals_route']])
                            @endcan
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
