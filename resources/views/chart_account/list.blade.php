@extends('layouts.app')

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
				@if ( count($transactions) > 0) 
					<table id="trxtable" class="table table-striped table-hovered" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th>{{ trans('finance.Date') }}</th>
								<th>{{ trans('finance.Source') }}</th>
								<th style="text-align:right;">{{ trans('finance.Debit') }}</th>
								<th style="text-align:right;">{{ trans('finance.Credit') }}</th>
								<th style="text-align:right;">{{ trans('finance.Balance') }}</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>{{ trans('finance.Date') }}</th>
								<th>{{ trans('finance.Source') }}</th>
								<th style="text-align:right;">{{ trans('finance.Debit') }}</th>
								<th style="text-align:right;">{{ trans('finance.Credit') }}</th>
								<th style="text-align:right;">{{ trans('finance.Balance') }}</th>
							</tr>
						</tfoot>
						<tbody>
						</tbody>
					</table>
				@endif
				</div>
			</div>
		</div>
	</div>
</div>

@endsection

@section('post-content')
	<script type="text/javascript">
		
		var perRequest = 20;

		function lastOffset() {
			if (typeof lastOffset.offset == 'undefined') {
				lastOffset.offset = 0;
			} else {
				lastOffset.offset += perRequest;
			}
			return lastOffset.offset;
		}

		function loadMoreTransactions() {
			$.ajax({
				type: 'GET',
				url: '/taccount/retrieve',
				data: {
						id : {{ $id }},
						offset : lastOffset(),
						count : perRequest,
					},
				dataType: 'html',
				beforeSend: function(data) {
					$('body div.main-view-port').append("<div class=\"progress-animation\"><i class=\"fa fa-spinner fa-pulse fa-5x fa-fw\"></i></div>");
				},
			}).done(function(data) {
				$('body div.main-view-port div.progress-animation').remove();
				var result = JSON.parse(data);
				for (var i = 0; i < result.length; i++) {
					$('table#trxtable tbody').append("<tr><td>" + result[i].date + "</td><td title=\"" + result[i].notes + "\">" + result[i].source + "</td><td style=\"text-align:right;\" title=\"" + result[i].debit_title + "\">" + result[i].debit + "</td><td style=\"text-align:right;\" title=\"" + result[i].credit_title + "\">" + result[i].credit + "</td><td style=\"text-align:right;\">" + result[i].balance + "</td></tr>");
				}
				$('div.panel-body button.btn').remove();
				if (result.length == perRequest) {
					$('div.panel-body').append("<button class=\"btn btn-primary pull-right\" onclick=\"loadMoreTransactions();\" ><i class=\"fa fa-download\"></i>&emsp;{{ trans("forms.Load more transactions") }}</button>");
				}
			}).fail(function(data) {
				$('div.panel-body button.btn').remove();
			});
		}

		$(document).ready(function() {
			loadMoreTransactions();
		});
	</script>
@endsection