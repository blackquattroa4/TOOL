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
				<div class="panel-heading">{{ trans("product.Product update notice") }}</div>
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
									<option value="{{ $oneProduct['id'] }}" {{ old('product.'.$oneProduct['id']) ? " selected" : "" }}>{{ $oneProduct->uniqueTradable['sku'] }}&emsp;&emsp;({{ $oneProduct->supplier->code }})</option>
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
							<label for="summary" class="col-md-3 control-label">{{ trans('forms.Summary') }}</label>

							<div class="col-md-7{{ $errors->has('summary') ? ' has-error' : '' }}">
							@if ($readonly)
								<input id="summary" type="text" class="form-control" name="summary" value="{{ old('summary') }}" readonly>
							@else
								<input id="summary" type="text" class="form-control" name="summary" value="{{ old('summary') }}" >
							@endif

								@if ($errors->has('summary'))
									<span class="help-block">
										<strong>{{ $errors->first('summary') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<!-- <div class="form-group">
							<label for="description" class="col-md-3 control-label">{{ trans('document.Description') }}</label>

							<div class="col-md-7{{ $errors->has('description') ? ' has-error' : '' }}">
							@if ($readonly)
								<textarea id="description" col="50" type="text" class="form-control" name="description" disabled>{{ old('description') }}</textarea>
							@else
								<textarea id="description" col="50" type="text" class="form-control" name="description">{{ old('description') }}</textarea>
							@endif

								@if ($errors->has('description'))
									<span class="help-block">
										<strong>{{ $errors->first('description') }}</strong>
									</span>
								@endif
							</div>
						</div> -->

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
