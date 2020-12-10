
<div class="modal fade" id="embeddedNewInteractionModal" tabindex="-1" role="dialog" aria-labelledby="embeddedNewInteractionModalLabel" data-backdrop="static" aria-hidden="false">
  <div style="width:60%;height:60%;" class="modal-dialog" role="file">
    <div class="modal-content">
      <div class="modal-header">
        <table width="100%">
          <tr>
            <td>
              <h4>{{ trans('forms.New request') }}</h4>
            </td>
            <td>
              <span v-if="'general' in errors" v-bind:class="{ 'text-danger' : 'general' in errors }" >
                <strong>@{{ errors['general'][0] }}</strong>
              </span>
            </td>
            <td>
              <button aria-label="Close" class="close" type="button" onclick="$(this).closest('#embeddedNewInteractionModal').modal('hide')">
                <span aria-hidden="true">&times;</span>
              </button>
            </td>
          </tr>
        </table>
      </div>
      <div class="modal-body">
        <input type="hidden" id="files" name="files" value="" />
        <div>
          <label class="form-label">{{ trans('tool.Title') }}</label>
          <div v-bind:class="{ 'has-error' : 'title' in errors }">
            <input class="form-control" id="title" name="title" class="col-md-6"></input>
            <span v-if="'title' in errors" class="help-block">
              <strong>@{{ errors['title'][0] }}</strong>
            </span>
          </div>
        </div>
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
        <button type="button" class="btn btn-primary" onclick="submitNewRequest();">{{ trans('forms.Submit') }}</button>
        <button type="button" class="btn btn-info" onclick="$(this).closest('#embeddedNewInteractionModal').modal('hide')">{{ trans('forms.Cancel') }}</button>
      </div>
    </div>
  </div>
</div>