

<div class="modal fade" id="embeddedDocumentModal" tabindex="-1" role="dialog" aria-labelledby="embeddedDocumentModalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">

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
						<label for="title" class="col-md-3 control-label">{{ trans('document.Title') }}</label>

						<div class="col-md-7" v-bind:class="{ 'has-error' : 'title' in errors }">
							<input id="title" type="text" class="form-control" name="title" v-model="form.title" v-bind:readonly="modal.readonly" />
							<span v-if="'title' in errors" class="help-block">
								<strong>@{{ errors['title'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label for="description" class="col-md-3 control-label">{{ trans('document.Description') }}</label>

						<div class="col-md-7" v-bind:class="{ 'has-error' : 'description' in errors }">
							<textarea id="description" col="50" type="text" class="form-control" name="description" v-bind:disabled="modal.readonly">@{{ form.description }}</textarea>
							<span v-if="'description' in errors" class="help-block">
								<strong>@{{ errors['description'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label for="thefile" class="col-md-3 control-label">{{ trans('document.File') }}</label>

						<div class="col-md-7" v-bind:class="{ 'has-error' : 'thefile' in errors }">
							<label class="btn btn-info" for="thefile">
								<a v-if="modal.readonly" v-bind:href="'/document/download/' + form.id">
									<span>@{{ form.file_name }}</span>
								</a>
								<!-- assuming value='C:\fakepath\filename' -->
								<input v-if="!modal.readonly" id="thefile" name="thefile" type="file" style="display:none;" onchange="$('#upload-selector-label').html( ($(this).val().substring($(this).val().lastIndexOf( '\\' ) + 1)) );" />
								<span v-if="!modal.readonly" id="upload-selector-label" >{{ trans('tool.Browse file') }}</span>
							</label>
							<span v-if="'thefile' in errors" class="help-block">
								<strong>@{{ errors['thefile'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label for="permissions" class="col-md-3 control-label">{{ trans('document.Permissions') }}</label>
						<div class="col-md-7" v-bind:class="{ 'has-error' : 'permissions' in errors }">

							<div class="panel panel-default">
								<div class="panel-heading">
									{{ trans('messages.Role') }}
								</div>
								<div class="panel-body">
									<div v-for="(content, index) in form.permission.roles" class="form-group">
										<div class="col-md-5">
											<br>
											<label class="control-label pull-right" v-bind:for="'permission[role][' + index + ']'">@{{ content.display }}</label>
										</div>

										<div class="col-md-2">
											{{ trans('forms.Read') }}
											<input class="form-control" style="width:30px;height:30px;" type="checkbox" title="{{ trans('forms.Read') }}" v-bind:id="'permission[roles][' + index + '][read]'" v-bind:name="'permission[roles][' + index + '][read]'" v-bind:checked="form.permission.roles[index].read" v-bind:disabled="modal.readonly">
										</div>

										<div class="col-md-2">
											{{ trans('forms.Update') }}
											<input class="form-control col-md-3" style="width:30px;height:30px;" type="checkbox" title="{{ trans('forms.Update') }}" v-bind:id="'permission[roles][' + index + '][update]'" v-bind:name="'permission[roles][' + index + '][update]'" v-bind:checked="form.permission.roles[index].update" v-bind:disabled="modal.readonly">
										</div>

										<div class="col-md-2">
											{{ trans('forms.Delete') }}
											<input class="form-control col-md-3" style="width:30px;height:30px;" type="checkbox" title="{{ trans('forms.Delete') }}" v-bind:id="'permission[roles][' + index + '][delete]'" v-bind:name="'permission[roles][' + index + '][delete]'" v-bind:checked="form.permission.roles[index].delete" v-bind:disabled="modal.readonly">
										</div>
									</div>
								</div>
							</div>

							<div class="panel panel-default">
								<div class="panel-heading">
									{{ trans('messages.User') }}
								</div>
								<div class="panel-body">
									<div v-for="(content, index) in form.permission.users" class="form-group">
										<div class="col-md-5">
											<br>
											<label class="control-label pull-right" v-bind:for="'permission[role][' + index + ']'">@{{ content.display }}</label>
										</div>

										<div class="col-md-2">
											{{ trans('forms.Read') }}
											<input class="form-check-input" style="width:30px;height:30px;" type="checkbox" title="{{ trans('forms.Read') }}" v-bind:id="'permission[users][' + index + '][read]'" v-bind:name="'permission[users][' + index + '][read]'" v-bind:checked="form.permission.users[index].read" v-bind:disabled="modal.readonly || (form.creator_id == form.permission.users[index].id)">
										</div>

										<div class="col-md-2">
											{{ trans('forms.Update') }}
											<input class="form-check-input" style="width:30px;height:30px;" type="checkbox" title="{{ trans('forms.Update') }}" v-bind:id="'permission[users][' + index + '][update]'" v-bind:name="'permission[users][' + index + '][update]'" v-bind:checked="form.permission.users[index].update" v-bind:disabled="modal.readonly || (form.creator_id == form.permission.users[index].id)">
										</div>

										<div class="col-md-2">
											{{ trans('forms.Delete') }}
											<input class="form-check-input" style="width:30px;height:30px;" type="checkbox" title="{{ trans('forms.Delete') }}" v-bind:id="'permission[users][' + index + '][delete]'" v-bind:name="'permission[users][' + index + '][delete]'" v-bind:checked="form.permission.users[index].delete" v-bind:disabled="modal.readonly || (form.creator_id == form.permission.users[index].id)">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</form>

				<br v-if="modal.readonly" />

				<div v-if="modal.readonly" class="panel panel-default">
					<div class="panel-heading">{{ trans('document.Past versions') }}</div>
					<div class="panel-body">
						<table v-if="modal.history.length > 0" id="oldversiontable" class="table table-striped table-bordered" cellspacing="0" width="100%">
							<thead>
								<tr>
									<th>{{ trans('document.Title') }}</th>
									<th>{{ trans('document.Description') }}</th>
									<th>{{ trans('document.Timestamp') }}</th>
								</tr>
							</thead>
							<tfoot>
								<tr>
									<th>{{ trans('document.Title') }}</th>
									<th>{{ trans('document.Description') }}</th>
									<th>{{ trans('document.Timestamp') }}</th>
								</tr>
							</tfoot>
							<tbody>
								<tr v-for="(content) in modal.history">
									<td><a v-bind:href="'/document/download/' + content.id">@{{ content['title'] }}</a>&emsp;(v@{{ content['version'] }})</td>
									<td>@{{ content['description'] }}</td>
									<td>@{{ content['create_date_display'] }}</td>
								</tr>
							</tbody>
						</table>
						<p v-if="modal.history.length == 0">{{ trans('document.No previous version') }}</p>
					</div>
				</div>
			</div>

			<div v-if="(Object.keys(modal.action).length > 0) || !modal.readonly" class="modal-footer">
				<div class="form-group">
					<div class="col-md-12">
						<button v-for="(callback, display) in modal.action" type="button" class="btn btn-primary pull-right" style="margin-left:3px;" v-on:click="window[callback]()" v-html="display"></button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>