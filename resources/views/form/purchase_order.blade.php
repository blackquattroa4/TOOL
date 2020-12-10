@extends('layouts.app')

@section('additional-style')
<style>
	.show-balance {
		position: fixed;
		bottom: 0em;
		left: 0px;
		text-decoration: none;
		color: #000000;
		background-color: rgba(255, 255, 255, 1.0);
		font-size: 15px;
		padding: 1em;
		display: none;
		z-index: 10;
	}
	#calculated_total {
		font-size: 38px;
		font-weight: bold;
		line-height: 38px;
	}
	.show-balance-hint {
		position: fixed;
		bottom: 0em;
		left: 0px;
		text-decoration: none;
		color: #000000;
		background-color: rgba(255, 255, 255, 0.0);
		font-size: 15px;
		padding: 1em;
		display: none;
		z-index: 10;
	}
</style>
@endsection

@section('content')
<div id="vue_container" class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
		@if (isset($source['history']) && count($source['history']))
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
						@foreach ($source['history'] as $oneLine)
							<p>{{ sprintf(trans('messages.%1$s %2$s at %3$s'), $oneLine->staff['name'], trans('action.'.$oneLine['process_status']), \App\Helpers\DateHelper::dbToGuiDate($oneLine['updated_at']->format("Y-m-d")) . " " . $oneLine['updated_at']->format("g:iA")) }}</p>
						@endforeach
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans("forms.Close") }}</button>
						</div>
					</div>
				</div>
			</div>
		@endif

			<div class="panel panel-default">
				<div class="panel-heading">
					<table width="100%">
						<tr>
							<td>{{ $source['title'] }}</td>
							<td>
							@if (isset($source['history']) && count($source['history']))
								<a href="#" data-toggle="modal" data-target="#historyModal"><span class="fa fa-2x fa-history pull-right"></span></a>
							@endif
							</td>
						</tr>
					</table>
				</div>
				<div class="panel-body">
					<form class="form-horizontal" role="form" method="POST" action="{{ $source['post_url'] }}">
						{{ csrf_field() }}

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
								<select id="_supplier" class="form-control" name="_supplier" v-model="form.supplier" disabled>
								@foreach ($supplier as $id => $display)
									<option value="{{ $id }}" >{{ $display }}</option>
								@endforeach
								</select>
								<span v-if="'supplier' in errors" class="help-block">
									<strong>@{{ errors['supplier'][0] }}</strong>
								</span>
							</div>
						</div>

						<div class="form-group">
							<label for="inputdate" class="col-md-2 control-label">{{ trans('forms.Date') }}</label>

							<div class="col-md-3" v-bind:class="{ 'has-error' : 'inputdate' in errors }">
								<div class="input-group date" {{ $readonly ? "data-provide=\"datepicker\"" : "" }}>
									<input id="inputdate" type="text" class="form-control" name="inputdate" v-model="form.inputdate" {{ $readonly ? "readonly" : "" }}>
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
								<select id="payment" class="form-control" name="payment" v-model="form.payment" {{ $readonly ? "disabled" : "" }}>
								@foreach ($payment as $id => $display)
									<option value="{{ $id }}" >{{ $display }}</option>
								@endforeach
								</select>
								<span v-if="'payment' in errors" class="help-block">
									<strong>@{{ errors['payment'][0] }}</strong>
								</span>
							</div>
						</div>

						<div class="form-group">
							<label for="incoterm" class="col-md-2 control-label">{{ trans('forms.Incoterm') }}</label>

							<div class="col-md-3" v-bind:class="{ 'has-error' : 'incoterm' in errors }">
								<input id="incoterm" type="text" class="form-control" name="incoterm" v-model="form.incoterm" {{ $readonly ? "readonly" : "" }}>
								<span v-if="'incoterm' in errors" class="help-block">
									<strong>@{{ errors['incoterm'][0] }}</strong>
								</span>
							</div>

							<label for="via" class="col-md-2 control-label">{{ trans('forms.Via') }}</label>

							<div class="col-md-4" v-bind:class="{ 'has-error' : 'via' in errors }">
								<input id="via" type="text" class="form-control" name="via" v-model="form.via" {{ $readonly ? "readonly" : "" }}>
								<span v-if="'via' in errors" class="help-block">
									<strong>@{{ errors['via'][0] }}</strong>
								</span>
							</div>
						</div>

						<div class="form-group">
							<label for="contact" class="col-md-2 control-label">{{ trans('forms.Contact') }}</label>

							<div class="col-md-4" v-bind:class="{ 'has-error' : 'contact' in errors }">
								<select id="contact" class="form-control" name="contact" v-model="form.contact" {{ $readonly ? "disabled" : "" }}>
								@foreach ($contact as $id => $display)
									<option value="{{ $id }}" >{{ $display }}</option>
								@endforeach
								</select>
								<span v-if="'contact' in errors" class="help-block">
									<strong>@{{ errors['contact'][0] }}</strong>
								</span>
							</div>

							<label for="reference" class="col-md-2 control-label">{{ trans('forms.Reference') }}</label>

							<div class="col-md-4" v-bind:class="{ 'has-error' : 'reference' in errors }">
								<input id="reference" type="text" class="form-control" name="reference" v-model="form.reference" {{ $readonly ? "readonly" : "" }}>
								<span v-if="'reference' in errors" class="help-block">
									<strong>@{{ errors['reference'][0] }}</strong>
								</span>
							</div>
						</div>

						<div class="form-group">
							<label for="staff" class="col-md-2 control-label">{{ trans('forms.Staff') }}</label>

							<div class="col-md-4" v-bind:class="{ 'has-error' : 'staff' in errors }">
								<select id="staff" class="form-control" name="staff" v-model="form.staff" {{ $readonly ? "disabled" : "" }}>
								@foreach ($staff as $id => $display)
									<option value="{{ $id }}" >{{ $display }}</option>
								@endforeach
								</select>
								<span v-if="'staff' in errors" class="help-block">
									<strong>@{{ errors['staff'][0] }}</strong>
								</span>
							</div>

							<label for="currency" class="col-md-2 control-label">{{ trans('forms.Currency') }}</label>

							<div class="col-md-4" v-bind:class="{ 'has-error' : 'currency' in errors }">
								<select id="currency" class="form-control" name="currency" v-model="form.currency" {{ $readonly ? "disabled" : "" }}>
								@foreach ($currency as $id => $display)
									<option value="{{ $id }}" >{{ $display }}</option>
								@endforeach
								</select>
								<span v-if="'currency' in errors" class="help-block">
									<strong>@{{ errors['currency'][0] }}</strong>
								</span>
							</div>
						</div>

						<div class="form-group">
							<label for="billing" class="col-md-2 control-label">{{ trans('forms.Bill to') }}</label>

							<div class="col-md-4" v-bind:class="{ 'has-error' : 'billing' in errors }">
								<div class="btn-group bootstrap-select form-control" style="height:100%;" {{ $readonly ? "disabled" : "" }}>
									<input id="billing" name="billing" type="hidden" v-model="form.billing">
	                <!-- pointer-events:none prevent change background color when 'hover' -->
	                <button type="button" class="btn dropdown-toggle selectpicker btn-default" data-toggle="dropdown" data-id="billing" aria-expanded="false" style="{{ $readonly ?  "pointer-events:none;color:#000;background-color:#eee" : "color:#555;background-color:#fff" }}" {{ $readonly ? "disabled" : "" }}>
	                  <span id="billing_display" name="billing_display" class="filter-option pull-left">
										@php
											$addressId = session()->getOldInput('billing');
										@endphp
	                    {{ $billing_address[$addressId]['name'] }}<br>
	                    {{ $billing_address[$addressId]['street'] }} {{ $billing_address[$addressId]['unit'] }}<br>
	                    {{ $billing_address[$addressId]['city'] }} {{ $billing_address[$addressId]['district'] }}<br>
	                    {{ $billing_address[$addressId]['state'] }}<br>
	                    {{ $billing_address[$addressId]['country'] }} {{ $billing_address[$addressId]['zipcode'] }}<br>
	                  </span>
	                  <span class="caret">
	                  </span>
	                </button>
	                <div class="dropdown-menu open" style="overflow: hidden; min-height: 0px;">
	                  <ul class="dropdown-menu inner selectpicker" role="menu" style="overflow-y: auto; min-height: 0px;">
										@foreach ($billing_address as $id => $display)
	                    <li rel="{{ $id }}">
	                      <a tabindex="0" class="" style="" onclick=" $('#billing_display').html($(this).html());" v-on:click="form.billing={{ $id }}">{{ $display['name'] }}<br>{{ $display['street'] }} {{ $display['unit'] }}<br>{{ $display['city'] }}<br>{{ $display['state'] }}<br>{{ $display['country'] }} {{ $display['zipcode'] }}<br>
	                      </a>
	                    </li>
										@endforeach
	                  </ul>
	                </div>
								</div>
								<span v-if="'billing' in errors" class="help-block">
									<strong>@{{ errors['billing'][0] }}</strong>
								</span>
							</div>

							<label for="shipping" class="col-md-2 control-label">{{ trans('forms.Ship to') }}</label>

							<div class="col-md-4" v-bind:class="{ 'has-error' : 'shipping' in errors }">
								<div class="btn-group bootstrap-select form-control" style="height:100%;" {{ $readonly ? "disabled" : "" }}>
									<input id="shipping" name="shipping" type="hidden" v-model="form.shipping">
	                <!-- pointer-events:none prevent change background color when 'hover' -->
	                <button type="button" class="btn dropdown-toggle selectpicker btn-default" data-toggle="dropdown" data-id="shipping" aria-expanded="false" style="{{ $readonly ?  "pointer-events:none;color:#000;background-color:#eee" : "color:#555;background-color:#fff" }}" {{ $readonly ? "disabled" : "" }}>
	                  <span id="shipping_display" name="shipping_display" class="filter-option pull-left">
										@php
											$addressId = session()->getOldInput('shipping');
										@endphp
	                    {{ $shipping_address[$addressId]['name'] }}<br>
	                    {{ $shipping_address[$addressId]['street'] }} {{ $shipping_address[$addressId]['unit'] }}<br>
	                    {{ $shipping_address[$addressId]['city'] }} {{ $shipping_address[$addressId]['district'] }}<br>
	                    {{ $shipping_address[$addressId]['state'] }}<br>
	                    {{ $shipping_address[$addressId]['country'] }} {{ $shipping_address[$addressId]['zipcode'] }}<br>
	                  </span>
	                  <span class="caret">
	                  </span>
	                </button>
	                <div class="dropdown-menu open" style="overflow: hidden; min-height: 0px;">
	                  <ul class="dropdown-menu inner selectpicker" role="menu" style="overflow-y: auto; min-height: 0px;">
										@foreach ($shipping_address as $id => $display)
	                    <li rel="{{ $id }}">
	                      <a tabindex="0" class="" style="" onclick=" $('#shipping_display').html($(this).html());" v-on:click="form.shipping={{ $id }}">{{ $display['name'] }}<br>{{ $display['street'] }} {{ $display['unit'] }}<br>{{ $display['city'] }}<br>{{ $display['state'] }}<br>{{ $display['country'] }} {{ $display['zipcode'] }}<br>
	                      </a>
	                    </li>
										@endforeach
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
								<select id="product[]" class="form-control" name="product[]" v-model="form.product[index]" {{ $readonly ? "disabled" : "" }} v-on:change="updateLineItem(index)">
								@foreach ($product_option as $oneKey => $oneOption)
									<option value="{{ $oneKey }}" >{{ $oneOption }}</option>
								@endforeach
								</select>
								<span v-if="'product.'+index in errors" class="help-block">
									<strong>@{{ errors['product.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-4" v-bind:class="{ 'has-error' : 'display.'+index in errors }">
								{{ trans('forms.Display') }}
								<input id="display[]" type="text" class="form-control" name="display[]" v-model="form.display[index]" {{ $readonly ? "readonly" : "" }}></input>
								<span v-if="'display.'+index in errors" class="help-block">
									<strong>@{{ errors['display.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-2" v-bind:class="{ 'has-error' : 'ivcost.'+index in errors }">
								{{ trans('forms.Inventory cost') }}
								<input id="ivcost[]" type="number" style="text-align:right" min="{{ sprintf("%.".$source['currencyFormat']['fdigit']."f", 0) }}" step="{{ $source['currencyFormat']['min'] }}" class="form-control" name="ivcost[]" v-model="form.ivcost[index]" {{ $readonly ? "disabled" : "" }}></input>
								<span v-if="'ivcost.'+index in errors" class="help-block">
									<strong>@{{ errors['ivcost.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-2" v-bind:class="{ 'has-error' : 'unitprice.'+index in errors }">
								{{ trans('forms.Unit price') }}
								<input id="unitprice[]" type="number" style="text-align:right" min="0" step="{{ $source['currencyFormat']['min'] }}" class="form-control" name="unitprice[]" v-model="form.unitprice[index]" {{ $readonly ? "readonly" : "" }} v-on:change="updateSubtotal(index)"></input>
								<span v-if="'unitprice.'+index in errors"class="help-block">
									<strong>@{{ errors['unitprice.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-7 col-md-offset-1" v-bind:class="{ 'has-error' : 'description.'+index in errors }">
								{{ trans('forms.Description') }}
								<input id="description[]" type="text" class="form-control" name="description[]" v-model="form.description[index]" {{ $readonly ? "readonly" : "" }}></input>
								<span v-if="'description.'+index in errors" class="help-block">
									<strong>@{{ errors['description.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-2 col-md-offset-2" v-bind:class="{ 'has-error' : 'quantity.'+index in errors }">
								{{ trans('forms.Quantity') }}
								<input id="quantity[]" type="number" style="text-align:right" min="0" step="1" class="form-control" name="quantity[]" v-model="form.quantity[index]" {{ $readonly ? "readonly" : "" }} v-on:change="updateSubtotal(index)"></input>
								<span v-if="'quantity.'+index in errors" class="help-block">
									<strong>@{{ errors['quantity.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-3 col-md-offset-1" v-bind:class="{ 'has-error' : 'ddate.'+index in errors }">
								{{ trans('forms.Delivery date') }}
								<div class="input-group date" {{ !$readonly ? "data-provide=\"datepicker\"" : "" }}>
									<input id="ddate[]" v-bind:ref="'ddate'+index" type="text" class="form-control" name="ddate[]" v-bind:data-line="index" v-model="form.ddate[index]" {{ $readonly ? "readonly" : "" }}>
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
								<select id="warehouse[]" class="form-control" name="warehouse[]" v-model="form.warehouse[index]" {{ $readonly ? "disabled" : "" }}>
								@foreach ($warehouse as $oneKey => $oneLocation)
									<option value="{{ $oneKey }}" >{{ $oneLocation }}</option>
								@endforeach
								</select>
								<span v-if="'warehouse.'+index in errors" class="help-block">
									<strong>@{{ errors['warehouse.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-1 col-md-offset-1" v-bind:class="{ 'has-error' : 'taxable.'+index in errors }">
								{{ trans('forms.Taxable') }}
								<input id="taxable[]" type="checkbox" class="form-control" name="taxable[]" v-model="form.taxable[index]" {{ $readonly ? "disabled" : "" }} v-on:change="updateTotal()"></input>
								<span v-if="'taxable.'+index in errors" class="help-block">
									<strong>@{{ errors['taxable.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-2" v-bind:class="{ 'has-error' : 'subtotal.'+index in errors }">
								{{ trans('forms.Subtotal') }}
								<input id="subtotal[]" type="text" style="text-align:right" class="form-control" name="subtotal[]" v-model="form.subtotal[index]" v-bind:key="form.subtotal[index]" readonly></input>
							</div>
						</div>

						<div class="form-group">
							<div class="col-md-2">
							@if (!$readonly)
								<button type="button" class="btn btn-info" onclick="return addNewOrderLine();">
									+&nbsp;{{ trans('forms.Add line') }}
								</button>
							@endif
							</div>

						@if (is_array($source['action']))
							<div class="col-md-{{ 10-2*count($source['action']) }}">
							</div>
							@foreach ($source['action'] as $keyAction => $oneAction)
							<div class="col-md-2">
								<button type="submit" class="btn btn-primary" id="submit" name="submit" value="{{ $keyAction }}">
									<i class="fa fa-btn fa-floppy-o"></i> {{ $oneAction }}
								</button>
							</div>
							@endforeach
						@else
							<div class="col-md-2 col-md-offset-8">
								<button type="submit" class="btn btn-primary">
									<i class="fa fa-btn fa-floppy-o"></i> {{ $source['action'] }}
								</button>
							</div>
						@endif
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<a href='#' class='show-balance-hint'>
		<img src="{{ asset('images/sum-512.png') }}" height='40' width='40'></img>
	</a>
	<div class='show-balance'>
		<p id='calculated_total'>{{ trans('forms.Untaxed subtotal') }}&emsp;@{{ form.untaxed_subtotal }}<br>{{ trans('forms.Taxed subtotal') }}&emsp;@{{ form.taxed_subtotal }}<br>{{ trans('forms.Tax amount') }}&emsp;@{{ form.tax_amount }}<br>{{ trans('forms.Grand total') }}&emsp;@{{ form.grand_total }}</p>
	</div>
</div>
@endsection

@section('post-content')

	<script src="/js/ShowHideHint.js"></script>

	<script type="text/javascript">

		var vueContainer = null;

		function dataSource() {
			// function that holds data
		}

	@if (!$readonly)
		function updateTotal() {
			let untaxed = 0, taxed = 0, tax = 0;

			for(index in vueContainer.form.subtotal) {
				if (vueContainer.form.taxable[index]) {
					taxed += parseFloat(vueContainer.form.subtotal[index]);
					tax += 0;
				} else {
					untaxed += parseFloat(vueContainer.form.subtotal[index]);
				}
			}

			vueContainer.form.untaxed_subtotal = untaxed.toLocaleString('{{ $source['currencyFormat']['regex'] }}', { style: 'currency', currency: '{{ $source['currencyFormat']['symbol'] }}' });
			vueContainer.form.taxed_subtotal = taxed.toLocaleString('{{ $source['currencyFormat']['regex'] }}', { style: 'currency', currency: '{{ $source['currencyFormat']['symbol'] }}' });
			vueContainer.form.tax_amount = tax.toLocaleString('{{ $source['currencyFormat']['regex'] }}', { style: 'currency', currency: '{{ $source['currencyFormat']['symbol'] }}' });;
			vueContainer.form.grand_total = (taxed + untaxed).toLocaleString('{{ $source['currencyFormat']['regex'] }}', { style: 'currency', currency: '{{ $source['currencyFormat']['symbol'] }}' });

			hideThenShowHint('.show-balance', '.show-balance-hint');
		}

		function updateSubtotal(index) {
			vueContainer.form.subtotal[index] = (parseFloat(vueContainer.form.unitprice[index]) * parseFloat(vueContainer.form.quantity[index])).toFixed({{ $source['currencyFormat']['fdigit'] }});

			updateTotal();
		}

		function updateLineItem(index) {
			let product = dataSource.extended_product_option[vueContainer.form.product[index]];

			vueContainer.form.display[index] = product.display;
			vueContainer.form.description[index] = product.description;
			vueContainer.form.ivcost[index] = product.unit_price;
			vueContainer.form.unitprice[index] = product.unit_price;

			updateSubtotal(index);
		}

		function addNewOrderLine() {
			vueContainer.form.line.push(0);
			vueContainer.form.product.push(Object.keys(dataSource.extended_product_option)[0]);
			vueContainer.form.display.push(Object.values(dataSource.extended_product_option)[0].display);
			vueContainer.form.ivcost.push(Object.values(dataSource.extended_product_option)[0].unit_price);
			vueContainer.form.unitprice.push(Object.values(dataSource.extended_product_option)[0].unit_price);
			vueContainer.form.description.push(Object.values(dataSource.extended_product_option)[0].description);
			vueContainer.form.quantity.push(0);
			vueContainer.form.ddate.push('{{ \App\Helpers\DateHelper::dbToGuiDate(date("Y-m-d")) }}');
			vueContainer.form.warehouse.push({{ array_keys($warehouse)[0] }});
			vueContainer.form.taxable.push(0);
			vueContainer.form.subtotal.push(0);
			// wait until next tick (component rendered) to install date selector
			let id = Object.keys(vueContainer.form.line).pop();
			Vue.nextTick(function () {
				$(vueContainer.$refs["ddate"+id]).datepicker().bind('change', function() {
					vueContainer.form.ddate[$(this).data('line')] = $(this).val();
				});
			});
		}
	@endif

		$(document).ready(function() {

			dataSource.extended_product_option = {!! json_encode($extended_product_option) !!}

			$('.selectpicker').selectpicker();
			//hideThenShowHint('.show-balance', '.show-balance-hint');
			$(window).mousemove(function(event) {
				showOrHideHint(event, '.show-balance', '.show-balance-hint')
			});

			vueContainer = new Vue({
				el : '#vue_container',
			@php
				$oldInput = session()->getOldInput();
			@endphp
				data : {
					form : {!! json_encode($oldInput) !!},
					errors : {!! json_encode($errors->toArray()) !!}
				},
				mounted : function() {
				@if (!$readonly)
					$('#inputdate').datepicker().bind('change', function(event) {
		        vueContainer.form.inputdate = $(this).val();
		      });
					$('input[name^="ddate["]').datepicker().bind('change', function(event) {
		        vueContainer.form.ddate[$(this).data('line')] = $(this).val();
		      });
				@endif
					hideThenShowHint('.show-balance', '.show-balance-hint');
				}
			});

		});
	</script>
@endsection
