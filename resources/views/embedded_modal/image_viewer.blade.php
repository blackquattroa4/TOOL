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
          <h4 class="modal-title">{{ $embedded_image_viewer_modal_title }}</h4>
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
