@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div id="customerwindow" class="panel panel-default">
				<div class="panel-heading">
					<table width='100%'>
						<tr>
							<td><h4>{{ trans('crm.View customer') }}</h4></td>
							<td align='right'></td>
						</tr>
					</table>
				</div>

				<div class="panel-body">
					<form class="form-horizontal" role="form" method="POST" action="">
						<div class="form-group">
							<label for="customer" class="col-md-2 control-label">{{ trans('forms.Customer') }}</label>

							<div class="col-md-3{{ $errors->has('customer') ? ' has-error' : '' }}">
								<select id="customer" class="form-control" name="customer">
								@foreach ($customers as $customer)
									<option value={{ $customer['id'] }}>{{ $customer['code'] }}</option>
								@endforeach
								</select>

							@if ($errors->has('customer'))
								<span class="help-block">
									<strong>{{ $errors->first('customer') }}</strong>
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
						<li class="active"><a class="clickable" data-toggle="tab" href="#pl_chart">{{ trans('finance.Profit') }}</a></li>
						<li><a class="clickable" data-toggle="tab" href="#runrate_table">{{ trans('finance.Runrate') }}</a></li>
					</ul>
					<div class="tab-content performance-statement">
						<div id="pl_chart" class="tab-pane fade in active">
							<div style="margin-top:20px;" class="panel panel-default">
								<div class="panel-body">
									<canvas id="customer_profit_loss_chart"></canvas>
								</div>
							</div>
						</div>
						<div id="runrate_table" class="tab-pane fade in">
							<div style="margin-top:20px;" class="panel panel-default">
								<div class="panel-body">
									<canvas id="customer_product_runrate_chart"></canvas>
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

		var customerProfitLossChartConfig = {
			type : 'line',
			data : {
				labels : [  ],
				datasets : [  ]
			},
			options : {
				responsive : true,
				title : {
					display : true,
					text : '{{ trans('finance.Performance') }}'
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

		var customerProfitLossChart = null;

		var customerRunrateChartConfig = {
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

		var customerRunrateChart = null;

		$('#customer,#history').bind('change', function() {
			customerProfitLossChartConfig.data.labels = [ ];
			customerProfitLossChartConfig.data.datasets = [ ];
			customerProfitLossChart.update();
		});

		$('#customer,#history').bind('change', function() {
			customerRunrateChartConfig.data.labels = [ ];
			customerRunrateChartConfig.data.datasets = [ ];
			customerRunrateChart.update();
		});

		// Ajax call to pull reports
		$('#refresh').bind('click', function() {
			$.ajax({
				type: 'GET',
				url: '/crm/detailcustomer/ajax',
				data: {
						customer : $('select#customer').val(),
						history : $('select#history').val(),
					},
				dataType: 'html',
				beforeSend: function(data) {
					customerProfitLossChartConfig.data.labels = [ ];
					customerProfitLossChartConfig.data.datasets = [ ];
					customerProfitLossChart.update();
					customerRunrateChartConfig.data.labels = [ ];
					customerRunrateChartConfig.data.datasets = [ ];
					customerRunrateChart.update();
					$('.ajax-processing').removeClass('hidden');
				},
			}).always(function(data) {
				let report = JSON.parse(data);
				$('.ajax-processing').addClass('hidden');
				if (!report['success']) {
					$('div.flash-message').append('<p class="alert alert-warning">' + '{{ trans('crm.Performance statistics cannot be generated') }}' + '<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a></p>');
				}
			}).done(function(data) {
				let report = JSON.parse(data);
				if (report['success']) {
					customerProfitLossChartConfig.data.labels = report['data']['labels'];
					customerProfitLossChartConfig.data.datasets.push(report['data']['sales']);
					customerProfitLossChartConfig.data.datasets.push(report['data']['expense']);
					customerProfitLossChartConfig.data.datasets.push(report['data']['profit']);
					customerProfitLossChart.update();
					customerRunrateChartConfig.data.labels = report['data']['labels'];
					customerRunrateChartConfig.data.datasets = report['data']['runrate'];
					customerRunrateChart.update();
				}
			});
		});

		$(document).ready(function() {
			customerProfitLossChart = new Chart(document.getElementById('customer_profit_loss_chart').getContext('2d'), customerProfitLossChartConfig);
			customerProfitLossChart.update();
		});

		$(document).ready(function() {
			customerRunrateChart = new Chart(document.getElementById('customer_product_runrate_chart').getContext('2d'), customerRunrateChartConfig);
			customerRunrateChart.update();
		});

	</script>
@endsection
