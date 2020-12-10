@extends('layouts.email')

@section('content')

<p>{{ $vendor->name }}({{ $vendor->code }}) entered a shipment.  Click <a href="{{ env('APP_URL') . '/document/view/' . $document->id }}">here</a> to download shipping documents</p>

@endsection
