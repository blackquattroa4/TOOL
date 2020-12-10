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
									<strong>@{{ errors['increment'][0] }}</strong>
								</span>
							</div>

							<label for="entity" class="col-md-2 control-label">{{ trans('forms.Entity') }}</label>

							<div class="col-md-4" v-bind:class="{ 'has-error' : 'entity' in errors }">
								<select id="entity" class="form-control" name="entity" v-model="form.entity" disabled>
								@foreach ($entity as $id => $display)
									<option value="{{ $id }}" >{{ $display }}</option>
								@endforeach
								</select>
								<span v-if="'entity' in errors" class="help-block">
									<strong>@{{ errors['entity'][0] }}</strong>
								</span>
							</div>
						</div>

						<div class="form-group">
							<label for="incurdate" class="col-md-2 control-label">{{ trans('forms.Date') }}</label>

							<div class="col-md-3" v-bind:class="{ 'has-error' : 'incurdate' in errors }">
								<div class="input-group date" {{ $readonly ? "" : "data-provide=\"datepicker\"" }}>
									<input id="incurdate" type="text" class="form-control" name="incurdate" v-model="form.incurdate" {{ $readonly ? "readonly" : "" }}>
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
							<label for="duedate" class="col-md-2 control-label">{{ trans('forms.Due date') }}</label>

							<div class="col-md-3" v-bind:class="{ 'has-error' : 'duedate' in errors }">
								<div class="input-group date" {{ $readonly ? "" : "data-provide=\"datepicker\"" }}>
									<input id="duedate" type="text" class="form-control" name="duedate" v-model="form.duedate" {{ $readonly ? "readonly" : "" }}>
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
								<input id="source" type="text" class="form-control" name="source" v-model="form.source" {{ $readonly ? "readonly" : "" }}>
								<span v-if="'source' in errors" class="help-block">
									<strong>@{{ $errors['source'][0] }}</strong>
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

							<div class="col-md-3" v-bind:class="{ 'has-error' : 'reference' in errors }">
								<input id="reference" type="text" class="form-control" name="reference" v-model="form.reference" {{ $readonly ? "readonly" : "" }}>
								<span v-if="'reference' in errors" class="help-block">
									<strong>@{{ errors['reference'][0] }}</strong>
								</span>
							</div>
						</div>

						<div class="form-group">
							<label for="staff" class="col-md-2 control-label">{{ trans('forms.Staff') }}</label>

							<div class="col-md-3" v-bind:class="{ 'has-error' : 'staff' in errors }">
								<select id="staff" type="text" class="form-control" name="staff" v-model="form.staff" {{ $readonly ? "disabled" : "" }}>
								@foreach ($staff as $id => $display)
									<option value="{{ $id }}" >{{ $display }}</option>
								@endforeach
								</select>
								<span v-if="'staff' in errors" class="help-block">
									<strong>@{{ errors['staff'][0] }}</strong>
								</span>
							</div>

							<label for="currency" class="col-md-2 control-label">{{ trans('forms.Currency') }}</label>

							<div class="col-md-3" v-bind:class="{ 'has-error' : 'currency' in errors }">
								<select id="currency" type="text" class="form-control" name="currency" v-model="form.currency" {{ $readonly ? "disabled" : "" }}>
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

							<div class="col-md-3" v-bind:class="{ 'has-error' : 'billing' in errors }">
								<select id="billing" class="form-control selectpicker" name="billing" v-model="form.billing" {{ $readonly ? "disabled" : "" }}>
								@foreach ($billing_address as $id => $display)
									<option value="{{ $id }}" data-content="{{ $display['name'] }}<br>{{ $display['street'] }}&nbsp;{{ $display['unit'] }}<br>{{ $display['city'] }}&nbsp;{{ $display['district'] }}<br>{{ $display['state'] }}<br>{{ $display['country'] }}&nbsp;{{ $display['zipcode'] }}<br>" >{{ $display['street'] }}</option>
								@endforeach
								</select>
								<span v-if="'billing' in errors" class="help-block">
									<strong>@{{ error['billing'][0] }}</strong>
								</span>
							</div>

							<label for="shipping" class="col-md-2 control-label">{{ trans('forms.Ship to') }}</label>

							<div class="col-md-3" v-bind:class="{ 'has-error' : 'shipping' in errors }">
								<select id="shipping" class="form-control selectpicker" name="shipping" v-model="form.shipping" {{ $readonly ? "disabled" : "" }}>
								@foreach ($shipping_address as $id => $display)
									<option value="{{ $id }}" data-content="{{ $display['name'] }}<br>{{ $display['street'] }}&nbsp;{{ $display['unit'] }}<br>{{ $display['city'] }}&nbsp;{{ $display['district'] }}<br>{{ $display['state'] }}<br>{{ $display['country'] }}&nbsp;{{ $display['zipcode'] }}<br>" >{{ $display['street'] }}</option>
								@endforeach
								</select>
								<span v-if="'shipping' in errors" class="help-block">
									<strong>@{{ errors['shipping'][0] }}</strong>
								</span>
							</div>
						</div>

						&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>

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
								<input id="display[]" type="text" class="form-control" name="display[]" v-model="form.display[index]" {{ $readonly ? "readonly" : "" }}></input>
								<span v-if="'display.'+index in errors" class="help-block">
									<strong>@{{ errors['display.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-2 col-md-offset-2" v-bind:class="{ 'has-error' : 'unitprice.'+index in errors }">
								{{ trans('forms.Unit price') }}
								<input id="unitprice[]" type="number" style="text-align:right" min="{{ $source['currencyFormat']['min'] }}" step="{{ $source['currencyFormat']['min'] }}" class="form-control" name="unitprice[]" v-model="form.unitprice[index]" {{ $readonly ? "readonly" : "" }}></input>
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

							<div class="col-md-2 col-md-offset-2" v-bind:class="{ 'has-error' : 'quantity.'+index in errors }">
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
									<input id="discount[]" type="number" min="0.00" step="0.01" class="form-control" name="discount[]" v-model="form.discount[index]" readonly></input>
								</div>
								<span v-if="'discount.'+index in errors" class="help-block">
									<strong>@{{ errors['discount.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-1 col-md-offset-1" v-model="{ 'has-error' : 'taxable.'+index in errors }">
								{{ trans('forms.Taxable') }}
								<input id="taxable[]" type="hidden" name="taxable[]" v-model="form.linetax[index] > 0" >
								<input id="_taxable[]" type="checkbox" class="form-control" name="_taxable[]" v-model="form.linetax[index] > 0" {{ $readonly ? "disabled" : "" }}></input>
								<span v-if="'taxable.'+index in errors" class="help-block">
									<strong>@{{ errors['taxable.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-2">
								{{ trans('forms.Subtotal') }}
								<input id="subtotal[]" type="text" style="text-align:right" min="{{ $source['currencyFormat']['min'] }}" step="{{ $source['currencyFormat']['min'] }}" class="form-control" name="subtotal[]" v-model="form.subtotal[index]" readonly></input>
							</div>

						</div>

						<div class="form-group">
							<div class="col-md-2">
							@if (!$readonly)
								<button type="button" class="btn btn-info" onclick="return addNewOrderLine();" >
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
<a href='#' class='show-balance-hint'>
@if ($source['valid'])
	<img src="{{ asset('images/sum-512.png') }}" height='40' width='40'></img>
@else
	<img src="{{ asset('images/void-512.png') }}" height='40' width='40'></img>
@endif
</a>
<div class='show-balance'>
@if ($source['valid'])
	<p id='calculated_total'>{{ trans('forms.Subtotal') }}&emsp;{{ old('grand_subtotal') }}<br>{{ trans('forms.Tax amount') }}&emsp;{{ old('tax_amount') }}<br>{{ trans('forms.Grand total') }}&emsp;{{ old('grand_total') }}<br>{{ trans('forms.Balance') }}&emsp;{{ old('balance') }}</p>
@else
	<p id='calculated_total'>{{ trans('messages.This transaction is invalid') }}</p>
@endif
</div>
@endsection

@section('post-content')
<script src="/js/ShowHideHint.js"></script>
<script type="text/javascript">

	var vueContainer = null;

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
				$('#billing').selectpicker().bind('change', function(event) {
					vueContainer.form.billing = $(this).val();
				});
				$('#shipping').selectpicker().bind('change', function(event) {
					vueContainer.form.shipping = $(this).val();
				});
			}
		});

		hideThenShowHint('.show-balance', '.show-balance-hint');
		$(window).mousemove(function(event) {
			showOrHideHint(event, '.show-balance', '.show-balance-hint');
		});
	});
</script>
@endsection
