@if(Auth::guard('admin')->check())
    <upcoming-reminders></upcoming-reminders>
@endif
