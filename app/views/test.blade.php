@extends('layout')

@section('content')
    <div class="container">
        test <b>{{ $test }}</b> blade
    </div>
    <div class="container">
        @datetime($birthday, 'FR-fr')
    </div>
@endsection
