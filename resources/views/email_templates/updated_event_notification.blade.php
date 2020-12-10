@extends('layouts.email')

@section('content')

<p>{{ $host->name }} updated event <a href="{{ url('calendar/dashboard?event=' . $event->id) }}">{{ $event->subject }}</a>.</p>

@endsection
