@extends('layouts.app')

@section('additional-style')
<style>
	input.reconciliation {
		width: 22px;
		height: 22px;
		border: 0px;
		margin: 0px;
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
<div class="container main-view-port">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div id="trxwindow" class="panel panel-default">
				<div class="panel-heading">
					<table width='100%'>
						<tr>
							<td><h4>{{ trans('finance.Account transaction') }}</h4></td>
							<td align='right'>{{ $account }}<br>({{ $currency }})</td>
						</tr>
					</table>
				</div>

				<div class="panel-body">
					<form class="form-horizontal" role="form" method="POST" action="{{ $url }}">
						{{ csrf_field() }}
					@if ( count($transactions) > 0)
						<table id="trxtable" class="table table-striped table-hovered" cellspacing="0" width="100%">
							<thead>
								<tr>
									<th>{{ trans('finance.Date') }}</th>
									<th>{{ trans('finance.Source') }}</th>
									<th style="text-align:right;">{{ trans('finance.Debit') }}</th>
									<th style="text-align:right;">{{ trans('finance.Credit') }}</th>
									<th style="text-align:right;">{{ trans('forms.Reconcile') }}</th>
								</tr>
							</thead>
							<tbody>
							@foreach ($transactions as $oneTransaction)
								<tr>
									<td>{{ $oneTransaction['book_date'] }}</td>
									<td>{{ $oneTransaction['source_display'] }}</td>
									<td style="text-align:right;" {!! empty($oneTransaction['debit_title']) ? "" : " title=\"" . $oneTransaction['debit_title'] . "\"" !!}>{{ $oneTransaction['debit'] }}</td>
									<td style="text-align:right;" {!! empty($oneTransaction['credit_title']) ? "" : " title=\"" . $oneTransaction['credit_title'] . "\"" !!}>{{ $oneTransaction['credit'] }}</td>
									<td style="text-align:right;"><input type="checkbox" class="reconciliation" id="reconciliation[{{ $oneTransaction['id'] }}]" name="reconciliation[{{ $oneTransaction['id'] }}]" onchange="adjustBalance(this, {{ $oneTransaction['amount'] }});" /></td>
								</tr>
							@endforeach
							</tbody>
							<tfoot>
								<tr>
									<th>{{ trans('finance.Date') }}</th>
									<th>{{ trans('finance.Source') }}</th>
									<th style="text-align:right;">{{ trans('finance.Debit') }}</th>
									<th style="text-align:right;">{{ trans('finance.Credit') }}</th>
									<th style="text-align:right;">{{ trans('forms.Reconcile') }}</th>
								</tr>
							</tfoot>
						</table>
					@endif
						<button type="submit" class="btn btn-primary pull-right">
							<i class="fa fa-btn fa-pencil-square-o"></i> {{ trans('forms.Update') }}
						</button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<input type="hidden" id="balance_raw" value="{{ $balance_raw }}" />
<a href='#' class='show-balance-hint'>
	<img src="{{ asset('images/sum-512.png') }}" height='40' width='40'></img>
</a>
<div class='show-balance'>
	<p id='calculated_total'>{{ trans('forms.Balance') . " " . $balance }}</p>
</div>

@endsection

@section('post-content')
	<script src="/js/ShowHideHint.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			//$('.selectpicker').selectpicker();
			hideThenShowHint('.show-balance', '.show-balance-hint');
			$(window).mousemove(function(event) {
				showOrHideHint(event, '.show-balance', '.show-balance-hint');
			});
		});

		function adjustBalance(obj, amount) {
			elem = document.getElementById("balance_raw");
			if (obj.checked) {
				elem.value = parseFloat(elem.value) + amount
			} else {
				elem.value = parseFloat(elem.value) - amount
			}
			document.getElementById("calculated_total").innerHTML = "{{ trans('forms.Balance') }} " + parseFloat(elem.value).toLocaleString('{{ $currencyFormat['regex'] }}', { style: 'currency', currency: '{{ $currencyFormat['symbol'] }}' });

			hideThenShowHint('.show-balance', '.show-balance-hint');
		}
	</script>
@endsection
