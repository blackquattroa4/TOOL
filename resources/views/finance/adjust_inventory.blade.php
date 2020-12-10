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

			<div class="panel panel-default">
				<div class="panel-heading">
					<table width="100%">
						<tr>
							<td>{{ trans('forms.Adjust inventory') }}</td>
							<td>
							</td>
						</tr>
					</table>
				</div>
				<div class="panel-body">

					<form class="form-horizontal" role="form" method="POST" action="">
						{{ csrf_field() }}

						<table id="inventorytable" class="table table-striped table-bordered" cellspacing="0" width="100%">
							<thead>
								<tr>
									<td>{{ trans('forms.SKU') }}</td>
									<td>{{ trans('forms.Location') }}</td>
									<td>{{ trans('forms.Unit price') }}</td>
									<td>{{ trans('forms.Quantity') }}</td>
									<td>{{ trans('forms.Subtotal') }}</td>
								</tr>
							</thead>
							<tfoot>
								<tr>
									<td>{{ trans('forms.SKU') }}</td>
									<td>{{ trans('forms.Location') }}</td>
									<td>{{ trans('forms.Unit price') }}</td>
									<td>{{ trans('forms.Quantity') }}</td>
									<td>{{ trans('forms.Subtotal') }}</td>
								</tr>
							</tfoot>
							<tbody>
							@foreach ($inventory as $item)
								<tr>
									<td>{{ $item['sku'] }}</td>
									<td>{{ $item['location'] }}</td>
									<td>
										<input id="old_cost[{{ $item['location_id'] }}][{{ $item['sku_id'] }}]" name="old_cost[{{ $item['location_id'] }}][{{ $item['sku_id'] }}]" type="hidden" value="{{ $item['unit_price'] }}" />
										<input id="unit_price[{{ $item['location_id'] }}][{{ $item['sku_id'] }}]" name="unit_price[{{ $item['location_id'] }}][{{ $item['sku_id'] }}]" class="form-control col-md-1" style="text-align:right;" type="number" min="0" step="{{ $currency['min'] }}" value="{{ $item['unit_price'] }}" data-original="{{ $item['unit_price'] }}" data-quantity="{{ sprintf(env('APP_QUANTITY_FORMAT'), $item['quantity']) }}" />
									</td>
									<td style="text-align:right;">
										{{ sprintf(env('APP_QUANTITY_FORMAT'), $item['quantity']) }}
										<input id="quantity[{{ $item['location_id'] }}][{{ $item['sku_id'] }}]" name="quantity[{{ $item['location_id'] }}][{{ $item['sku_id'] }}]" type="hidden" value="{{ sprintf(env('APP_QUANTITY_FORMAT'), $item['quantity']) }}" />
									</td>
									<td style="text-align:right;">{{ $item['amount'] }}</td>
								</tr>
							@endforeach
							</tbody>
						</table>
						<button class="btn btn-info pull-right" type="submit" disabled><i class="fa fa-btn fa-floppy-o"></i> {{ trans("forms.Submit")}}</button>
						<div class="col-md-4 pull-right">
							<select class="form-control" id="expense_t_account_id" name="expense_t_account_id">
								<option value="">{{ trans('finance.Select an expense account') }}</option>
							@foreach ($expense_account as $account)
								<option value="{{ $account->id }}">{{ $account->description }}&emsp;({{ $account->account }})</option>
							@endforeach
							</select>
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
	<p id='calculated_total'>{{ trans('forms.Grand total') }}&emsp;{{ $grant_total }}<br>{{ trans('forms.Adjusted amount') }}&emsp;{{ $adjusted_amount }}</p>
</div>
@endsection

@section('post-content')
	<script src="/js/ShowHideHint.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$('#inventorytable').DataTable({ "order": [[ 0, 'asc' ]], "pageLength": 25 });

			hideThenShowHint('.show-balance', '.show-balance-hint');
			$(window).mousemove(function(event) {
				showOrHideHint(event, '.show-balance', '.show-balance-hint');
			});

			$('input[type="number"]').bind('keyup change click', function() {
				if ($(this).val() == $(this).data('original')) {
					$(this).css('color', '#555');
				} else {
					$(this).css('color', 'red');
				}

				// recalcualte total
				$(this).parent().next().next().html(($(this).val() * $(this).data('quantity')).toLocaleString('{{ $currency['regex'] }}', { style: 'currency', currency: '{{ $currency['symbol'] }}' }));
				var total = 0, delta = 0;
				$('input[type="number"]').each(function() {
					total += $(this).val() * $(this).data('quantity');
					delta += ($(this).val() - $(this).data('original')) * $(this).data('quantity');
				});

				$('div.show-balance p#calculated_total').html("{{ trans('forms.Grand total') }}&emsp;" +
								total.toLocaleString('{{ $currency['regex'] }}', { style: 'currency', currency: '{{ $currency['symbol'] }}' }) +
								"<br>{{ trans('forms.Adjusted amount') }}&emsp;" +
								delta.toLocaleString('{{ $currency['regex'] }}', { style: 'currency', currency: '{{ $currency['symbol'] }}' }));

				hideThenShowHint('.show-balance', '.show-balance-hint');
			});

			$('select#expense_t_account_id').bind('change', function() {
				if ($(this).val() != "") {
					$('button[type="submit"]').prop('disabled', false);
				} else {
					$('button[type="submit"]').prop('disabled', true);
				}
			});
		});
	</script>
@endsection
