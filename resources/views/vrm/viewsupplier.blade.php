@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div id="supplierwindow" class="panel panel-default">
				<div class="panel-heading">
					<table width='100%'>
						<tr>
							<td><h4>{{ trans('vrm.View supplier') }}</h4></td>
							<td align='right'></td>
						</tr>
					</table>
				</div>

				<div class="panel-body">
					<form class="form-horizontal" role="form" method="POST" action="">
						<div class="form-group">
							<label for="supplier" class="col-md-2 control-label">{{ trans('forms.Supplier') }}</label>

							<div class="col-md-3{{ $errors->has('supplier') ? ' has-error' : '' }}">
								<select id="supplier" class="form-control" name="supplier">
								@foreach ($suppliers as $supplier)
									<option value={{ $supplier['id'] }}>{{ $supplier['code'] }}</option>
								@endforeach
								</select>

							@if ($errors->has('supplier'))
								<span class="help-block">
									<strong>{{ $errors->first('supplier') }}</strong>
								</span>
							@endif
							</div>

							<label for="period" class="col-md-2 control-label">{{ trans('forms.History') }}</label>

							<div class="col-md-3{{ $errors->has('history') ? ' has-error' : '' }}">
								<select id="history" class="form-control" name="history">
									<option value=12>{{ trans('forms.Past year') }}</option>
									<option value=18>{{ trans('forms.Past 1.5 years') }}</option>
									<option value=24>{{ trans('forms.Past 2 years') }}</option>
									<option value=36>{{ trans('forms.Past 3 years') }}</option>
									<option value=60>{{ trans('forms.Past 5 years') }}</option>
								</select>

							@if ($errors->has('history'))
								<span class="help-block">
									<strong>{{ $errors->first('history') }}</strong>
								</span>
							@endif
							</div>

							<a id="refresh" class="btn btn-info"><span class="fa fa-refresh"></span>&nbsp;{{ trans('forms.Update') }}</a>
						</div>
					</form>

					<ul class="nav nav-tabs">
						<li class="active"><a class="clickable" data-toggle="tab" href="#runrate_table">{{ trans('finance.Runrate') }}</a></li>
					</ul>
					<div class="tab-content performance-statement">
						<div id="runrate_table" class="tab-pane fade in active">
							<div style="margin-top:20px;" class="panel panel-default">
								<div class="panel-body">
									<canvas id="supplier_product_runrate_chart"></canvas>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@endsection

@section('post-content')

@if (!App::environment('local'))
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js" charset="utf-8"></script>
@else
	<script type="text/javascript" src="{{ asset('external/ajax/libs/Chart.js/2.7.1/Chart.min.js') }}"></script>
@endif

	<script type="text/javascript">

		var supplierRunrateChartConfig = {
			type : 'line',
			data : {
				labels : [  ],
				datasets : [  ]
			},
			options : {
				responsive : true,
				title : {
					display : true,
					text : '{{ trans('') }}'
				},
				scales : {
					yAxes : [
						{
							display : true,
							type : 'linear',
							position : 'left',
							ticks : {
								callback: function (value, index, values) {
									return Intl.NumberFormat('en-US', { notation: "compact" , compactDisplay: "short" }).format(value)
								}
							}
						}
					]
				}
			}
		};

		var supplierRunrateChart = null;

		$('#supplier,#history').bind('change', function() {
			supplierRunrateChartConfig.data.labels = [ ];
			supplierRunrateChartConfig.data.datasets = [ ];
			supplierRunrateChart.update();
		});

		// Ajax call to pull reports
		$('#refresh').bind('click', function() {
			$.ajax({
				type: 'GET',
				url: '/vrm/detailsupplier/ajax',
				data: {
						supplier : $('select#supplier').val(),
						history : $('select#history').val(),
					},
				dataType: 'html',
				beforeSend: function(data) {
					supplierRunrateChartConfig.data.labels = [ ];
					supplierRunrateChartConfig.data.datasets = [ ];
					supplierRunrateChart.update();
					$('.ajax-processing').removeClass('hidden');
				},
			}).always(function(data) {
				let report = JSON.parse(data);
				$('.ajax-processing').addClass('hidden');
				if (!report['success']) {
					$('div.flash-message').append('<p class="alert alert-warning">' + '{{ trans('vrm.Performance statistics cannot be generated') }}' + '<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a></p>');
				}
			}).done(function(data) {
				var report = JSON.parse(data);
				if (report['success']) {
					supplierRunrateChartConfig.data.labels = report['data']['labels'];
					supplierRunrateChartConfig.data.datasets = report['data']['runrate'];
					supplierRunrateChart.update();
				}
			});
		});

		$(document).ready(function() {
			supplierRunrateChart = new Chart(document.getElementById('supplier_product_runrate_chart').getContext('2d'), supplierRunrateChartConfig);
			supplierRunrateChart.update();
		});

	</script>
@endsection
