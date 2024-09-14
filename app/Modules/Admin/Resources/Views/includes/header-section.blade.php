<div class="content-header">
    <div class="header-section {{ isset($extra) ? 'flex-header-row' : '' }}">
        <h1>{{ $name }}</h1>
        @if(isset($extra))
            <div class="asset-info">
                <div>
                    <a href="{{$extra['asset_route']}}">
                        <i class="el-icon-house"></i> {{ $extra['asset_name'] }}
                    </a>
                </div>
                <div><i class="el-icon-user"></i> {{ $extra['investor_name'] }}</div>
            </div>
        @endif
    </div>
</div>
