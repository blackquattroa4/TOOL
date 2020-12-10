
<div class="modal fade" id="embeddedTradableFormModal" tabindex="-1" role="dialog" aria-labelledby="embeddedTradableFormModalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">

	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<table width="100%">
					<tr>
						<td>
							<font size="4" style="padding-right:30px;">@{{ modal.title }}</font>
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

					<input type="hidden" name="_token" v-model="modal.csrf">

					<div class="form-group" v-bind:class="{ 'has-error' : 'model' in errors }">
						<label for="model" class="col-md-4 control-label">{{ trans('forms.Model') }}&nbsp;/&nbsp;{{ trans('forms.SKU') }}</label>

						<div class="col-md-6">
							<input id="model" type="text" class="form-control" name="model" v-model="form.sku" v-bind:readonly="modal.readonly || (form.id != 0)">
							<span v-if="'model' in errors" class="help-block">
								<strong>@{{ errors['model'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'description' in errors }">
						<label for="description" class="col-md-4 control-label">{{ trans('forms.Description') }}</label>

						<div class="col-md-6">
							<input id="description" type="text" class="form-control" name="description" v-model="form.description" v-bind:disabled="modal.readonly">
							<span v-if="'description' in errors" class="help-block">
								<strong>@{{ errors['description'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'productid' in errors }">
						<label for="productid" class="col-md-4 control-label">UPC&nbsp;/&nbsp;EAN</label>

						<div class="col-md-6">
							<input id="productid" type="text" class="form-control" name="productid" v-model="form.product_id" v-bind:disabled="modal.readonly">
							<span v-if="'productid' in errors" class="help-block">
								<strong>@{{ errors['productid'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'phaseout' in errors }">
						<label for="phaseout" class="col-md-4 control-label">{{ trans('forms.Phasing-out') }}</label>

						<div class="col-md-1">
							<input id="phaseout" type="checkbox" class="form-control" name="phaseout" v-model="form.phasing_out" v-bind:disabled="modal.readonly">
							<span v-if="'phaseout' in errors" class="help-block">
								<strong>@{{ errors['phaseout'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'itemtype' in errors }">
						<label for="itemtype" class="col-md-4 control-label">{{ trans('forms.Item type') }}</label>

						<label for="itemtype" class="col-md-2 control-label">{{ trans('forms.Stockable') }}</label>
						<div class="col-md-1">
							<input id="itemtype" type="radio" class="form-control" name="itemtype" value="stockable" v-model="form.item_type" v-bind:disabled="modal.readonly">
						</div>
						<label for="itemtype" class="col-md-2 control-label">{{ trans('forms.Expendable') }}</label>
						<div class="col-md-1">
							<input id="itemtype" type="radio" class="form-control" name="itemtype" value="expendable" v-model="form.item_type" v-bind:disabled="modal.readonly">
						</div>
						<span v-if="'itemtype' in errors" class="help-block">
							<strong>@{{ errors['itemtype'][0] }}</strong>
						</span>
					</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'forecast' in errors }">
						<label for="phaseout" class="col-md-4 control-label">{{ trans('forms.Forecastable') }}</label>

						<div class="col-md-1">
							<input id="forecast" type="checkbox" class="form-control" name="forecast" v-model="form.forecastable" v-bind:disabled="modal.readonly">
							<span v-if="'forecast' in errors" class="help-block">
								<strong>@{{ errors['forecast'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'account' in errors }">
						<label for="account" class="col-md-4 control-label">{{ trans('forms.Expense account') }}</label>

						<div class="col-md-6">
							<select id="account" class="form-control" name="account" v-model="form.account" v-bind:disabled="modal.readonly">
								<option value="0">{{ trans('forms.Inventory account') }}</option>
								<option v-for="account in modal.account" v-bind:value="account.id" v-html="account.account + '&emsp;' + account.description"></option>
							</select>
							<span v-if="'account' in errors" class="help-block">
								<strong>@{{ errors['account'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'active' in errors }">
						<label for="active" class="col-md-4 control-label">{{ trans('forms.Active') }}</label>

						<div class="col-md-1">
							<input id="active" type="checkbox" class="form-control" name="active" v-model="form.current" v-bind:disabled="modal.readonly">
							<span v-if="'active' in errors" class="help-block">
								<strong>@{{ errors['active'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'serial_pattern' in errors }">
						<label for="serial_pattern" class="col-md-4 control-label">{{ trans('forms.Serial pattern') }}</label>

						<div class="col-md-6">
							<input id="serial_pattern" type="text" class="form-control" name="serial_pattern" placeholder="regular expression of serial number" v-model="form.serial_pattern" v-bind:disabled="modal.readonly">
							<span v-if="'serial_pattern' in errors" class="help-block">
								<strong>@{{ errors['serial_pattern'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'supplier' in errors }">
						<label for="supplier" class="col-md-4 control-label">{{ trans('forms.Supplier') }}</label>

						<div class="col-md-6">
							<select id="supplier" class="form-control" name="supplier" v-model="form.supplier" v-bind:disabled="modal.readonly">
								<option v-for="supplier in modal.supplier" v-bind:value="supplier.id" >@{{ supplier.display }}</option>
							</select>
							<span v-if="supplier in modal.supplier" class="help-block">
								<strong>@{{ errors['supplier'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<div class="col-md-4" v-bind:class="{ 'has-error' : 'unit_length' in errors || 'unit_width' in errors || 'unit_height' in errors }">
							<label for="phone" style="padding-right:0px;" class="col-md-12 control-label">{{ trans('forms.Unit dimension') }}</label>
						</div>

						<div class="col-md-2" v-bind:class="{ 'has-error' : 'unit_length' in errors }">
							<input id="unit_length" type="number" min="0.00" step="0.01" class="form-control text-right" name="unit_length" placeholder="length" v-model="form.unit_length" v-bind:disabled="modal.readonly">@{{ modal.length }}<span v-if="'unit_length' in errors" class="help-block">
								<strong>@{{ errors['unit_length'][0] }}</strong>
							</span>
						</div>

						<div class="col-md-2" v-bind:class="{ 'has-error' : 'unit_width' in errors }">
							<input id="unit_width" type="number" min="0.00" step="0.01" class="form-control text-right" name="unit_width" placeholder="width" v-model="form.unit_width" v-bind:disabled="modal.readonly">@{{ modal.length }}<span v-if="'unit_width' in errors" class="help-block">
								<strong>@{{ errors['unit_width'][0] }}</strong>
							</span>
						</div>

						<div class="col-md-2" v-bind:class="{ 'has-error' : 'unit_height' in errors }">
							<input id="unit_height" type="number" min="0.00" step="0.01" class="form-control text-right" name="unit_height" placeholder="height" v-model="form.unit_height" v-bind:disabled="modal.readonly">@{{ modal.length }}<span v-if="'unit_height' in errors" class="help-block">
								<strong>@{{ errors['unit_height'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'unit_weight' in errors }">
						<label for="unit_weight" class="col-md-4 control-label">{{ trans('forms.Unit weight') }}</label>

						<div class="col-md-2">
							<input id="unit_weight" type="number" min="0.00" step="0.01" class="form-control text-right" name="unit_weight" v-model="form.unit_weight" v-bind:disabled="modal.readonly">@{{ modal.weight }}<span v-if="'unit_weight' in errors" class="help-block">
								<strong>@{{ errors['unit_weight'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'per_carton' in errors }">
						<label for="per_carton" class="col-md-4 control-label">{{ trans('forms.Unit per carton') }}</label>

						<div class="col-md-2">
							<input id="per_carton" type="number" min="0" step="1" class="form-control text-right" name="per_carton" v-model="form.unit_per_carton" v-bind:disabled="modal.readonly">
							<span v-if="'per_carton' in errors"class="help-block">
								<strong>@{{ errors['per_carton'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<div class="col-md-4" v-bind:class="{ 'has-error' : 'carton_length' in errors || 'carton_width' in errors || 'carton_height' in errors }">
							<label for="phone" style="padding-right:0px;" class="col-md-12 control-label">{{ trans('forms.Carton dimension') }}</label>
						</div>

						<div class="col-md-2" v-bind:class="{ 'has-error' : 'carton_length' in errors }">
							<input id="carton_length" type="number" min="0.00" step="0.01" class="form-control text-right" name="carton_length" placeholder="length" v-model="form.carton_length" v-bind:disabled="modal.readonly">@{{ modal.length }}<span v-if="'carton_length' in errors" class="help-block">
								<strong>@{{ errors['carton_length'][0] }}</strong>
							</span>
						</div>

						<div class="col-md-2" v-bind:class="{ 'has-error' : 'carton_width' in errors }">
							<input id="carton_width" type="number" min="0.00" step="0.01" class="form-control text-right" name="carton_width" placeholder="width" v-model="form.carton_width" v-bind:disabled="modal.readonly">@{{ modal.length }}<span v-if="'carton_width' in errors" class="help-block">
								<strong>@{{ errors['carton_width'][0] }}</strong>
							</span>
						</div>

						<div class="col-md-2" v-bind:class="{ 'has-error' : 'carton_height' in errors }">
							<input id="carton_height" type="number" min="0.00" step="0.01" class="form-control text-right" name="carton_height" placeholder="height" v-model="form.carton_height" v-bind:disabled="modal.readonly">@{{ modal.length }}<span v-if="'carton_height' in errors" class="help-block">
								<strong>@{{ errors['carton_height'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'carton_weight' in errors }">
						<label for="carton_weight" class="col-md-4 control-label">{{ trans('forms.Carton weight') }}</label>

						<div class="col-md-2">
							<input id="carton_weight" type="number" min="0.00" step="0.01" class="form-control text-right" name="carton_weight" v-model="form.carton_weight" v-bind:disabled="modal.readonly">@{{ modal.weight }}<span v-if="'carton_weight' in errors" class="help-block">
								<strong>@{{ errors['carton_weight'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'per_pallet' in errors }">
						<label for="per_pallet" class="col-md-4 control-label">{{ trans('forms.Carton per pallet') }}</label>

						<div class="col-md-2">
							<input id="per_pallet" type="number" min="0" step="1" class="form-control text-right" name="per_pallet" v-model="form.carton_per_pallet" v-bind:disabled="modal.readonly">
							<span v-if="'per_pallet' in errors" class="help-block">
								<strong>@{{ errors['per_pallet'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'lead_day' in errors }">
						<label for="lead_day" class="col-md-4 control-label">{{ trans('forms.Lead days') }}</label>

						<div class="col-md-2">
							<input id="lead_day" type="number" min="0" step="1" class="form-control text-right" name="lead_day" v-model="form.lead_day" v-bind:disabled="modal.readonly">
							<span v-if="'lead_day' in errors" class="help-block">
								<strong>@{{ errors['lead_day'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'content' in errors }">
						<label for="content" class="col-md-4 control-label">{{ trans('forms.Content') }}</label>

						<div class="col-md-6">
							<textarea id="content" class="form-control" name="content" rows=4 v-model="form.content" v-bind:disabled="modal.readonly"></textarea>
							<span v-if="'content' in errors" class="help-block">
								<strong>@{{ errors['content'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'country' in errors }">
						<label for="country" class="col-md-4 control-label">{{ trans('forms.Manufacture origin') }}</label>

						<div class="col-md-6">
							<select id="country" class="form-control" name="country" v-model="form.origin" v-bind:disabled="modal.readonly">
								<option v-for="(display, abbr) in modal.origin" v-bind:value="abbr" v-html="abbr + '&emsp;&emsp;(' + display + ')'"></option>
							</select>
							<span v-if="'country' in errors" class="help-block">
								<strong>@{{ errors['country'][0] }}</strong>
							</span>
						</div>
					</div>

				</form>
			</div>

			<div v-if="Object.keys(modal.action).length > 0" class="modal-footer">
				<div class="form-group">
					<div class="col-md-12">
						<button v-for="(callback, display) in modal.action" type="button" class="btn btn-primary pull-right" style="margin-left:3px;" v-on:click="window[callback]()" v-html="display">
						</button>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>