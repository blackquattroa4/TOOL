@extends('layouts.email')

@section('content')

<p>{{ $respondent->name }} {{ $action }} to attend <a href="{{ url('calendar/dashboard?event=' . $event->id) }}">{{ $event->subject }}</a>.</p>

@endsection
