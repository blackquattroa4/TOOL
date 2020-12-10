

<div class="modal fade" id="embeddedPurchaseEntryModal" tabindex="-1" role="dialog" aria-labelledby="embeddedPurchaseEntryLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">

	<!-- history modal -->
	<div class="modal fade" id="historyModal" tabindex="-1" role="dialog" aria-labelledby="historyModalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" aria-label="Close" onclick="$(this).closest('#historyModal').modal('hide')">
						<span aria-hidden="true">&times;</span>
					</button>
					<h4 class="modal-title" id="deleteModalLabel">{{ trans('forms.History') }}</h4>
				</div>
				<div class="modal-body">
					<p v-for="record in modal.history">@{{ record }}</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" onclick="$(this).closest('#historyModal').modal('hide')">{{ trans("forms.Close") }}</button>
				</div>
			</div>
		</div>
	</div>

	<!-- aggregation modal -->
	<div class="modal fade" id="aggregationModal" tabindex="-1" role="dialog" aria-labelledby="aggregationModalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" aria-label="Close" onclick="$(this).closest('#aggregationModal').modal('hide')">
						<span aria-hidden="true">&times;</span>
					</button>
					<h4 class="modal-title" id="aggregationModalLabel">{{ trans('forms.Summary') }}</h4>
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
					<button type="button" class="btn btn-secondary" onclick="$(this).closest('#aggregationModal').modal('hide')">{{ trans("forms.Close") }}</button>
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
							<a style="padding-right:20px;" href="#" data-toggle="modal" data-target="#embeddedPurchaseEntryModal #aggregationModal"><span class="fa fa-2x fa-list"></span></a>
							<a v-if="modal.history && (modal.history.length > 0)" style="padding-right:20px;" href="#" data-toggle="modal" data-target="#embeddedPurchaseEntryModal #historyModal"><span class="fa fa-2x fa-history"></span></a>
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
				<form id="purchase_order" class="form-horizontal" role="form" method="POST" v-bind:action="modal.post_url">

					<input type="hidden" name="_token" v-model="modal.csrf">

					<input type="hidden" id="type" name="type" v-model="form.type" />

					<div class="form-group">
						<label for="increment" class="col-md-2 control-label">{{ trans('forms.Document') }}</label>

						<div class="col-md-3" v-bind:class="{ 'has-error' : 'increment' in errors }">
							<input id="increment" type="text" class="form-control" name="increment" v-model="form.increment" readonly>
							<span v-if="'increment' in errors" class="help-block">
								<strong>@{{ errors['increment'] }}</strong>
							</span>
						</div>

						<label for="supplier" class="col-md-2 control-label">{{ trans('forms.Supplier') }}</label>

						<input id="supplier" type="hidden" class="form-control" name="supplier" v-model="form.supplier" >

						<div class="col-md-4" v-bind:class="{ 'has-error' : 'supplier' in errors }">
							<select id="_supplier" class="form-control" name="_supplier" v-model="form.supplier" v-bind:disabled="modal.readonly || (form.id != 0)" v-on:change="changePurchaseEntryModalEntity()">
								<option v-for="(display, index) in modal.supplier" v-bind:value="index" >@{{ display.display }}</option>
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

						<label for="incoterm" class="col-md-2 control-label">{{ trans('forms.Payment') }}</label>

						<div class="col-md-4" v-bind:class="{ 'has-error' : 'payment' in errors }">
							<input id="payment" type="hidden" name="payment" v-model="form.payment">
							<select id="_payment" class="form-control" name="_payment" v-model="form.payment" disabled>
								<option v-for="(display, index) in modal.payment" v-bind:value="index" >@{{ display }}</option>
							</select>
							<span v-if="'payment' in errors" class="help-block">
								<strong>@{{ errors['payment'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label for="incoterm" class="col-md-2 control-label">{{ trans('forms.Incoterm') }}</label>

						<div class="col-md-3" v-bind:class="{ 'has-error' : 'incoterm' in errors }">
							<input id="incoterm" type="text" class="form-control" name="incoterm" v-model="form.incoterm" v-bind:readonly="modal.readonly">
							<span v-if="'incoterm' in errors" class="help-block">
								<strong>@{{ errors['incoterm'][0] }}</strong>
							</span>
						</div>

						<label for="via" class="col-md-2 control-label">{{ trans('forms.Via') }}</label>

						<div class="col-md-4" v-bind:class="{ 'has-error' : 'via' in errors }">
							<input id="via" type="text" class="form-control" name="via" v-model="form.via" v-bind:readonly="modal.readonly">
							<span v-if="'via' in errors" class="help-block">
								<strong>@{{ errors['via'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label for="contact" class="col-md-2 control-label">{{ trans('forms.Contact') }}</label>

						<div class="col-md-4" v-bind:class="{ 'has-error' : 'contact' in errors }">
							<select id="contact" class="form-control" name="contact" v-model="form.contact" v-bind:disabled="modal.readonly">
								<option v-for="(display, index) in modal.contact" v-bind:value="display.id" >@{{ display.name }}</option>
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

						<div class="col-md-4" v-bind:class="{ 'has-error' : 'staff' in errors }">
							<select id="staff" class="form-control" name="staff" v-model="form.staff" v-bind:disabled="modal.readonly">
								<option v-for="(display, index) in modal.staff" v-bind:value="index" >@{{ display }}</option>
							</select>
							<span v-if="'staff' in errors" class="help-block">
								<strong>@{{ errors['staff'][0] }}</strong>
							</span>
						</div>

						<label for="currency" class="col-md-2 control-label">{{ trans('forms.Currency') }}</label>

						<div class="col-md-4" v-bind:class="{ 'has-error' : 'currency' in errors }">
							<input id="currency" type="hidden" name="currency" v-model="form.currency" >
							<select id="_currency" class="form-control" name="_currency" v-model="form.currency" disabled>
								<option v-for="(display, index) in modal.currency" v-bind:value="index" >@{{ display }}</option>
							</select>
							<span v-if="'currency' in errors" class="help-block">
								<strong>@{{ errors['currency'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label for="billing" class="col-md-2 control-label">{{ trans('forms.Bill to') }}</label>

						<div class="col-md-4" v-bind:class="{ 'has-error' : 'billing' in errors }">
							<div class="btn-group bootstrap-select form-control" style="height:100%;" v-bind:disabled="modal.readonly">
								<input id="billing" name="billing" type="hidden" v-model="form.billing_address">
                <!-- pointer-events:none prevent change background color when 'hover' -->
                <button type="button" class="btn dropdown-toggle selectpicker btn-default" data-toggle="dropdown" data-id="billing" aria-expanded="false" v-bind:style="{ 'pointer-events' : modal.readonly ? 'none' : '', 'color' : modal.readonly ? '#000' : '#555', 'background-color' : modal.readonly ? '#eee' : '#fff' }" v-bind:disabled="modal.readonly">
                  <span id="billing_display" name="billing_display" class="filter-option pull-left">@{{ billing_address.name }}<br>@{{ billing_address.street }} @{{ billing_address.unit }}<br>@{{ billing_address.city }} @{{ billing_address.district }}<br>@{{ billing_address.state }}<br>@{{ billing_address.country }} @{{ billing_address.zipcode }}<br>
                  </span>
                  <span class="caret">
                  </span>
                </button>
                <div class="dropdown-menu open" style="overflow: hidden; min-height: 0px;">
                  <ul class="dropdown-menu inner selectpicker" role="menu" style="overflow-y: auto; min-height: 0px;">
                    <li v-for="(address, index) in modal.billing_address" v-bind:rel="index">
                      <a tabindex="0" class="" style="" v-on:click="form.billing_address=address.id">@{{ address.name }}<br>@{{ address.street }} @{{ address.unit }}<br>@{{ address.city }}<br>@{{ address.state }}<br>@{{ address.country }} @{{ address.zipcode }}<br>
                      </a>
                    </li>
                  </ul>
                </div>
							</div>
							<span v-if="'billing' in errors" class="help-block">
								<strong>@{{ errors['billing'][0] }}</strong>
							</span>
						</div>

						<label for="shipping" class="col-md-2 control-label">{{ trans('forms.Ship to') }}</label>

						<div class="col-md-4" v-bind:class="{ 'has-error' : 'shipping' in errors }">
							<div class="btn-group bootstrap-select form-control" style="height:100%;" v-bind:disabled="modal.readonly">
								<input id="shipping" name="shipping" type="hidden" v-model="form.shipping_address">
                <!-- pointer-events:none prevent change background color when 'hover' -->
                <button type="button" class="btn dropdown-toggle selectpicker btn-default" data-toggle="dropdown" data-id="shipping" aria-expanded="false" v-bind:style="{ 'pointer-events' : modal.readonly ? 'none' : '', 'color' : modal.readonly ? '#000' : '#555', 'background-color' : modal.readonly ? '#eee' : '#fff' }" v-bind:disabled="modal.readonly">
                  <span id="shipping_display" name="shipping_display" class="filter-option pull-left">@{{ shipping_address.name }}<br>@{{ shipping_address.street }} @{{ shipping_address.unit }}<br>@{{ shipping_address.city }} @{{ shipping_address.district }}<br>@{{ shipping_address.state }}<br>@{{ shipping_address.country }} @{{ shipping_address.zipcode }}<br>
                  </span>
                  <span class="caret">
                  </span>
                </button>
                <div class="dropdown-menu open" style="overflow: hidden; min-height: 0px;">
                  <ul class="dropdown-menu inner selectpicker" role="menu" style="overflow-y: auto; min-height: 0px;">
                    <li v-for="(address, index) in modal.shipping_address" v-bind:rel="index">
                      <a tabindex="0" class="" style="" v-on:click="form.shipping_address=address.id">@{{ address.name }}<br>@{{ address.street }} @{{ address.unit }}<br>@{{ address.city }}<br>@{{ address.state }}<br>@{{ address.country }} @{{ address.zipcode }}<br>
                      </a>
                    </li>
                  </ul>
                </div>
							</div>
							<span v-if="'shipping' in errors" class="help-block">
								<strong>@{{ errors['shipping'][0] }}</strong>
							</span>
						</div>
					</div>

					<hr />

					<div v-for="(line, index) in form.line" class="form-group detail-line">
						<input id="line[]" type="hidden" name="line[]" v-model="line" ></input>

						<div class="col-md-4" v-bind:class="{ 'has-error' : 'product.'+index in errors }">
							{{ trans('forms.Item') }}
							<select id="product[]" class="form-control" name="product[]" v-model="form.product[index]" v-bind:disabled="modal.readonly" v-on:change="updatePurchaseEntryLineItem(index)">
								<option v-for="(display, index2) in modal.product" v-bind:value="index2" >@{{ display.sku }}</option>
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

						<div class="col-md-2" v-bind:class="{ 'has-error' : 'ivcost.'+index in errors }">
							{{ trans('forms.Inventory cost') }}
							<input id="ivcost[]" type="number" style="text-align:right" v-bind:min="modal.currency_min" v-bind:step="modal.currency_min" class="form-control" name="ivcost[]" v-model="form.ivcost[index]" v-bind:disabled="modal.readonly"></input>
							<span v-if="'ivcost.'+index in errors" class="help-block">
								<strong>@{{ errors['ivcost.'+index][0] }}</strong>
							</span>
						</div>

						<div class="col-md-2" v-bind:class="{ 'has-error' : 'unitprice.'+index in errors }">
							{{ trans('forms.Unit price') }}
							<input id="unitprice[]" type="number" style="text-align:right" min="0" v-bind:step="modal.currency_min" class="form-control" name="unitprice[]" v-model="form.unitprice[index]" v-bind:readonly="modal.readonly" v-on:change="updatePurchaseEntrySubtotal(index)"></input>
							<span v-if="'unitprice.'+index in errors"class="help-block">
								<strong>@{{ errors['unitprice.'+index][0] }}</strong>
							</span>
						</div>

						<div class="col-md-7 col-md-offset-1" v-bind:class="{ 'has-error' : 'description.'+index in errors }">
							{{ trans('forms.Description') }}
							<input id="description[]" type="text" class="form-control" name="description[]" v-model="form.description[index]" v-bind:readonly="modal.readonly"></input>
							<span v-if="'description.'+index in errors" class="help-block">
								<strong>@{{ errors['description.'+index][0] }}</strong>
							</span>
						</div>

						<div class="col-md-2 col-md-offset-2" v-bind:class="{ 'has-error' : 'quantity.'+index in errors }">
							{{ trans('forms.Quantity') }}
							<input id="quantity[]" type="number" style="text-align:right" min="0" step="1" class="form-control" name="quantity[]" v-model="form.quantity[index]" v-bind:readonly="modal.readonly" v-on:change="updatePurchaseEntrySubtotal(index)"></input>
							<span v-if="'quantity.'+index in errors" class="help-block">
								<strong>@{{ errors['quantity.'+index][0] }}</strong>
							</span>
						</div>

						<div class="col-md-3 col-md-offset-1" v-bind:class="{ 'has-error' : 'ddate.'+index in errors }">
							{{ trans('forms.Delivery date') }}
							<div class="input-group date" v-bind:data-provide="modal.readonly ? '' : 'datepicker'">
								<input id="ddate[]" v-bind:ref="'ddate'+index" type="text" class="form-control" name="ddate[]" v-bind:data-line="index" v-model="form.ddate[index]" v-bind:readonly="modal.readonly" >
								<div class="input-group-addon">
									<span class="glyphicon glyphicon-calendar"></span>
								</div>
							</div>
							<span v-if="'ddate.'+index in errors" class="help-block">
								<strong>@{{ errors['ddate.'+index][0] }}</strong>
							</span>
						</div>

						<div class="col-md-4" v-bind:class="{ 'has-error' : 'warehouse.'+index in errors }">
							{{ trans('forms.Destination') }}
							<select id="warehouse[]" class="form-control" name="warehouse[]" v-model="form.warehouse[index]" v-bind:disabled="modal.readonly">
								<option v-for="(display, index2) in modal.warehouse" v-bind:value="display.id" >@{{ display.name }}</option>
							</select>
							<span v-if="'warehouse.'+index in errors" class="help-block">
								<strong>@{{ errors['warehouse.'+index][0] }}</strong>
							</span>
						</div>

						<div class="col-md-1 col-md-offset-1" v-bind:class="{ 'has-error' : 'taxable.'+index in errors }">
							{{ trans('forms.Taxable') }}
							<input id="taxable[]" type="hidden" class="form-control" name="taxable[]" v-model="form.taxable[index]" ></input>
							<input id="_taxable[]" type="checkbox" class="form-control" name="_taxable[]" v-model="form.taxable[index]" v-bind:disabled="modal.readonly" v-on:change="updatePurchaseEntryTotal()"></input>
							<span v-if="'taxable.'+index in errors" class="help-block">
								<strong>@{{ errors['taxable.'+index][0] }}</strong>
							</span>
						</div>

						<div class="col-md-2" v-bind:class="{ 'has-error' : 'subtotal.'+index in errors }">
							{{ trans('forms.Subtotal') }}
							<input id="subtotal[]" type="text" style="text-align:right" class="form-control" name="subtotal[]" v-model="form.subtotal[index]" v-bind:key="form.subtotal[index]" readonly></input>
						</div>
					</div>
				</form>
			</div>

			<div v-if="!modal.readonly || (Object.keys(modal.action).length > 0)" class="modal-footer">
				<div class="form-group">
					<div class="col-md-2">
						<button v-if="!modal.readonly" type="button" class="btn btn-primary pull-right" onclick="return addNewPurchaseEntryLine();">+&nbsp;{{ trans('forms.Add line') }}</button>
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