
<div class="modal fade" id="embeddedRoleEntryModal" tabindex="-1" role="dialog" aria-labelledby="embeddedRoleEntryLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">

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
				<form id="role_form" class="form-horizontal" role="form" method="POST" action="">

          <input type="hidden" name="_token" v-model="modal.csrf">

          <div class="form-group">
            <div class="col-md-10 col-md-offset-1" v-bind:class="{ 'has-error' : 'name' in errors }">
              {{ trans('forms.Name') }}
              <input placeholder="{{ trans('forms.Name') }}" class="form-control" name="name" type="text" v-model="form.name" v-bind:disabled="modal.readonly || (form.id > 0)">
              <span v-if="'name' in errors" class="help-block">
                <strong>@{{ errors['name'][0] }}</strong>
              </span>
            </div>
          </div>

          <div class="form-group">
            <div class="col-md-10 col-md-offset-1" v-bind:class="{ 'has-error' : 'display_name' in errors }">
              {{ trans('forms.Display') }}
              <input placeholder="{{ trans('forms.Display') }}" class="form-control" name="display_name" type="text" v-model="form.display_name" v-bind:readonly="modal.readonly">
  						<span v-if="'display_name' in errors" class="help-block">
  							<strong>@{{ errors['display_name'][0] }}</strong>
  						</span>
  					</div>
          </div>

					<div class="form-group">
            <div class="col-md-10 col-md-offset-1" v-bind:class="{ 'has-error' : 'description' in errors }">
  						{{ trans('tool.Description') }}
              <textarea class="form-control" name="description" v-model="form.description" v-bind:readonly="modal.readonly"></textarea>
  						<span v-if="'description' in errors" class="help-block">
  							<strong>@{{ errors['description'][0] }}</strong>
  						</span>
  					</div>
          </div>

          <div class="form-group">
            <div class="col-md-10 col-md-offset-1" v-bind:class="{ 'has-error' : 'permission' in errors }">
							{{ trans('tool.Permission') }}
							<br/>
							@foreach(\App\Permission::all() as $permission)
							<div class="col-md-10 col-md-offset-1">
                <input type="checkbox" id="permission[{{ $permission->id }}]" name="permission[{{ $permission->id }}]" style="width:25px;height:25px;" v-model='form.permission["{{ $permission->id }}"]' v-bind:disabled="modal.readonly">
								<span style="padding-left:5px;vertical-align:super;font-size:16px;">{{ $permission->display_name }}</span>
							</div>
							@endforeach
							<span v-if="'permission' in errors" class="help-block">
								<strong>@{{ errors['permission'][0] }}</strong>
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