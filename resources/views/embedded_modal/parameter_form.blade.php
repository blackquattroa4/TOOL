
<div class="modal fade" id="embeddedParameterModal" tabindex="-1" role="dialog" aria-labelledby="embeddedParameterLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">

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

			<div class="modal-body">
				<form class="form-horizontal" role="form" enctype="multipart/form-data" method="POST" action="">

					<input type="hidden" name="_token" v-model="modal.csrf">

					<div class="form-group">
						<label for="param_key" class="col-md-3 control-label">{{ trans('forms.Key') }}</label>

						<div class="col-md-7" v-bind:class="{ 'has-error' : 'param_key' in errors }">
							<input id="param_key" type="text" class="form-control" name="param_key" v-model="form.key" v-bind:readonly="modal.readonly">
							<span v-if="'param_key' in errors" class="help-block">
								<strong>@{{ errors['param_key'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label for="param_value" class="col-md-3 control-label">{{ trans('forms.Value') }}</label>

						<div class="col-md-7" v-bind:class="{ 'has-error' : 'param_value' in errors }">
							<input id="param_value" type="text" class="form-control" name="param_value" v-model="form.value" v-bind:readonly="modal.readonly">
							<span v-if="'param_value' in errors" class="help-block">
								<strong>@{{ errors['param_value'][0] }}</strong>
							</span>
						</div>
					</div>
				</form>
			</div>

      <div v-if="!modal.readonly || (Object.keys(modal.action).length > 0)" class="modal-footer">
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