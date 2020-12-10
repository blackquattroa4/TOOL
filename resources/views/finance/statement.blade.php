@extends('layouts.app')

@section('additional-style')
<style>
	.financial-statement div {
		padding: 20px;
	}

	.financial-statement ul {
		list-style-type: none;
	}

	.list-group-item {
		border: 0px; !important
	}
</style>
@endsection

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<!-- retain earning modal -->
			<div class="modal fade" id="retainEarningModal" tabindex="-1" role="dialog" aria-labelledby="retainEarningModalLabel" data-backdrop="static" aria-hidden="false">
				<div class="modal-dialog" role="">
					<div class="modal-content">
						<div class="modal-header">
							<table style="width:100%;"><tr>
								<td>
									{{ trans('finance.Retain earning') }}
								</td>
							</tr></table>
						</div>
						<div class="modal-body">
							<form id="retain_earning_form" class="form-horizontal" role="form" method="POST" action="{{ url('/finance/earning/ajax') }}" >
								{{ csrf_field() }}

								<div class="form-group">
									<label class="col-xs-2 col-sm-2 col-md-2 control-label">{{ trans('forms.Period') }}</label>
									<div class="col-xs-4 col-sm-4 col-md-4{{ $errors->has('re_year') ? ' has-error' : '' }}">
										<select id="re_year" name="re_year" class="form-control" >
											@foreach (range(date("Y")-10, date("Y")) as $year)
												<option value="{{ $year }}" {{ ($year == date("Y")) ? " selected" : "" }}>{{ $year }}</option>
											@endforeach
										</select>
										@if ($errors->has('re_year'))
											<p class="help-block"><strong>{{ $errors->first('re_year') }}</strong></p>
										@endif
									</div>
									<div class="col-xs-4 col-sm-4 col-md-4{{ $errors->has('re_month') ? ' has-error' : '' }}">
										<select id="re_month" name="re_month" class="form-control" >
											@foreach (range(1,12) as $idx)
												@if (!empty($idx))
													<option value="{{ $idx }}" {{ ($idx == date("m")) ? " selected" : "" }}>{{ trans("forms.".date("F", strtotime("2000-".$idx."-01"))) }}</option>
												@endif
											@endforeach
										</select>
										@if ($errors->has('re_month'))
											<p class="help-block"><strong>{{ $errors->first('re_month') }}</strong></p>
										@endif
									</div>
								</div>
								<div class="form-group">
									<label class="col-xs-2 col-sm-2 col-md-2 control-label">{{ trans('finance.Amount') }}</label>
									<div class="col-xs-4 col-sm-4 col-md-4{{ $errors->has('re_amount') ? ' has-error' : '' }}">
										<input id="re_amount" name="re_amount" class="form-control text-right" type="number" value="{{ sprintf("%0.".$currency['fdigit']."f", 0) }}" step="{{ $currency['min'] }}" />
										@if ($errors->has('re_year'))
											<p class="help-block"><strong>{{ $errors->first('re_year') }}</strong></p>
										@endif
									</div>
								</div>
							</form>
						</div>
						<div class="modal-footer">
							<div class="text-right hide"><i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i></div>
							<button type="button" class="btn btn-primary" id="finalize_earning">{{ trans('forms.Submit') }}</button>
							<button type="button" class="btn btn-primary hide" id="close_earning" data-dismiss="modal">{{ trans('forms.Close') }}</button>
							<button type="button" class="btn btn-primary" id="cancel_earning" data-dismiss="modal">{{ trans('forms.Cancel') }}</button>
						</div>
					</div>
				</div>
			</div>

			<div class="panel panel-default">
				<div class="panel-heading">
					<table width="100%">
						<tr>
							<td>{{ trans('finance.Financial statement') }}</td>
							<td>
							</td>
						</tr>
					</table>
				</div>
				<div class="panel-body">

					<button class="btn btn-info pull-right" data-toggle="modal" data-target="#retainEarningModal"><span class="fa fa-pencil-square-o"></span>&nbsp;{{ trans('finance.Retain earning') }}</button>

					<form class="form-horizontal" role="form" method="POST" action="">
						<div class="form-group">
							<label for="period" class="col-md-1 control-label">{{ trans('forms.Period') }}</label>

							<div class="col-md-2{{ $errors->has('chosen_year') ? ' has-error' : '' }}">
								<select id="chosen_year" class="form-control" name="chosen_year">
								@foreach (range(date("Y")-10, date("Y")) as $year)
									<option value="{{ $year }}" {{ ($year == date("Y")) ? " selected" : "" }}>{{ $year }}</option>
								@endforeach
								</select>

								@if ($errors->has('chosen_year'))
									<span class="help-block">
										<strong>{{ $errors->first('chosen_year') }}</strong>
									</span>
								@endif
							</div>

							<div class="col-md-2{{ $errors->has('chosen_month') ? ' has-error' : '' }}">
								<select id="chosen_month" class="form-control" name="chosen_month">
									<option value="0">---</option>
								@foreach (range(1,12) as $idx)
									<option value="{{ $idx }}" {{ ($idx == date("m")) ? " selected" : "" }}>{{ trans("forms." . date("F", strtotime("2000-".$idx."-01"))) }}</option>
								@endforeach
								</select>

								@if ($errors->has('chosen_month'))
									<span class="help-block">
										<strong>{{ $errors->first('chosen_month') }}</strong>
									</span>
								@endif
							</div>

							<label for="currency" class="col-md-1 control-label">{{ trans('forms.Currency') }}</label>

							<div class="col-md-2{{ $errors->has('currency') ? ' has-error' : '' }}">
								<select id="currency" class="form-control" name="currency">
									<option value="">{{ trans("forms.Default") }}</option>
								@foreach (\App\Currency::where([['active', 1], ['id', '<>', \App\TaxableEntity::theCompany()->currency_id]])->get() as $currency)
									<option value="{{ $currency->id }}">{{ $currency->symbol }}</option>
								@endforeach
								</select>

								@if ($errors->has('currency'))
									<span class="help-block">
										<strong>{{ $errors->first('currency') }}</strong>
									</span>
								@endif
							</div>

							<a id="refresh" class="btn btn-info"><span class="fa fa-refresh"></span>&nbsp;{{ trans('forms.Update') }}</a>
						</div>
					</form>

					<ul class="nav nav-tabs">
						<!-- populated by AJAX -->
					</ul>
					<div class="tab-content financial-statement">
						<!-- populated by AJAX -->
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('post-content')
	<script type="text/javascript">
		$('#close_earning,#cancel_earning').bind('click', function() {
			$('select#re_year').val({{ date('Y') }});
			$('select#re_month').val({{ date('m') }});
			$('input#re_amount').val({{ sprintf("%0.".$currency['fdigit']."f", 0) }});
			$('div#retainEarningModal div.modal-footer div').addClass('hide');
			$('div#retainEarningModal div.modal-footer button#finalize_earning').removeClass('hide');
			$('div#retainEarningModal div.modal-footer button#close_earning').addClass('hide');
			$('div#retainEarningModal div.modal-footer button#cancel_earning').removeClass('hide');
		});

		$('#finalize_earning').bind('click', function() {
			$.ajax({
				type: 'POST',
				url: '/finance/earning/ajax',
				data: {
						_token: "{{ csrf_token() }}",
						year : $('select#re_year').val(),
						month : $('select#re_month').val(),
						amount: $('input#re_amount').val(),
					},
				dataType: 'html',
				beforeSend: function(data) {
					// clear out error messages
					$('#re_year,#re_month,#re_amount').parent().removeClass('has-error');
					$('#re_year,#re_month,#re_amount').parent().find('p').remove();
					// clear out 'submit' and 'cancel' button
					$('div#retainEarningModal div.modal-footer div').removeClass('hide');
					$('div#retainEarningModal div.modal-footer button#finalize_earning').addClass('hide');
					$('div#retainEarningModal div.modal-footer button#close_earning').addClass('hide');
					$('div#retainEarningModal div.modal-footer button#cancel_earning').addClass('hide');
				},
			}).done(function(data) {
				var result = JSON.parse(data);
				if (result['result']) {
					$('div#retainEarningModal div.modal-footer div').addClass('hide');
					$('div#retainEarningModal div.modal-footer button#finalize_earning').addClass('hide');
					$('div#retainEarningModal div.modal-footer button#close_earning').removeClass('hide');
					$('div#retainEarningModal div.modal-footer button#cancel_earning').addClass('hide');
				} else {
					var errors = result['errors'];
					for (element in result['errors']) {
						$('#'+element).parent().addClass('has-error');
						$('#'+element).parent().append('<p class="help-block"><strong>' + result['errors'][element] + '</strong></p>');
					}
					$('div#retainEarningModal div.modal-footer div').addClass('hide');
					$('div#retainEarningModal div.modal-footer button#finalize_earning').removeClass('hide');
					$('div#retainEarningModal div.modal-footer button#close_earning').addClass('hide');
					$('div#retainEarningModal div.modal-footer button#cancel_earning').removeClass('hide');
				}
			}).fail(function(data) {
				$('div#retainEarningModal div.modal-footer div').addClass('hide');
				$('div#retainEarningModal div.modal-footer button#finalize_earning').removeClass('hide');
				$('div#retainEarningModal div.modal-footer button#close_earning').addClass('hide');
				$('div#retainEarningModal div.modal-footer button#cancel_earning').removeClass('hide');
			});
		});

		$('#chosen_year').bind('change', function() {
			$('.nav.nav-tabs').html("");
			$('.tab-content.financial-statement').html("");
		});

		$('#chosen_month').bind('change', function() {
			$('.nav.nav-tabs').html("");
			$('.tab-content.financial-statement').html("");
		});

		// Ajax call to pull reports
		$('#refresh').bind('click', function() {
			$.ajax({
				type: 'GET',
				url: '/finance/statement/ajax',
				data: {
						year : $('select#chosen_year').val(),
						month : $('select#chosen_month').val(),
						currency : $('select#currency').val(),
					},
				dataType: 'html',
				beforeSend: function(data) {
					$('.nav.nav-tabs').html("");
					$('.tab-content.financial-statement').html('<div class=\"text-center\"><i class=\"fa fa-spinner fa-pulse fa-2x fa-fw\"></i></div>');
				},
			}).done(function(data) {
				var statements = JSON.parse(data);
				var header = "";
				var content = "";
				for(statement in statements) {
					header += "<li><a data-toggle=\"tab\" href=\"#" + statement + "\">" + statements[statement]['title'] + "</a></li>";
					content += "<div id=\"" + statement + "\" class=\"tab-pane fade in\"><div><ul class=\"list-group\">";
					for (item in statements[statement]["items"]) {
						content += "<li class=\"list-group-item justify-content-between\">" + statements[statement]["items"][item]["title"] + "</li><ul>";
						for (subItem in statements[statement]["items"][item]["items"]) {
							content += "<li class=\"list-group-item justify-content-between\">" + statements[statement]["items"][item]["items"][subItem]["title"] + "<span class=\"pull-right\">" + statements[statement]["items"][item]["items"][subItem]['amount'] + "</span></li>";
						}
						content += "</ul><li class=\"list-group-item justify-content-between\">&emsp;<span class=\"pull-right\">{{ trans('forms.Subtotal') }}&emsp;" + statements[statement]["items"][item]["amount"] + "</span></li>";
					}
					content += "</ul></div><a href=\"{{ url('/finance/statement/print') }}?year=" + $('select#chosen_year').val() + "&month=" + $('select#chosen_month').val() + "\" class=\"btn btn-primary pull-right\">{{ trans('forms.View PDF') }}</a></div>";
				}
				$('.nav.nav-tabs').html(header);
				$('.tab-content.financial-statement').html(content);
				$('.nav.nav-tabs li:first-child a').click();
			}).fail(function(data) {
				$('.tab-content.financial-statement').html('<div class=\"text-center\">{{ trans('finance.Statement cannot be generated') }}</div>');
			});
		});
	</script>
@endsection
