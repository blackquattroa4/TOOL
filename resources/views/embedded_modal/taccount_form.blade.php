

<div class="modal fade" id="embeddedTaccountFormModal" tabindex="-1" role="dialog" aria-labelledby="embeddedTaccountFormLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">

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
				<form class="form-horizontal" role="form" enctype="multipart/form-data" method="POST" >

					<input type="hidden" name="_token" v-model="modal.csrf">

					<div class="form-group">
						<label for="account" class="col-md-3 control-label">{{ trans('forms.Account') }}</label>

						<div class="col-md-7" v-bind:class="{ 'has-error' : 'account' in errors }">
							<input id="account" type="text" class="form-control" name="account" v-model="form.account" v-bind:readonly="modal.readonly">
							<span class="help-block" v-if="'account' in errors">
								<strong>@{{ errors['account'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label for="type" class="col-md-3 control-label">{{ trans('forms.Type') }}</label>

						<div class="col-md-7" v-bind:class="{ 'has-error' : 'type' in errors }">
							<select id="type" class="form-control" name="type" v-model="form.type" v-bind:readonly="modal.readonly">
								<option v-for="(display, index) in modal.type" v-bind:value="index" >@{{ display }}</option>
							</select>
							<span class="help-block" v-if="'type' in errors">
								<strong>@{{ errors['type'][0] }}</strong>
							</span>
            </div>
					</div>

					<div class="form-group">
						<label for="type" class="col-md-3 control-label">{{ trans('forms.Currency') }}</label>

						<div class="col-md-7" v-bind:class="{ 'has-error' : 'currency' in errors }">
							<select id="currency" class="form-control" name="currency" v-model="form.currency" v-bind:readonly="modal.readonly">
								<option v-for="(display,index) in modal.currency" v-bind:value="index" >@{{ display }}</option>
							</select>
							<span class="help-block" v-if="'currency' in errors">
								<strong>@{{ errors['currency'][0] }}</strong>
							</span>
						</div>
					</div>

		      <div class="form-group">
						<label for="active" class="col-md-3 control-label">{{ trans('forms.Active') }}</label>

						<div class="col-md-1" v-bind:class="{ 'has-error' : 'active' in errors }">
              <input id="active" type="hidden" name="active" v-model="form.active">
							<input id="_active" type="checkbox" class="form-control" name="_active" v-model="form.active">
							<span class="help-block" v-if="'active' in errors">
								<strong>@{{ errors['active'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label for="description" class="col-md-3 control-label">{{ trans('forms.Description') }}</label>

						<div class="col-md-7" v-bind:class="{ 'has-error' : 'description' in errors }">
							<input id="description" type="text" class="form-control" name="description" v-model="form.description" v-bind:readonly="modal.readonly">
							<span class="help-block" v-if="'description' in errors">
								<strong>@{{ errors['description'][0] }}</strong>
							</span>
						</div>
					</div>
				</form>
			</div>

      <div v-if="!modal.readonly" class="modal-footer">
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