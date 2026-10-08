@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        @if(isset($header))
            <div class="bg-navy-900 text-white p-6 sm:p-8 rounded-3xl shadow-xl flex items-center justify-between">
                {{ $header }}
            </div>
        @endif

        {{ $slot }}
    </div>
@endsection
