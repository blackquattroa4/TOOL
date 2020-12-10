@extends('layouts.app')

@section('content')
<div class="container main-view-port">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div id="trxwindow" class="panel panel-default">
				<div class="panel-heading">
					<table width='100%'>
						<tr>
							<td><h4>{{ trans('warehouse.Transaction') }}</h4></td>
						</tr>
					</table>
				</div>

				<div class="panel-body">

					<form class="form-horizontal" role="form" enctype="multipart/form-data" method="POST" action="#">
						{{ csrf_field() }}

						<div class="form-group">
							<div class="col-md-3{{ $errors->has('location') ? ' has-error' : '' }}">
								<label for="location" class="col-md-12">{{ trans('forms.Location') }}</label>
								<select id="location" class="form-control" name="location" >
								@foreach ($locations as $oneLocation)
									<option value="{{ $oneLocation->id }}" {{ ($oneLocation->id == $selected_location) ? " selected" : "" }}>{{ $oneLocation->name }}</option>
								@endforeach
								</select>

								@if ($errors->has('location'))
									<span class="help-block">
										<strong>{{ $errors->first('location') }}</strong>
									</span>
								@endif
							</div>

							<div class="col-md-3{{ $errors->has('owner') ? ' has-error' : '' }}">
								<label for="owner" class="col-md-12">{{ trans('forms.Owner') }}</label>
								<select id="owner" class="form-control" name="owner" >
								@foreach ($entities as $oneEntity)
									<option value="{{ $oneEntity->id }}" {{ ($oneEntity->id == $selected_entity) ? " selected" : "" }}>{{ $oneEntity->name }}</option>
								@endforeach
								</select>

								@if ($errors->has('owner'))
									<span class="help-block">
										<strong>{{ $errors->first('') }}</strong>
									</span>
								@endif
							</div>


							<div class="col-md-3{{ $errors->has('product') ? ' has-error' : '' }}">
								<label for="product" class="col-md-12">{{ trans('forms.SKU') }}</label>
								<select id="product" class="form-control" name="product" >
								@foreach ($skus as $oneSku)
									<option value="{{ $oneSku->id }}" {{ ($oneSku->id == $selected_sku) ? " selected" : "" }}>{{ $oneSku->sku }}</option>
								@endforeach
								</select>

								@if ($errors->has('product'))
									<span class="help-block">
										<strong>{{ $errors->first('') }}</strong>
									</span>
								@endif
							</div>

							<div class="col-md-3">
								<label for="stats_update" class="col-md-12">&nbsp;</label>
								<a id="stats_update" class="btn btn-primary"><i class="icon-refresh icon-white"></i>&nbsp;{{ trans('forms.Update') }}</a>
							</div>
						</div>
					</form>

					<table id="trxtable" class="table table-striped" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th>{{ trans('forms.Date') }}</th>
								<th>{{ trans('forms.Source') }}</th>
								<th style="text-align:right;">{{ trans('forms.Quantity') }}</th>
								<th style="text-align:right;">{{ trans('warehouse.Balance') }}</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>{{ trans('forms.Date') }}</th>
								<th>{{ trans('forms.Source') }}</th>
								<th style="text-align:right;">{{ trans('forms.Quantity') }}</th>
								<th style="text-align:right;">{{ trans('warehouse.Balance') }}</th>
							</tr>
						</tfoot>
						<tbody>
						</tbody>
					</table>
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
				url: '/accounting/tradable/transactions/ajax',
				data: {
						location : $('#location').val(),
						owner: $('#owner').val(),
						sku: $('#product').val(),
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
					var chrono = result[i].date.split(" ");
					$('table#trxtable tbody').append("<tr><td title=\"" + chrono[1] + "\">" + chrono[0] + "</td><td title=\"" + result[i].notes + "\">" + result[i].source + "</td><td style=\"text-align:right;\" >" + result[i].quantity + "</td><td style=\"text-align:right;\">" + result[i].balance + "</td></tr>");
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
			$('#location,#owner,#product').change(function() {
				$('table#trxtable tbody').html("");
				lastOffset.offset = 0 - perRequest;
			});

			$('#stats_update').click(function() {
				loadMoreTransactions();
			});

			loadMoreTransactions();
		});
	</script>
@endsection
