@extends('layouts.app')
	<style>
		.title {
			font-size: 72px;
			margin-bottom: 40px;
		}
	</style>

@section('content')
	<div class="container">
		<div class="row">
			<div class="col-md-10 col-md-offset-1">
				<div class="title">{{ trans('email.You did not setup email completely') }}</div>
			</div>
		</div>
	</div>
@endsection

@section('post-content')
@endsection
