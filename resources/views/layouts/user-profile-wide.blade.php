@extends('layouts.app')

@section('content')
</div>
<div class="container-fluid">
    @include('users.partials.action-buttons', ['user' => $user])
    <h2 class="page-header">
        {{ $user->name }}

        @php
            $family = $user->subFamily ?? $user->family;
        @endphp
        
        @if ($family)
            ( <a href="{{ route('families.show', $family->id) }}">{{ $family->name }}</a> )
        @endif
        
        <small>@yield('subtitle')</small>
            </h2>
    @yield('user-content')
@endsection
