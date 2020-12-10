@extends('layouts.app')

@section('additional-style')
<style>
</style>
@endsection

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<div class="panel panel-default">
				<div class="panel-heading">{{ trans("product.Product FAQ") }}</div>
				<div class="panel-body">
					<form class="form-horizontal" role="form" enctype="multipart/form-data" method="POST" action="">
						{{ csrf_field() }}

						<div class="form-group">
							<label for="product[]" class="col-md-3 control-label">{{ trans('forms.Product') }}</label>

							<div class="col-md-7{{ $errors->has('product') ? ' has-error' : '' }}">
							@if ($readonly)
								<select id="product[]" class="form-control" name="product[]" multiple="multiple" size="{{ count($product) }}" disabled>
							@else
								<select id="product[]" class="form-control" name="product[]" multiple="multiple" size="{{ count($product) }}">
							@endif
								@foreach ($product as $oneProduct)
									<option value="{{ $oneProduct['id'] }}" {{ old('product.'.$oneProduct['id']) ? " selected" : "" }}>{{ $oneProduct->uniqueTradable['sku'] }}</option>
								@endforeach
								</select>

								@if ($errors->has('product'))
									<span class="help-block">
										<strong>{{ $errors->first('product') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group">
							<label for="question" class="col-md-3 control-label">{{ trans('product.Question') }}</label>

							<div class="col-md-7{{ $errors->has('question') ? ' has-error' : '' }}">
							@if ($readonly)
								<input id="question" type="text" class="form-control" name="question" value="{{ old('question') }}" readonly>
							@else
								<input id="question" type="text" class="form-control" name="question" value="{{ old('question') }}" >
							@endif

								@if ($errors->has('question'))
									<span class="help-block">
										<strong>{{ $errors->first('question') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group">
							<label for="answer" class="col-md-3 control-label">{{ trans('product.Answer') }}</label>

							<div class="col-md-7{{ $errors->has('answer') ? ' has-error' : '' }}">
							@if ($readonly)
								<textarea id="answer" col="50" type="text" class="form-control" name="answer" disabled>{{ old('answer') }}</textarea>
							@else
								<textarea id="answer" col="50" type="text" class="form-control" name="answer">{{ old('answer') }}</textarea>
							@endif

								@if ($errors->has('answer'))
									<span class="help-block">
										<strong>{{ $errors->first('answer') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group">
							<label for="thefile" class="col-md-3 control-label">{{ trans('document.File') }}</label>

							<div class="col-md-7{{ $errors->has('thefile') ? ' has-error' : '' }}">
								<label class="btn btn-info" for="thefile">
								@if ($readonly)
									<a href="{{ url('/document/view').'/'.old('id') }}">
										<span id="download-button[{{ old('id') }}]">{{ old('filename') }}</span>
									</a>
								@else
									<!-- assuming value='C:\fakepath\filename' -->
									<input id="thefile" name="thefile" type="file" style="display:none;" onchange="$('#upload-selector-label').html( ($(this).val().substring($(this).val().lastIndexOf( '\\' ) + 1)) );" />
									<span id="upload-selector-label" >{{ trans('tool.Browse file') }}</span>
								@endif
								</label>

							@if ($errors->has('thefile'))
								<span class="help-block">
									<strong>{{ $errors->first('thefile') }}</strong>
								</span>
							@endif
							</div>
						</div>

						<div class="form-group">
							<div class="col-md-2 col-md-offset-8">
								<button type="submit" class="btn btn-primary">
									<i class="fa fa-btn fa-floppy-o"></i> {{ $action }}
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('post-content')
	<script type="text/javascript">
	</script>
@endsection
