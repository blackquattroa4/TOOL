@extends('layouts.app')

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

							<label for="supplier" class="col-md-2 control-label">{{ trans('forms.Supplier') }}</label>

							<div class="col-md-4" v-bind:class="{ 'has-error' : 'supplier' in errors }">
							@if (!$readonly)
								<input id="supplier" class="hidden" name="supplier" v-model="form.supplier">
							@endif
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
								<div class="input-group date" {{ !$readonly ? "data-provide=\"datepicker\"" : "" }}>
									<input id="inputdate" type="text" class="form-control" name="inputdate" v-model="form.inputdate" {{ $readonly ? "readonly" : "" }}>
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
							<label for="expiration" class="col-md-2 control-label">{{ trans('forms.Expiration') }}</label>

							<div class="col-md-3" v-bind:class="{ 'has-error' : 'expiration' in errors }">
								<div class="input-group date" {{ !$readonly ? "data-provide=\"datepicker\"" : "" }}>
									<input id="expiration" type="text" class="form-control" name="expiration" v-model="form.expiration" {{ $readonly ? "readonly" : "" }}>
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
								<input id="incoterm" type="text" class="form-control" name="incoterm" v-model="form.incoterm" {{ $readonly ? "readonly" : "" }}>
								<span v-if="'incoterm' in errors" class="help-block">
									<strong>@{{ errors['incoterm'][0] }}</strong>
								</span>
							</div>
						</div>

						<div class="form-group">
							<label for="contact" class="col-md-2 control-label">{{ trans('forms.Contact') }}</label>

							<div class="col-md-3" v-bind:class="{ 'has-error' : 'contact' in errors }">
								<select id="contact" class="form-control" name="contact" v-model="form.contact" {{ $readonly? "disabled" : "" }}>
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

							<div class="col-md-4" v-bind:class="{ 'has-error' : 'currency' in errors }">
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

						<hr />

						<div v-for="(line, index) in form.line" class="form-group detail-line">
							<input id="line[]" type="hidden" name="line[]" v-model="line" ></input>

							<div class="col-md-4" v-bind:class="{ 'has-error' : 'product.'+index in errors }">
								{{ trans('forms.Item') }}
								<select id="product[]" class="form-control" name="product[]" v-model="form.product[index]" v-on:change="fillDefaultValue(index)" {{ $readonly ? "disabled" : "" }}>
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
									<strong>{{ $errors->first('display.'.$id) }}</strong>
								</span>
							</div>

							<div class="col-md-2 col-md-offset-2" v-bind:class="{ 'has-error' : 'unitprice.'+index in errors }">
								{{ trans('forms.Unit price') }}
								<input id="unitprice[]" type="number" style="text-align:right" min="{{ $source['currencyFormat']['min'] }}" step="{{ $source['currencyFormat']['min'] }}" class="form-control" name="unitprice[]" v-model="form.unitprice[index]" {{ $readonly ? "readonly" : "" }}></input>
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

						</div>

						<div class="form-group">
							<div class="col-md-2">
							@if (!$readonly)
								<button type="button" class="btn btn-info" onclick="return addNewQuoteLine();">
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

		function dataSource() {
			// function that holds data
		}

	@if (!$readonly)
		function addNewQuoteLine() {
			vueContainer.form.line.push(0);
			vueContainer.form.product.push(Object.keys(dataSource.extended_product_option)[0]);
			vueContainer.form.display.push(Object.values(dataSource.extended_product_option)[0].display);
			vueContainer.form.unitprice.push(0);
			vueContainer.form.description.push(Object.values(dataSource.extended_product_option)[0].description);
			vueContainer.form.quantity.push(1);
		}

		function fillDefaultValue(index) {
			let pid = vueContainer.form.product[index];
			vueContainer.form.display[index] = dataSource.extended_product_option[pid]["display"];
			vueContainer.form.description[index] = dataSource.extended_product_option[pid]["description"];
		}
	@endif

		$(document).ready(function() {

			dataSource.extended_product_option = {!! json_encode($extended_product_option) !!};

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
					$('#inputdate').datepicker().bind('change', function(event) {
						vueContainer.form.inputdate = $(this).val();
					});
					$('#expiration').datepicker().bind('change', function(event) {
						vueContainer.form.expiration = $(this).val();
					});
				}
			});
		});

	</script>

@endsection
