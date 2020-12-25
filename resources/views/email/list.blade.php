@extends('layouts.app')

@section('additional-style')
@if (!App::environment('local'))
	<link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.7.0/min/dropzone.min.css" rel="stylesheet" type="text/css">
@else
	<link rel="stylesheet" href="{{ asset('external/dropzone/5.7.0/dropzone.min.css') }}">
@endif

	<style>
		.dropzone {
			min-height : 30px !important;
			padding: 10px !important;
		}

		.dropzone .dz-message {
			margin : 5px !important;
		}

		.dropzone .dz-preview {
			min-height: 30px !important;
			margin : 5px !important;
		}

		.dropzone .dz-details {
			min-width : 120%;
			text-align : left;
			padding : 1px !important;
		}

		.dropzone .dz-preview .dz-image {
      width: 60px;
      height: 60px;
    }
	</style>
@endsection

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<!-- modal that shows work-in-progress -->
			<div class="modal fade" id="emailwipModal" tabindex="-1" role="dialog" aria-labelledby="emailWipModalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">
				<div class="modal-dialog" role="email">
					<div class="modal-content">
						<div class="modal-header">
						</div>
						<div class="modal-body">
						</div>
					</div>
				</div>
			</div>

			<!-- email reader modal -->
			<div class="modal fade" id="emailReaderModal" tabindex="-1" role="dialog" aria-labelledby="emailReaderModalLabel" data-backdrop="static" aria-hidden="false">
				<div style="width:80%;height:80%;" class="modal-dialog" role="email">
					<div class="modal-content">
						<div class="modal-header">
							<table style="width:100%;"><tr>
								<td>
									<p class="modal-title" id="emailReaderModalLabel" style="vertical-align:middle">
										<i id="emailentity" title="" class="fa fa-users fa-2x" aria-hidden="true" ></i>
									</p>
								</td>
								<td>
									<p id="emailsubject" style="margin-bottom:0px;"></p>
								</td>
								<td>
									<button type="button" style="vertical-align:top" class="close" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								</td>
							</tr></table>
						</div>
						<div style="height: 50vh; overflow-y: scroll;" class="modal-body" id="emailbody">
						</div>
						<div class="modal-footer" id="readerattach">
						</div>
					</div>
				</div>
			</div>

			<!-- email writer modal -->
			<div class="modal fade" id="emailWriterModal" tabindex="-1" role="dialog" aria-labelledby="emailWriterModalLabel" data-backdrop="static" aria-hidden="false">
				<div style="width:80%;height:80%;" class="modal-dialog" role="email">
					<div class="modal-content">
						<div class="modal-header">
							<table style="width:100%;">
								<tr>
									<td>{{ trans('email.Subject') }}</td>
									<td>&emsp;</td>
									<td style="padding-right:15px;" width="100%">
										<input style="width:100%;" id="emailcomposersubject" name="emailcomposersubject" placeholder="{{ trans('email.Subject') }}"/>
									</td>
									<td>
										<button type="button" style="padding-left:20px;align:top;" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</td>
								</tr>
								<tr>
									<td>{{ trans('email.Recipient') }}</td>
									<td>&emsp;</td>
									<td width="100%">
										<table width="100%">
											<tr>
												<td><span class="align-middle">&nbsp;{{ trans("email.To") }}&nbsp;</span></td>
												<td style="padding:5px 15px 5px 0px;width:100%"><input style="width:100%;" id="emailcomposerto" name="emailcomposerto" placeholder="{{ trans('email.Put your email recipient here') }}"/></td>
											</tr>
											<tr>
												<td><span class="align-middle">&nbsp;{{ trans("email.Cc") }}&nbsp;</span></td>
												<td style="padding:5px 15px 5px 0px;width:100%"><input style="width:100%;" id="emailcomposercc" name="emailcomposercc" placeholder="{{ trans('email.Put your email recipient here') }}"/></td>
											</tr>
											<tr>
												<td><span class="align-middle">&nbsp;{{ trans("email.Bcc") }}&nbsp;</span></td>
												<td style="padding:5px 15px 5px 0px;width:100%"><input style="width:100%;" id="emailcomposerbcc" name="emailcomposerbcc" placeholder="{{ trans('email.Put your email recipient here') }}"/></td>
											</tr>
										</table>
									</td>
									<td></td>
								</tr>
								<tr>
									<td>{{ trans('forms.Attachment') }}</td>
									<td>&emsp;</td>
									<td>
										<div class="dropzone" id="email-attachment"></div>
									</td>
									<td></td>
								</tr>
							</table>
						</div>
						<!-- <div style="height: 50vh; overflow-y: scroll;" class="modal-body" id="emailbody"> -->
						<div class="modal-body" id="emailbody">
							<textarea id="emailcomposer" name="emailcomposer">
							</textarea>
						</div>
						<div class="modal-footer" id="emailattach">
							<button id="email-send-button" class='btn btn-info' onclick="ajaxSendMail();" ><i class="fa fa-paper-plane" aria-hidden="true"> {{ trans('forms.Send') }}</i></button>
						</div>
					</div>
				</div>
			</div>

			<div id="inboxwindow" class="panel panel-default">
				<div class="panel-heading">
					<table width='100%'>
						<tr>
							<td><h4>{{ trans('email.Inbox') }}</h4></td>
							<td align='right'>
								<input type="button" class="btn btn-primary pull-right" onclick="ajaxDownload('INBOX');" value="{{ trans('email.Download') }}"/>
							</td>
						</tr>
					</table>
				</div>

				<div class="panel-body">
					<table id="inboxtable" class="table table-striped table-bordered" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th style="width:20%;">{{ trans('email.Date') }}</th>
								<th style="width:40%;">{{ trans('email.Subject') }}</th>
								<th style="width:25%;">{{ trans('email.From') }}</th>
								<th></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>{{ trans('email.Date') }}</th>
								<th>{{ trans('email.Subject') }}</th>
								<th>{{ trans('email.From') }}</th>
								<th></th>
							</tr>
						</tfoot>
						<tbody>
							<!-- body generated by ajax -->
							<tr>
								<td data-order="1970-01-01"></td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			<div id="sentwindow" class="panel panel-default">
				<div class="panel-heading">
					<table width='100%'>
						<tr>
							<td><h4>{{ trans('email.Sent') }}</h4></td>
							<td align='right'>
								<input type="button" class="btn btn-primary pull-right" onclick="ajaxPrepareCorrespondence('compose', 0);" value="{{ trans('email.Compose message') }}"/>
							</td>
						</tr>
					</table>
				</div>

				<div class="panel-body">
					<table id="senttable" class="table table-striped table-bordered" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th style="width:25%;">{{ trans('email.Date') }}</th>
								<th style="width:45%;">{{ trans('email.Subject') }}</th>
								<th style="width:30%;">{{ trans('email.From') }}</th>
								<!-- <th></th> -->
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>{{ trans('email.Date') }}</th>
								<th>{{ trans('email.Subject') }}</th>
								<th>{{ trans('email.From') }}</th>
								<!-- <th></th> -->
							</tr>
						</tfoot>
						<tbody>
							<!-- body generated by ajax -->
							<td data-order="1970-01-01"></td>
							<td></td>
							<td></td>
							<!-- <td></td> -->
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<ul id="contextMenu" class="dropdown-menu" role="menu" style="display:none" >
	<li style="text-align:center;"><b>{{ trans('messages.Quick scroll menu') }}</b></li>
	<li class="divider"></li>
	<li><a tabindex="-1" onclick="$('html,body').animate({scrollTop:$('#inboxwindow').offset().top}, 500);">{{ trans('email.Inbox') }}</a></li>
	<!-- <li><a tabindex="-1" onclick="$('html,body').animate({scrollTop:$('#draftwindow').offset().top}, 500);">{{ trans('email.Draft') }}</a></li> -->
	<li><a tabindex="-1" onclick="$('html,body').animate({scrollTop:$('#sentwindow').offset().top}, 500);">{{ trans('email.Sent') }}</a></li>
	<!-- <li><a tabindex="-1" onclick="$('html,body').animate({scrollTop:$('#eventwindow').offset().top}, 500);">{{ trans('email.Event') }}</a></li> -->
	<li><a tabindex="-1" onclick="$('html,body').animate({scrollTop:$('.container').offset().top}, 500);">{{ trans('messages.Return to top') }}<i style="padding-left:1em;" class="fa fa-arrow-up" aria-hidden="true"></i></a></li>
</ul>
@endsection

@section('post-content')
@if (!App::environment('local'))
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ckeditor/4.12.1/ckeditor.js"></script>
@else
	<script src="{{ asset('external/ckeditor/4.12.1/ckeditor.js') }}"></script>
@endif

@if (!App::environment('local'))
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.7.0/min/dropzone.min.js"></script>
@else
	<script type="text/javascript" src="{{ asset('external/dropzone/5.7.0/dropzone.min.js') }}"></script>
@endif

	<script type="text/javascript" src="{{ asset('js/DataTableHelper.js') }}" ></script>

	<script type="text/javascript" src="{{ asset('js/HtmlTemplateHelper.js') }}" ></script>

	<script type="text/javascript" src="{{ asset('js/ContextMenu.js') }}"></script>

	<script id="inbox-table-template" type="text/x-custom-template">
		<tr id="email-row-{id}">
			<td data-order="{date}">{date_display}</td>
			<td title="{subject}">
				{subject}
				<i data-condition="{is_deleted}" class="fa fa-trash pull-right" aria-hidden="true" title="{{ trans('email.Deleted') }}"></i>
				<i data-condition="{is_answered}" class="fa fa-exchange pull-right" aria-hidden="true" title="{{ trans('email.Answered') }}"></i>
				<i data-condition="{is_flagged}" class="fa fa-flag pull-right" aria-hidden="true" title="{{ trans('email.Flagged') }}"></i>
				<i data-condition="{is_unseen}" class="fa fa-envelope pull-right" aria-hidden="true" title="{{ trans('email.Unseen') }}"></i>
				<i data-condition="{is_recent}" class="fa fa-history pull-right" aria-hidden="true" title="{{ trans('email.Recent') }}"></i>
			</td>
			<td title="{from_email}">
				{from_name}
			</td>
			<td style="width:110px">
				<button class='btn btn-info btn-xs' title="{{ trans('forms.View') }}" data-toggle="modal" data-target="#emailReaderModal" onclick="ajaxGetMailContent({id});" ><i class="fa fa-eye" aria-hidden="true"></i></button>
				<button class='btn btn-info btn-xs' title="{{ trans('email.Reply') }}" onclick="ajaxPrepareCorrespondence('reply', {id});"><i class="fa fa-reply" aria-hidden="true"></i></button>
				<button class='btn btn-info btn-xs' title="{{ trans('email.Forward') }}" onclick="ajaxPrepareCorrespondence('forward', {id});"><i class="fa fa-share" aria-hidden="true"></i></button>
				<button class='btn btn-info btn-xs' title="{{ trans('email.Delete') }}" onclick="ajaxDeleteMail({id});" ><i class="fa fa-trash" aria-hidden="true"></i></button>
			</td>
		</tr>
	</script>

	<script id="sent-table-template" type="text/x-custom-template">
		<tr id="email-row-{id}">
			<td data-order="{date}">{date_display}</td>
			<td title="{subject}">
				{subject}
			</td>
			<td title="{from_email}">
				{from_name}
			</td>
		</tr>
	</script>

	<script type="text/javascript">
		$(document).ready(function() {
			$('#inboxtable').DataTable(Object.assign(
				dataTableDefaultConfig('{{ trans('forms.Refresh/Reload') }}', 'GET', '/email/box/inbox', { }, '#inboxtable', '#inbox-table-template', '{{ trans('tool.Enter search keyword') }}', '', {{ env('DATATABLE_DEFAULT_ROWS', 10) }}),
				{
					order : [[ 0, 'desc' ]],
					columnDefs : [{ orderable : false, targets : 3 }]
				}
			)).on('preXhr.dt', function(e, settings) {
				showDataTableProcessingTransient('#inboxtable');
			});
		} );
		$(document).ready(function() {
			$('#senttable').DataTable(Object.assign(
				dataTableDefaultConfig('{{ trans('forms.Refresh/Reload') }}', 'GET', '/email/box/sent', { }, '#senttable', '#sent-table-template', '{{ trans('tool.Enter search keyword') }}', '', {{ env('DATATABLE_DEFAULT_ROWS', 10) }}),
				{
					order : [[ 0, 'desc' ]],
				}
			)).on('preXhr.dt', function(e, settings) {
				showDataTableProcessingTransient('#senttable');
			});
		} );

		$(document).ready(function() { CKEDITOR.replace('emailcomposer'); } );

		$(document).ready(function() {
			Dropzone.options.emailAttachment = {
				init : function () {
          this.on("addedfile", function (data) {
            var ext = data.name.split('.').pop().toLowerCase();

						switch (ext) {
            case "pdf":
              $(data.previewElement).find(".dz-image img").attr("src", "/images/pdf-icon.png");
							break;
            case "doc":
						case "docx":
              $(data.previewElement).find(".dz-image img").attr("src", "/images/word-icon.png");
							break;
            case "xls":
						case "xlsx":
              $(data.previewElement).find(".dz-image img").attr("src", "/images/excel-icon.png");
							break;
						case "ppt":
						case "pptx":
              $(data.previewElement).find(".dz-image img").attr("src", "/images/powerpoint-icon.png");
							break;
            }
          })
        },
				uploadMultiple : true,
				url : "/email/attach",
				thumbnailWidth : 60,
				thumbnailHeight: 60,
				sending: function(file, xhr, formData) {
        	formData.append("_token", "{{ csrf_token() }}");
				},
			}
		});

		$(".container").contextMenu({
			menuSelector: "#contextMenu",
			/*
			menuSelected: function (invokedOn, selectedMenu) {
				var msg = "You selected the menu item '" + selectedMenu.text() + "' on the value '" + invokedOn.text() + "'";
				alert(msg);
			}
			*/
		});

		function ajaxDownload(box) {
			$.ajax({
				type: 'GET',
				url: '/email/get',
				data: {
						box: box,
					},
				dataType: 'html',
				beforeSend: function(data) {
					$('div#emailwipModal div.modal-header').html('{{ trans('email.Downloading email') }}');
					$('div#emailwipModal div.modal-body').html('<i class="fa fa-spinner fa-pulse fa-5x fa-fw"></i>');
					$('div#emailwipModal').modal('show');
				},
			}).done(function(data) {
				var result = JSON.parse(data);
				for (var idx in result) {
					$('table#inboxtable tbody').prepend('<tr class="email-row-' + result[idx]['id'] + '"><td>' + result[idx]['sent_at'] + '</td><td>' + result[idx]['subject'] + '<i class="fa fa-envelope pull-right" aria-hidden="true" title="{{ trans('email.Unseen') }}"></i><i class="fa fa-history pull-right" aria-hidden="true" title="{{ trans('email.Recent') }}"></i></td><td>' + result[idx]['from'] + '</td><td><button class="btn btn-info btn-xs" title="{{ trans('forms.View') }}" data-toggle="modal" data-target="#emailReaderModal" onclick="ajaxGetMailContent(' + result[idx]['id'] + ');" ><i class="fa fa-eye" aria-hidden="true"></i></button>\n<button class="btn btn-info btn-xs" title="{{ trans('email.Reply') }}" onclick="ajaxPrepareCorrespondence(\'reply\', ' + result[idx]['id'] + ');"><i class="fa fa-reply" aria-hidden="true"></i></button>\n<button class="btn btn-info btn-xs" title="{{ trans('email.Forward') }}" onclick="ajaxPrepareCorrespondence(\'forward\', ' + result[idx]['id'] + ');"><i class="fa fa-share" aria-hidden="true"></i></button>\n<button class="btn btn-info btn-xs" title="{{ trans('email.Delete') }}" onclick="ajaxDeleteMail(' + result[idx]['id'] + ');" ><i class="fa fa-trash" aria-hidden="true"></i></button>\n</td></tr>');
				}
			}).fail(function(data) {
				$('div#emailwipModal div.modal-body').html('{{ trans("messages.System failure") }}');
			}).always(function(data) {
				$('div#emailwipModal').modal('hide');
			});
		}

		function ajaxGetMailContent(id) {
			var xhttp = new XMLHttpRequest();

			var fileExtension = {
				png:"fa-file-image-o",
				PNG:"fa-file-image-o",
				jpeg:"fa-file-image-o",
				JPEG:"fa-file-image-o",
				jpg:"fa-file-image-o",
				JPG:"fa-file-image-o",
				bmp:"fa-file-image-o",
				BMP:"fa-file-image-o",
				pdf:"fa-file-pdf-o",
				PDF:"fa-file-pdf-o",
				xls:"fa-file-excel-o",
				XLS:"fa-file-excel-o",
				xlsx:"fa-file-excel-o",
				XLSX:"fa-file-excel-o",
				mp3:"fa-file-audio-o",
				MP3:"fa-file-audio-o",
				doc:"fa-file-word-o",
				DOC:"fa-file-word-o",
				docx:"fa-file-word-o",
				DOCX:"fa-file-word-o",
				zip:"fa-file-archive-o",
				ZIP:"fa-file-archive-o",
				mp4:"fa-file-video-o",
				MP4:"fa-file-video-o",
				ppt:"fa-file-powerpoint-o",
				PPT:"fa-file-powerpoint-o",
				pptx:"fa-file-powerpoint-o",
				PPTX:"fa-file-powerpoint-o",
				txt:"fa-file-o",
				TXT:"fa-file-o",
			};
			// clear content first.
			document.getElementById("emailentity").title = "";
			document.getElementById("emailsubject").innerHTML = "";
			document.getElementById("emailbody").innerHTML = "<i class=\"fa fa-spinner fa-pulse fa-5x fa-fw\"></i>";
			document.getElementById("readerattach").innerHTML = "";
			// AJAX call to obtain content.
			xhttp.onreadystatechange = function() {
				if (this.readyState == 4) {
					switch (this.status) {
					case 200:
						response = JSON.parse(this.responseText);
						document.getElementById("emailentity").title = 'From: ' + response['from'];
						if (response['to'].length > 0) {
							document.getElementById("emailentity").title += '\nTo: ' + response['to'][0];
							for (var i = 1, j = response['to'].length; i < j; i++) {
								document.getElementById("emailentity").title += '\n    ' + response['to'][i];
							}
						}
						if (response['cc'].length > 0) {
							document.getElementById("emailentity").title += '\nCC: ' + response['cc'][0];
							for (var i = 1, j = response['cc'].length; i < j; i++) {
								document.getElementById("emailentity").title += '\n    ' + response['cc'][i];
							}
						}
						if (response['bcc'].length > 0) {
							document.getElementById("emailentity").title += '\nBCC: ' + response['bcc'][0];
							for (var i = 1, j = response['bcc'].length; i < j; i++) {
								document.getElementById("emailentity").title += '\n    ' + response['bcc'][i];
							}
						}
						document.getElementById("emailsubject").innerHTML = response['subject'];
						if (!response['htmlmsg'] || response['htmlmsg'].length === 0) {
							document.getElementById("emailbody").innerHTML = response['plainmsg'].replace("\r\n", "<br>").replace("\n", "<br>").replace("\r", "<br>");
						} else {
							document.getElementById("emailbody").innerHTML = response['htmlmsg'];
						}
						for (fileName in response['attachments']) {
							var fileExt = fileName.substring(fileName.lastIndexOf('.')+1);
							if (!Object.keys(fileExtension).includes(fileExt)) {
								fileExt = "txt";
							}
							document.getElementById("readerattach").innerHTML += "<a href=\"/email/attachment/" + response['attachments'][fileName] + "\"><i class=\"fa " + fileExtension[fileExt] + " fa-2x\" title=\"" + fileName + "\" ></i></a>&emsp;";
						}
						// remove 'unseen' logo
						$('tr.email-row-' + id + ' i.fa-envelope').remove();
						break;
					default:
						document.getElementById("emailbody").innerHTML = "{{ trans('email.Failed to retrieve message') }}";
						break;
					}
				}
			};
			xhttp.open("GET", "/email/view/"+id, true);
			xhttp.send();
		}

		function ajaxDeleteMail(id) {
			$.ajax({
				type: 'POST',
				url: '/email/delete/' + id,
				data: {
						_token: '{{ csrf_token() }}',
					},
				dataType: 'html',
				beforeSend: function(data) {
					$('div#emailwipModal div.modal-header').html('{{ trans('email.Deleting email') }}');
					$('div#emailwipModal div.modal-body').html('<i class="fa fa-spinner fa-pulse fa-5x fa-fw"></i>');
					$('div#emailwipModal').modal('show');
				},
			}).done(function(data) {
				// delete the row
				$('tr.email-row-' + id ).remove();
			}).fail(function(data) {
				$('div#emailwipModal div.modal-body').html('{{ trans("messages.System failure") }}');
			}).always(function(data) {
				$('div#emailwipModal').modal('hide');
			});
		}

		function ajaxSendMail() {
			var files = Dropzone.forElement("#email-attachment").getAcceptedFiles();
			var filePaths = [];
			for (var i in files) {
				filePaths.push(JSON.parse(files[i].xhr.response)[0]);
			}

			$.ajax({
				type: 'POST',
				url: '/email/send',
				data: {
						_token: '{{ csrf_token() }}',
						to: $('input#emailcomposerto').val(),
						cc: $('input#emailcomposercc').val(),
						bcc: $('input#emailcomposerbcc').val(),
						subject: $('input#emailcomposersubject').val(),
						content: CKEDITOR.instances.emailcomposer.getData(),
						attachment: filePaths,
					},
				dataType: 'html',
				beforeSend: function(data) {
					$('div#emailWriterModal div.modal-footer').prepend('<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>');
					$('button#email-send-button').prop('disabled', true);
				},
			}).done(function(data) {
				var result = JSON.parse(data);
				if (result.hasOwnProperty('id') && result.hasOwnProperty('sent_at') && result.hasOwnProperty('subject') && result.hasOwnProperty('from')) {
					$('table#senttable tbody').prepend('<tr><td id="email-row-' + result['id'] + '">' + result['sent_at'] + '</td><td>' + result['subject'] + '</td><td>' + result['from'] + '</td>' +
						//'<td><button class="btn btn-info btn-xs" title="{{ trans('forms.View') }}" data-toggle="modal" data-target="#emailReaderModal" onclick="ajaxGetMailContent(' + result['id'] + ');" ><i class="fa fa-eye" aria-hidden="true"></i></button></td>' +
						'</tr>');
				}
			}).fail(function(data) {
			}).always(function(data) {
				$('div#emailWriterModal').modal('hide');
				$('input#emailcomposerto').val("");
				$('input#emailcomposercc').val("");
				$('input#emailcomposerbcc').val("");
			 	$('input#emailcomposersubject').val("");
				Dropzone.forElement("#email-attachment").removeAllFiles(true);	// clear out attachment
				CKEDITOR.instances.emailcomposer.setData("");
				$('div#emailWriterModal div.modal-footer i.fa-fw').remove();
				$('button#email-send-button').prop('disabled', false);
			});
		}

		function ajaxPrepareCorrespondence(mode, id) {
			$.ajax({
				type: 'GET',
				url: '/email/prepare',
				data: {
						mode: mode,
						id: id,
					},
				dataType: 'html',
				beforeSend: function(data) {
					$('input#emailcomposerto').val("");
					$('input#emailcomposercc').val("");
					$('input#emailcomposerbcc').val("");
				 	$('input#emailcomposersubject').val("");
					Dropzone.forElement("#email-attachment").removeAllFiles(true);	// clear out attachment
					CKEDITOR.instances.emailcomposer.setData("");
					$('div#emailWriterModal div.modal-footer').prepend('<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>');
					$('button#email-send-button').prop('disabled', true);
					$('div#emailWriterModal').modal('show');
				},
			}).done(function(data) {
				var result = JSON.parse(data);
				$('input#emailcomposerto').val(result['to']);
				$('input#emailcomposercc').val(result['cc']);
				$('input#emailcomposerbcc').val(result['bcc']);
			 	$('input#emailcomposersubject').val(result['subject']);
				CKEDITOR.instances.emailcomposer.setData(result['content']);
				if (result['attachments'] !== 'undefined') {
					for (var index in result['attachments']) {
						var fileName = result['attachments'][index]['name'];
						// faking Dropzone object
						var fileObj = {
								name : fileName.substring(fileName.lastIndexOf("/") + 1),
								size : result['attachments'][index]['size'],
								accepted : true,
								xhr : {
									response : JSON.stringify([ fileName ]),
									responseText : JSON.stringify([ fileName ]),
								}
							};
						Dropzone.forElement("#email-attachment").emit("addedfile", fileObj);
						Dropzone.forElement("#email-attachment").emit("success", fileObj);
						Dropzone.forElement("#email-attachment").emit("complete", fileObj);
						Dropzone.forElement("#email-attachment").files.push(fileObj);
					}
				}
			}).fail(function(data) {
			}).always(function(data) {
				$('div#emailWriterModal div.modal-footer i.fa-fw').remove();
				$('button#email-send-button').prop('disabled', false);
			});
		}
	</script>
@endsection
