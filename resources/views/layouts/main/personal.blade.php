@extends('layouts.main', ['adaptive' => true])

@section('content')
    <main>
        <div class="container">
            <div class="row no-gutters">
                <div class="lk">
                @include('layouts.main.personal.include.menu')
                @yield('content.personal')
                @if(!isset($right) || $right === true)
                    @include('layouts.main.personal.include.right')
                @endif
                </div>
            </div>
        </div>
    </main>
@endsection