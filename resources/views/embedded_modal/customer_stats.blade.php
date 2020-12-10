
<div class="modal fade" id="embeddedCustomerStatsModal" tabindex="-1" role="dialog" aria-labelledby="embeddedCustomerStatsModalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">

  <div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<table width="100%">
					<tr>
						<td>
							<font size="4" style="padding-right:30px;">{{ trans('crm.View customer') }}</font>
              <span v-if="'general' in errors" v-bind:class="{ 'text-danger' : 'general' in errors }" >
								<strong>@{{ errors['general'][0] }}</strong>
							</span>
						</td>
						<td>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
            </td>
					</tr>
				</table>
			</div>

      <div style="height: 70vh; overflow-y: scroll;" class="modal-body">
				<form class="form-horizontal" role="form" method="POST" action="">

					<div class="form-group">
						<label for="customer" class="col-md-2 control-label">{{ trans('forms.Customer') }}</label>

						<div class="col-md-3" v-bind:class="{ 'has-error' : 'customer' in errors }">
							<select id="customer" class="form-control" name="customer" v-model="form.customer_id">
							@foreach (\App\TaxableEntity::getActiveCustomers('code', 'asc') as $customer)
								<option value={{ $customer['id'] }}>{{ $customer['code'] }}</option>
							@endforeach
							</select>
							<span v-if="'customer' in errors" class="help-block">
								<strong>@{{ errors['customer'][0] }}</strong>
							</span>
						</div>

						<label for="period" class="col-md-2 control-label">{{ trans('forms.History') }}</label>

						<div class="col-md-3" v-bind:class="{ 'has-error' : 'history' in errors }">
							<select id="history" class="form-control" name="history" v-model="form.history">
								<option value=12>{{ trans('forms.Past year') }}</option>
								<option value=18>{{ trans('forms.Past 1.5 years') }}</option>
								<option value=24>{{ trans('forms.Past 2 years') }}</option>
								<option value=36>{{ trans('forms.Past 3 years') }}</option>
								<option value=60>{{ trans('forms.Past 5 years') }}</option>
							</select>
							<span v-if="'history' in errors" class="help-block">
								<strong>@{{ errors['history'][0] }}</strong>
							</span>
						</div>

						<a id="refresh" class="btn btn-info"><span class="fa fa-refresh"></span>&nbsp;{{ trans('forms.Update') }}</a>
					</div>
				</form>

				<ul class="nav nav-tabs">
					<li class="active"><a class="clickable" data-toggle="tab" href="#customer_pl_chart">{{ trans('finance.Profit') }}</a></li>
					<li><a class="clickable" data-toggle="tab" href="#customer_runrate_table">{{ trans('finance.Runrate') }}</a></li>
				</ul>
				<div class="tab-content performance-statement">
					<div id="customer_pl_chart" class="tab-pane fade in active">
						<div style="margin-top:20px;" class="panel panel-default">
							<div class="panel-body">
								<canvas id="customer_profit_loss_chart"></canvas>
							</div>
						</div>
					</div>
					<div id="customer_runrate_table" class="tab-pane fade in">
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