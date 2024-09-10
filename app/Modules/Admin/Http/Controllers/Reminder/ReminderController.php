<?php

namespace App\Modules\Admin\Http\Controllers\Reminder;

use App\Modules\Admin\Models\Reminder;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class ReminderController extends Controller
{
    public function index()
    {
        return Reminder::where('admin_id', auth()->id())->where('done', false)->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'reminder_date' => 'required|date',
            'comment' => 'required|string',
        ]);

        Reminder::create([
            'admin_id' => auth()->id(),
            'reminder_date' => $request->reminder_date,
            'comment' => $request->comment,
        ]);

        return response()->json(['message' => 'Reminder added successfully']);
    }

    public function markDone($id)
    {
        $reminder = Reminder::where('admin_id', auth()->id())->where('id', $id)->firstOrFail();
        $reminder->update(['done' => true]);

        return response()->json(['message' => 'Reminder marked as done']);
    }
}
