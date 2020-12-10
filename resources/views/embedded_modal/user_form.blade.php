
<div class="modal fade" id="embeddedUserEntryModal" tabindex="-1" role="dialog" aria-labelledby="embeddedUserEntryLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">

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
				<form id="user_form" class="form-horizontal" role="form" method="POST" action="">

          <input type="hidden" name="_token" v-model="modal.csrf">

					<div class="form-group">
						<div class="col-md-10 col-md-offset-1" v-bind:class="{ 'has-error' : 'name' in errors }">
							{{ trans('tool.Name') }}
              <input id="name" name="name" class="form-control" placeholder="{{ trans('forms.Name') }}" v-model="form.name" v-bind:disabled="modal.readonly">
							<span v-if="'name' in errors" class="help-block">
                <strong>@{{ errors['name'][0] }}</strong>
							</span>
						</div>
					</div>

          <div class="form-group">
            <div class="col-md-10 col-md-offset-1" v-bind:class="{ 'has-error' : 'email' in errors }">
              {{ trans('tool.Email') }}
              <input id="email" name="email" class="form-control" placeholder="{{ trans('tool.Email') }}" v-model="form.email" v-bind:disabled="modal.readonly">
							<span v-if="'email' in errors" class="help-block">
                <strong>@{{ errors['email'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<div class="col-md-10 col-md-offset-1" v-bind:class="{ 'has-error' : 'password' in errors }">
							{{ trans('tool.Password') }}
              <input id="password" name="password" type="password" class="form-control" placeholder="{{ trans('tool.Password') }}" v-model="form.password" v-bind:disabled="modal.readonly">
							<span v-if="'password' in errors" class="help-block">
                <strong>{{ $errors->first('password') }}</strong>
              </span>
						</div>
					</div>

          <div class="form-group">
						<div class="col-md-10 col-md-offset-1" v-bind:class="{ 'has-error' : 'password_confirm' in errors }">
							{{ trans('tool.Confirm Password') }}
              <input id="confirm-password" name="confirm-password" type="password" class="form-control" placeholder="{{ trans('tool.Confirm Password') }}" v-model="form.password_confirm" v-bind:disabled="modal.readonly">
							<span v-if="'confirm-password' in errors" class="help-block">
                <strong>@{{ errors['confirm-password'][0] }}</strong>
              </span>
						</div>
					</div>

					<div class="form-group">
						<div class="col-md-10 col-md-offset-1" v-bind:class="{ 'has-error' : 'roles' in errors }">
							{{ trans('tool.Role') }}
              <select id="roles[]" name="roles[]" class="form-control multiple" v-model="form.roles" multiple>
              @foreach (\App\Role::all() as $role)
                <option value="{{ $role->id }}">{{ $role->display_name }}</option>
              @endforeach
              </select>
							<span v-if="'roles' in errors" class="help-block">
								<strong>@{{ errors['roles'][0] }}</strong>
							</span>
						</div>
          </div>
        </form>
			</div>

      <div v-if="Object.keys(modal.action).length > 0" class="modal-footer">
        <div class="col-md-12">
          <button v-for="(callback, display) in modal.action" type="button" class="btn btn-primary pull-right" style="margin-left:3px;" v-on:click="window[callback]()" v-html="display">
          </button>
        </div>
      </div>

    </div>
	</div>
</div>