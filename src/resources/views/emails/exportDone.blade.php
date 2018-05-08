@extends('laravel-enso/core::emails.layouts.main')

@section('content')

    @foreach($lines as $line)
    <p>
        {{$line}}
    <p>
    @endforeach

@endsection
