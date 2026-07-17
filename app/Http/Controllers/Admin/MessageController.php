<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkActions;
use App\Http\Controllers\Admin\Concerns\HandlesListQuery;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    use HandlesBulkActions, HandlesListQuery;

    public function index(Request $request)
    {
        $messages = $this->applyListQuery(
            ContactMessage::query(),
            $request,
            ['name', 'email', 'subject'],
            ['name', 'email', 'created_at'],
        )->paginate(15)->withQueryString();

        return view('admin.messages.index', compact('messages'));
    }

    public function bulk(Request $request)
    {
        return $this->runBulkAction($request, ContactMessage::class, 'messages');
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
