@include('errors.layout', [
    'title' => 'Forbidden',
    'code' => '403',
    'heading' => 'Access denied',
    'message' => 'You do not have permission to view this page.',
    'backUrl' => url()->previous() !== url()->current() ? url()->previous() : url('/'),
    'backLabel' => 'Go back',
    'secondaryUrl' => auth()->check() && auth()->user()->isAdmin() ? route('admin.dashboard') : url('/'),
    'secondaryLabel' => auth()->check() && auth()->user()->isAdmin() ? 'Admin dashboard' : 'Home',
])
