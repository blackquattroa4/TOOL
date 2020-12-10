@extends('layouts.email')

@section('content')

<p>Sales {{ $order->type }} <a href="{{ url('crm/approve' . $order->type . '/' . $order->id) }}">{{ $order->title }}</a> requires your approval.</p>

@endsection
