@extends('layouts.app')

@section('additional-style')
	<link rel="stylesheet" type="text/css" href="/external/imagebox/css/style.css">
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

			<!-- attachment-loading progress modal -->
			<div class="modal fade" id="attachmentModal" tabindex="-1" role="dialog" aria-labelledby="attachmentModalLabel" data-backdrop="static" aria-hidden="false">
				<div class="modal-dialog" role="attachment">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title" id="attachmentModalLabel">{{ trans('forms.Attachment') }}</h4>
						</div>
						<div class="modal-body">
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
					<form class="form-horizontal" role="form" enctype="multipart/form-data" method="POST" action="{{ $source['post_url'] }}">
						{{ csrf_field() }}

						<div class="form-group">
							<label for="increment" class="col-md-2 control-label">{{ trans('forms.Expense') }}</label>

							<div class="col-md-3" v-bind:class="{ 'has-error' : 'increment' in errors }">
								<input id="increment" type="text" class="form-control" name="increment" v-model="form.increment" readonly>
								<span v-if="'increment' in errors" class="help-block">
									<strong>@{{ errors['increment'][0] }}</strong>
								</span>
							</div>

							<label for="entity" class="col-md-2 control-label">{{ trans('forms.Entity') }}</label>

							<input id="entity" type="hidden" class="form-control" name="entity" v-model="form.entity" >

							<div class="col-md-4" v-bind:class="{ 'has-error' : 'entity' in errors }">
								<select id="_entity" class="form-control" name="_entity" v-model="form.entity" disabled>
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
							<label for="reference" class="col-md-2 control-label">{{ trans('forms.Reference') }}</label>

							<div class="col-md-3" v-bind:class="{ 'has-error' : 'reference' in errors }">
								<input id="reference" type="text" class="form-control" name="reference" v-model="form.reference" {{ $readonly ? "readonly" : "" }}>
								<span v-if="'reference' in errors" class="help-block">
									<strong>@{{ errors['reference'][0] }}</strong>
								</span>
							</div>

							<label for="staff" class="col-md-2 control-label">{{ trans('forms.Staff') }}</label>

							<div class="col-md-4" v-bind:class="{ 'has-error' : 'staff' in errors }">
								<select id="staff" type="text" class="form-control" name="staff" v-model="form.staff" disabled>
								@foreach ($staff as $id => $display)
									<option value="{{ $id }}" >{{ $display }}</option>
								@endforeach
								</select>
								<span v-if="'staff' in errors" class="help-block">
									<strong>{{ $errors->first('staff') }}</strong>
								</span>
							</div>
						</div>

						<div class="form-group">
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

							<label for="notes" class="col-md-2 control-label">{{ trans('forms.Notes') }}</label>

							<div class="col-md-4" v-bind:class="{ 'has-error' : 'notes' in errors }">
								<textarea id="notes" type="text" class="form-control" name="notes" rows="5" v-model="form.notes" {{ $readonly ? "readonly" : "" }}></textarea>
								<span v-if="'notes' in errors" class="help-block">
									<strong>@{{ errors['notes'][0] }}</strong>
								</span>
							</div>
						</div>

						<hr />

						<div v-for="(line, index) in form.line" class="form-group detail-line">
							<input id="line[]" type="hidden" name="line[]" v-model="line" ></input>

							<div class="col-md-3" v-bind:class="{ 'has-error' : 'product.'+index in errors }">
								{{ trans('forms.Item') }}
								<select id="product[]" class="form-control" name="product[]" v-model="form.product[index]" {{ $readonly ? "disabled" : "" }}>
								@foreach ($item_option as $oneKey => $oneOption)
									<option value="{{ $oneKey }}" >{{ $oneOption }}</option>
								@endforeach
								</select>
								<span v-if="'product.'+index in errors" class="help-block">
									<strong>@{{ errors['product.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-3" v-bind:class="{ 'has-error' : 'incurdate.'+index in errors }">
								{{ trans('forms.Incur date') }}
								<div class="input-group date" {{ !$readonly ? "data-provide=\"datepicker\"" : "" }}>
									<input id="incurdate[]" v-bind:ref="'incurdate'+index" type="text" class="form-control" v-bind:data-line="index" name="incurdate[]" v-model="form.incurdate[index]" {{ $readonly ? "readonly" : "" }}>
									<div class="input-group-addon">
										<span class="glyphicon glyphicon-calendar"></span>
									</div>
								</div>
								<span v-if="'incurdate.'+index in errors" class="help-block">
									<strong>@{{ errors['incurdate.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-4" v-bind:calss="{ 'has-error' : 'description.'+index in errors }">
								{{ trans('forms.Detail') }}
								<input id="description[]" type="text" class="form-control" name="description[]" v-model="form.description[index]" {{ $readonly ? "readonly" : "" }}></input>
								<span v-if="'description.'+index in errors" class="help-block">
									<strong>@{{ errors['description.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-2" v-bind:class="{ 'has-error' : 'upload-selector.'+index in errors }">
								{{ trans('forms.Attachment') }}<br>
							@if ($readonly)
								<button type="button" class="btn btn-info image-button" v-bind:data-url="form.fileurl[index]">
									@{{ form.filename[index] }}
								</button>
							@else
								<label class="btn" v-bind:class="{ 'btn-danger' : 'upload-selector.'+index in errors, 'btn-info' : !('upload-selector.'+index in errors) }" v-bind:for="'upload-selector['+index+']'">
									<!-- assuming value='C:\fakepath\filename' -->
									<input v-bind:id="'upload-selector['+index+']'" v-bind:name="'upload-selector['+index+']'" type="file" style="display:none;" v-bind:ref="'attachment'+index" v-on:change="updateFileNameDisplay(index);" />
									<span id="upload-selector-label[]" v-bind:key="form.filename[index]" >@{{ form.filename[index] }}</span>
								</label>
							@endif
								<span v-if="'upload-selector.'+index in errors" class="help-block">
									<strong>@{{ errors['upload-selector.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-2 col-md-offset-5" v-bind:class="{ 'has-error' : 'unitprice.'+index in errors }">
								{{ trans('forms.Unit price') }}
								<input id="unitprice[]" type="number" style="text-align:right" min="{{ $source['currencyFormat']['min'] }}" step="{{ $source['currencyFormat']['min'] }}" class="form-control" name="unitprice[]" v-model="form.unitprice[index]" v-on:change="updateSubtotal(index)" {{ $readonly ? "readonly" : "" }}></input>
								<span v-if="'unitprice.'+index in errors" class="help-block">
									<strong>@{{ errors['unitprice.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-2" v-bind:class="{ 'has-error' : 'quantity.'+index in errors }">
								{{ trans('forms.Quantity') }}
								<input id="quantity[]" type="number" style="text-align:right" min="0" step="1" class="form-control" name="quantity[]" v-model="form.quantity[index]" v-on:change="updateSubtotal(index)" {{ $readonly ? "readonly" : "" }}></input>
								<span v-if="'quantity.'+index in errors" class="help-block">
									<strong>@{{ errors['quantity.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-2">
								{{ trans('forms.Subtotal') }}
								<input id="subtotal[]" type="text" style="text-align:right" class="form-control" name="subtotal[]" v-model="form.subtotal[index]" v-bind:key="form.subtotal[index]" readonly></input>
							</div>

						</div>

					@if ($readonly)
						<div class="image-group">
							<div class="image-container">
								<img id="image-canvas" class="img hide" data-url="" />
							</div>
						</div>
					@endif

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
								<button type="submit" id="submit" name="submit" class="btn btn-primary" value="{{ $keyAction }}">
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
@if ($readonly)
	<script src="{{ asset('external/imagebox/js/jquery.imagebox.js') }}"></script>
@endif

	<script type="text/javascript">

		var vueContainer = null;

		function updateSubtotal(index) {
			vueContainer.form.subtotal[index] = parseFloat(vueContainer.form.unitprice[index]) * parseFloat(vueContainer.form.quantity[index]);
			// force subtotal to update
			vueContainer.$forceUpdate();
		}

	@if (!$readonly)
		function updateFileNameDisplay(index) {
			let fullPath = $(vueContainer.$refs["attachment"+index]).val();
			vueContainer.form.filename[index] = fullPath.substring(fullPath.lastIndexOf('\\')+1);
		}

		function addNewOrderLine() {
			vueContainer.form.line.push(0);
			vueContainer.form.product.push({{ array_keys($item_option)[0] }});
			vueContainer.form.incurdate.push('{{ \App\Helpers\DateHelper::dbToGuiDate(date("Y-m-d")) }}');
			vueContainer.form.unitprice.push(0);
			vueContainer.form.quantity.push(1);
			vueContainer.form.description.push('');
			vueContainer.form.subtotal.push(0);
			vueContainer.form.filename.push('{{ trans('tool.Browse file') }}');
			vueContainer.form.fileurl.push('');
			// wait until next tick (component rendered) to install date selector
			let id = Object.keys(vueContainer.form.line).pop();
			Vue.nextTick(function () {
				$(vueContainer.$refs["incurdate"+id]).datepicker().bind('change', function() {
					vueContainer.form.incurdate[$(this).data('line')] = $(this).val();
				});
			});
		}
	@endif

		$(document).ready(function() {

			vueContainer = new Vue({
				el : "#vue_container",
			@php
				$oldInput = session()->getOldInput();
			@endphp
				data : {
					form : {!! json_encode($oldInput) !!},
					errors : {!! json_encode($errors->toArray()) !!}
				},
				mounted : function() {
				@if ($readonly)
					$("div.image-group").imageBox();

					$("button.image-button").bind('click', function() {
						var url = $(this).data('url') + "?base64=1";

						$.ajax({
							type: 'GET',
							url: url,
							dataType: 'html',
							beforeSend: function(data) {
								// show spinning wheel when downloading...
								$('#attachmentModal div.modal-body').html('<i class="fa fa-spinner fa-pulse fa-2x fa-fw" aria-hidden="true"></i>');
								$('#attachmentModal div.modal-footer button').addClass('hidden');
								$('#attachmentModal').modal('show');
							},
						}).done(function(data) {
							$('#attachmentModal').modal('hide');
							var result = JSON.parse(data);
							if (result['success']) {
								$('div.image-group div img#image-canvas').data('url', result['content']);
								$('div.image-group div img#image-canvas').trigger('click');
							}
						}).fail(function(data) {
							// show failed message.
							$('#attachmentModal div.modal-body').html('{{ trans('messages.Attachment download failed') }}');
							$('#attachmentModal div.modal-footer button').removeClass('hidden');
						}).always(function(data) {
						});
					});
				@else
					$('input[name^="incurdate["]').datepicker().bind('change', function(event) {
						vueContainer.form.incurdate[$(this).data('line')] = $(this).val();
					});
				@endif
				}
			});

		});
	</script>
@endsection
