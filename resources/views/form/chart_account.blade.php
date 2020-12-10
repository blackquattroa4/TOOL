@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<div class="panel panel-default">
				<div class="panel-heading">{{ $title }}</div>
				<div class="panel-body">
					<form class="form-horizontal" role="form" enctype="multipart/form-data" method="POST" action="{{ $postUrl }}">
						{{ csrf_field() }}

						<div class="form-group">
							<label for="account" class="col-md-3 control-label">{{ trans('forms.Account') }}</label>

							<div class="col-md-7{{ $errors->has('account') ? ' has-error' : '' }}">
							@if ($readonly)
								<input id="account" type="text" class="form-control" name="account" value="{{ old('account') }}" readonly>
							@else
								<input id="account" type="text" class="form-control" name="account" value="{{ old('account') }}" >
							@endif

								@if ($errors->has('account'))
									<span class="help-block">
										<strong>{{ $errors->first('account') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group">
							<label for="type" class="col-md-3 control-label">{{ trans('forms.Type') }}</label>

							<div class="col-md-7{{ $errors->has('type') ? ' has-error' : '' }}">
								@if ($readonly)
									<select id="type" type="text" class="form-control" name="type" readonly>
								@else
									<select id="type" type="text" class="form-control" name="type" >
								@endif
									@foreach ($allAccountTypes as $val => $display)
										<option value="{{ $val }}" {{ (old('type') == $val) ? ' selected' : '' }}>{{ $display }}</option>
									@endforeach
									</select>

								@if ($errors->has('type'))
									<span class="help-block">
										<strong>{{ $errors->first('type') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group">
							<label for="type" class="col-md-3 control-label">{{ trans('forms.Currency') }}</label>

							<div class="col-md-7{{ $errors->has('currency_id') ? ' has-error' : '' }}">
								@if ($readonly)
									<select id="currency_id" type="text" class="form-control" name="currency_id" readonly>
								@else
									<select id="currency_id" type="text" class="form-control" name="currency_id" >
								@endif
									@foreach ($allCurrencies as $oneCurrency)
										<option value="{{ $oneCurrency->id }}" {{ (old('currency_id') == $oneCurrency->id) ? ' selected' : '' }}>{{ $oneCurrency->description . " (" . $oneCurrency->symbol . ")" }}</option>
									@endforeach
									</select>

								@if ($errors->has('currency_id'))
									<span class="help-block">
										<strong>{{ $errors->first('currency_id') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group">
							<label for="active" class="col-md-3 control-label">{{ trans('forms.Active') }}</label>

							<div class="col-md-1{{ $errors->has('active') ? ' has-error' : '' }}">
								<input id="active" type="checkbox" class="form-control" name="active" {{ old('active') ? "checked" : ""}}>

								@if ($errors->has('active'))
									<span class="help-block">
										<strong>{{ $errors->first('active') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group">
							<label for="description" class="col-md-3 control-label">{{ trans('forms.Description') }}</label>

							<div class="col-md-7{{ $errors->has('description') ? ' has-error' : '' }}">
							@if ($readonly)
								<input id="description" type="text" class="form-control" name="description" value="{{ old('description') }}" readonly>
							@else
								<input id="description" type="text" class="form-control" name="description" value="{{ old('description') }}" >
							@endif

								@if ($errors->has('description'))
									<span class="help-block">
										<strong>{{ $errors->first('description') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="col-md-2 col-md-offset-8">
							<button type="submit" class="btn btn-primary">
								<i class="fa fa-btn fa-floppy-o"></i> {{ $action }}
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('post-content')
@endsection
