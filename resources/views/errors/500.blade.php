@include('errors.layout', [
    'title' => 'Server error',
    'code' => '500',
    'heading' => 'Something went wrong',
    'message' => 'An unexpected error occurred. Please try again or return to a safe page.',
    'backUrl' => url()->previous() !== url()->current() ? url()->previous() : url('/'),
    'backLabel' => 'Go back',
    'secondaryUrl' => auth()->check() && auth()->user()->isAdmin() ? route('admin.dashboard') : url('/'),
    'secondaryLabel' => auth()->check() && auth()->user()->isAdmin() ? 'Admin dashboard' : 'Home',
])
