@if(Auth::guard('investor')->check())
    <pending-payments :investor-view="{{1}}"></pending-payments>
@else
    <pending-payments :investor-view="{{0}}"></pending-payments>
@endif
