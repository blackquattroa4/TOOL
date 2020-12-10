
<div class="modal fade" id="embeddedTradableNoticeModal" tabindex="-1" role="dialog" aria-labelledby="embeddedTradableNoticeModalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">

	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<table width="100%">
					<tr>
						<td>
							<font size="4" style="padding-right:30px;">@{{ modal.title }}</font>
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
				<form class="form-horizontal" role="form" enctype="multipart/form-data" method="POST" action="">

					<input type="hidden" name="_token" v-model="modal.csrf">

					<div class="form-group">
						<label for="product[]" class="col-md-3 control-label">{{ trans('forms.Product') }}</label>

						<div class="col-md-7" v-bind:class="{ 'has-error' : 'product' in errors }">
							<select id="product[]" class="form-control" name="product[]" multiple="multiple" v-bind:size="modal.tradable.length" v-model="form.product" v-bind:disabled="modal.readonly">
                <option v-for="tradable in modal.tradable" v-bind:value="tradable.id" v-html="tradable.sku + '&emsp;&emsp;(' + tradable.code + ')'"></option>
							</select>
							<span v-if="'product' in errors" class="help-block">
								<strong>@{{ errors['product'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label for="summary" class="col-md-3 control-label">{{ trans('forms.Summary') }}</label>

						<div class="col-md-7" v-bind:class="{ 'has-error' : 'summary' in errors }">
              <input id="summary" type="text" class="form-control" name="summary" v-model="form.summary" v-bind:readonly="modal.readonly">
							<span v-if="'summary' in errors" class="help-block">
								<strong>@{{ errors['summary'][0] }}</strong>
							</span>
						</div>
					</div>

					<!--
          <div class="form-group">
						<label for="description" class="col-md-3 control-label">{{ trans('document.Description') }}</label>

						<div class="col-md-7" v-bind:class="{ 'has-error' : 'description' in errors }">
							<textarea id="description" col="50" type="text" class="form-control" name="description" v-model="description" v-bind:disabled="modal.readonly"></textarea>
							<span v-if="'description' in errors" class="help-block">
								<strong>@{{ errors['description'][0] }}</strong>
							</span>
						</div>
					</div>
          -->

					<div class="form-group">
						<label for="thefile" class="col-md-3 control-label">{{ trans('document.File') }}</label>

						<div class="col-md-7" v-bind:class="{ 'has-error' : 'thefile' in errors }">
							<label class="btn btn-info" for="thefile">
								<a v-if="modal.readonly" v-bind:href="'/document/view/' + form.document_id">
									<span v-bind:id="'download-button[' + form.id + ']'">@{{ form.file_name }}</span>
								</a>
								<input v-if="!modal.readonly" id="thefile" name="thefile" type="file" style="display:none;" onchange="$('#embeddedTradableNoticeModal #upload-selector-label').html( ($(this).val().substring($(this).val().lastIndexOf( '\\' ) + 1)) );" />
								<span v-if="!modal.readonly" id="upload-selector-label" >{{ trans('tool.Browse file') }}</span>
							</label>
							<span v-if="'thefile' in errors" class="help-block">
								<strong>@{{ errors['thefile'][0] }}</strong>
							</span>
						</div>
					</div>
				</form>
			</div>

      <div v-if="Object.keys(modal.action).length > 0" class="modal-footer">
        <div class="form-group">
          <div class="col-md-12">
            <button v-for="(callback, display) in modal.action" type="button" class="btn btn-primary pull-right" style="margin-left:3px;" v-on:click="window[callback]()" v-html="display">
            </button>
          </div>
        </div>
      </div>

		</div>
	</div>
</div>