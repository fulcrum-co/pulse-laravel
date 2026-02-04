@extends('components.layouts.app')

@section('content')
    <livewire:collect.dictation-capture :token="$token->token" />
@endsection
