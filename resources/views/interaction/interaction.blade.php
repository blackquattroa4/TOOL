@extends('layouts.app')

@section('additional-style')
	<link rel="stylesheet" type="text/css" href="/external/chat-bubble/chat-bubble.css">
	<link rel="stylesheet" type="text/css" href="/external/imagebox/css/style.css">
@if (!App::environment('local'))
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.7.0/min/dropzone.min.css">
@else
	<link rel="stylesheet" href="{{ asset('external/dropzone/5.7.0/dropzone.min.css') }}">
@endif
@endsection

@section('content')
<div class="container">

	<!-- document-loading progress modal -->
	<div class="modal fade" id="progressModal" tabindex="-1" role="dialog" aria-labelledby="progressModalLabel" data-backdrop="static" aria-hidden="false">
		<div class="modal-dialog" role="progress">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="progressModalLabel">{{ trans('forms.Attachment') }}</h4>
				</div>
				<div class="modal-body">
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('forms.Close') }}</button>
				</div>
			</div>
		</div>
	</div>

	<div class="image-group">
		<div class="image-container">
			<img id="image-canvas" class="img hide" data-url="" />
		</div>
	</div>

	<!-- modal to create new log entry -->
	<div class="modal fade" id="newLogModal" tabindex="-1" role="dialog" aria-labelledby="newLogModalLabel" data-backdrop="static" aria-hidden="false">
		<div style="width:60%;height:60%;" class="modal-dialog" role="file">
			<div class="modal-content">
				<div class="modal-header">
					<!-- <button type="button pull-right" style="vertical-align:top; horizontal-align:right;" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button> -->
					<h4>{{ trans('forms.New log') }}</h4>
				</div>
				<div class="modal-body">
					<input type="hidden" id="files" name="files" value="" />
					<div>
						<label class="form-label">{{ trans('forms.Description') }}</label>
						<textarea class="form-control" style="width:100%;"></textarea>
					</div>
					<label class="form-label">{{ trans('forms.Attachment') }}</label>
					<div id="uploaded-files" class="dropzone"></div>
					<span>{{ trans('messages.Drag file into above boxes to upload.')}}</span>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary" onclick="submitNewLog();">{{ trans('forms.Submit') }}</button>
					<button type="button" class="btn btn-info" data-dismiss="modal" aria-label="Close">{{ trans('forms.Cancel') }}</button>
				</div>
			</div>
		</div>
	</div>

	<!-- modal to update attribute of interaction -->
	<div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" data-backdrop="static" aria-hidden="false">
		<div class="modal-dialog" role="file">
			<div class="modal-content">
				<div class="modal-header">
					<h4>{{ trans('forms.Update') }}</h4>
				</div>
				<div class="modal-body">
						<div class="row form-group">
							<label class="control-label col-md-3 col-md-offset-1 text-right">{{ trans('forms.Type') }}</label>
							<div class="col-md-4">
								<select class="form-control" id="interaction_type" name="interaction_type">
								@foreach (['request' => 'Request', 'assignment' => 'Assignment'] as $oneType => $display)
									<option value="{{ $oneType }}" {{ ($request->type == $oneType) ? "selected" : "" }}>{{ trans('forms.' . $display) }}</option>
								@endforeach
								</select>
							</div>
						</div>
						<div class="row form-group">
							<label class="control-label col-md-3 col-md-offset-1 text-right">{{ trans('forms.Status') }}</label>
							<div class="col-md-4">
								<select class="form-control" id="interaction_status" name="interaction_status">
								@foreach ([
										'requested' => [
											'display' => 'Requested',
											'class' => 'request',
										],
										'evaluating' => [
											'display' => 'Evaluating',
											'class' => 'request',
										],
										'in-progress' => [
											'display' => 'In-progress',
											'class' => 'assignment',
										],
										'closed' => [
											'display' => 'Closed',
											'class' => 'request assignment',
										]
									] as $oneStatus => $attribute)
									<option class="{{ $attribute['class'] }} {{ in_array($request->type, explode(" ", $attribute['class'])) ? "" : "hidden" }}" value="{{ $oneStatus }}" {{ ($request->status == $oneStatus) ? "selected" : "" }}>{{ trans('status.' . $attribute['display']) }}</option>
								@endforeach
								</select>
							</div>
						</div>
						<div class="row form-group">
							<label class="control-label col-md-3 col-md-offset-1 text-right">{{ trans('forms.Responsibility') }}</label>
							<div class="col-md-4">
								<select class="form-control" id="interaction_responsibility" name="interaction_responsibility">
								@foreach ($request->users as $user)
									<option value="{{ $user->id }}" {{ ($request->users(['requestee', 'assignee'])->first()->id == $user->id) ? "selected" : "" }}>{{ $user->name }}</option>
								@endforeach
								</select>
							</div>
						</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary" onclick="submitUpdate();">{{ trans('forms.Submit') }}</button>
					<button type="button" class="btn btn-info" data-dismiss="modal" aria-label="Close">{{ trans('forms.Cancel') }}</button>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">
					<button type="button" class="btn btn-primary pull-right" onclick="openNewLogModal();">{{ trans('forms.New log') }}</button>
					<button type="button" class="btn btn-primary pull-right" style="margin-right:5px;" onclick="openUpdateModal();">{{ trans('forms.Update') }}</button>
          <h4>{{ $request->description }}</h4>
        </div>

				<div class="panel-body">
					<span class="badge pull-right" style="background-color:{{ ($request->status != "closed") ? "#33FF33" : "#000000" }};">
						{{ trans('status.' . ucfirst($request->status)) }}
					</span>

					@foreach ($request->groupLogs() as $oneGroup)
							<div class="talk-bubble tri-right round border {{ $oneGroup['is_self'] ? "left-in" : "right-in pull-right" }}">
		  					<div class="talktext">
									<h4>{{ $oneGroup['user'] }}</h4>
								@foreach ($oneGroup['logs'] as $oneLog)
		    					<p>{{ $oneLog }}</p>
								@endforeach
									&emsp;<br>
								@foreach ($oneGroup['downloads'] as $download)
		    					<button class="btn btn-info media-button" data-hash="{{ $download['hash'] }}">{{ $download['name'] }}</button>
								@endforeach
									<p>&emsp;</p>
									<p class="pull-right"><small>{{ $oneGroup['time'] }}</small></p>
		  					</div>
							</div>
					@endforeach

				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('post-content')

	<script src="{{ asset('external/imagebox/js/jquery.imagebox.js') }}"></script>

@if (!App::environment('local'))
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.7.0/min/dropzone.min.js"></script>
@else
	<script type="text/javascript" src="{{ asset('external/dropzone/5.7.0/dropzone.min.js') }}"></script>
@endif

	<script type="text/javascript">
		$(document).ready(function() {

			$("div.image-group").imageBox();

			Dropzone.options.uploadedFiles = {
				init : function () {
					this.on("addedfile", function (data) {
						var ext = data.name.split('.').pop().toLowerCase();

						switch (ext) {
						case "pdf":
							$(data.previewElement).find(".dz-image img").attr("src", "/images/pdf-icon.png");
							break;
						case "gif":
						case "jpg":
						case "jpeg":
						case "png":
							$(data.previewElement).find(".dz-image img").attr("src", "/images/image-icon.png");
							break;
						case "mp4":
							$(data.previewElement).find(".dz-image img").attr("src", "/images/video-icon.png");
							break;
						}
					});
					this.on("success", function (data, response) {
						var newIds = response['ids'];
						var oldIdString = $('div#newLogModal div.modal-body input#files').val();
						var oldIds = (oldIdString.length > 0) ? oldIdString.split(",") : [];
						// if uploading multiple files, ids will duplicate, duplicates are
						// eliminated by constructing unique set and then covert back to array
						var newIdString = Array.from(new Set(oldIds.concat(newIds)));
						$('div#newLogModal div.modal-body input#files').val(newIdString.join(","));
					});
				},
				uploadMultiple : true,
				url : "/interaction/upload",
				thumbnailWidth : 80,
				thumbnailHeight: 80,
				sending: function(file, xhr, formData) {
					formData.append("_token", "{{ csrf_token() }}");
				},
			}

      $("button.media-button").bind('click', function() {

        var url = '/file/download/' + $(this).data('hash') + '?base64=1';

        $.ajax({
          type: 'GET',
          url: url,
          dataType: 'html',
          beforeSend: function(data) {
            // show spinning wheel when downloading...
            $('div#progressModal div.modal-body').html('<i class="fa fa-spinner fa-pulse fa-2x fa-fw" aria-hidden="true"></i>');
            $('div#progressModal div.modal-footer button').addClass('hidden');
            $('div#progressModal').modal('show');
          },
        }).done(function(data) {
          $('#progressModal').modal('hide');
          var result = JSON.parse(data);
          if (result['success']) {
            $('div.image-group div img#image-canvas').data('url', result['content']);
            $('div.image-group div img#image-canvas').trigger('click');
          }
        }).fail(function(data) {
          // show failed message.
          $('#progressModal div.modal-body').html('{{ trans('messages.Attachment download failed') }}');
          $('#progressModal div.modal-footer button').removeClass('hidden');
        }).always(function(data) {
        });
      });

			$('select#interaction_type').bind('change', function() {
				var theType = $(this).find('option:selected').val();
				$('select#interaction_status option.' + theType).removeClass('hidden');
				$('select#interaction_status option').not('.' + theType).addClass('hidden');
				$('select#interaction_status').val("");
			});
		});

		// functions prepares log-entry modal
		function openNewLogModal() {
			$('div.has-error').removeClass("has-error");
			$('span.help-block').remove();
			$('div#newLogModal div.modal-footer span').detach();
			$('div#newLogModal div.modal-body input').val('');
			$('div#newLogModal div.modal-body textarea').val('');
			Dropzone.forElement("#uploaded-files").removeAllFiles(true);			// clear out dropbox
			$('div#newLogModal').modal('show');
		}

		// Ajax function to add new request
		function submitNewLog()
		{
			$('div.has-error').removeClass("has-error");
			$('span.help-block').remove();

			// error checking
			var hasError = false;

			if ($('div#newLogModal div.modal-body input#title').val() === "") {
				var domParent = $('div#newLogModal div.modal-body input#title').closest('div');
				domParent.addClass("has-error");
				domParent.append('<span class="help-block"><strong>' + "{{ str_replace(":attribute", "title", trans('validation.required')) }}" + '</strong></span>');
				hasError = true;
			}

			if ($('div#newLogModal div.modal-body textarea').val() === "") {
				var domParent = $('div#newLogModal div.modal-body textarea').closest('div');
				domParent.addClass("has-error");
				domParent.append('<span class="help-block"><strong>' + "{{ str_replace(":attribute", "description", trans('validation.required')) }}" + '</strong></span>');
				hasError = true;
			}

			if (hasError) return;

			$.ajax({
				type: 'POST',
				url: '/interaction/update/{{ $request->id }}',
				data: {
						description: $('div#newLogModal div.modal-body textarea').val(),
						files: $('div#newLogModal div.modal-body input#files').val(),
						_token: "{{ csrf_token() }}",
					},
				dataType: 'html',
				beforeSend: function(data) {
					// show spinning wheel when submitting...
					$('#newLogModal div.modal-footer span').detach();
					$('#newLogModal div.modal-footer').prepend('<i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i>');
					$('#newLogModal div.modal-footer button').addClass('hidden');
				},
			}).done(function(data) {
				var result = JSON.parse(data);
				if (result['result']) {
					$('div#newLogModal').modal('hide');
					location.reload(true);
				} else {
					$('#newLogModal div.modal-footer').prepend('<span>' + result['message'] + '</span>');
				}
			}).fail(function(data) {
				$('#newLogModal div.modal-footer').prepend('<span>' + result['message'] + '</span>');
			}).always(function(data) {
				$('#newLogModal div.modal-footer i').detach();
				$('#newLogModal div.modal-footer button').removeClass('hidden');
			});
		}

		function openUpdateModal()
		{
			$('div#updateModal').modal('show');
		}

		function submitUpdate()
		{
			$('div.has-error').removeClass("has-error");
			$('span.help-block').remove();

			// error checking
			var hasError = false;

			if (($('div#updateModal div.modal-body select#interaction_status').val() === "") ||
					($('div#updateModal div.modal-body select#interaction_status').val() == null)) {
				console.log("no status");
				var domParent = $('div#updateModal div.modal-body select#interaction_status').closest('div');
				domParent.addClass("has-error");
				domParent.append('<span class="help-block"><strong>' + "{{ str_replace(":attribute", "status", trans('validation.required')) }}" + '</strong></span>');
				hasError = true;
			}

			if (hasError) return;

			$.ajax({
				type: 'POST',
				url: '/interaction/{{ $request->id }}',
				data: {
						type: $('#updateModal div.modal-body select#interaction_type').val(),
						status: $('#updateModal div.modal-body select#interaction_status').val(),
						responsibility: $('#updateModal div.modal-body select#interaction_responsibility').val(),
						_token: "{{ csrf_token() }}",
					},
				dataType: 'html',
				beforeSend: function(data) {
					// show spinning wheel when submitting...
					$('#updateModal div.modal-footer span').detach();
					$('#updateModal div.modal-footer').prepend('<i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i>');
					$('#updateModal div.modal-footer button').addClass('hidden');
				},
			}).done(function(data) {
				var result = JSON.parse(data);
				if (result['success']) {
					$('div#updateModal').modal('hide');
					location.reload(true);
				} else {
					$('#updateModal div.modal-footer').prepend('<span>' + result['message'] + '</span>');
				}
			}).fail(function(data) {
				$('#updateModal div.modal-footer').prepend('<span>' + result['message'] + '</span>');
			}).always(function(data) {
				$('#updateModal div.modal-footer i').detach();
				$('#updateModal div.modal-footer button').removeClass('hidden');
			});
		}

	</script>
@endsection
