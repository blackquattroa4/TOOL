@extends('layouts.email')

@section('content')

<p>RMA <a href="{{ url('rma/approve/' . $order->id) }}">{{ $order->title }}</a> requires your approval.</p>

@endsection
