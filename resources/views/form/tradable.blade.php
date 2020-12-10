@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<div class="panel panel-default">
				<div class="panel-heading">{{ $source['title'] }}</div>
				<div class="panel-body">
					<form class="form-horizontal" role="form" method="POST" action="{{ $source['post_url'] }}">
						{{ csrf_field() }}

						<div class="form-group{{ $errors->has('model') ? ' has-error' : '' }}">
							<label for="model" class="col-md-4 control-label">{{ trans('forms.Model') }}&nbsp;/&nbsp;{{ trans('forms.SKU') }}</label>

							<div class="col-md-6">
								<input id="model" type="text" class="form-control" name="model" value="{{ old('model') }}" {{ $read['model'] ? "readonly" : ""}}>

								@if ($errors->has('model'))
									<span class="help-block">
										<strong>{{ $errors->first('model') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('description') ? ' has-error' : '' }}">
							<label for="description" class="col-md-4 control-label">{{ trans('forms.Description') }}</label>

							<div class="col-md-6">
								<input id="description" type="text" class="form-control" name="description" value="{{ old('description') }}">

								@if ($errors->has('description'))
									<span class="help-block">
										<strong>{{ $errors->first('description') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('productid') ? ' has-error' : '' }}">
							<label for="productid" class="col-md-4 control-label">UPC&nbsp;/&nbsp;EAN</label>

							<div class="col-md-6">
								<input id="productid" type="text" class="form-control" name="productid" value="{{ old('productid') }}">

								@if ($errors->has('productid'))
									<span class="help-block">
										<strong>{{ $errors->first('productid') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('phaseout') ? ' has-error' : '' }}">
							<label for="phaseout" class="col-md-4 control-label">{{ trans('forms.Phasing-out') }}</label>

							<div class="col-md-1">
								<input id="phaseout" type="checkbox" class="form-control" name="phaseout" {{ old('phaseout') ? "checked" : ""}}>

								@if ($errors->has('phaseout'))
									<span class="help-block">
										<strong>{{ $errors->first('phaseout') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('itemtype') ? ' has-error' : '' }}">
							<label for="itemtype" class="col-md-4 control-label">{{ trans('forms.Item type') }}</label>

							<label for="itemtype" class="col-md-2 control-label">{{ trans('forms.Stockable') }}</label>
							<div class="col-md-1">
								<input id="itemtype" type="radio" class="form-control" name="itemtype" value="stockable" {{ (old('itemtype') == "stockable") ? "checked" : ""}}>
							</div>
							<label for="itemtype" class="col-md-2 control-label">{{ trans('forms.Expendable') }}</label>
							<div class="col-md-1">
								<input id="itemtype" type="radio" class="form-control" name="itemtype" value="expendable" {{ (old('itemtype') == "expendable") ? "checked" : ""}}>
							</div>

								@if ($errors->has('itemtype'))
									<span class="help-block">
										<strong>{{ $errors->first('itemtype') }}</strong>
									</span>
								@endif
						</div>

						<div class="form-group{{ $errors->has('forecast') ? ' has-error' : '' }}">
							<label for="phaseout" class="col-md-4 control-label">{{ trans('forms.Forecastable') }}</label>

							<div class="col-md-1">
								<input id="forecast" type="checkbox" class="form-control" name="forecast" {{ old('forecast') ? "checked" : ""}}>

								@if ($errors->has('forecast'))
									<span class="help-block">
										<strong>{{ $errors->first('forecast') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('account') ? ' has-error' : '' }}">
							<label for="account" class="col-md-4 control-label">{{ trans('forms.Expense account') }}</label>

							<div class="col-md-6">
								<select id="account" class="form-control" name="account" >
									<option value="0" {{ (old('account') == 0) ? "selected" : ""}}>{{ trans('forms.Inventory account') }}</option>
								@foreach ($account as $id => $display)
									<option value="{{ $id }}" {{ (old('account') == $id) ? "selected" : ""}}>{{ $display }}</option>
								@endforeach
								</select>

								@if ($errors->has('account'))
									<span class="help-block">
										<strong>{{ $errors->first('account') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('active') ? ' has-error' : '' }}">
							<label for="active" class="col-md-4 control-label">{{ trans('forms.Active') }}</label>

							<div class="col-md-1">
								<input id="active" type="checkbox" class="form-control" name="active" {{ old('active') ? "checked" : ""}}>

								@if ($errors->has('active'))
									<span class="help-block">
										<strong>{{ $errors->first('active') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('serial_pattern') ? ' has-error' : '' }}">
							<label for="contact" class="col-md-4 control-label">{{ trans('forms.Serial pattern') }}</label>

							<div class="col-md-6">
								<input id="serial_pattern" type="text" class="form-control" name="serial_pattern" placeholder="regular expression of serial number" value="{{ old('serial_pattern') }}">

								@if ($errors->has('serial_pattern'))
									<span class="help-block">
										<strong>{{ $errors->first('serial_pattern') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('supplier') ? ' has-error' : '' }}">
							<label for="email" class="col-md-4 control-label">{{ trans('forms.Supplier') }}</label>

							<div class="col-md-6">
								<select id="supplier" class="form-control" name="supplier">
								@foreach ($supplier as $id => $display)
									<option value="{{ $id }}" {{ (old('supplier') == $id) ? "selected" : ""}}>{{ $display }}</option>
								@endforeach
								</select>

								@if ($errors->has('supplier'))
									<span class="help-block">
										<strong>{{ $errors->first('supplier') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group">
							<div class="col-md-4{{ ($errors->has('unit_length') || $errors->has('unit_width') || $errors->has('unit_height')) ? ' has-error' : '' }}">
								<label for="phone" style="padding-right:0px;" class="col-md-12 control-label">{{ trans('forms.Unit dimension') }}</label>
							</div>

							<div class="col-md-2{{ $errors->has('unit_length') ? ' has-error' : '' }}">
								<input id="unit_length" type="number" min="0.00" step="0.01" class="form-control text-right" name="unit_length" placeholder="length" value="{{ old('unit_length') ?: "0" }}">{{ $misc['length'] }}

								@if ($errors->has('unit_length'))
									<span class="help-block">
										<strong>{{ $errors->first('unit_length') }}</strong>
									</span>
								@endif
							</div>

							<div class="col-md-2{{ $errors->has('unit_width') ? ' has-error' : '' }}">
								<input id="unit_width" type="number" min="0.00" step="0.01" class="form-control text-right" name="unit_width" placeholder="width" value="{{ old('unit_width') ?: "0" }}">{{ $misc['length'] }}

								@if ($errors->has('unit_width'))
									<span class="help-block">
										<strong>{{ $errors->first('unit_width') }}</strong>
									</span>
								@endif
							</div>

							<div class="col-md-2{{ $errors->has('unit_height') ? ' has-error' : '' }}">
								<input id="unit_height" type="number" min="0.00" step="0.01" class="form-control text-right" name="unit_height" placeholder="height" value="{{ old('unit_height') ?: "0" }}">{{ $misc['length'] }}

								@if ($errors->has('unit_height'))
									<span class="help-block">
										<strong>{{ $errors->first('unit_height') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('unit_weight') ? ' has-error' : '' }}">
							<label for="unit_weight" class="col-md-4 control-label">{{ trans('forms.Unit weight') }}</label>

							<div class="col-md-2">
								<input id="unit_weight" type="number" min="0.00" step="0.01" class="form-control text-right" name="unit_weight" value="{{ old('unit_weight') ?: "0" }}">{{ $misc['weight'] }}

								@if ($errors->has('unit_weight'))
									<span class="help-block">
										<strong>{{ $errors->first('unit_weight') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('per_carton') ? ' has-error' : '' }}">
							<label for="per_carton" class="col-md-4 control-label">{{ trans('forms.Unit per carton') }}</label>

							<div class="col-md-2">
								<input id="per_carton" type="number" min="0" step="1" class="form-control text-right" name="per_carton" value="{{ old('per_carton') ?: "0" }}">

								@if ($errors->has('per_carton'))
									<span class="help-block">
										<strong>{{ $errors->first('per_carton') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group">
							<div class="col-md-4{{ ($errors->has('carton_length') || $errors->has('carton_width') || $errors->has('carton_height')) ? ' has-error' : '' }}">
								<label for="phone" style="padding-right:0px;" class="col-md-12 control-label">{{ trans('forms.Carton dimension') }}</label>
							</div>

							<div class="col-md-2{{ $errors->has('carton_length') ? ' has-error' : '' }}">
								<input id="carton_length" type="number" min="0.00" step="0.01" class="form-control text-right" name="carton_length" placeholder="length" value="{{ old('carton_length') ?: "0" }}">{{ $misc['length'] }}

								@if ($errors->has('carton_length'))
									<span class="help-block">
										<strong>{{ $errors->first('carton_length') }}</strong>
									</span>
								@endif
							</div>

							<div class="col-md-2{{ $errors->has('carton_width') ? ' has-error' : '' }}">
								<input id="carton_width" type="number" min="0.00" step="0.01" class="form-control text-right" name="carton_width" placeholder="width" value="{{ old('carton_width') ?: "0" }}">{{ $misc['length'] }}

								@if ($errors->has('carton_width'))
									<span class="help-block">
										<strong>{{ $errors->first('carton_width') }}</strong>
									</span>
								@endif
							</div>

							<div class="col-md-2{{ $errors->has('carton_height') ? ' has-error' : '' }}">
								<input id="carton_height" type="number" min="0.00" step="0.01" class="form-control text-right" name="carton_height" placeholder="height" value="{{ old('carton_height') ?: "0" }}">{{ $misc['length'] }}

								@if ($errors->has('carton_height'))
									<span class="help-block">
										<strong>{{ $errors->first('carton_height') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('carton_weight') ? ' has-error' : '' }}">
							<label for="carton_weight" class="col-md-4 control-label">{{ trans('forms.Carton weight') }}</label>

							<div class="col-md-2">
								<input id="carton_weight" type="number" min="0.00" step="0.01" class="form-control text-right" name="carton_weight" value="{{ old('carton_weight') ?: "0" }}">{{ $misc['weight'] }}

								@if ($errors->has('carton_weight'))
									<span class="help-block">
										<strong>{{ $errors->first('carton_weight') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('per_pallet') ? ' has-error' : '' }}">
							<label for="per_pallet" class="col-md-4 control-label">{{ trans('forms.Carton per pallet') }}</label>

							<div class="col-md-2">
								<input id="per_pallet" type="number" min="0" step="1" class="form-control text-right" name="per_pallet" value="{{ old('per_pallet') ?: "0" }}">

								@if ($errors->has('per_pallet'))
									<span class="help-block">
										<strong>{{ $errors->first('per_pallet') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('lead_day') ? ' has-error' : '' }}">
							<label for="lead_day" class="col-md-4 control-label">{{ trans('forms.Lead days') }}</label>

							<div class="col-md-2">
								<input id="lead_day" type="number" min="0" step="1" class="form-control text-right" name="lead_day" value="{{ old('lead_day') ?: "0" }}">

								@if ($errors->has('lead_day'))
									<span class="help-block">
										<strong>{{ $errors->first('lead_day') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('content') ? ' has-error' : '' }}">
							<label for="content" class="col-md-4 control-label">{{ trans('forms.Content') }}</label>

							<div class="col-md-6">
								<textarea id="content" class="form-control" name="content" rows=4>{{ old('content') }}</textarea>

								@if ($errors->has('content'))
									<span class="help-block">
										<strong>{{ $errors->first('content') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('country') ? ' has-error' : '' }}">
							<label for="country" class="col-md-4 control-label">{{ trans('forms.Manufacture origin') }}</label>

							<div class="col-md-6">
								<select id="country" class="form-control" name="country" >
								@foreach ($country as $abbr => $fullname)
									<option value="{{ $abbr }}" {{ (old('country') == $abbr) ? "selected" : ""}}>{{ $abbr }}&emsp;{{ $fullname }}</option>
								@endforeach
								</select>

								@if ($errors->has('country'))
									<span class="help-block">
										<strong>{{ $errors->first('country') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group">
							<div class="col-md-6 col-md-offset-4">
								<button type="submit" class="btn btn-primary">
									<i class="fa fa-btn fa-floppy-o"></i> {{ $source['action'] }}
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
