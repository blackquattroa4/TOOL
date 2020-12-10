@extends('layouts.app')

@section('additional-style')
<style>
	.show-balance {
		position: fixed;
		bottom: 0em;
		left: 0px;
		text-decoration: none;
		color: #000000;
		background-color: rgba(255, 255, 255, 1.0);
		font-size: 15px;
		padding: 1em;
		display: none;
		z-index: 10;
	}
	#calculated_total {
		font-size: 38px;
		font-weight: bold;
		line-height: 38px;
	}
	.show-balance-hint {
		position: fixed;
		bottom: 0em;
		left: 0px;
		text-decoration: none;
		color: #000000;
		background-color: rgba(255, 255, 255, 0.0);
		font-size: 15px;
		padding: 1em;
		display: none;
		z-index: 10;
	}
</style>
@endsection

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div id="rowwindow" class="panel panel-default">
				<div class="panel-heading">
					<table width='100%'>
						<tr>
							<td><h4>{{ $phrases['title'] }}</h4></td>
							<td></td>
						</tr>
					</table>
				</div>

				<div class="panel-body">
					<form class="form-horizontal" role="form" method="POST" action="{{ $post_url }}" >
						{{ csrf_field() }}

							<div class="form-group">
								<label class="col-xs-2 col-sm-2 col-md-2 col-lg-2 control-label">{{ $phrases['direction'] }}</label>
								<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
									<input id="code" type="code" name="code" class="form-control" value="{{ $code }}" disabled/>
								</div>
								<label class="col-xs-2 col-sm-2 col-md-2 col-xs-offset-1 col-sm-offset-1 col-md-offset-1 control-label">{{ trans('forms.Date') }}</label>
								<div class="col-xs-3 col-sm-3 col-md-3{{ $errors->has('inputdate') ? ' has-error' : '' }}">
									<div class="input-group date" data-provide="datepicker">
										<input id="inputdate" type="text" class="form-control" name="inputdate" value="{{ old('inputdate') }}" >
										<div class="input-group-addon">
											<span class="glyphicon glyphicon-calendar"></span>
										</div>
									</div>
									@if ($errors->has('inputdate'))
										<p class="help-block"><strong>{{ $errors->first('inputdate') }}</strong></p>
									@endif
								</div>
						</div>

							<div class="form-group">
								<label class="col-xs-2 col-sm-2 col-md-2 control-label">{{ trans('forms.Bank account') }}</label>
								<div class="col-xs-5 col-sm-5 col-md-5{{ $errors->has('bank_account') ? ' has-error' : '' }}">
									<select id="bank_account" name="bank_account" class="form-control" >
										<option value="">{{ trans('forms.Select an account') }}</option>
										@foreach ($bankaccount as $oneAccount)
											<option value="{{ $oneAccount['id'] }}">{{ $oneAccount['description'] }}</option>
										@endforeach
									</select>
									@if ($errors->has('bank_account'))
										<p class="help-block"><strong>{{ $errors->first('bank_account') }}</strong></p>
									@endif
								</div>
							</div>

							<div class="form-group">
								<label class="col-xs-2 col-sm-2 col-md-2 control-label">{{ trans('forms.Reference') }}</label>
								<div class="col-xs-6 col-sm-6 col-md-6{{ $errors->has('reference') ? ' has-error' : '' }}">
									<input id="reference" name="reference" class="form-control" value="{{ old('reference') }}" />
									@if ($errors->has('reference'))
										<p class="help-block"><strong>{{ $errors->first('reference') }}</strong></p>
									@endif
								</div>
							</div>

						<hr />

						<table class="table table-striped">
							<tr>
								<th class="col-xs-1 col-sm-1 col-md-1">{{ trans('forms.Invoice') }}</th>
								<th class="col-xs-2 col-sm-2 col-md-2">{{ trans('forms.Date') }}</th>
								<th class="col-xs-2 col-sm-2 col-md-2">{{ trans('forms.Due') }}</th>
								<th class="col-xs-3 col-sm-3 col-md-3">{{ trans('forms.Summary') }}</th>
								<th class="col-xs-2 col-sm-2 col-md-2" style="text-align:right;">{{ trans('forms.Balance') }}</th>
								<th class="col-xs-2 col-sm-2 col-md-2" style="text-align:right;">{{ trans('forms.Apply') }}</th>
							</tr>
						@foreach ($transactables as $oneTransactable)
							<tr>
								<td>{{ $oneTransactable['title'] }}</td>
								<td>{{ $oneTransactable['incur'] }}</td>
								<td>{{ $oneTransactable['due'] }}</td>
								<td>{{ $oneTransactable['summary'] }}</td>
								<td class="text-right" title="Total {{ $oneTransactable['total'] }}">
								@if (!$oneTransactable['credit'])
									<span class="badge badge-info">{{ trans('forms.Debit') }}</span>&emsp;
								@endif
									{{ $oneTransactable['balance'] }}
								</td>
								<td>
									<div style="margin:1px;" class="form-group{{ $errors->has('transactable.' . $oneTransactable['id']) ? ' has-error' : '' }}">
										<input id="transactable[{{ $oneTransactable['id'] }}]" name="transactable[{{ $oneTransactable['id'] }}]" class="form-control text-right span1{{ ($oneTransactable['credit']) ? ' credit' : '' }}" value="{{ sprintf("%0.".$currencyFormat['fdigit']."f", old('transactable.'.$oneTransactable['id'])) }}" min="{{ sprintf("%0.".$currencyFormat['fdigit']."f", (($oneTransactable['balance_raw'] > 0) ? 0 : $oneTransactable['balance_raw'])) }}" step="{{ $currencyFormat['min'] }}" max="{{ sprintf("%0.".$currencyFormat['fdigit']."f", (($oneTransactable['balance_raw'] > 0) ? $oneTransactable['balance_raw'] : 0)) }}" type="number" />
										@if ($errors->has('transactable.' . $oneTransactable['id']))
											<p class="help-block"><strong>{{ $errors->first('transactable.' . $oneTransactable['id']) }}</strong></p>
										@endif
									</div>
								</td>
							</tr>
						@endforeach
						</table>

						<div class="col-xs-12 col-sm-12 col-md-12 text-center">
							<button type="submit" class="btn btn-primary pull-right"><i class="fa fa-btn fa-floppy-o"></i> {{ trans('forms.Update') }}</button>
						</div>

					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<a href='#' class='show-balance-hint'>
	<img src="{{ asset('images/sum-512.png') }}" height='40' width='40'></img>
</a>
<div class='show-balance'>
	<p id='calculated_total'>{{ trans('forms.Amount issued') }}<br>{{ $initialAmount }}</p>
</div>

@endsection

@section('post-content')
	<script src="/js/ShowHideHint.js"></script>
	<script type="text/javascript">

		$('input[type=number]').bind("change", function() {

			// calculate available credit
			var total = 0;
			$('[name^="amount_"]').each(function () {
				total += parseFloat($(this).val() * 1);
			});
			var applying = 0;
			$('[name^="transactable\["]').each(function () {
				applying += parseFloat($(this).val() * ($(this).hasClass('credit') ? -1 : 1));
			});
			$('#calculated_total').html('{{ trans("forms.Amount issued") }}<br>' +
								(total - applying).toLocaleString('{{ $currencyFormat['regex'] }}', { style: 'currency', currency: '{{ $currencyFormat['symbol'] }}' }) +
								'<br>');

			// warn if greater than max
			if (($(this).val()*1) > ($(this).attr('max')*1)) {
				$(this).parent().addClass("has-error");
				if ($(this).parent().children().length == 1) {
					$(this).parent().append('<p class="help-block"><strong>out of range</strong></p>');
				}
				$('[type="submit"]').attr('disabled', 'disabled');
				return false;
			}
			// warn if less than min
			if (($(this).val()*1) < ($(this).attr('min')*1)) {
				$(this).parent().addClass("has-error");
				if ($(this).parent().children().length == 1) {
					$(this).parent().append('<p class="help-block"><strong>out of range</strong></p>');
				}
				$('[type="submit"]').attr('disabled', 'disabled');
				return false;
			}

			$(this).parent().removeClass("has-error");
			if ($(this).parent().children().length > 1) {
				$(this).parent().children().last().remove();
			}
			if ($('.has-error').length == 0) {
				$('[type="submit"]').removeAttr('disabled');
			}
			hideThenShowHint('.show-balance', '.show-balance-hint');
			return false;
		});

		$(document).ready(function() {
			hideThenShowHint('.show-balance', '.show-balance-hint');
			$(window).mousemove(function(event) {
				showOrHideHint(event, '.show-balance', '.show-balance-hint');
			});
		});
	</script>
@endsection
