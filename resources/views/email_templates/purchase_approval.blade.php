@extends('layouts.email')

@section('content')

<p>Purchase {{ $order->type }} <a href="{{ url('vrm/approve' . $order->type . '/' . $order->id) }}">{{ $order->title }}</a> requires your approval.</p>

@endsection
