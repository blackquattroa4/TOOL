
<div class="modal fade" id="embeddedSupplierStatsModal" tabindex="-1" role="dialog" aria-labelledby="embeddedSupplierStatsModalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">

  <div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<table width="100%">
					<tr>
						<td>
							<font size="4" style="padding-right:30px;">{{ trans('vrm.View supplier') }}</font>
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
						<label for="supplier" class="col-md-2 control-label">{{ trans('forms.Supplier') }}</label>

						<div class="col-md-3" v-bind:class="{ 'has-error' : 'supplier' in errors }">
							<select id="supplier" class="form-control" name="supplier" v-model="form.supplier_id">
		          @foreach (\App\TaxableEntity::getActiveSuppliers('code', 'asc') as $supplier)
								<option value={{ $supplier['id'] }}>{{ $supplier['code'] }}</option>
							@endforeach
							</select>
							<span v-if="'supplier' in errors" class="help-block">
								<strong>@{{ errors['supplier'][0] }}</strong>
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
					<li class="active"><a class="clickable" data-toggle="tab" href="#supplier_runrate_table">{{ trans('finance.Runrate') }}</a></li>
				</ul>
				<div class="tab-content performance-statement">
					<div id="supplier_runrate_table" class="tab-pane fade in active">
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