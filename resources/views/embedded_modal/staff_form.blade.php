

<div class="modal fade" id="embeddedStaffModal" tabindex="-1" role="dialog" aria-labelledby="embeddedStaffModalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">

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
						<h4 class="modal-title">{{ trans('hr.Staff files') }}</h4>
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
        <form class="form-horizontal" role="form" method="POST" >

          <input type="hidden" name="_token" v-model="modal.csrf">

          <div class="form-group" v-bind:class="{ 'has-error' : 'name' in errors }">
            <label for="name" class="col-md-4 control-label">{{ trans('messages.Name') }}</label>

            <div class="col-md-6">
              <input id="name" type="text" class="form-control" name="name" v-model="form.name" v-bind:disabled="modal.readonly">
              <span class="help-block" v-if="'name' in errors">
            		<strong>@{{ errors['name'][0] }}</strong>
              </span>
            </div>
        	</div>

          <div class="form-group" v-bind:class="{ 'has-error' : 'email' in errors }">
            <label for="email" class="col-md-4 control-label">{{ trans('messages.E-mail address') }}</label>

            <div class="col-md-6">
              <input id="email" type="text" class="form-control" name="email" v-model="form.email" v-bind:disabled="modal.readonly" >
              <span class="help-block" v-if="'email' in errors">
                <strong>@{{ errors['email'][0] }}</strong>
              </span>
            </div>
        	</div>

        	<div class="form-group" v-bind:class="{ 'has-error' : 'phone' in errors }">
            <label for="email" class="col-md-4 control-label">{{ trans('messages.Contact phone') }}</label>

            <div class="col-md-6">
              <input id="phone" type="phone" class="form-control" name="phone" v-model="form.phone" v-bind:disabled="modal.readonly" >
              <span class="help-block" v-if="'phone' in errors">
                <strong>@{{ errors['phone'][0] }}</strong>
              </span>
      			</div>
  				</div>

          <div class="form-group" v-bind:class="{ 'has-error' : 'street' in errors }">
            <label for="street" class="col-md-4 control-label">{{ trans('messages.Street address') }}</label>

            <div class="col-md-6">
              <input id="street" type="street" class="form-control" name="street" v-model="form.street" v-bind:disabled="modal.readonly" >
              <span class="help-block" v-if="'street' in errors">
                <strong>@{{ errors['street'][0] }}</strong>
            	</span>
            </div>

            <div class="col-md-2">
              <input id="unit" type="unit" class="form-control" name="unit" v-model="form.unit" v-bind:disabled="modal.readonly" >
              <span class="help-block" v-if="'unit' in errors">
                <strong>@{{ errors['unit'][0] }}</strong>
              </span>
            </div>
        	</div>

          <div class="form-group" v-bind:class="{ 'has-error' : 'city' in errors }">
            <label for="city" class="col-md-4 control-label">{{ trans('messages.City') }}</label>

            <div class="col-md-6">
              <input id="city" type="city" class="form-control" name="city" v-model="form.city" v-bind:disabled="modal.readonly" >
              <span class="help-block" v-if="'city' in errors">
                <strong>@{{ errors['city'] }}</strong>
              </span>
            </div>
          </div>

          <div class="form-group" v-bind:class="{ 'has-error' : 'district' in errors }">
            <label for="phone" class="col-md-4 control-label">{{ trans('messages.District') }}</label>

            <div class="col-md-6">
              <input id="district" type="district" class="form-control" name="district" v-model="form.district" v-bind:disabled="modal.readonly" >
              <span class="help-block" v-if="'district' in errors">
                <strong>@{{ errors['district'][0] }}</strong>
              </span>
            </div>
          </div>

          <div class="form-group" v-bind:class="{ 'has-error' : 'state' in errors }">
            <label for="city" class="col-md-4 control-label">{{ trans('messages.State') }}</label>

            <div class="col-md-6">
              <input id="state" type="state" class="form-control" name="state" v-model="form.state" v-bind:disabled="modal.readonly" >
              <span class="help-block" v-if="'state' in errors">
                <strong>@{{ errors['state'][0] }}</strong>
              </span>
          	</div>
          </div>

          <div class="form-group" v-bind:class="{ 'has-error' : 'country' in errors }">
            <label for="country" class="col-md-4 control-label">{{ trans('messages.Country') }}</label>

            <div class="col-md-6">
              <select id="country" type="country" class="form-control" name="country" v-model="form.country" v-bind:disabled="modal.readonly" >
								<option v-for="(display, index) in modal.country" v-bind:value="index" >@{{ index }}&emsp;@{{ display }}</option>
							</select>
              <span class="help-block" v-if="'country' in errors">
                <strong>@{{ errors['country'][0] }}</strong>
            	</span>
            </div>
          </div>

          <div class="form-group" v-bind:class="{ 'has-error' : 'zipcode' in errors }">
            <label for="zipcode" class="col-md-4 control-label">{{ trans('messages.Zipcode') }}</label>

            <div class="col-md-6">
              <input id="zipcode" type="zipcode" class="form-control" name="zipcode" v-model="form.zipcode" v-bind:disabled="modal.readonly" >
              <span class="help-block" v-if="'zipcode' in errors">
                <strong>@{{ errors['zipcode'][0] }}</strong>
              </span>
            </div>
          </div>

				</form>

        <div v-if="modal.readonly" style="margin-top:30px;" class="panel panel-default col-md-10 col-md-offset-1">
          <div class="panel-heading">{{ trans('hr.Staff files') }}</div>
          <div class="panel-body">
            <table width="100%" class="table table-striped">
              <tr>
                <th>{{ trans('forms.Date') }}</th>
                <th>{{ trans('forms.Description') }}</th>
                <th>{{ trans('forms.Staff') }}</th>
                <th></th>
              </tr>
              <tr v-for="(id, index) in form.file">
                <td>@{{ form.file_date[index] }}</td>
                <td>@{{ form.file_title[index] }}</td>
                <td>@{{ form.file_creator[index] }}</td>
                <td>
                  <button class="btn btn-info btn-xs image-button" title="{{ trans('forms.View') }}" v-bind:data-url="'/hr/archive/' + form.file_path[index] + '/download'">
                    <i class="fa fa-eye" aria-hidden="true"></i>
                  </button>
                </td>
              </tr>
            </table>
          </div>
        </div>
			</div>

			<div v-if="Object.keys(modal.action).length > 0" class="modal-footer">
				<div class="form-group">
					<div class="col-md-12">
						<button v-for="(callback, display) in modal.action" type="button" class="btn btn-primary pull-right" style="margin-left:3px;" v-on:click="window[callback]()" v-bind:disabled="form.file.length < 1" v-html="display">
						</button>
					</div>
				</div>
			</div>

    </div>
  </div>
</div>