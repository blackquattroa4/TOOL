

<div class="modal fade" id="embeddedChargeModal" tabindex="-1" role="dialog" aria-labelledby="embeddedChargeModalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">

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

	<div class="image-group">
		<div class="image-container">
			<img id="image-canvas" class="img hide" data-url="" />
		</div>
		<div id="downloadableAttachmentModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="downloadableAttachmentModalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button aria-label="Close" class="close" type="button" onclick="$(this).closest('#downloadableAttachmentModal').modal('hide')">
							<span aria-hidden="true">&times;</span>
						</button>
						<h4 class="modal-title">{{ trans('forms.Attachment') }}</h4>
					</div>
					<div style="min-height: 350px;max-height: 500px;" class="modal-body">
						<div id="img-preview">
						</div>
						<!--
						<div class="img-op">
							<span class="btn btn-primary zoom-in">
								<i class="fa fa-search-plus"></i>
							</span>
							<span class="btn btn-primary zoom-out">
								<i class="fa fa-search-minus"></i>
							</span>
							<span class="btn btn-primary rotate">Rotate</span>
							<br>
							<span role="prev" class="btn btn-primary switch">Prev</span>
							<span role="next" class="btn btn-primary switch">Next</span>
						</div>
						-->
					</div>
					<div class="modal-footer">
						<div class="img-op">
							<button type="button" class="btn btn-primary zoom-in">
								<i class="fa fa-search-plus"></i>
							</button>
							<button type="button" class="btn btn-primary zoom-out">
								<i class="fa fa-search-minus"></i>
							</button>
							<button class="btn btn-default pull-right" type="button" onclick="$(this).closest('#downloadableAttachmentModal').modal('hide')">{{ trans('forms.Close') }}</button>
						</div>
					</div>
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
							<a v-if="modal.history && (modal.history.length > 0)" style="padding-right:20px;" href="#" data-toggle="modal" data-target="#embeddedChargeModal #historyModal"><span class="fa fa-2x fa-history"></span></a>
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

				<form id="charge_entry" class="form-horizontal" role="form" enctype="multipart/form-data" method="POST" v-bind:action="modal.post_url">

					<input type="hidden" name="_token" v-model="modal.csrf">

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
							<select id="entity" class="form-control" name="entity" v-model="form.entity" v-bind:disabled="form.id > 0">
								<option v-for="(display, index) in modal.entity" v-bind:value="index" >@{{ display }}</option>
							</select>
							<span v-if="'entity' in errors" class="help-block">
								<strong>@{{ errors['entity'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label for="reference" class="col-md-2 control-label">{{ trans('forms.Reference') }}</label>

						<div class="col-md-3" v-bind:class="{ 'has-error' : 'reference' in errors }">
							<input id="reference" type="text" class="form-control" name="reference" v-model="form.reference" v-bind:readonly="modal.readonly">
							<span v-if="'reference' in errors" class="help-block">
								<strong>@{{ errors['reference'][0] }}</strong>
							</span>
						</div>

						<label for="staff" class="col-md-2 control-label">{{ trans('forms.Staff') }}</label>

						<div class="col-md-4" v-bind:class="{ 'has-error' : 'staff' in errors }">
							<select id="staff" type="text" class="form-control" name="staff" v-model="form.staff" disabled>
								<option v-for="(display, index) in modal.staff" v-bind:value="index" >@{{ display }}</option>
							</select>
							<span v-if="'staff' in errors" class="help-block">
								<strong>{{ $errors->first('staff') }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label for="currency" class="col-md-2 control-label">{{ trans('forms.Currency') }}</label>

						<div class="col-md-3" v-bind:class="{ 'has-error' : 'currency' in errors }">
							<select id="currency" type="text" class="form-control" name="currency" v-model="form.currency" v-bind:disabled="modal.readonly" >
								<option v-for="(display, index) in modal.currency" v-bind:value="index" >@{{ display }}</option>
							</select>
							<span v-if="'currency' in errors" class="help-block">
								<strong>@{{ errors['currency'][0] }}</strong>
							</span>
						</div>

						<label for="notes" class="col-md-2 control-label">{{ trans('forms.Notes') }}</label>

						<div class="col-md-4" v-bind:class="{ 'has-error' : 'notes' in errors }">
							<textarea id="notes" type="text" class="form-control" name="notes" rows="5" v-model="form.notes" v-bind:readonly="modal.readonly"></textarea>
							<span v-if="'notes' in errors" class="help-block">
								<strong>@{{ errors['notes'][0] }}</strong>
							</span>
						</div>
					</div>

					<hr />

					<div v-for="(line, index) in form.line" class="form-group detail-line">
						<input id="line[]" type="hidden" name="line[]" v-model="line" ></input>

						<div class="col-md-3 col-md-offset-1" v-bind:class="{ 'has-error' : 'product.'+index in errors }">
							{{ trans('forms.Item') }}
							<select id="product[]" class="form-control" name="product[]" v-model="form.product[index]" v-bind:readonly="modal.readonly">
								<option v-for="(display, index) in modal.item" v-bind:value="index" >@{{ display }}</option>
							</select>
							<span v-if="'product.'+index in errors" class="help-block">
								<strong>@{{ errors['product.'+index][0] }}</strong>
							</span>
						</div>

						<div class="col-md-3" v-bind:class="{ 'has-error' : 'incurdate.'+index in errors }">
							{{ trans('forms.Incur date') }}
							<div class="input-group date" >
								<input id="incurdate[]" v-bind:ref="'incurdate'+index" type="text" class="form-control" v-bind:data-line="index" name="incurdate[]" v-model="form.incurdate[index]" v-bind:readonly="modal.readonly">
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
							<input id="description[]" type="text" class="form-control" name="description[]" v-model="form.description[index]" v-bind:readonly="modal.readonly" ></input>
							<span v-if="'description.'+index in errors" class="help-block">
								<strong>@{{ errors['description.'+index][0] }}</strong>
							</span>
						</div>

						<div class="clearfix"></div>

						<div class="col-md-2 col-md-offset-2" v-bind:class="{ 'has-error' : 'unitprice.'+index in errors }">
							{{ trans('forms.Unit price') }}
							<input id="unitprice[]" type="number" style="text-align:right" min="0" step="{{ \App\TaxableEntity::theCompany()->currency->getFormat()['min'] }}" class="form-control" name="unitprice[]" v-model="form.unitprice[index]" v-on:change="updateChargeSubtotal(index)" v-bind:readonly="modal.readonly"></input>
							<span v-if="'unitprice.'+index in errors" class="help-block">
								<strong>@{{ errors['unitprice.'+index][0] }}</strong>
							</span>
						</div>

						<div class="col-md-2" v-bind:class="{ 'has-error' : 'quantity.'+index in errors }">
							{{ trans('forms.Quantity') }}
							<input id="quantity[]" type="number" style="text-align:right" min="0" step="1" class="form-control" name="quantity[]" v-model="form.quantity[index]" v-on:change="updateChargeSubtotal(index)" v-bind:readonly="modal.readonly"></input>
							<span v-if="'quantity.'+index in errors" class="help-block">
								<strong>@{{ errors['quantity.'+index][0] }}</strong>
							</span>
						</div>

						<div class="col-md-2">
							{{ trans('forms.Subtotal') }}
							<input id="subtotal[]" type="text" style="text-align:right" class="form-control" name="subtotal[]" v-model="form.subtotal[index]" v-bind:key="form.subtotal[index]" readonly></input>
						</div>

						<div class="col-md-2 col-md-offset-1" v-bind:class="{ 'has-error' : 'upload-selector.'+index in errors }">
							{{ trans('forms.Attachment') }}<br>
							<button v-if="modal.readonly" type="button" class="btn btn-info image-button" v-bind:title="form.filename[index]" v-bind:data-url="form.fileurl[index]" v-html="form.filename[index]"></button>
							<label v-if="!modal.readonly" class="btn" v-bind:class="{ 'btn-danger' : 'upload-selector.'+index in errors, 'btn-info' : !('upload-selector.'+index in errors) }" v-bind:for="'upload-selector['+index+']'">
								<!-- assuming value='C:\fakepath\filename' -->
								<input v-bind:id="'upload-selector['+index+']'" v-bind:name="'upload-selector['+index+']'" type="file" style="display:none;" v-bind:ref="'attachment'+index" v-on:change="updateChargeAttachment(index);" />
								<span id="upload-selector-label[]" v-bind:title="form.filename[index]" v-bind:key="form.filename[index]" v-bind:ref="'file_display'+index">{{ trans('tool.Upload file') }}</span>
							</label>
							<span v-if="'upload-selector.'+index in errors" class="help-block">
								<strong>@{{ errors['upload-selector.'+index][0] }}</strong>
							</span>
						</div>
					</div>
				</form>
			</div>
			<div v-if="Object.keys(modal.action).length > 0" class="modal-footer">
				<div class="form-group">
					<div class="col-md-2">
						<button v-if="!modal.readonly" type="button" class="btn btn-info" onclick="return addNewChargeLine();">+&nbsp;{{ trans('forms.Add line') }}</button>
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