@extends('layouts.app')

@section('content')
<div id="vue_container" class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">{{ $source['title'] }}</div>
					<div class="panel-body">
						<form class="form-horizontal" role="form" method="POST" action="{{ $source['post_url'] }}">
							{{ csrf_field() }}

							<input type="hidden" id="type" name="type" value="{{ $source['type'] }}" />

							<div class="form-group">
								<label for="increment" class="col-md-2 control-label">{{ trans('forms.Document') }}</label>

								<div class="col-md-3" v-bind:class="{ 'has-error' : 'increment' in errors }">
									<input id="increment" type="text" class="form-control" name="increment" v-model="form.increment" readonly disabled>
									<span v-if="'increment' in errors" class="help-block">
										<strong>@{{ errors['increment'][0] }}</strong>
									</span>
								</div>

								<label for="customer" class="col-md-2 control-label">{{ trans('forms.Customer') }}</label>

								<div class="col-md-4" v-bind:class="{ 'has-error' : 'customer' in errors }">
									<select id="customer" class="form-control" name="customer" v-model="form.customer" disabled>
									@foreach ($customer as $id => $display)
										<option value="{{ $id }}" >{{ $display }}</option>
									@endforeach
									</select>
									<span v-if="'customer' in errors" class="help-block">
										<strong>@{{ errors['customer'][0] }}</strong>
									</span>
								</div>
							</div>

							<div class="form-group">
								<label for="inputdate" class="col-md-2 control-label">{{ trans('forms.Date') }}</label>

								<div class="col-md-3" v-bind:class="{ 'has-error' : 'inputdate' in errors }">
									<div class="input-group date" {{ $readonly ? "" : "data-provide=\"datepicker\"" }}>
										<input id="inputdate" type="text" class="form-control" name="inputdate" v-model="form.inputdate" {{ $readonly ? "readonly disabled" : "" }}>
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
									<select id="payment" type="text" class="form-control" name="payment" v-model="form.payment" {{ $readonly ? "disabled" : "" }}>
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
									<input id="incoterm" type="text" class="form-control" name="incoterm" v-model="form.incoterm" {{ $readonly ? "readonly disabled" : "" }}>
									<span v-if="'incoterm' in errors" class="help-block">
										<strong>@{{ errors['incoterm'][0] }}</strong>
									</span>
								</div>
							</div>

							<div class="form-group">
								<label for="contact" class="col-md-2 control-label">{{ trans('forms.Contact') }}</label>

								<div class="col-md-3" v-bind:class="{ 'has-error' : 'contact' in errors }">
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
									<input id="reference" type="text" class="form-control" name="reference" v-model="form.reference" {{ $readonly ? "readonly disabled" : "" }}>
									<span v-if="'reference' in errors" class="help-block">
										<strong>@{{ errors['reference'][0] }}</strong>
									</span>
								</div>
							</div>

							<div class="form-group">
								<label for="staff" class="col-md-2 control-label">{{ trans('forms.Staff') }}</label>

								<div class="col-md-3" v-bind:class="{ 'has-error' : 'staff' in errors }">
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

							<hr />

							<div v-for="(id, index) in form.line" class="form-group detail-line">

								<input id="line[]" type="hidden" name="line[]" v-model="id" ></input>

								<div class="col-md-4" v-bind:class="{ 'has-error' : 'product.'+index in errors }">
									{{ trans('forms.Item') }}
									<select id="product[]" class="form-control" name="product[]" v-model="form.product[index]" {{ $readonly ? "disabled" : "" }}>
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
									<input id="display[]" type="text" class="form-control" name="display[]" v-model="form.display[index]" {{ $readonly ? "readonly disabled" : "" }}></input>
									<span v-if="'display.'+index in errors" class="help-block">
										<strong>@{{ errors['display.'+index][0] }}</strong>
									</span>
								</div>

								<div class="col-md-2 col-md-offset-2" v-bind:class="{ 'has-error' : 'unitprice.'+index in errors }">
									{{ trans('forms.Unit price') }}
									<input id="unitprice[]" type="number" style="text-align:right" min="{{ $currencyFormat['min'] }}" step="{{ $currencyFormat['min'] }}" class="form-control" name="unitprice[]" v-model="form.unitprice[index]" {{ $readonly ? "readonly" : "" }}></input>
									<span v-if="'unitprice.'+index in errors" class="help-block">
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

								<div class="col-md-2" v-bind:class="{ 'has-error' : 'processing.'+index in errors }">
									{{ trans('forms.Processing') }}
									<input id="processing[]" type="number" style="text-align:right" min="0" max="@{{ form.quantity[index] }}" step="1" class="form-control" name="processing[]" v-model="form.processing[index]" ></input>
									<span v-if="'processing.'+index in errors" class="help-block">
										<strong>@{{ errors['processing.'+index][0] }}</strong>
									</span>
								</div>

								<div class="col-md-2" v-bind:class="{ 'has-error' : 'quantity.'+index in errors }">
									{{ trans('forms.Quantity') }}
									<input id="quantity[]" type="number" style="text-align:right" min="0" step="1" class="form-control" name="quantity[]" v-model="form.quantity[index]" {{ $readonly ? "readonly" : "" }}></input>
									<span v-if="'quantity.'+index in errors" class="help-block">
										<strong>@{{ errors['quantity.'+index][0] }}</strong>
									</span>
								</div>

								<div class="col-md-3 col-md-offset-5" v-bind:class="{ 'has-error' : 'discount.'+index in errors }">
									{{ trans('forms.Discount') }}
									<div class="input-group">
										<span class="input-group-addon" ><strong>@{{ form.disctype[index] }}</strong></span>
										<input id="disctype[]" type="hidden" name="disctype[]" v-model="form.disctype[index]" ></input>
										<input id="discount[]" type="number" style="text-align:right" min="{{ $currencyFormat['min'] }}" step="{{ $currencyFormat['min'] }}" class="form-control" name="discount[]" v-model="form.discount[index]" {{ $readonly ? "readonly" : "" }}></input>
									</div>
									<span v-if="'discount.'+index in errors" class="help-block">
										<strong>@{{ errors['discount.'+index][0] }}</strong>
									</span>
								</div>

								<div class="col-md-1 col-md-offset-1" v-bind:class="{ 'has-error' : 'taxable.'+index in errors }">
									{{ trans('forms.Taxable') }}
									<input id="taxable[]" type="hidden" name="taxable[]" v-model="form.taxable[index]" ></input>
									<input id="_taxable[]" type="checkbox" class="form-control" name="_taxable[]" v-model="form.taxable[index]" {{ $readonly ? "disabled" : "" }}></input>
									<span v-if="'taxable.'+index in errors" class="help-block">
										<strong>@{{ errors['taxable.'+index][0] }}</strong>
									</span>
								</div>

								<div class="col-md-2" v-bind:class="{ 'has-error' : 'subtotal.'+index in errors }">
									{{ trans('forms.Subtotal') }}
									<input id="subtotal[]" type="number" style="text-align:right" min="{{ $currencyFormat['min'] }}" step="{{ $currencyFormat['min'] }}" class="form-control" name="subtotal[]" v-model="form.subtotal[index]" {{ $readonly ? "readonly" : "" }}></input>
									<span v-if="'subtotal.'+index in errors" class="help-block">
										<strong>@{{ errors['subtotal.'+index][0] }}</strong>
									</span>
								</div>
							</div>

							<div class="form-group">
								<div class="col-md-2">
								@if (!$readonly)
									<button type="button" class="btn btn-info" onclick="return addNewQuoteLine();" >
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
	</div>
@endsection

@section('post-content')
<script type="text/javascript">

	var vueContainer = null;

	function calculateSubtotal(unitprice, processing, discount, disctype, subtotal) {
		subtotal.value = parseFloat(unitprice.value) * parseFloat(processing.value);
		switch (disctype.value) {
		case '%':
			subtotal.value = (subtotal.value * (100 - parseFloat(discount.value)) / 100).toFixed({{ $currencyFormat['fdigit'] }});
			break;
		case '{!! $source['currencySymbol'] !!}':
			subtotal.value = (subtotal.value - parseFloat(discount.value) * parseFloat(processing.value)).toFixed({{ $currencyFormat['fdigit'] }});
			break;
		}
	}

	$(document).ready(function() {
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
				$('#expiration').datepicker().bind('change', function(event) {
					vueContainer.form.expiration = $(this).val();
				});
			}
		});
	});

</script>
@endsection
