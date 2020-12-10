@extends('layouts.email')

@section('content')

<p>Expense <a href="{{ url('charge/approve/' . $expense->id) }}">{{ $expense->title }}</a> requires your approval.</p>

@endsection
