@include('errors.layout', [
    'title' => 'Page not found',
    'code' => '404',
    'heading' => 'Page not found',
    'message' => 'The page you are looking for does not exist or has been moved.',
    'backUrl' => url()->previous() !== url()->current() ? url()->previous() : url('/'),
    'backLabel' => 'Go back',
    'secondaryUrl' => auth()->check() && auth()->user()->isAdmin() ? route('admin.dashboard') : url('/'),
    'secondaryLabel' => auth()->check() && auth()->user()->isAdmin() ? 'Admin dashboard' : 'Home',
])
