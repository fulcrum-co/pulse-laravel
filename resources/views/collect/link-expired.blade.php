@extends('components.layouts.app')

@section('content')
    <div class="max-w-lg mx-auto px-4 py-12">
        <div class="bg-white border border-gray-200 rounded-xl p-6 text-center">
            <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-4">
                <x-icon name="exclamation-triangle" class="w-6 h-6" />
            </div>
            <h1 class="text-lg font-semibold text-gray-900 mb-2">Link Expired</h1>
            <p class="text-sm text-gray-600">
                This link is no longer valid. Please contact your organization for a new link.
            </p>
        </div>
    </div>
@endsection
