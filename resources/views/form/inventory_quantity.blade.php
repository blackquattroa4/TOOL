@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">
					{{ trans('accounting.Adjust consignment')}}
				</div>
				<div class="panel-body">
					<form class="form-horizontal" role="form" method="POST" action="{{ "/" . request()->path() }}">
						{{ csrf_field() }}

						<div class="form-group">
							<label for="entity" class="col-md-2 control-label">{{ trans('forms.Entity') }}</label>

							<div class="col-md-5">
								<select id="entity" class="form-control" name="entity" >
								@foreach ($suppliers as $supplier)
									<option value="{{ $supplier->id }}">{{ $supplier->name }}&emsp;({{ $supplier->code }})</option>
								@endforeach
								</select>

							@if ($errors->has('entity'))
								<span class="help-block">
									<strong>{{ $errors->first('entity') }}</strong>
								</span>
							@endif
							</div>
						</div>

						<div class="form-group">
							<label for="location" class="col-md-2 control-label">{{ trans('forms.Location') }}</label>

							<div class="col-md-3">
								<select id="location" class="form-control" name="location" >
								@foreach ($locations as $location)
									<option value="{{ $location->id }}">{{ $location->name }}</option>
								@endforeach
								</select>

							@if ($errors->has('location'))
								<span class="help-block">
									<strong>{{ $errors->first('location') }}</strong>
								</span>
							@endif
							</div>
						</div>

						<div class="form-group">
							<label for="recorddate" class="col-md-2 control-label">{{ trans('forms.Date') }}</label>

							<div class="col-md-3{{ $errors->has('recorddate') ? ' has-error' : '' }}">
								<div class="input-group date" data-provide="datepicker">
									<input id="recorddate" type="text" class="form-control" name="recorddate" value="{{ \App\Helpers\DateHelper::dbToGuiDate(date("Y-m-d")) }}" >
									<div class="input-group-addon">
										<span class="glyphicon glyphicon-calendar"></span>
									</div>
								</div>

							@if ($errors->has('recorddate'))
								<span class="help-block">
									<strong>{{ $errors->first('recorddate') }}</strong>
								</span>
							@endif
							</div>
						</div>

						<div class="form-group">
							<label for="notes" class="col-md-2 control-label">{{ trans('forms.Notes') }}</label>

							<div class="col-md-6{{ $errors->has('notes') ? ' has-error' : '' }}">
								<input id="notes" type="text" class="form-control" name="notes" value="" >

							@if ($errors->has('notes'))
								<span class="help-block">
									<strong>{{ $errors->first('notes') }}</strong>
								</span>
							@endif
							</div>
						</div>

						<hr/>

						<div class="form-group detail-line">
						</div>

						<div class="form-group">
							<div class="col-md-2 col-md-offset-10">
								<button type="submit" class="btn btn-primary">
									<i class="fa fa-btn fa-floppy-o"></i> {{ trans('forms.Submit') }}
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
		$(document).ready(function() {
			$('select#entity,select#location').bind('change', function() {
				$.ajax({
					type: 'GET',
					url: '/warehouse/consignment/ajax',
					data: {
							supplier : $('select#entity').val(),
							location : $('select#location').val(),
						},
					dataType: 'html',
					beforeSend: function(data) {
						$('div.detail-line').html('<div class="col-md-12"><i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i></div>');
					},
				}).done(function(data) {
					var htmlText = "";
					var result = JSON.parse(data);
					for (var idx in result) {
						htmlText += "<div class=\"col-md-3\">{{ trans('forms.Item') }}<input type=\"text\" id=\"product[" + result[idx]['id'] + "]\" class=\"form-control\" name=\"product[" + result[idx]['id'] + "]\" value=\"" + result[idx]['sku'] + "\" readonly></div><div class=\"col-md-2 col-md-offset-1\">{{ trans('forms.Unit price') }}<input id=\"cost[" + result[idx]['id'] + "]\" type=\"number\" style=\"text-align:right\" class=\"form-control\" min=\"0\" step=\"{{ pow(10, -$currency['fdigit']) }}\" name=\"cost[" + result[idx]['id'] + "]\" value=\"" + parseFloat(result[idx]['cost'], 10).toFixed({{ $currency['fdigit'] }}) + "\" ></input></div><div class=\"col-md-2\">{{ trans('forms.Balance') }}<input id=\"balance[" + result[idx]['id'] + "]\" type=\"number\" style=\"text-align:right\" class=\"form-control\" name=\"balance[" + result[idx]['id'] + "]\" value=\"" + parseInt(result[idx]['sum'], 10) + "\" readonly></input></div><div class=\"col-md-2\">{{ trans('forms.Quantity') }}<input id=\"quantity[" + result[idx]['id'] + "]\" type=\"number\" style=\"text-align:right\" min=\"-" + parseInt(result[idx]['sum'], 10) + "\" step=\"1\" class=\"form-control\" name=\"quantity[" + result[idx]['id'] + "]\" value=\"0\" onchange=\"document.getElementById('result[" + result[idx]['id'] + "]').value = parseInt(" + result[idx]['sum'] + ", 10) + parseInt(this.value, 10);\"></input></div><div class=\"col-md-2\">{{ trans('forms.Result') }}<input id=\"result[" + result[idx]['id'] + "]\" type=\"number\" style=\"text-align:right\" min=\"0\" step=\"1\" class=\"form-control\" name=\"result[" + result[idx]['id'] + "]\" value=\"" + parseInt(result[idx]['sum'], 10) + "\" readonly></input></div>";
					}
					$('div.detail-line').html(htmlText);
				}).fail(function(data) {
				}).always(function(data) {
				});
			});

			$('select#entity').trigger('change');
		});
	</script>
@endsection
