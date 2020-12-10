

<div class="modal fade" id="embeddedTransactableModal" tabindex="-1" role="dialog" aria-labelledby="embeddedTransactableModalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">

	<!-- history modal -->
	<div class="modal fade" id="historyModal" tabindex="-1" role="dialog" aria-labelledby="historyModalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" aria-label="Close" onclick="$(this).closest('#historyModal').modal('hide')">
						<span aria-hidden="true">&times;</span>
					</button>
					<h4 class="modal-title" id="historyModalTitleLabel">{{ trans('forms.History') }}</h4>
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

	<div class="modal fade" id="aggregationModal" tabindex="-1" role="dialog" aria-labelledby="aggregationModalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" aria-label="Close" onclick="$(this).closest('#aggregationModal').modal('hide')">
						<span aria-hidden="true">&times;</span>
					</button>
					<h4 class="modal-title" id="aggregationModalTitleLabel">{{ trans('forms.Summary') }}</h4>
				</div>
				<div class="modal-body">
					<table v-if="form.status != 'void'">
						<tr>
							<td>{{ trans('forms.Subtotal') }}</td>
							<td style="padding-left:35px;" class="text-right">@{{ form.grand_subtotal }}</td>
						</tr>
						<tr>
							<td>{{ trans('forms.Tax amount') }}</td>
							<td style="padding-left:35px;" class="text-right">@{{ form.tax_amount }}</td>
						</tr>
						<tr>
							<td>{{ trans('forms.Grand total') }}</td>
							<td style="padding-left:35px;" class="text-right">@{{ form.grand_total }}</td>
						</tr>
						<tr>
							<td>{{ trans('forms.Balance') }}</td>
							<td style="padding-left:35px;" class="text-right">@{{ form.balance }}</td>
						</tr>
					</table>
					<p v-if="form.status == 'void'" id='calculated_total'>{{ trans('messages.This transaction is invalid') }}</p>
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
							<a style="padding-right:20px;" href="#" data-toggle="modal" data-target="#embeddedTransactableModal #aggregationModal"><span class="fa fa-2x fa-list"></span></a>
							<a v-if="modal.history && (modal.history.length > 0)" style="padding-right:20px;" href="#" data-toggle="modal" data-target="#embeddedTransactableModal #historyModal"><span class="fa fa-2x fa-history"></span></a>
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

				<form id="transactable_form" class="form-horizontal" role="form" method="POST" v-bind:action="modal.post_url">

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

						<label for="entity" class="col-md-2 control-label">{{ trans('forms.Entity') }}</label>

						<div class="col-md-4" v-bind:class="{ 'has-error' : 'entity' in errors }">
							<select id="entity" class="form-control" name="entity" v-model="form.entity" disabled>
								<option v-for="(display, id) in modal.entity" v-bind:value="id" >@{{ display }}</option>
							</select>
							<span v-if="'entity' in errors" class="help-block">
								<strong>@{{ errors['entity'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label for="incurdate" class="col-md-2 control-label">{{ trans('forms.Date') }}</label>

						<div class="col-md-3" v-bind:class="{ 'has-error' : 'incurdate' in errors }">
							<div class="input-group date">
								<input id="incurdate" type="text" class="form-control" v-bind:class="{ 'datepicker' : !modal.readonly }" name="incurdate" v-model="form.incur_date" v-bind:readonly="modal.readonly" >
								<div class="input-group-addon">
									<span class="glyphicon glyphicon-calendar"></span>
								</div>
							</div>
							<span v-if="'incurdate' in errors" class="help-block">
								<strong>@{{ errors['incurdate'][0] }}</strong>
							</span>
						</div>

						<label for="incoterm" class="col-md-2 control-label">{{ trans('forms.Payment') }}</label>

						<div class="col-md-3" v-bind:class="{ 'has-error' : 'payment' in errors }">
							<select id="payment" type="text" class="form-control" name="payment" v-model="form.payment" v-bind:disabled="modal.readonly">
								<option v-for="(display, id) in modal.payment" v-bind:value="id" >@{{ display }}</option>
							</select>
							<span v-if="'payment' in errors" class="help-block">
								<strong>@{{ errors['payment'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label for="duedate" class="col-md-2 control-label">{{ trans('forms.Due date') }}</label>

						<div class="col-md-3" v-bind:class="{ 'has-error' : 'duedate' in errors }">
							<div class="input-group date" >
								<input id="duedate" type="text" class="form-control" v-bind:class="{ 'datepicker' : !modal.readonly}" name="duedate" v-model="form.due_date" v-bind:readonly="modal.readonly">
								<div class="input-group-addon">
									<span class="glyphicon glyphicon-calendar"></span>
								</div>
							</div>
							<span v-if="'duedate' in errors" class="help-block">
								<strong>@{{ $errors['duedate'][0] }}</strong>
							</span>
						</div>

						<label for="source" class="col-md-2 control-label">{{ trans('forms.Source') }}</label>

						<div class="col-md-3" v-bind:class="{ 'has-error' : 'source' in errors }">
							<input id="source" type="text" class="form-control" name="source" v-model="form.source" v-bind:readonly="modal.readonly">
							<span v-if="'source' in errors" class="help-block">
								<strong>@{{ $errors['source'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label for="contact" class="col-md-2 control-label">{{ trans('forms.Contact') }}</label>

						<div class="col-md-3" v-bind:class="{ 'has-error' : 'contact' in errors }">
							<select id="contact" class="form-control" name="contact" v-model="form.contact" v-bind:disabled="modal.readonly">
								<option v-for="(display, id) in modal.contact" v-bind:value="display.id" >@{{ display.name }}</option>
							</select>
							<span v-if="'contact' in errors" class="help-block">
								<strong>@{{ errors['contact'][0] }}</strong>
							</span>
						</div>

						<label for="reference" class="col-md-2 control-label">{{ trans('forms.Reference') }}</label>

						<div class="col-md-3" v-bind:class="{ 'has-error' : 'reference' in errors }">
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
								<option v-for="(display, id) in modal.staff" v-bind:value="id" >@{{ display }}</option>
							</select>
							<span v-if="'staff' in errors" class="help-block">
								<strong>@{{ errors['staff'][0] }}</strong>
							</span>
						</div>

						<label for="currency" class="col-md-2 control-label">{{ trans('forms.Currency') }}</label>

						<div class="col-md-3" v-bind:class="{ 'has-error' : 'currency' in errors }">
							<select id="currency" type="text" class="form-control" name="currency" v-model="form.currency" v-bind:disabled="modal.readonly">
								<option v-for="(display, id) in modal.currency" v-bind:value="id" >@{{ display }}</option>
							</select>
							<span v-if="'currency' in errors" class="help-block">
								<strong>@{{ errors['currency'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label for="billing" class="col-md-2 control-label">{{ trans('forms.Bill to') }}</label>

						<div class="col-md-3" v-bind:class="{ 'has-error' : 'billing' in errors }">
							<!-- multiline select-option -->
              <div class="btn-group bootstrap-select form-control" style="height:100%;" v-bind:disabled="modal.readonly">
                <input id="billing" name="billing" type="hidden" v-model="form.billing">
                <!-- pointer-events:none prevent change background color when 'hover' -->
                <button type="button" class="btn dropdown-toggle selectpicker btn-default" data-toggle="dropdown" data-id="address" aria-expanded="false" v-bind:style="{ 'pointer-events' : modal.readonly ? 'none' : '', 'color' : modal.readonly ? '#000' : '#555', 'background-color' : modal.readonly ? '#eee' : '#fff' }" v-bind:disabled="modal.readonly">
                  <span id="billing_display" class="filter-option pull-left">@{{ billing_display.name }}<br>@{{ billing_display.street }} @{{ billing_display.unit }}<br>@{{ billing_display.city }} @{{ billing_display.district }}<br>@{{ billing_display.state }}<br>@{{ billing_display.country }} @{{ billing_display.zipcode }}<br>
                  </span>
                  <span class="caret">
                  </span>
                </button>
                <div class="dropdown-menu open" style="overflow: hidden; min-height: 0px;">
                  <ul class="dropdown-menu inner selectpicker" role="menu" style="overflow-y: auto; min-height: 0px;">
                    <li v-for="(address, index) in modal.billing" v-bind:rel="index">
                      <a tabindex="0" class="" style="" v-on:click="form.billing=address.id">@{{ address.name }}<br>@{{ address.street }} @{{ address.unit }}<br>@{{ address.city }}<br>@{{ address.state }}<br>@{{ address.country }} @{{ address.zipcode }}<br>
                        <i class="glyphicon glyphicon-ok icon-ok check-mark"></i>
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
							<span v-if="'billing' in errors" class="help-block">
								<strong>@{{ error['billing'][0] }}</strong>
							</span>
						</div>

						<label for="shipping" class="col-md-2 control-label">{{ trans('forms.Ship to') }}</label>

						<div class="col-md-3" v-bind:class="{ 'has-error' : 'shipping' in errors }">
							<!-- multiline select-option -->
              <div class="btn-group bootstrap-select form-control" style="height:100%;" v-bind:disabled="modal.readonly">
                <input id="shipping" name="shipping" type="hidden" v-model="form.shipping">
                <!-- pointer-events:none prevent change background color when 'hover' -->
                <button type="button" class="btn dropdown-toggle selectpicker btn-default" data-toggle="dropdown" data-id="address" aria-expanded="false" v-bind:style="{ 'pointer-events' : modal.readonly ? 'none' : '', 'color' : modal.readonly ? '#000' : '#555', 'background-color' : modal.readonly ? '#eee' : '#fff' }" v-bind:disabled="modal.readonly">
                  <span id="shipping_display" class="filter-option pull-left">@{{ shipping_display.name }}<br>@{{ shipping_display.street }} @{{ shipping_display.unit }}<br>@{{ shipping_display.city }} @{{ shipping_display.district }}<br>@{{ shipping_display.state }}<br>@{{ shipping_display.country }} @{{ shipping_display.zipcode }}<br>
                  </span>
                  <span class="caret">
                  </span>
                </button>
                <div class="dropdown-menu open" style="overflow: hidden; min-height: 0px;">
                  <ul class="dropdown-menu inner selectpicker" role="menu" style="overflow-y: auto; min-height: 0px;">
                    <li v-for="(address, index) in modal.shipping" v-bind:rel="index">
                      <a tabindex="0" class="" style="" v-on:click="form.shipping=address.id">@{{ address.name }}<br>@{{ address.street }} @{{ address.unit }}<br>@{{ address.city }}<br>@{{ address.state }}<br>@{{ address.country }} @{{ address.zipcode }}<br>
                        <i class="glyphicon glyphicon-ok icon-ok check-mark"></i>
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

					<div v-for="(id, index) in form.line" class="form-group detail-line">

						<input id="line[]" type="hidden" name="line[]" v-model="id" ></input>

						<div class="col-md-4" v-bind:class="{ 'has-error' : 'product.'+index in errors }">
							{{ trans('forms.Item') }}
							<select id="product[]" class="form-control" name="product[]" v-model="form.product[index]" v-bind:disabled="modal.readonly">
								<option v-for="(display, id) in modal.item" v-bind:value="id" >@{{ display }}</option>
							</select>
							<span v-if="'product.'+index in errors" class="help-block">
								<strong>@{{ errors['product.'+index][0] }}</strong>
							</span>
						</div>

						<div class="col-md-4" v-bind:class="{ 'has-error' : 'display.'+index in errors }">
							{{ trans('forms.Display') }}
							<input id="display[]" type="text" class="form-control" name="display[]" v-model="form.display[index]" v-bind:readonly="modal.readonly" ></input>
							<span v-if="'display.'+index in errors" class="help-block">
								<strong>@{{ errors['display.'+index][0] }}</strong>
							</span>
						</div>

						<div class="col-md-2 col-md-offset-2" v-bind:class="{ 'has-error' : 'unitprice.'+index in errors }">
							{{ trans('forms.Unit price') }}
							<input id="unitprice[]" type="number" style="text-align:right" v-bind:min="form.currencyFormat.min" v-bind:step="form.currencyFormat.min" class="form-control" name="unitprice[]" v-model="form.unitprice[index]" v-bind:readonly="modal.readonly"></input>
							<span v-if="'unitprice.'+index in errors" class="help-block">
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
							<input id="quantity[]" type="number" style="text-align:right" min="0" step="1" class="form-control" name="quantity[]" v-model="form.quantity[index]" v-bind:readonly="modal.readonly"></input>
							<span v-if="'quantity.'+index in errors" class="help-block">
								<strong>@{{ errors['quantity.'+index][0] }}</strong>
							</span>
						</div>

						<div class="col-md-3 col-md-offset-5" v-bind:class="{ 'has-error' : 'discount.'+index in errors }">
							{{ trans('forms.Discount') }}
							<div class="input-group">
								<span class="input-group-addon" ><strong>@{{ form.disctype[index] }}</strong></span>
								<input id="discount[]" type="number" min="0.00" step="0.01" class="form-control" name="discount[]" v-model="form.discount[index]" v-bind:readonly="modal.readonly"></input>
							</div>
							<span v-if="'discount.'+index in errors" class="help-block">
								<strong>@{{ errors['discount.'+index][0] }}</strong>
							</span>
						</div>

						<div class="col-md-1 col-md-offset-1" v-model="{ 'has-error' : 'taxable.'+index in errors }">
							{{ trans('forms.Taxable') }}
							<input id="taxable[]" type="hidden" name="taxable[]" v-model="form.linetax[index] > 0" >
							<input id="_taxable[]" type="checkbox" class="form-control" name="_taxable[]" v-model="form.linetax[index] > 0" v-bind:readonly="modal.readonly" v-bind:disabled="modal.readonly"></input>
							<span v-if="'taxable.'+index in errors" class="help-block">
								<strong>@{{ errors['taxable.'+index][0] }}</strong>
							</span>
						</div>

						<div class="col-md-2">
							{{ trans('forms.Subtotal') }}
							<input id="subtotal[]" type="text" style="text-align:right" v-bind:min="form.currency.min" v-bind:step="form.currency.min" class="form-control" name="subtotal[]" v-model="form.subtotal[index]" readonly></input>
						</div>

					</div>

				</form>

			</div>
			<div v-if="(Object.keys(modal.action).length > 0) || !modal.readonly" class="modal-footer">
				<div class="form-group">
					<div class="col-md-2">
						<button v-if="!modal.readonly" type="button" class="btn btn-info" onclick="return addNewOrderLine();" >+&nbsp;{{ trans('forms.Add line') }}</button>
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