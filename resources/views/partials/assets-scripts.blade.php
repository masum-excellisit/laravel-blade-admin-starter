{{-- Static JS vendors + app. Order matters: app.js before Alpine so alpine:init handlers register first. --}}
<script defer src="{{ asset('vendor/sortablejs/Sortable.min.js') }}"></script>
<script defer src="{{ asset('vendor/jodit/jodit.min.js') }}"></script>
<script defer src="{{ asset('js/app.js') }}"></script>
<script defer src="{{ asset('vendor/alpine/alpine.min.js') }}"></script>
@stack('scripts')
