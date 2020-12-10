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

			<!-- add product modal -->
			<div class="modal fade" id="productModal" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="productModalLabel" data-backdrop="static" aria-hidden="false">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="postCapture()" >
								<span aria-hidden="false">&times;</span>
							</button>
							<h4 class="modal-title" id="productModalLabel">{{ trans("warehouse.Please enter SKU or UPC") }}</h4>
						</div>
						<div class="modal-body">
							<input id="product_input" name="product_input" type="text" class="form-control" onkeypress="if(event.keyCode==13){document.getElementById('productModalSubmit').click();}" value="" ></input>
						</div>
						<div class="modal-footer">
							<button type="button" id="productModalSubmit" class="btn btn-primary" onclick="processCapture();" >{{ trans('forms.Enter') }}</button>
							<button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close" onclick="postCapture()" >{{ trans('forms.Cancel') }}</button>
						</div>
					</div>
				</div>
			</div>

			<!-- split product modal -->
			<div class="modal fade" id="splitProductModal" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="splitProductModalLabel" data-backdrop="static" aria-hidden="false">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="postSplit()" >
								<span aria-hidden="false">&times;</span>
							</button>
							<h4 class="modal-title" id="splitProductModalLabel">{{ trans("warehouse.Please enter quantity to split") }}</h4>
						</div>
						<div class="modal-body">
							<div class="row">
								<div class="col-md-4">
									{{ trans('warehouse.Original quantity') }}
									<input id="split_source" name="split_source" type="number" style="text-align:right;" class="form-control" data-index="" data-prev="" value="" ></input>
								</div>

								<div class="col-md-4 col-md-offset-1">
									{{ trans('warehouse.Split quantity') }}
									<input id="split_target" name="split_target" type="number" style="text-align:right;" class="form-control" data-prev="" value="" ></input>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-primary" onclick="processSplit();" >{{ trans('forms.Enter') }}</button>
							<button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close" onclick="postSplit()" >{{ trans('forms.Cancel') }}</button>
						</div>
					</div>
				</div>
			</div>

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
					<form id="warehouse_form" class="form-horizontal" role="form" method="POST" action="{{ $source['post_url'] }}">
						{{ csrf_field() }}

						<input type="hidden" id="type" name="type" value="{{ $source['type'] }}" />

						<div class="form-group">

							<label for="increment" class="col-md-2 control-label">{{ trans('forms.Document') }}</label>
							<div class="col-md-3" v-bind:class="{ 'has-error' : 'increment' in errors }">
								<input id="increment" type="text" class="form-control" name="increment" v-model="form.increment" readonly>
								<span v-if="'increment' in errors" class="help-block">
									<strong>@{{ errors['increment'][0] }}</strong>
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

							<label for="process_date" class="col-md-2 control-label">{{ trans('forms.Date') }}</label>
							<div class="col-md-3" v-bind:class="{ 'has-error' : 'process_date' in errors }">
								<div class="input-group date" {{ $readonly ? "data-provide=\"datepicker\"" : "" }}>
									<input id="process_date" type="text" class="form-control datepicker" name="process_date" v-model="form.process_date" {{ $readonly ? "readonly" : "" }}>
									<div class="input-group-addon">
										<span class="glyphicon glyphicon-calendar"></span>
									</div>
								</div>
								<span v-if="'process_date' in errors" class="help-block">
									<strong>@{{ errors['process_date'][0] }}</strong>
								</span>
							</div>

							<label for="staff" class="col-md-2 control-label">{{ trans('forms.Staff') }}</label>
							<div class="col-md-4" v-bind:class="{ 'has-error' : 'staff' in errors }">
								<input id="staff" type="text" class="form-control" name="staff" v-model="form.staff" readonly>
								<span v-if="'staff' in errors" class="help-block">
									<strong>@{{ errors['staff'][0] }}</strong>
								</span>
							</div>
						</div>

						<div class="form-group">

							<label for="via" class="col-md-2 control-label">{{ trans('forms.Via') }}</label>
							<div class="col-md-3" v-bind:class="{ 'has-error' : 'via' in errors }">
								<input id="via" type="text" class="form-control" name="via" v-model="form.via" {{ $readonly ? "readonly" : "" }}>
								<span v-if="'via' in errors" class="help-block">
									<strong>@{{ errors['via'][0] }}</strong>
								</span>
							</div>

							<label for="location" class="col-md-2 control-label">{{ trans('forms.Warehouse') }}</label>
							<div class="col-md-4" v-bind:class="{ 'has-error' : 'location' in errors }">
								<select id="location" class="form-control" name="location" v-model="form.location" v-on:change="binLocationRefresh()" {{ $readonly ? "disabled" : "" }}>
								@foreach (\App\Location::getActiveWarehouses('name', 'asc') as $oneLocation)
									<option value="{{ $oneLocation->id }}" {{ (old('location') == $oneLocation->id) ? "selected" : "" }}>{{ $oneLocation->name }}</option>
								@endforeach
								</select>
								<span v-if="'location' in errors" class="help-block">
									<strong>@{{ errors['location'][0] }}</strong>
								</span>
							</div>
						</div>

						<div class="form-group">

							<label for="entity" class="col-md-2 control-label">{{ trans('forms.Entity') }}</label>
							<div class="col-md-3" v-bind:class="{ 'has-error' : 'entity' in errors }">
								<input id="entity" class="form-control" name="entity" v-model="form.entity" readonly/>
								<span v-if="'entity' in errors" class="help-block">
									<strong>@{{ errors['entity'][0] }}</strong>
								</span>
							</div>

							<label for="address" class="col-md-2 control-label">{{ trans('forms.External address') }}</label>
							<div class="col-md-4" v-bind:class="{ 'has-error' : 'address' in errors }">
								<select id="address" class="form-control selectpicker" name="address" v-model="form.address" {{ $readonly ? "disabled" : "" }}>
								@foreach ($source['address'] as $oneAddress)
									<option value="{{ $oneAddress->id }}" data-content="{{ $oneAddress->name }}<br>{{ $oneAddress->street }}&nbsp;{{ $oneAddress->unit }}<br>{{ $oneAddress->city }}&nbsp;{{ $oneAddress->district }}<br>{{ $oneAddress->state }}<br>{{ $oneAddress->country }}&nbsp;{{ $oneAddress->zipcode }}<br>" {{ (old('address') == $oneAddress->id) ? "selected" : ""}}>{{ $oneAddress->street }}></option>
								@endforeach
								</select>
								<span v-if="'address' in errors" class="help-block">
									<strong>@{{ errors['address'][0] }}</strong>
								</span>
							</div>
						</div>

						&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>

						<hr />

						<!-- VUE binding for new elements -->
						<div v-for = "(product, index) in form.product_id" class="form-group detail-line">
							<input id="product_id[]" type="hidden" v-model="product" class="form-control col-md-2" name="product_id[]" ></input>
							<div class="col-md-3">
								{{ trans('forms.Item') }}
								<input id="sku[]" type="text" v-model="form.sku[index]" class="form-control" name="sku[]" readonly>
							</div>

							<div class="col-md-6">
								{{ trans('forms.Description') }}
								<input id="description[]" type="text" v-model="form.description[index]" class="form-control" name="description[]" readonly>
							</div>

							<div class="col-md-1 col-md-offset-2">
								<br>
								<a class="btn btn-danger" title="{{ trans('forms.Delete') }}" v-on:click="deleteRow(index);">&times;</a>
							</div>

							<div class="col-md-1 col-md-offset-7">
								<br>
								<a class="btn btn-info pull-right" v-bind:class="{ 'hidden' : form.quantity[index] < 2 }" title="{{ trans('forms.Split') }}" v-on:click="prepareSplit(index);"><span class="fa fa-expand"></span></a>
							</div>

							<div class="col-md-2" v-bind:class="{ 'has-error' : 'quantity.'+index in errors }">
								{{ trans('forms.Processed') }}
								<input id="quantity[]" type="number" v-model="form.quantity[index]" style="text-align:right;" min="{{ $quantity_formatter['zero'] }}" step="{{ $quantity_formatter['step'] }}" class="form-control" name="quantity[]" >
								<span v-if="'quantity.'+index in errors" class="help-block">
									<strong>@{{ errors['quantity.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-2" v-bind:class="{ 'has-error' : 'bin.'+index in errors }">
								{{ trans('forms.Bin') }}
								<select id="bin[]" style="text-align:right;" class="form-control" name="bin[]" v-model="form.bin[index]" >
									<option v-for="(bin, bindex) in form.bins[index]" v-bind:value="bindex" >@{{ bin }}</option>
								</select>
								<span v-if="'bin.'+index in errors" class="help-block">
									<strong>@{{ errors['bin.'+index][0] }}</strong>
								</span>
							</div>
						</div>

						<div class="form-group">
							<div class="col-md-2">
								<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#productModal" >
									+&nbsp;{{ trans("forms.Add") }}
								</button>
							</div>
							<div class="col-md-2 col-md-offset-8">
								<button type="submit" class="btn btn-primary" {!! $detail_required ? "v-bind:style=\"{ 'pointer-events' : (form.product_id.length < 1) ? 'none' : '' }\" v-bind:class=\"{ 'disabled' : (form.product_id.length < 1) }\"" : "" !!}>
									<i class="fa fa-btn fa-floppy-o"></i> {{ $source['action'] }}
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('post-content')

	<script type='text/javascript'>

		var vueContainer = null;

		function dataLookup()
		{
			// function to hold data structure
		}

		function postCapture()
		{
			$('#product_input').val("");
		}

		function prepareSplit(index)
		{
			let qty = vueContainer.form.quantity[index];
			$('#split_source').val(qty);
			$('#split_source').data('prev', qty);
			$('#split_source').data('index', index);
			$('#split_target').val("0");
			$('#split_target').data('prev', "0");
			$('#split_source').bind('change', function (event) {
				let delta = parseInt($(this).data('prev')) - parseInt($(this).val());
				$(this).data('prev', $(this).val());
				let newTarget = parseInt($('#split_target').val()) + delta;
				$('#split_target').val(newTarget);
				$('#split_target').data('prev', newTarget);
			});
			$('#split_target').bind('change', function (event) {
				let delta = parseInt($(this).data('prev')) - parseInt($(this).val());
				$(this).data('prev', $(this).val());
				let newSource = parseInt($('#split_source').val()) + delta;
				$('#split_source').val(newSource);
				$('#split_source').data('prev', newSource);
			});
			$('#splitProductModal').modal('show');
		}

		function postSplit()
		{
			$('#split_source').val('');
			$('#split_source').data('prev', '');
			$('#split_source').data('index', '');
			$('#split_target').val('');
			$('#split_target').data('prev', '');
		}

		function processCapture()
		{
			let input = $('#product_input').val();

			// lookup product id by SKU or UPC
			if (input in dataLookup.products) {
				// find last instead of first
				let theIndex = vueContainer.form.product_id.map(o => o === dataLookup.products[input]["id"]).lastIndexOf(true);
				if (theIndex === -1) {
					let targetProduct = dataLookup.products[input];
					let locationId = $('select#location').val();
					vueContainer.form.product_id.push(targetProduct['id']);
					vueContainer.form.sku.push(targetProduct['sku']);
					vueContainer.form.description.push(targetProduct['description']);
					vueContainer.form.quantity.push(1);
					vueContainer.form.bins.push(targetProduct['bins'][locationId]);
					vueContainer.form.bin.push((Object.keys(targetProduct['bins'][locationId]).length > 0) ? Object.keys(targetProduct['bins'][locationId])[0] : 0);
					dataLookup.prevRowIndex = vueContainer.form.product_id.length - 1;
				} else {
					vueContainer.form.quantity[theIndex]++;
					dataLookup.prevRowIndex = theIndex;
					vueContainer.$forceUpdate();
				}
			} else if (dataLookup.prevRowIndex !== undefined) {
				let binId = -1;
				for (idx in vueContainer.form.bins[dataLookup.prevRowIndex]) {
					if (vueContainer.form.bins[dataLookup.prevRowIndex][idx] == input) {
						binId = idx;
						break;
					}
				}
				if (binId != -1) {
					vueContainer.form.bin[dataLookup.prevRowIndex] = binId;
					vueContainer.$forceUpdate();
				}
			}

			postCapture();
		}

		function deleteRow(index)
		{
			vueContainer.form.product_id.splice(index, 1);
			vueContainer.form.sku.splice(index, 1);
			vueContainer.form.description.splice(index, 1);
			vueContainer.form.quantity.splice(index, 1);
			vueContainer.form.bins.splice(index, 1);
			vueContainer.form.bin.splice(index, 1);
		}

		function processSplit()
		{
			let index = $('#split_source').data('index');
			vueContainer.form.quantity[index] = $('#split_source').val();
			let locationId = $('select#location').val();
			vueContainer.form.product_id.splice(index+1, 0, vueContainer.form.product_id[index]);
			vueContainer.form.sku.splice(index+1, 0, vueContainer.form.sku[index]);
			vueContainer.form.description.splice(index+1, 0, vueContainer.form.description[index]);
			vueContainer.form.quantity.splice(index+1, 0, $('#split_target').val());
			vueContainer.form.bins.splice(index+1, 0, vueContainer.form.bins[index]);
			vueContainer.form.bin.splice(index+1, 0, (Object.keys(vueContainer.form.bins[index]).length > 0) ? Object.keys(vueContainer.form.bins[index])[0] : 0);

			postSplit();
		}

		function binLocationRefresh()
		{
			let locationId = $('select#location').val();
			for (index in vueContainer.form.sku) {
				vueContainer.form.bins[index] = dataLookup.products[vueContainer.form.sku[index]].bins[locationId];
				// just grab first visible option
				vueContainer.form.bin[index] = Object.keys(vueContainer.form.bins[index])[0];
			}
		}

		$(document).ready(function() {

			dataLookup.products = {
			@foreach (\App\Tradable::getCurrentProducts('id', 'asc') as $product)
				@php
					$uniqueProduct = $product->uniqueTradable;
				@endphp
				"{{ $uniqueProduct->sku }}" : {
					id : "{{ $product->id }}",
					sku : "{{ $uniqueProduct->sku }}",
					description : "{{ $uniqueProduct->description }}",
					bins : {!! json_encode($source['bins'][$product->id]) !!}
				},
				@if ( !empty($uniqueProduct->product_id) )
					"{{ $uniqueProduct->product_id }}" : {
						id : "{{ $product->id }}",
						sku : "{{ $uniqueProduct->sku }}",
						description : "{{ $uniqueProduct->description }}",
						bins : {!! json_encode($source['bins'][$product->id]) !!}
					},
				@endif
			@endforeach
			};

			vueContainer = new Vue({
				el: '#vue_container',
			@php
				// transform data for Vue.
				$oldInput = session()->getOldInput();
				// add additional (bins) attribute
				$oldInput['bins'] = array_map(
					function($product_id) use ($source, $oldInput) {
		 				return $source['bins'][$product_id][$oldInput['location']];
					},
					$oldInput['product_id']);
			 @endphp
				 data: {
					 	form : {!! json_encode($oldInput) !!},
						errors : {!! json_encode($errors->toArray()) !!},
				 },
				 mounted: function() {
					 $('#process_date').datepicker().bind('change', function(event) {
						 vueContainer.form.process_date = $(this).val();
					 });
					 $('#address').selectpicker().bind('change', function(event) {
						 vueContainer.form.address = $(this).val();
					 });
					 $('#productModal').on('shown.bs.modal', function (e) {
						 $('#product_input').focus();
					 });
					 $('form#warehouse_form').keypress(function (e) {
						 if (e.which == '13') { e.preventDefault(); }
					 });
				 }
			});
		});

	</script>

@endsection
