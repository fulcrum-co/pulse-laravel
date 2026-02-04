@extends('components.layouts.app')

@section('content')
    <div class="max-w-2xl mx-auto px-4 py-10">
        <div class="bg-white border border-gray-200 rounded-xl p-6">
            <h1 class="text-xl font-semibold text-gray-900 mb-2">{{ $plan->title }}</h1>
            <p class="text-sm text-gray-600">Status: {{ ucfirst($plan->status) }}</p>
            @if($plan->review_at)
                <p class="text-sm text-gray-600 mt-2">Review due: {{ $plan->review_at->format('M j, Y g:i A') }}</p>
            @endif
        </div>
    </div>
@endsection
