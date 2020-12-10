@extends('layouts.email')

@section('content')

<p>{{ $host->name }} invited you to attend <a href="{{ url('calendar/dashboard?event=' . $event->id) }}">{{ $event->subject }}</a>.</p>

@endsection
