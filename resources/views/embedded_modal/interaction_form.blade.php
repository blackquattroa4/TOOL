
<div class="modal fade" id="embeddedInteractionModal" tabindex="-1" role="dialog" aria-labelledby="embeddedInteractionModalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">

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

	<!-- modal to create new log entry -->
	<div class="modal fade" id="newLogModal" tabindex="-1" role="dialog" aria-labelledby="newLogModalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">
		<div style="width:60%;height:60%;" class="modal-dialog" role="file">
			<div class="modal-content">
				<div class="modal-header">
          <table width="100%">
            <tr>
              <td>
                <h4>{{ trans('forms.New log') }}</h4>
              </td>
              <td>
                <span v-if="'general' in errors" v-bind:class="{ 'text-danger' : 'general' in errors }" >
                  <strong>@{{ errors['general'][0] }}</strong>
                </span>
              </td>
              <td>
                <button aria-label="Close" class="close" type="button" onclick="$(this).closest('#newLogModal').modal('hide')">
                  <span aria-hidden="true">&times;</span>
                </button>
              </td>
            </tr>
          </table>
				</div>
				<div class="modal-body">
					<input type="hidden" id="files" name="files" value="" />
					<div>
						<label class="form-label">{{ trans('forms.Description') }}</label>
            <div v-bind:class="{ 'has-error' : 'description' in errors }">
			        <textarea class="form-control" style="width:100%;"></textarea>
              <span v-if="'description' in errors" class="help-block">
                <strong>@{{ errors['description'][0] }}</strong>
              </span>
            </div>
					</div>
					<label class="form-label">{{ trans('forms.Attachment') }}</label>
					<div id="uploaded-files" class="dropzone"></div>
					<span>{{ trans('messages.Drag file into above boxes to upload.')}}</span>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary" onclick="submitNewLog();">{{ trans('forms.Submit') }}</button>
					<button type="button" class="btn btn-info" onclick="$(this).closest('#newLogModal').modal('hide')">{{ trans('forms.Cancel') }}</button>
				</div>
			</div>
		</div>
	</div>

	<!-- modal to update attribute of interaction -->
	<div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">
		<div class="modal-dialog" role="file">
			<div class="modal-content">
				<div class="modal-header">
          <table width="100%">
            <tr>
              <td>
                <h4>{{ trans('forms.Update') }}</h4>
              </td>
              <td>
                <span v-if="'general' in errors" v-bind:class="{ 'text-danger' : 'general' in errors }" >
                  <strong>@{{ errors['general'][0] }}</strong>
                </span>
              </td>
              <td>
                <button aria-label="Close" class="close" type="button" onclick="$(this).closest('#updateModal').modal('hide')">
                  <span aria-hidden="true">&times;</span>
                </button>
              </td>
            </tr>
          </table>
				</div>
				<div class="modal-body">
						<div class="row form-group">
							<label class="control-label col-md-3 col-md-offset-1 text-right">{{ trans('forms.Type') }}</label>
							<div class="col-md-4" v-bind:class="{ 'has-error' : 'type' in errors }">
								<select class="form-control" id="interaction_type" name="interaction_type" v-model="form.type">
									<option v-for="(display, key) in modal.allTypes" v-bind:value="key">@{{ display }}</option>
								</select>
                <span v-if="'type' in errors" class="help-block">
  								<strong>@{{ errors['type'][0] }}</strong>
  							</span>
							</div>
						</div>
						<div class="row form-group">
							<label class="control-label col-md-3 col-md-offset-1 text-right">{{ trans('forms.Status') }}</label>
							<div class="col-md-4" v-bind:class="{ 'has-error' : 'status' in errors }">
								<select class="form-control" id="interaction_status" name="interaction_status" v-model="form.status">
									<option v-for="(content, key) in modal.allStatuses" v-bind:value="key" v-bind:class="{ 'hidden' : !content.class.includes(form.type) }">@{{ content.display }}</option>
								</select>
                <span v-if="'status' in errors" class="help-block">
  								<strong>@{{ errors['status'][0] }}</strong>
  							</span>
							</div>
						</div>
						<div class="row form-group">
							<label class="control-label col-md-3 col-md-offset-1 text-right">{{ trans('forms.Responsibility') }}</label>
							<div class="col-md-4" v-bind:class="{ 'has-error' : 'responder_id' in errors }">
								<select class="form-control" id="interaction_responsibility" name="interaction_responsibility" v-model="form.responder_id">
									<option v-for="(display, key) in modal.allParticipants" v-bind:value="key">@{{ display }}</option>
								</select>
                <span v-if="'responder_id' in errors" class="help-block">
  								<strong>@{{ errors['responder_id'][0] }}</strong>
  							</span>
							</div>
						</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary" onclick="submitUpdate();">{{ trans('forms.Submit') }}</button>
					<button type="button" class="btn btn-info" onclick="$(this).closest('#updateModal').modal('hide')">{{ trans('forms.Cancel') }}</button>
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
							<font size="4" style="padding-right:30px;">@{{ form.description }}</font>
              <i v-if="form.type == 'request'" class="fa fa-commenting-o fa-2x" style="margin-right:5px;" aria-hidden="true" title="{{ trans('forms.Request') }}"></i>
              <i v-if="form.type == 'assignment'" class="fa fa-check-square-o fa-2x" style="margin-right:5px;" aria-hidden="true" title="{{ trans('forms.Assignment') }}"></i>
              <i v-if="form.status == 'requested'" class="fa fa-comments-o fa-2x" style="margin-right:5px;" aria-hidden="true" title="{{ trans('status.Requested') }}"></i>
              <i v-if="form.status == 'evaluating'" class="fa fa-balance-scale fa-2x" style="margin-right:5px;" aria-hidden="true" title="{{ trans('status.Evaluating') }}"></i>
              <i v-if="form.status == 'in-progress'" class="fa fa-cogs fa-2x" style="margin-right:5px;" aria-hidden="true" title="{{ trans('status.In-progress') }}"></i>
              <i v-if="form.status == 'closed'" class="fa fa-archive fa-2x" style="margin-right:5px;" aria-hidden="true" title="{{ trans('status.Closed') }}"></i>
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
				<div v-for="(oneGroup, idx) in form.groupLog" class="talk-bubble tri-right round border" v-bind:class="{ 'left-in' : oneGroup.is_self, 'right-in' : !oneGroup.is_self, 'pull-right' : !oneGroup.is_self }">
					<div class="talktext">
						<h4>@{{ oneGroup.user }}</h4>
  					<p v-for="(oneLog, idx2) in oneGroup.logs">@{{ oneLog }}</p>&emsp;<br>
            <span v-for="(download) in oneGroup.downloads">
              <button style="margin-right:3px;margin-bottom:3px;" class="btn btn-info media-button" v-bind:data-hash="download.hash">@{{ download.name }}</button>
            </span>
						<p>&emsp;</p>
						<p class="pull-right"><small>@{{ oneGroup.time }}</small></p>
					</div>
				</div>
			</div>
      <div v-if="!modal.readonly" class="modal-footer">
        <button type="button" class="btn btn-primary pull-right" onclick="openNewLogModal();">{{ trans('forms.New log') }}</button>
        <button v-if="modal.canUpdate" type="button" class="btn btn-primary pull-right" style="margin-right:5px;" onclick="openUpdateModal();">{{ trans('forms.Update') }}</button>
      </div>
		</div>
	</div>
</div>