<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        return view('admin.messages.index', ['messages' => ContactMessage::latest()->paginate(15)]);
    }

    public function show(ContactMessage $message)
    {
        $message->update(['read' => true]);

        return view('admin.messages.show', compact('message'));
    }

    public function destroy(Request $request, ContactMessage $message)
    {
        abort_unless($request->user()->can('messages.delete'), 403);
        $message->delete();

        return redirect()->route('admin.messages.index')->with('success', 'Message deleted.');
    }
}
