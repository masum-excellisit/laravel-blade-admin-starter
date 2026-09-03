@include('errors.layout', [
    'title' => 'Method not allowed',
    'code' => '405',
    'heading' => 'Method not allowed',
    'message' => 'This action is not available. You may have followed an outdated link.',
    'backUrl' => url()->previous() !== url()->current() ? url()->previous() : url('/'),
    'backLabel' => 'Go back',
    'secondaryUrl' => auth()->check() && auth()->user()->isAdmin() ? route('admin.dashboard') : url('/'),
    'secondaryLabel' => auth()->check() && auth()->user()->isAdmin() ? 'Admin dashboard' : 'Home',
])
