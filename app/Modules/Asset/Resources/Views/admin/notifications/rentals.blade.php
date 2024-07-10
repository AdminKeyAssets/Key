@if(Auth::guard('investor')->check())
    <pending-rentals :investor-view="{{1}}"></pending-rentals>
@else
    <pending-rentals :investor-view="{{0}}"></pending-rentals>
@endif
