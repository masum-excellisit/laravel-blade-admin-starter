@extends('layouts.app')
@section('title', cms('contact', 'intro', 'title', 'Contact'))
@section('content')
<div class="brand-gradient text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-20 text-center">
        <h1 class="text-4xl sm:text-5xl font-bold">{{ cms('contact', 'intro', 'title', 'Get in touch') }}</h1>
        <p class="mt-3 text-white/80">{{ cms('contact', 'intro', 'subtitle', "We'd love to hear from you.") }}</p>
    </div>
</div>
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-16 grid grid-cols-1 lg:grid-cols-2 gap-12">
    <div>
        <h2 class="text-2xl font-bold mb-4">Contact details</h2>
        <div class="space-y-3 text-slate-600 whitespace-pre-line">
            <p><strong>Email:</strong> {{ cms('contact', 'intro', 'email', $settings['contact_email'] ?? '') }}</p>
            <p><strong>Phone:</strong> {{ cms('contact', 'intro', 'phone', $settings['contact_phone'] ?? '') }}</p>
            <p><strong>Address:</strong> {{ cms('contact', 'intro', 'address', $settings['contact_address'] ?? '') }}</p>
        </div>
    </div>
    <div class="rounded-2xl border border-slate-100 shadow-sm p-8">
        <form method="POST" action="{{ route('contact.submit') }}" class="space-y-5">@csrf
            <x-form.input name="name" label="Name" required />
            <x-form.input name="email" type="email" label="Email" required />
            <x-form.input name="subject" label="Subject" />
            <x-form.textarea name="message" label="Message" rows="5" required />
            <x-btn type="submit" class="w-full">Send message</x-btn>
        </form>
    </div>
</div>
@endsection
