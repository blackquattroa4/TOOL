
<!-- modal to choose which SO/SR to convert into receivable -->
<div class="modal fade" id="receivableModal" tabindex="-1" role="dialog" aria-labelledby="receivableModalLabel" data-backdrop="static" aria-hidden="false">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title" id="receivableModalLabel">{{ trans('crm.New receivable') }}</h4>
			</div>
			<div style="height: 50vh; overflow-y: scroll;" class="modal-body">
				<h4>{{ trans('crm.From which order/return?') }}</h4>
				<ul class="list-unstyled">
					<!-- body generated by ajax -->
				</ul>
			</div>
			<!-- <div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary">Send message</button>
			</div> -->
		</div>
	</div>
</div>

<!-- modal to enter quantity to convert into receivable -->
<div class="modal fade" id="embeddedSalesProcessModal" tabindex="-1" role="dialog" aria-labelledby="embeddedSalesProcessLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">

	<!-- history modal -->
	<div class="modal fade" id="embeddedSalesProcessModalHistoryModal" tabindex="-1" role="dialog" aria-labelledby="embeddedSalesProcessModalHistoryModalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" aria-label="Close" onclick="$(this).closest('#embeddedSalesProcessModalHistoryModal').modal('hide')">
						<span aria-hidden="true">&times;</span>
					</button>
					<h4 class="modal-title" id="deleteModalLabel">{{ trans('forms.History') }}</h4>
				</div>
				<div class="modal-body">
					<p v-for="record in modal.history">@{{ record }}</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" onclick="$(this).closest('#embeddedSalesProcessModalHistoryModal').modal('hide')">{{ trans("forms.Close") }}</button>
				</div>
			</div>
		</div>
	</div>

	<!-- aggregation modal -->
	<div class="modal fade" id="embeddedSalesProcessModalAggregationModal" tabindex="-1" role="dialog" aria-labelledby="embeddedSalesProcessModalAggregationModalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" aria-label="Close" onclick="$(this).closest('#embeddedSalesProcessModalAggregationModal').modal('hide')">
						<span aria-hidden="true">&times;</span>
					</button>
					<h4 class="modal-title" id="embeddedSalesProcessModalAggregationModalLabel">{{ trans('forms.Summary') }}</h4>
				</div>
				<div class="modal-body">
					<table v-if="form.status != 'void'">
						<tr>
							<td>{{ trans('forms.Untaxed subtotal') }}</td>
							<td style="padding-left:35px;" class="text-right">@{{ form.untaxed_subtotal }}</td>
						</tr>
						<tr>
							<td>{{ trans('forms.Taxed subtotal') }}</td>
							<td style="padding-left:35px;" class="text-right">@{{ form.taxed_subtotal }}</td>
						</tr>
						<tr>
							<td>{{ trans('forms.Tax amount') }}</td>
							<td style="padding-left:35px;" class="text-right">@{{ form.tax_amount }}</td>
						</tr>
						<tr>
							<td>{{ trans('forms.Grand total') }}</td>
							<td style="padding-left:35px;" class="text-right">@{{ form.grand_total }}</td>
						</tr>
					</table>
					<p v-if="form.status == 'void'">{{ trans('messages.This entry is voided') }}</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" onclick="$(this).closest('#embeddedSalesProcessModalAggregationModal').modal('hide')">{{ trans("forms.Close") }}</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<table width="100%">
					<tr>
						<td>
							<font size="4" style="padding-right:30px;">@{{ modal.title }}</font>
							<a style="padding-right:20px;" href="#" data-toggle="modal" data-target="#embeddedSalesProcessModalAggregationModal"><span class="fa fa-2x fa-list"></span></a>
							<a v-if="modal.history && (modal.history.length > 0)" style="padding-right:20px;" href="#" data-toggle="modal" data-target="#embeddedSalesProcessModalHistoryModal"><span class="fa fa-2x fa-history"></span></a>
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
				<form class="form-horizontal" role="form" method="POST" action="modal.post_url">

					<input type="hidden" name="_token" v-model="modal.csrf">

					<div class="form-group">
						<label for="increment" class="col-md-2 control-label">{{ trans('forms.Document') }}</label>

						<div class="col-md-3" v-bind:class="{ 'has-error' : 'increment' in errors }">
							<input id="increment" type="text" class="form-control" name="increment" v-model="form.increment" readonly>
							<span v-if="'increment' in errors" class="help-block">
								<strong>@{{ errors['increment'][0] }}</strong>
							</span>
						</div>

						<label for="customer" class="col-md-2 control-label">{{ trans('forms.Customer') }}</label>

						<div class="col-md-4" v-bind:class="{ 'has-error' : 'customer' in errors }">
							<select id="customer" class="form-control" name="customer" v-model="form.customer" disabled>
								<option v-for="(display, index) in modal.customer" v-bind:value="index" >@{{ display }}</option>
							</select>
							<span v-if="'customer' in errors" class="help-block">
								<strong>@{{ errors['customer'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label for="inputdate" class="col-md-2 control-label">{{ trans('forms.Date') }}</label>

						<div class="col-md-3" v-bind:class="{ 'has-error' : 'inputdate' in errors }">
							<div class="input-group date">
								<input id="inputdate" type="text" class="form-control" name="inputdate" v-model="form.inputdate" disabled>
								<div class="input-group-addon">
									<span class="glyphicon glyphicon-calendar"></span>
								</div>
							</div>
							<span v-if="'inputdate' in errors" class="help-block">
								<strong>@{{ errors['inputdate'][0] }}</strong>
							</span>
						</div>

						<label for="incoterm" class="col-md-2 control-label">{{ trans('forms.Payment') }}</label>

						<div class="col-md-4" v-bind:class="{ 'has-error' : 'payment' in errors }">
							<select id="payment" type="text" class="form-control" name="payment" v-model="form.payment" disabled>
								<option v-for="(display, index) in modal.payment" v-bind:value="index" >@{{ display }}</option>
							</select>
							<span v-if="'payment' in errors" class="help-block">
								<strong>@{{ errors['payment'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label for="expiration" class="col-md-2 control-label">{{ trans('forms.Process date') }}</label>

						<div class="col-md-3" v-bind:class="{ 'has-error' : 'expiration' in errors }">
							<div class="input-group date" data-provide="datepicker">
								<input id="expiration" type="text" class="form-control" name="expiration" v-model="form.expiration" >
								<div class="input-group-addon">
									<span class="glyphicon glyphicon-calendar"></span>
								</div>
							</div>
							<span v-if="'expiration' in errors" class="help-block">
								<strong>@{{ errors['expiration'][0] }}</strong>
							</span>
						</div>

						<label for="incoterm" class="col-md-2 control-label">{{ trans('forms.Incoterm') }}</label>

						<div class="col-md-4" v-bind:class="{ 'has-error' : 'incoterm' in errors }">
							<input id="incoterm" type="text" class="form-control" name="incoterm" v-model="form.incoterm" readonly>
							<span v-if="'incoterm' in errors" class="help-block">
								<strong>@{{ errors['incoterm'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label for="contact" class="col-md-2 control-label">{{ trans('forms.Contact') }}</label>

						<div class="col-md-3" v-bind:class="{ 'has-error' : 'contact' in errors }">
							<select id="contact" class="form-control" name="contact" v-model="form.contact" disabled>
								<option v-for="(display, index) in modal.contact" v-bind:value="display.id" >@{{ display.name }}</option>
							</select>
							<span v-if="'contact' in errors" class="help-block">
								<strong>@{{ errors['contact'][0] }}</strong>
							</span>
						</div>

						<label for="reference" class="col-md-2 control-label">{{ trans('forms.Reference') }}</label>

						<div class="col-md-4" v-bind:class="{ 'has-error' : 'reference' in errors }">
							<input id="reference" type="text" class="form-control" name="reference" v-model="form.reference" disabled>
							<span v-if="'reference' in errors" class="help-block">
								<strong>@{{ errors['reference'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label for="staff" class="col-md-2 control-label">{{ trans('forms.Staff') }}</label>

						<div class="col-md-3" v-bind:class="{ 'has-error' : 'staff' in errors }">
							<select id="staff" class="form-control" name="staff" v-model="form.staff" disabled>
								<option v-for="(display, index) in modal.staff" v-bind:value="index" >@{{ display }}</option>
							</select>
							<span v-if="'staff' in errors" class="help-block">
								<strong>@{{ errors['staff'][0] }}</strong>
							</span>
						</div>

						<label for="currency" class="col-md-2 control-label">{{ trans('forms.Currency') }}</label>

						<div class="col-md-4" v-bind:class="{ 'has-error' : 'currency' in errors }">
							<select id="currency" class="form-control" name="currency" v-model="form.currency" disabled>
								<option v-for="(display, index) in modal.currency" v-bind:value="index" >@{{ display }}</option>
							</select>
							<span v-if="'currency' in errors" class="help-block">
								<strong>@{{ errors['currency'][0] }}</strong>
							</span>
						</div>
					</div>

					<hr />

					<div v-for="(id, index) in form.line" class="form-group detail-line">

						<input id="line[]" type="hidden" name="line[]" v-model="id" ></input>

						<div class="col-md-4" v-bind:class="{ 'has-error' : 'product.'+index in errors }">
							{{ trans('forms.Item') }}
							<select id="product[]" class="form-control" name="product[]" v-model="form.product[index]" disabled>
								<option v-for="(display, index) in modal.product" v-bind:value="index" >@{{ display }}</option>
							</select>
							<span v-if="'product.'+index in errors" class="help-block">
								<strong>@{{ errors['product.'+index][0] }}</strong>
							</span>
						</div>

						<div class="col-md-4" v-bind:class="{ 'has-error' : 'display.'+index in errors }">
							{{ trans('forms.Display') }}
							<input id="display[]" type="text" class="form-control" name="display[]" v-model="form.display[index]" disabled></input>
							<span v-if="'display.'+index in errors" class="help-block">
								<strong>@{{ errors['display.'+index][0] }}</strong>
							</span>
						</div>

						<div class="col-md-2 col-md-offset-2" v-bind:class="{ 'has-error' : 'unitprice.'+index in errors }">
							{{ trans('forms.Unit price') }}
							<input id="unitprice[]" type="number" style="text-align:right" min="0" v-bind:step="modal.currency_min" class="form-control" name="unitprice[]" v-model="form.unitprice[index]" disabled></input>
							<span v-if="'unitprice.'+index in errors" class="help-block">
								<strong>@{{ errors['unitprice.'+index][0] }}</strong>
							</span>
						</div>

						<div class="col-md-7 col-md-offset-1" v-bind:class="{ 'has-error' : 'description.'+index in errors }">
							{{ trans('forms.Description') }}
							<input id="description[]" type="text" class="form-control" name="description[]" v-model="form.description[index]" disabled></input>
							<span v-if="'description.'+index in errors" class="help-block">
								<strong>@{{ errors['description.'+index][0] }}</strong>
							</span>
						</div>

						<div class="col-md-2" v-bind:class="{ 'has-error' : 'processing.'+index in errors }">
							{{ trans('forms.Processing') }}
							<input id="processing[]" type="number" style="text-align:right" min="0" v-bind:max="form.quantity[index]" step="1" class="form-control" name="processing[]" v-model="form.processing[index]" ></input>
							<span v-if="'processing.'+index in errors" class="help-block">
								<strong>@{{ errors['processing.'+index][0] }}</strong>
							</span>
						</div>

						<div class="col-md-2" v-bind:class="{ 'has-error' : 'quantity.'+index in errors }">
							{{ trans('forms.Quantity') }}
							<input id="quantity[]" type="number" style="text-align:right" min="0" step="1" class="form-control" name="quantity[]" v-model="form.quantity[index]" disabled></input>
							<span v-if="'quantity.'+index in errors" class="help-block">
								<strong>@{{ errors['quantity.'+index][0] }}</strong>
							</span>
						</div>

						<div class="col-md-3 col-md-offset-5" v-bind:class="{ 'has-error' : 'discount.'+index in errors }">
							{{ trans('forms.Discount') }}
							<div class="input-group">
								<span class="input-group-addon" ><strong>@{{ form.disctype[index] }}</strong></span>
								<input id="disctype[]" type="hidden" name="disctype[]" v-model="form.disctype[index]" ></input>
								<input id="discount[]" type="number" style="text-align:right" min="0" step="0.01" class="form-control" name="discount[]" v-model="form.discount[index]" readonly></input>
							</div>
							<span v-if="'discount.'+index in errors" class="help-block">
								<strong>@{{ errors['discount.'+index][0] }}</strong>
							</span>
						</div>

						<div class="col-md-1 col-md-offset-1" v-bind:class="{ 'has-error' : 'taxable.'+index in errors }">
							{{ trans('forms.Taxable') }}
							<input id="taxable[]" type="hidden" name="taxable[]" v-model="form.taxable[index]" ></input>
							<input id="_taxable[]" type="checkbox" class="form-control" name="_taxable[]" v-model="form.taxable[index]" disabled></input>
							<span v-if="'taxable.'+index in errors" class="help-block">
								<strong>@{{ errors['taxable.'+index][0] }}</strong>
							</span>
						</div>

						<div class="col-md-2" v-bind:class="{ 'has-error' : 'subtotal.'+index in errors }">
							{{ trans('forms.Subtotal') }}
							<input id="subtotal[]" type="number" style="text-align:right" min="0" v-bind:step="modal.currency_min" class="form-control" name="subtotal[]" v-model="form.subtotal[index]" readonly></input>
							<span v-if="'subtotal.'+index in errors" class="help-block">
								<strong>@{{ errors['subtotal.'+index][0] }}</strong>
							</span>
						</div>
					</div>
				</form>
			</div>

			<div class="modal-footer">
				<div class="col-md-12">
					<button v-for="(callback, display) in modal.action" type="button" class="btn btn-primary pull-right" style="margin-left:3px;" v-on:click="window[callback]()" v-bind:disabled="form.processing.every(x => x == 0)" v-html="display">
					</button>
				</div>
			</div>
		</div>
	</div>
</div>