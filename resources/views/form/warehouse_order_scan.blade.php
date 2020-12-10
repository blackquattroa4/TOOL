@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div id="orderwindow" class="panel panel-default">
				<div class="panel-heading">
					<table width="100%"><tr>
						<td><h4>{{ trans('warehouse.Process order') }}</h4></td>
						<!--
						<td><input type="button" class="btn btn-secondary pull-right" onclick="displayCapture()" value="Display" /></td>
						-->
					</tr></table>
				</div>

				<div class="panel-body">

					<form id="order_submission" class="form-horizontal" role="form" method="POST" action="{{ $source['post_url'] }}">
						{{ csrf_field() }}

						<div class="form-group">
							<label for="increment" class="col-md-2 control-label">{{ trans('forms.Document') }}</label>

							<div class="col-md-3{{ $errors->has('increment') ? ' has-error' : '' }}">
							@if ($readonly)
								<input id="increment" type="text" class="form-control" name="increment" value="{{ old('increment') }}" readonly>
							@else
								<input id="increment" type="text" class="form-control" name="increment" value="{{ old('increment') }}" >
							@endif

							@if ($errors->has('increment'))
								<span class="help-block">
									<strong>{{ $errors->first('increment') }}</strong>
								</span>
							@endif
							</div>

							<label for="inputdate" class="col-md-2 control-label">{{ trans('forms.Date') }}</label>

							<div class="col-md-3{{ $errors->has('inputdate') ? ' has-error' : '' }}">
							@if ($readonly)
								<div class="input-group date">
									<input id="inputdate" type="text" class="form-control" name="inputdate" value="{{ old('inputdate') }}" readonly>
							@else
								<div class="input-group date" data-provide="datepicker">
									<input id="inputdate" type="text" class="form-control" name="inputdate" value="{{ old('inputdate') }}" >
							@endif
									<div class="input-group-addon">
										<span class="glyphicon glyphicon-calendar"></span>
									</div>
								</div>

								@if ($errors->has('inputdate'))
									<span class="help-block">
										<strong>{{ $errors->first('inputdate') }}</strong>
									</span>
								@endif
							</div>

						</div>

						<div class="form-group">
							<label for="location" class="col-md-2 control-label">{{ trans('forms.Warehouse') }}</label>

							<div class="col-md-3{{ $errors->has('location') ? ' has-error' : '' }}">
							@if ($readonly)
								<select id="location" class="form-control" name="location" disabled>
							@else
								<select id="location" class="form-control" name="location" >
							@endif
							@foreach ($location as $oneLocation)
									<option value="{{ $oneLocation['id'] }}" {{ (old('location') == $oneLocation['id']) ? " selected" : "" }}>{{ $oneLocation['name'] }}</option>
							@endforeach
								</select>

								@if ($errors->has('location'))
									<span class="help-block">
										<strong>{{ $errors->first('location') }}</strong>
									</span>
								@endif
							</div>

							<label for="reference" class="col-md-2 control-label">{{ trans('forms.Reference') }}</label>

							<div class="col-md-3{{ $errors->has('reference') ? ' has-error' : '' }}">
							@if ($readonly)
								<input id="reference" type="text" class="form-control" name="reference" value="{{ old('reference') }}" readonly>
							@else
								<input id="reference" type="text" class="form-control" name="reference" value="{{ old('reference') }}" >
							@endif

								@if ($errors->has('reference'))
									<span class="help-block">
										<strong>{{ $errors->first('reference') }}</strong>
									</span>
								@endif
							</div>

						</div>

						<div class="form-group">
							<label for="address" class="col-md-2 control-label">{{ trans('forms.External address') }}</label>

							<div class="col-md-4{{ $errors->has('address') ? ' has-error' : '' }}">
								@if ($readonly)
									<select id="address" class="form-control selectpicker" name="address" disabled>
								@else
									<select id="address" class="form-control selectpicker" name="address" >
								@endif
									@foreach ($addresses as $display)
										<option value="{{ $display['id'] }}" data-content="{{ $display['name'] }}<br>{{ $display['street'] }}&nbsp;{{ $display['unit'] }}<br>{{ $display['city'] }}&nbsp;{{ $display['district'] }}<br>{{ $display['state'] }}<br>{{ $display['country'] }}&nbsp;{{ $display['zipcode'] }}<br>" {{ (old('address') == $display['id']) ? "selected" : ""}}>{{ $display['street'] }}</option>
									@endforeach
									</select>

								@if ($errors->has('address'))
									<span class="help-block">
										<strong>{{ $errors->first('address') }}</strong>
									</span>
								@endif
							</div>

							<label for="notes" class="col-md-2 control-label">{{ trans('forms.Notes') }}</label>

							<div class="col-md-4{{ $errors->has('notes') ? ' has-error' : '' }}">
							@if ($readonly)
								<textarea id="notes" cols="30" rows="5" type="text" class="form-control" name="notes" disabled>{{ old('notes') }}</textarea>
							@else
								<textarea id="notes" cols="30" rows="5" type="text" class="form-control" name="notes">{{ old('notes') }}</textarea>
							@endif

								@if ($errors->has('notes'))
									<span class="help-block">
										<strong>{{ $errors->first('notes') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<hr />

						<div class="col-md-2">
							<button type="button" class="btn btn-secondary" data-toggle="modal" onclick="registerAddSingleSerialFunction()" data-target="#serialModal" >
								<i class="fa fa-btn fa-plus-square-o"></i>&nbsp;+&nbsp;{{ trans('forms.Single') }}
							</button>
						</div>

						<div class="col-md-2">
							<button type="button" class="btn btn-secondary" data-toggle="modal" onclick="registerRemoveSingleSerialFunction()" data-target="#serialModal" >
								<i class="fa fa-btn fa-minus-square-o"></i>&nbsp;-&nbsp;{{ trans('forms.Single') }}
							</button>
						</div>

						<div class="col-md-2">
							<button type="button" class="btn btn-secondary" data-toggle="modal" onclick="registerAddMultipleSerialFunction()" data-target="#serialModal" >
								<i class="fa fa-btn fa-plus-square"></i>&nbsp;+&nbsp;{{ trans('forms.Multiple') }}
							</button>
						</div>

						<div class="col-md-2">
							<button type="button" class="btn btn-secondary" data-toggle="modal" onclick="registerRemoveMultipleSerialFunction()" data-target="#serialModal" >
								<i class="fa fa-btn fa-minus-square"></i>&nbsp;-&nbsp;{{ trans('forms.Multiple') }}
							</button>
						</div>

						<div class="col-md-2">
							<button type="button" class="btn btn-secondary" data-toggle="modal" onclick="registerRemoveProductBatch()" data-target="#productModal" >
								<i class="fa fa-btn fa-trash"></i>&nbsp;-&nbsp;{{ trans('forms.Product') }}
							</button>
						</div>

						<div class="col-md-2">
							<button type="button" class="btn btn-secondary" data-toggle="modal" onclick="registerRenameUnknownBatch()" data-target="#productModal" >
								<i class="fa fa-btn fa-tag"></i>&nbsp;{{ trans('forms.Rename') }}
							</button>
						</div>

						<div class="col-md-12" id="result_submission" >&emsp;</div>

						<div class="col-md-12" id="bin_submission" >&emsp;</div>

						<div class="col-md-8 col-md-offset-2" id="result_display" ></div>

						<hr />

						<div class="col-md-2 col-md-offset-10">
							<button type="button" class="btn btn-primary" onclick="preSubmitProcessing()" >
								<i class="fa fa-btn fa-floppy-o"></i>&nbsp;{{ trans('forms.Submit') }}
							</button>
						</div>

					</form>
				</div>
			</div>

			<!-- add/remove serial modal -->
			<div class="modal fade" id="serialModal" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="serialModalLabel" data-backdrop="static" aria-hidden="false">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="displayCapture()" >
								<span aria-hidden="false">&times;</span>
							</button>
							<h4 class="modal-title" id="serialModalLabel"></h4>
						</div>
						<div class="modal-body">
							<input id="serial_input" name="serial_input" type="text" class="form-control" onkeypress="if(event.keyCode==13){document.getElementById('serialModalSubmit').click();}" value="" ></input>
						</div>
						<div class="modal-footer">
							<button type="button" id="serialModalSubmit" class="btn btn-primary" onclick="alert('function not installed')" >{{ trans('forms.Enter') }}</button>
							<button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close" onclick="displayCapture()" >{{ trans('forms.Cancel') }}</button>
						</div>
					</div>
				</div>
			</div>

			<!-- remove product / rename unknown modal -->
			<div class="modal fade" id="productModal" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="productModalLabel" data-backdrop="static" aria-hidden="false">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close" >
								<span aria-hidden="false">&times;</span>
							</button>
							<h4 class="modal-title" id="productModalLabel"></h4>
						</div>
						<div class="modal-body">
							<select id="product_select" name="product_select" class="form-control" >
							</select>
						</div>
						<div class="modal-footer">
							<button type="button" id="productModalSubmit" class="btn btn-primary" data-dismiss="modal" aria-label="Close" onclick="alert('function not installed')" >{{ trans('forms.Enter') }}</button>
							<button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close" >{{ trans('forms.Cancel') }}</button>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>

@endsection

@section('post-content')
	<script type="text/javascript" src="/js/SerialRange.js"></script>
	<script type="text/javascript">
		function findProductIdWithSerial(oneSerial) {
			var result = 0;
			if (typeof findProductIdWithSerial.pattern === 'undefined') {
				findProductIdWithSerial.pattern = [
				@foreach ($products as $oneProduct)
					new RegExp("^{!! $oneProduct['pattern'] !!}$", "i"),
				@endforeach
				];
			}
			if (typeof findProductIdWithSerial.association === 'undefined') {
				findProductIdWithSerial.association = [
				@foreach ($products as $idx => $oneProduct)
					{!! $idx !!},
				@endforeach
				];
			}
			if (typeof findProductIdWithSerial.model === 'undefined') {
				findProductIdWithSerial.model = [];
				findProductIdWithSerial.model[0] = '{{ trans('product.Unknown') }}';
			@foreach ($products as $idx => $oneProduct)
				findProductIdWithSerial.model[{!! $idx !!}] = '{!! $oneProduct['sku'] !!}';
			@endforeach
			}
			findProductIdWithSerial.pattern.some(
				function(regexp, index) {
					var res = regexp.test(srl);
					if (res) {
						result = findProductIdWithSerial.association[index];
						return true;
					}
					return false;
				});
			return result;
		}

		function  registerAddSingleSerialFunction() {
			document.getElementById('serialModalLabel').innerHTML = "{{ trans('warehouse.Add single serial number') }}";
			document.getElementById('serial_input').value = "";
			document.getElementById('serial_input').placeholder = "{{ trans('warehouse.Enter serial number') }}";
			document.getElementById('serialModalSubmit').onclick = addSingleSerial;
		}

		function  registerRemoveSingleSerialFunction() {
			document.getElementById('serialModalLabel').innerHTML = "{{ trans('warehouse.Remove single serial number') }}";
			document.getElementById('serial_input').value = "";
			document.getElementById('serial_input').placeholder = "{{ trans('warehouse.Enter serial number') }}";
			document.getElementById('serialModalSubmit').onclick = removeSingleSerial;
		}

		function  registerAddMultipleSerialFunction() {
			document.getElementById('serialModalLabel').innerHTML = "{{ trans('warehouse.Add multiple serial number') }}";
			document.getElementById('serial_input').value = "";
			document.getElementById('serial_input').placeholder = "{{ trans('warehouse.Enter beginning serial number') }}";
			document.getElementById('serialModalSubmit').onclick = addMultipleSerialStep1;
		}

		function  registerRemoveMultipleSerialFunction() {
			document.getElementById('serialModalLabel').innerHTML = "{{ trans('warehouse.Remove multiple serial number') }}";
			document.getElementById('serial_input').value = "";
			document.getElementById('serial_input').placeholder = "{{ trans('warehouse.Enter beginning serial number') }}";
			document.getElementById('serialModalSubmit').onclick = removeMultipleSerialStep1;
		}

		function registerRemoveProductBatch() {
			document.getElementById('productModalLabel').innerHTML = "{{ trans('warehouse.Remove product') }}";
			document.getElementById('product_select').innerHTML = "";
			for (var idx in inputCapture.ary) {
				document.getElementById('product_select').innerHTML += "<option value=\"" + idx + "\">" + findProductIdWithSerial.model[idx] + "</option>";
			}
			document.getElementById('productModalSubmit').onclick = removeProductBatch;
		}

		function registerRenameUnknownBatch() {
			document.getElementById('productModalLabel').innerHTML = "{{ trans('warehouse.Rename unknown product') }}";
			document.getElementById('product_select').innerHTML = "";
		@foreach ($products as $idx => $oneProduct)
			document.getElementById('product_select').innerHTML += "<option value=\"{{ $idx }}\">{{ $oneProduct['sku'] }}</option>";
		@endforeach
			document.getElementById('productModalSubmit').onclick = renameUnknownBatch;
		}

		function inputCapture() {
			// just a function to hold static variable.
		}

		function isBinLocation(str) {
			const bin_map = Object.freeze({
			@foreach (DB::select("SELECT id, name FROM warehouse_bins WHERE location_id = " . old('location') . " AND valid = 1") as $binLocation)
				"{{ $binLocation->name }}" : {{ $binLocation->id }},
			@endforeach
			});
			return (str in bin_map) ?  bin_map[str] : false;
		}

		function displayCapture() {
			document.getElementById("result_display").innerHTML = "";
			for (var idx in inputCapture.ary) {
				//alert(findProductIdWithSerial.model[idx] + " => " + inputCapture.ary[idx].sort());
				inputCapture.ary[idx].sort();
				document.getElementById("result_display").innerHTML += "<b>" + findProductIdWithSerial.model[idx] + "</b><br>";
				var previous = "";
				var beginning = "";
				for (var idxx in inputCapture.ary[idx]) {
					if (beginning.length === 0) {
						beginning = inputCapture.ary[idx][idxx];
						previous = inputCapture.ary[idx][idxx];
						continue;
					}
					if (compare(previous, inputCapture.ary[idx][idxx], 10) === -1) {
						previous = inputCapture.ary[idx][idxx];
					} else {
						if (compare(beginning, previous) === 0) {
							document.getElementById("result_display").innerHTML += "&emsp;" + beginning + "<br>";
						} else {
							document.getElementById("result_display").innerHTML += "&emsp;" + beginning + " - " + previous + "<br>";
						}
						beginning = inputCapture.ary[idx][idxx];
						previous = inputCapture.ary[idx][idxx];
					}
				}
				if (compare(beginning, previous) === 0) {
					document.getElementById("result_display").innerHTML += "&emsp;" + beginning + "<br>";
				} else {
					document.getElementById("result_display").innerHTML += "&emsp;" + beginning + " - " + previous + "<br>";
				}
			}
		}

		function addSingleSerial() {
			if (typeof inputCapture.ary === 'undefined') {
				inputCapture.ary = [];
			}
			srl = document.getElementById('serial_input').value;
			if ((srl != null) && (srl != "")) {
				i = findProductIdWithSerial(srl);
				binLoc = isBinLocation(srl);
				if ((i === 0) && (binLoc !== false)) {
					// this is a bin location.
					if (typeof inputCapture.bin === 'undefined') {
						inputCapture.bin = {};
					}
					if (typeof addSingleSerial.lastScanned !== 'undefined') {
						inputCapture.bin[addSingleSerial.lastScanned] = binLoc;
					}
					delete addSingleSerial.lastScanned;
				} else {
					if (typeof inputCapture.ary[i] === 'undefined') {
						inputCapture.ary[i] = [];
					}
					// this is a serial number. make sure array element unique
					if (inputCapture.ary[i].indexOf(srl) == -1) {
						inputCapture.ary[i].push(srl);
					}
					addSingleSerial.lastScanned = srl;
				}
			}
			document.getElementById('serial_input').value = "";
			displayCapture();
		}

		function removeProductFromStructure(pid, forced) {
			cnt = inputCapture.ary[pid].length;
			if ((cnt == 0) || forced) {
				if ((cnt > 0) && (typeof inputCapture.bin !== 'undefined')) {
					for (var i in inputCapture.ary[pid]) {
						srl = inputCapture.ary[pid][i];
						delete inputCapture.bin[srl];
					}
				}
				delete inputCapture.ary[pid];
			}
		}

		function removeSerialFromStructure(pid, srl) {
			id = inputCapture.ary[pid].indexOf(srl);
			if (id > -1) {
				inputCapture.ary[pid].splice(id, 1);
			}
			removeProductFromStructure(pid, false);
		}

		function removeSingleSerial() {
			if (typeof inputCapture.ary === 'undefined') {
				inputCapture.ary = [];
			}
			srl = document.getElementById('serial_input').value;
			if ((srl != null) && (srl != "")) {
				i = findProductIdWithSerial(srl);
				if (typeof inputCapture.ary[i] !== 'undefined') {
					removeSerialFromStructure(i, srl);
				}
				if (typeof inputCapture.bin !== 'undefined') {
					delete inputCapture.bin[srl];
				}
			}
			document.getElementById('serial_input').value = "";
			displayCapture();
		}

		function addMultipleSerialStep1() {
			srl = document.getElementById('serial_input').value;
			if ((srl != null) && (srl != "")) {
				binLoc = isBinLocation(srl);
				if (binLoc !== false) {
					// this is a bin location.
					if (typeof inputCapture.bin === 'undefined') {
						inputCapture.bin = {};
					}
					if (typeof addMultipleSerialStep1.lastScanned !== 'undefined') {
						srls = addMultipleSerialStep1.lastScanned.split(",");
						range = new SerialRange(srls[0], srls[1], 10);
						range.reset();
						_srl = range.next();
						while ( _srl != null ) {
							// make sure array element unique
							inputCapture.bin[_srl] = binLoc;
							_srl = range.next();
						}
					}
					delete addMultipleSerialStep1.lastScanned;
					document.getElementById('serial_input').value = "";
					document.getElementById('serial_input').placeholder = "{{ trans('warehouse.Enter beginning serial number') }}";
					document.getElementById('serialModalSubmit').onclick = addMultipleSerialStep1;
				} else {
					// this is a serial number
					addMultipleSerialStep2.buffer = srl;
					document.getElementById('serial_input').value = "";
					document.getElementById('serial_input').placeholder = "{{ trans('warehouse.Enter ending serial number') }}";
					document.getElementById('serialModalSubmit').onclick = addMultipleSerialStep2;
				}
			}
		}

		function addMultipleSerialStep2() {
			esrl = document.getElementById('serial_input').value;
			if ((esrl != null) && (esrl != "")) {
				bsrl = addMultipleSerialStep2.buffer;
				bpid = findProductIdWithSerial(bsrl);
				epid = findProductIdWithSerial(esrl);
				if (bpid == epid) {
					if (typeof inputCapture.ary === 'undefined') {
						inputCapture.ary = [];
					}
					if (typeof inputCapture.ary[bpid] === 'undefined') {
						inputCapture.ary[bpid] = [];
					}
					range = new SerialRange(bsrl, esrl, 10);
					range.reset();
					srl = range.next();
					while ( srl != null ) {
						// make sure array element unique
						if (inputCapture.ary[bpid].indexOf(srl) == -1) {
							inputCapture.ary[bpid].push(srl);
						}
						srl = range.next();
					}
					addMultipleSerialStep1.lastScanned = bsrl + "," + esrl;
				}
			}
			addMultipleSerialStep2.buffer = "";
			document.getElementById('serial_input').value = "";
			document.getElementById('serial_input').placeholder = "{{ trans('warehouse.Enter beginning serial number') }}";
			document.getElementById('serialModalSubmit').onclick = addMultipleSerialStep1;
			displayCapture();
		}

		function removeMultipleSerialStep1() {
			srl = document.getElementById('serial_input').value;
			if ((srl != null) && (srl != "")) {
				removeMultipleSerialStep2.buffer = srl;
				document.getElementById('serial_input').value = "";
				document.getElementById('serial_input').placeholder = "{{ trans('warehouse.Enter ending serial number') }}";
				document.getElementById('serialModalSubmit').onclick = removeMultipleSerialStep2;
			}
		}

		function removeMultipleSerialStep2() {
			esrl = document.getElementById('serial_input').value;
			if ((esrl != null) && (esrl != "")) {
				bsrl = removeMultipleSerialStep2.buffer;
				bpid = findProductIdWithSerial(bsrl);
				epid = findProductIdWithSerial(esrl);
				if (bpid == epid) {
					if (typeof inputCapture.ary === 'undefined') {
						inputCapture.ary = [];
					}
					if (typeof inputCapture.ary[bpid] === 'undefined') {
						inputCapture.ary[bpid] = [];
					}
					range = new SerialRange(bsrl, esrl, 10);
					range.reset();
					srl = range.next();
					while ( srl != null ) {
						if (typeof inputCapture.ary[bpid] !== 'undefined') {
							removeSerialFromStructure(bpid, srl);
						}
						if (typeof inputCapture.bin !== 'undefined') {
							delete inputCapture.bin[srl];
						}
						srl = range.next();
					}
				}
			}
			removeMultipleSerialStep2.buffer = "";
			document.getElementById('serial_input').value = "";
			document.getElementById('serial_input').placeholder = "{{ trans('warehouse.Enter beginning serial number') }}";
			document.getElementById('serialModalSubmit').onclick = removeMultipleSerialStep1;
			displayCapture();
		}

		function removeProductBatch() {
			pd = document.getElementById('product_select');
			idx = pd.options[pd.selectedIndex].value;
			removeProductFromStructure(idx, true);
			displayCapture();
		}

		function renameUnknownBatch() {
			pd = document.getElementById('product_select');
			idx = pd.options[pd.selectedIndex].value;
			if (typeof inputCapture.ary[idx] === 'undefined') {
				inputCapture.ary[idx] = inputCapture.ary[0].slice(0);
			} else {
				for (var idxx in inputCapture.ary[0]) {
					inputCapture.ary[idx].push(inputCapture.ary[0][idxx]);
				}
			}
			delete inputCapture.ary[0];
			displayCapture();
		}

		function preSubmitProcessing() {
			document.getElementById('result_submission').innerHTML = "&emsp;";
			for (var idx in inputCapture.ary) {
				for (var idxx in inputCapture.ary[idx]) {
					document.getElementById('result_submission').innerHTML += "<input id=\"serial[" + idx + "][]\" name=\"serial[" + idx + "][]\" type=\"hidden\" value=\"" + inputCapture.ary[idx][idxx] + "\">";
				}
			}
			for (var srl in inputCapture.bin) {
				document.getElementById('bin_submission').innerHTML += "<input id=\"bins[" + srl + "]\" name=\"bins[" + srl + "]\" type=\"hidden\" value=\"" + inputCapture.bin[srl] + "\">";
			}
			document.getElementById('order_submission').submit();
		}

		$('#serialModal').on('shown.bs.modal', function () {
			$('#serial_input').focus();
		})

		$('#productModal').on('shown.bs.modal', function () {
			$('#product_select').focus();
		})

		$(document).ready(function() {
			$('.selectpicker').selectpicker();
		});
	</script>
@endsection
