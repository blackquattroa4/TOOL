@extends('layouts.email')

@section('content')

<p>Purchase {{ $order->type }} #{{ $order->title }} released.</p>

@endsection
