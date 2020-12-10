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
							<label for="param_key" class="col-md-3 control-label">{{ trans('forms.Key') }}</label>

							<div class="col-md-7{{ $errors->has('param_key') ? ' has-error' : '' }}">
							@if ($readonly)
								<input id="param_key" type="text" class="form-control" name="param_key" value="{{ old('param_key') }}" readonly>
							@else
								<input id="param_key" type="text" class="form-control" name="param_key" value="{{ old('param_key') }}" >
							@endif

								@if ($errors->has('param_key'))
									<span class="help-block">
										<strong>{{ $errors->first('param_key') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group">
							<label for="param_value" class="col-md-3 control-label">{{ trans('forms.Value') }}</label>

							<div class="col-md-7{{ $errors->has('param_value') ? ' has-error' : '' }}">
							@if ($readonly)
								<input id="param_value" type="text" class="form-control" name="param_value" value="{{ old('param_value') }}" readonly>
							@else
								<input id="param_value" type="text" class="form-control" name="param_value" value="{{ old('param_value') }}" >
							@endif

								@if ($errors->has('param_value'))
									<span class="help-block">
										<strong>{{ $errors->first('param_value') }}</strong>
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
