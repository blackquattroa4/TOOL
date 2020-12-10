@extends('layouts.app')

@section('additional-style')
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

						<div class="form-group">
							<label for="increment" class="col-md-2 control-label">{{ trans('forms.Document') }}</label>

							<div class="col-md-3" v-bind:class="{ ' has-error' : 'increment' in errors }">
								<input id="increment" type="text" class="form-control" name="increment" v-model="form.increment" readonly>
								<span v-if="'increment' in errors" class="help-block">
									<strong>@{{ errors['increment'][0] }}</strong>
								</span>
							</div>

							<label for="customer" class="col-md-2 control-label">{{ trans('forms.Customer') }}</label>

							<div class="col-md-3" v-bind:class="{ 'has-error' : 'customer' in errors }">
								<input id="customer" type="hidden" class="form-control" name="customer" v-model="form.customer" >
								<select id="_customer" class="form-control" name="_customer" v-model="form.customer" disabled>
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
						      <input id="inputdate" type="text" class="form-control datepicker" name="inputdate" v-model="form.inputdate" {{ $readonly ? "readonly" : ""}} >
						      <div class="input-group-addon">
						        <span class="glyphicon glyphicon-calendar"></span>
						      </div>
						    </div>
						    <span v-if="'inputdate' in errors" class="help-block">
						      <strong>@{{ errors['inputdate'][0] }}</strong>
						    </span>
						  </div>

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

						  <label for="reference" class="col-md-2 control-label">{{ trans('forms.Reference') }}</label>

						  <div class="col-md-3" v-bind:class="{ 'has-error' : 'reference' in errors }">
						    <input id="reference" type="text" class="form-control" name="reference" v-model="form.reference" {{ $readonly ? "readonly" : "" }}>
						    <span v-if="'reference' in errors" class="help-block">
						      <strong>@{{ errors['reference'][0] }}</strong>
						    </span>
						  </div>
						</div>

						<div class="form-group">
						  <label for="billing" class="col-md-2 control-label">{{ trans('forms.Bill to') }}</label>

						  <div class="col-md-3" v-bind:class="{ 'has-error' : 'billing' in errors }">
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
						      <strong>{{ $errors->first('billing') }}</strong>
						    </span>
						  </div>

						  <label for="shipping" class="col-md-2 control-label">{{ trans('forms.Ship to') }}</label>

						  <div class="col-md-3" v-bind:class="{ 'has-error' : 'shipping' in errors }">
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
						      <strong>{{ $errors->first('shipping') }}</strong>
						    </span>
						  </div>
						</div>

						&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>

						<hr />

						<div v-for="(id, index) in form.line" class="form-group detail-line">
						  <input id="line[]" type="hidden" name="line[]" v-model="id" ></input>

							<div class="col-md-3 col-md-offset-1" v-bind:class="{ 'has-error' : 'product.'+index in errors }">
						    {{ trans('forms.Item') }}
						    <select id="product[]" class="form-control" name="product[]" v-model="form.product[index]" {{ $readonly ? "disabled" : "" }}>
						    @foreach (\App\UniqueTradable::getActiveProducts('sku', 'asc') as $product)
						      <option value="{{ $product->id }}" >{{ $product->sku }}</option>
						    @endforeach
						    </select>
						    <span v-if="'product.'+index in errors" class="help-block">
						      <strong>@{{ errors['product.'+index][0] }}</strong>
						    </span>
						  </div>

							<div class="col-md-4" v-bind:class="{ 'has-error' : 'serial.'+index in errors }">
						    {{ trans('forms.Serial') }}
						    <input id="serial[]" class="form-control" name="serial[]" v-model="form.serial[index]" {{ $readonly ? "disabled" : "" }}>
						    <span v-if="'serial.'+index in errors" class="help-block">
						      <strong>@{{ errors['serial.'+index][0] }}</strong>
						    </span>
						  </div>

							<div class="col-md-3" v-bind:class="{ 'has-error' : 'resolution.'+index in errors }">
						    {{ trans('rma.Resolution') }}
						    <select id="resolution[]" class="form-control" name="resolution[]" v-model="form.resolution[index]" {{ $readonly ? "disabled" : "" }}>
						    @foreach ([ "replace" => trans('rma.Replace'), "refund" => trans('rma.Credit')] as $value => $display)
						      <option value="{{ $value }}">{{ $display }}</option>
						    @endforeach
						    </select>
						    <span v-if="'resolution.'+index in errors" class="help-block">
						      <strong>@{{ errors['resolution.'+index][0] }}</strong>
						    </span>
						  </div>

						  <div class="clearfix">
						  </div>

							<div class="col-md-4 col-md-offset-2" v-bind:class="{ 'has-error' : 'reason.'+index in errors }">
						    {{ trans('rma.Reason') }}
						    <select id="reason[]" class="form-control" name="reason[]" v-model="form.reason[index]" {{ $readonly ? "disabled" : "" }}>
						    @foreach (\App\RmaReason::orderBy('summary', 'asc')->get() as $reason)
						      <option value="{{ $reason->id }}" >{{ $reason->summary }}</option>
						    @endforeach
						    </select>
						    <span v-if="'reason.'+index in errors" class="help-block">
						      <strong>@{{ errors['product.'+index][0] }}</strong>
						    </span>
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
</div>
@endsection

@section('post-content')
	<script type="text/javascript">

		var vueContainer = null;

	@if (!$readonly)
		function addNewOrderLine() {
			vueContainer.form.line.push(0);
			vueContainer.form.product.push({{ \App\UniqueTradable::getActiveProducts('sku', 'asc')->first()->id }});
			vueContainer.form.serial.push('');
			vueContainer.form.resolution.push('replace');
			vueContainer.form.reason.push({{ \App\RmaReason::orderBy('summary', 'asc')->first()->id }});
		}
	@endif

		$(document).ready(function() {
			vueContainer = new Vue({
				el : '#vue_container',
				data : {
				@php
					$oldInput = session()->getOldInput();
				@endphp
					form : {!! json_encode($oldInput) !!},
					errors : {!! json_encode($errors->toArray()) !!}
				},
				mounted : function() {
				@if (!$readonly)
					$('.datepicker').datepicker().bind('change', function(event) {
						vueContainer.form.inputdate = $(this).val();
					});
				@endif
				}
			});
		});
	</script>
@endsection
