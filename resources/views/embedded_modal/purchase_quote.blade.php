

<div class="modal fade" id="embeddedPurchaseQuoteModal" tabindex="-1" role="dialog" aria-labelledby="embeddedPurchaseQuoteLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">

	<!-- history modal -->
	<div class="modal fade" id="historyModal" tabindex="-1" role="dialog" aria-labelledby="historyModalLabel" data-backdrop="static" aria-hidden="false">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
					<h4 class="modal-title" id="deleteModalLabel">{{ trans('forms.History') }}</h4>
				</div>
				<div class="modal-body">
					<p v-for="record in modal.history">@{{ record }}</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans("forms.Close") }}</button>
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
							<a v-if="modal.history && (modal.history.length > 0)" style="padding-right:20px;" href="#" data-toggle="modal" data-target="#embeddedPurchaseQuoteModal #historyModal"><span class="fa fa-2x fa-history"></span></a>
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

					<input type="hidden" id="type" name="type" v-model="form.type" />

					<div class="form-group">
						<label for="increment" class="col-md-2 control-label">{{ trans('forms.Document') }}</label>

						<div class="col-md-3" v-bind:class="{ 'has-error' : 'increment' in errors }">
							<input id="increment" type="text" class="form-control" name="increment" v-model="form.increment" readonly>
							<span v-if="'increment' in errors" class="help-block">
								<strong>@{{ errors['increment'][0] }}</strong>
							</span>
						</div>

						<label for="supplier" class="col-md-2 control-label">{{ trans('forms.Supplier') }}</label>

						<div class="col-md-4" v-bind:class="{ 'has-error' : 'supplier' in errors }">
							<input id="supplier" class="hidden" name="supplier" v-model="form.entity">
							<select id="_supplier" class="form-control" name="_supplier" v-model="form.entity" v-on:change="purchaseQuoteEntityChange()" v-bind:disabled="modal.readonly || (form.id != 0)">
								<option v-for="(supplier, index) in modal.entity" v-bind:value="index" >@{{ supplier.display }}</option>
							</select>
							<span v-if="'supplier' in errors" class="help-block">
								<strong>@{{ errors['supplier'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label for="inputdate" class="col-md-2 control-label">{{ trans('forms.Date') }}</label>

						<div class="col-md-3" v-bind:class="{ 'has-error' : 'inputdate' in errors }">
							<div class="input-group date" v-bind:data-provide="modal.readonly ? '' : 'datepicker'">
								<input id="inputdate" type="text" class="form-control" name="inputdate" v-model="form.inputdate" v-bind:readonly="modal.readonly">
								<div class="input-group-addon">
									<span class="glyphicon glyphicon-calendar"></span>
								</div>
							</div>
							<span v-if="'inputdate' in errors" class="help-block">
								<strong>@{{ errors['inputdate'][0] }}</strong>
							</span>
						</div>

						<label for="payment" class="col-md-2 control-label">{{ trans('forms.Payment') }}</label>

						<div class="col-md-4" v-bind:class="{ 'has-error' : 'payment' in errors }">
							<input id="payment" type="hidden" class="form-control" name="payment" v-model="form.payment" >
							<select id="_payment" type="text" class="form-control" name="_payment" v-model="form.payment" disabled>
								<option v-for="(payment, index) in modal.payment" v-bind:value="index" >@{{ payment }}</option>
							</select>
							<span v-if="'payment' in errors" class="help-block">
								<strong>@{{ errors['payment'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label for="expiration" class="col-md-2 control-label">{{ trans('forms.Expiration') }}</label>

						<div class="col-md-3" v-bind:class="{ 'has-error' : 'expiration' in errors }">
							<div class="input-group date" v-bind:data-provide="modal.readonly ? '' : 'datepicker'">
								<input id="expiration" type="text" class="form-control" name="expiration" v-model="form.expiration" v-bind:readonly="modal.readonly">
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
							<input id="incoterm" type="text" class="form-control" name="incoterm" v-model="form.incoterm" v-bind:readonly="modal.readonly">
							<span v-if="'incoterm' in errors" class="help-block">
								<strong>@{{ errors['incoterm'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label for="contact" class="col-md-2 control-label">{{ trans('forms.Contact') }}</label>

						<div class="col-md-3" v-bind:class="{ 'has-error' : 'contact' in errors }">
							<input id="contact" type="hidden" class="form-control" name="contact" v-model="form.contact" >
							<select id="_contact" class="form-control" name="_contact" v-model="form.contact" v-bind:disabled="modal.readonly">
								<option v-for="contact in modal.contact[form.entity]" v-bind:value="contact.id" >@{{ contact.name }}</option>
							</select>
							<span v-if="'contact' in errors" class="help-block">
								<strong>@{{ errors['contact'][0] }}</strong>
							</span>
						</div>

						<label for="reference" class="col-md-2 control-label">{{ trans('forms.Reference') }}</label>

						<div class="col-md-4" v-bind:class="{ 'has-error' : 'reference' in errors }">
							<input id="reference" type="text" class="form-control" name="reference" v-model="form.reference" v-bind:readonly="modal.readonly">
							<span v-if="'reference' in errors" class="help-block">
								<strong>@{{ errors['reference'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label for="staff" class="col-md-2 control-label">{{ trans('forms.Staff') }}</label>

						<div class="col-md-3" v-bind:class="{ 'has-error' : 'staff' in errors }">
							<select id="staff" type="text" class="form-control" name="staff" v-model="form.staff" v-bind:disabled="modal.readonly">
								<option v-for="(display, index) in modal.staff" v-bind:value="index" >@{{ display }}</option>
							</select>
							<span v-if="'staff' in errors" class="help-block">
								<strong>@{{ errors['staff'][0] }}</strong>
							</span>
						</div>

						<label for="currency" class="col-md-2 control-label">{{ trans('forms.Currency') }}</label>

						<div class="col-md-4" v-bind:class="{ 'has-error' : 'currency' in errors }">
							<input id="currency" type="hidden" class="form-control" name="currency" v-model="form.currency" >
							<select id="_currency" type="text" class="form-control" name="_currency" v-model="form.currency" disabled>
								<option v-for="(display, index) in modal.currency" v-bind:value="index" >@{{ display.symbol }} (@{{ display.description }})</option>
							</select>
							<span v-if="'currency' in errors" class="help-block">
								<strong>@{{ errors['currency'][0] }}</strong>
							</span>
						</div>
					</div>

					<hr />

					<div v-for="(line, index) in form.line" class="form-group detail-line">
						<input id="line[]" type="hidden" name="line[]" v-model="line" ></input>

						<div class="col-md-4" v-bind:class="{ 'has-error' : 'product.'+index in errors }">
							{{ trans('forms.Item') }}
							<select id="product[]" class="form-control" name="product[]" v-model="form.product[index]" v-on:change="fillPurchaseQuoteDefaultValue(index)" v-bind:disabled="modal.readonly">
								<option v-for="(display, index2) in modal.product[form.entity]" v-bind:value="index2" >@{{ display.sku }}</option>
							</select>
							<span v-if="'product.'+index in errors" class="help-block">
								<strong>@{{ errors['product.'+index][0] }}</strong>
							</span>
						</div>

						<div class="col-md-4" v-bind:class="{ 'has-error' : 'display.'+index in errors }">
							{{ trans('forms.Display') }}
							<input id="display[]" type="text" class="form-control" name="display[]" v-model="form.display[index]" v-bind:readonly="modal.readonly"></input>
							<span v-if="'display.'+index in errors" class="help-block">
								<strong>@{{ errors['display.'+index][0] }}</strong>
							</span>
						</div>

						<div class="col-md-2 col-md-offset-2" v-bind:class="{ 'has-error' : 'unitprice.'+index in errors }">
							{{ trans('forms.Unit price') }}
							<input id="unitprice[]" type="number" style="text-align:right" min="modal.currency[form.currency].min" v-bind:step="modal.currency[form.currency].min" class="form-control" name="unitprice[]" v-model="form.unitprice[index]" v-bind:readonly="modal.readonly"></input>
							<span v-if="'unitprice.'+index in errors" class="help-block">
								<strong>@{{ errors['unitprice.'+index][0] }}</strong>
							</span>
						</div>

						<!--
						<div class="col-md-1" v-bind:class="{ 'has-error' : 'taxable.'+index in errors }">
							{{ trans('forms.Taxable') }}
							<input id="taxable[]" type="checkbox" class="form-control" name="taxable[]" v-model="form.taxable[index]" ></input>
							<span v-if="'taxable.'+index in errors" class="help-block">
								<strong>@{{ errors['taxable.'+index][0] }}</strong>
							</span>
						</div>
						-->

						<div class="col-md-7 col-md-offset-1" v-bind:class="{ 'has-error' : 'description.'+index in errors }">
							{{ trans('forms.Description') }}
							<input id="description[]" type="text" class="form-control" name="description[]" v-model="form.description[index]" v-bind:readonly="modal.readonly"></input>
							<span v-if="'description.'+index in errors" class="help-block">
								<strong>@{{ errors['description.'+index][0] }}</strong>
							</span>
						</div>

						<div class="col-md-2 col-md-offset-2" v-bind:class="{ 'has-error' : 'quantity.'+index in errors }">
							{{ trans('forms.Quantity') }}
							<input id="quantity[]" type="number" style="text-align:right" min="0" step="1" class="form-control" name="quantity[]" v-model="form.quantity[index]" v-bind:readonly="modal.readonly"></input>
							<span v-if="'quantity.'+index in errors" class="help-block">
								<strong>@{{ errors['quantity.'+index][0] }}</strong>
							</span>
						</div>
					</div>

				</form>
			</div>

			<div v-if="!modal.readonly || (Object.keys(modal.action).length > 0)" class="modal-footer">
				<div class="form-group">
					<div class="col-md-2">
						<button v-if="!modal.readonly" type="button" class="btn btn-primary pull-right" onclick="return addNewPurchaseQuoteLine();">+&nbsp;{{ trans('forms.Add line') }}</button>
					</div>
          <div class="col-md-10">
						<button v-for="(callback, display) in modal.action" type="button" class="btn btn-primary pull-right" style="margin-left:3px;" v-on:click="window[callback]()" v-bind:disabled="form.line.length < 1" v-html="display">
						</button>
					</div>
        </div>
			</div>
		</div>
	</div>
</div>