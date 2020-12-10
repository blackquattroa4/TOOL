@extends('layouts.app')

@section('additional-style')
	<link rel="stylesheet" type="text/css" href="/external/imagebox/css/style.css">
@if (!App::environment('local'))
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.7.0/min/dropzone.min.css">
@else
	<link rel="stylesheet" href="{{ asset('external/dropzone/5.7.0/dropzone.min.css') }}">
@endif
@endsection

@section('content')
<div class="container">
	<div class="row">

		<!-- modal to add archive files -->
		<div class="modal fade" id="fileUploaderModal" tabindex="-1" role="dialog" aria-labelledby="fileUploaderModalLabel" data-backdrop="static" aria-hidden="false">
			<div style="width:60%;height:60%;" class="modal-dialog" role="file">
				<div class="modal-content">
					<div class="modal-header">
						<!-- <button type="button pull-right" style="vertical-align:top; horizontal-align:right;" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button> -->
						<h4>{{ trans('tool.File upload') }}</h4>
					</div>
					<div class="modal-body">
						<input type="hidden" id="staff_id" name="staff_id" value=""></input>
						<div id="uploaded-files" class="dropzone"></div>
						<span>{{ trans('messages.Drag file into above boxes to upload; filename will be stored as description.')}}</span>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Close">{{ trans('forms.Close') }}</button>
					</div>
				</div>
			</div>
		</div>

		<div class="col-md-10 col-md-offset-1">

			<!-- staff modal -->
		@if ($controlSwitch['staff-modal'])
			@include(prioritizeCustomizedTemplateOverDefault('embedded_modal.staff_form'))
		@endif

		@if ($controlSwitch['staff-window'])
			<div id="staffwindow" class="panel panel-default">
				<div class="panel-heading">
					<table width='100%'>
						<tr>
							<td><h4>{{ trans('hr.Staff') }}</h4></td>
							<td align='right'>
								<!-- <input type="button" class="btn btn-primary" /> -->
							</td>
						</tr>
					</table>
				</div>

				<div class="panel-body">
					<table id="stafftable" class="table table-striped table-bordered" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th>{{ trans('forms.Code') }}</th>
								<th>{{ trans('tool.Name') }}</th>
								<th>{{ trans('forms.Phone') }}</th>
                <th>{{ trans('tool.Email') }}</th>
								<th></th>
								<th></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
                <th>{{ trans('forms.Code') }}</th>
								<th>{{ trans('tool.Name') }}</th>
								<th>{{ trans('forms.Phone') }}</th>
                <th>{{ trans('tool.Email') }}</th>
								<th></th>
								<th></th>
							</tr>
						</tfoot>
						<tbody>
							<!-- body genereated by ajax; need an initial stuffer row  in order to get 'data-order' attribute active -->
							<tr>
								<td></td>
								<td></td>
								<td></td>
                <td></td>
								<td></td>
								<td></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		@endif
		</div>
	</div>
</div>

<ul id="contextMenu" class="dropdown-menu" role="menu" style="display:none" >
	<li style="text-align:center;"><b>{{ trans('messages.Quick scroll menu') }}</b></li>
	<li class="divider"></li>
@if ($controlSwitch['staff-window'])
	<li><a tabindex="-1" onclick="$('html,body').animate({scrollTop:$('#staffwindow').offset().top}, 500);">{{ trans('hr.Staff') }}</a></li>
@endif
	<li><a tabindex="-1" onclick="$('html,body').animate({scrollTop:$('.container').offset().top}, 500);">{{ trans('messages.Return to top') }}<i style="padding-left:1em;" class="fa fa-arrow-up" aria-hidden="true"></i></a></li>
</ul>
@endsection

@section('post-content')

	<script type="text/javascript" src="{{ asset('js/ContextMenu.js') }}"></script>

	<script type="text/javascript">

		$(".container").contextMenu({
			menuSelector: "#contextMenu",
			/*
			menuSelected: function (invokedOn, selectedMenu) {
				var msg = "You selected the menu item '" + selectedMenu.text() + "' on the value '" + invokedOn.text() + "'";
				alert(msg);
			}
			*/
		});
	</script>

@if (!App::environment('local'))
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.7.0/min/dropzone.min.js"></script>
@else
	<script type="text/javascript" src="{{ asset('external/dropzone/5.7.0/dropzone.min.js') }}"></script>
@endif

	<script src="{{ asset('external/imagebox/js/jquery.imagemodal.js') }}"></script>

	<script type="text/javascript" src="{{ asset('js/DataTableHelper.js') }}" ></script>

	<script type="text/javascript" src="{{ asset('js/HtmlTemplateHelper.js') }}" ></script>

@if ($controlSwitch['staff-template'])
	<script id="staff-table-template" type="text/x-custom-template">
		<tr id="staff-{id}">
			<td>{code}</td>
			<td>{name}</td>
			<td>{phone}</td>
			<td>{email}</td>
			<td>{search-key}</td>
			<td>
				<button data-condition="{can_view}" class='btn btn-info btn-xs' title="{{ trans('forms.View') }}" onclick="viewStaffInModal({id});"><i class="fa fa-eye" aria-hidden="true"></i></button>
				<button data-condition="{can_edit}" class='btn btn-info btn-xs' title="{{ trans('forms.Update') }}" onclick="updateStaffInModal({id});"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
				<button data-condition="{can_edit}" class='btn btn-info btn-xs' title="{{ trans('forms.Archive') }}" onclick="openUploadModal({id})"><i class="fa fa-files-o" aria-hidden="true"></i></button>
			</td>
		</tr>
	</script>
@endif

@if ($controlSwitch['staff-window'])
	<script type="text/javascript">
		$(document).ready(function() {
			$('#stafftable').DataTable(Object.assign(
				dataTableDefaultConfig('{{ trans('forms.Refresh/Reload') }}', 'GET', '/dashboard/staff/ajax', { }, '#stafftable', '#staff-table-template', '{{ trans('tool.Enter search keyword') }}', '', {{ env('DATATABLE_DEFAULT_ROWS', 10) }}),
				{
					order : [[ 0, 'asc' ]],
					columnDefs : [{ orderable : false, targets : 5 }, { visible : false, targets : 4 }]
				}
			)).on('preXhr.dt', function(e, settings) {
				showDataTableProcessingTransient('#stafftable');
			});
		} );
	</script>
@endif

	<script type="text/javascript">
		Dropzone.options.uploadedFiles = {
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
				});
			},
			uploadMultiple : true,
			url : "/hr/staff/0/archive",  // will be changed dynamically
			thumbnailWidth : 80,
			thumbnailHeight: 80,
			sending: function(file, xhr, formData) {
				formData.append("_token", "{{ csrf_token() }}");
			},
		}
	</script>

	<script type="text/javascript">
		// functions prepares archive modal
		function openUploadModal(userId) {
			$('div#fileUploaderModal div.modal-body input#staff_id').val(userId);
			Dropzone.forElement("#uploaded-files").removeAllFiles(true);			// clear out dropbox
			Dropzone.forElement("#uploaded-files").options.url = "/hr/staff/" + $('div#fileUploaderModal div.modal-body input#staff_id').val() + "/archive";
			$('div#fileUploaderModal').modal('show');
		}
	</script>

@if ($controlSwitch['staff-template'])
	<script type="text/javascript">
		function refreshTableWithStaff(staff, is_new) {
			let content = populateHtmlTemplateWithData($('#staff-table-template').html().toString(), staff);

			if (!is_new && $('#stafftable').DataTable().row('#staff-' + staff['id']).length) {
				$('#stafftable').DataTable().row('#staff-' + staff['id']).remove();
			}
			$('#stafftable').DataTable().row.add($(content)).draw();
		}
	</script>
@endif

@if ($controlSwitch['staff-modal'])
	<script type="text/javascript">
		function vueStaffDataSource() {
			// function that holds global variables
		}

		$(document).ready(function () {
			// button of 'update'
			vueStaffDataSource.button_update = "<i class=\"fa fa-btn fa-pencil-square-o\"></i>" + "{{ trans('forms.Update') }}";
			// text of 'view staff'
			vueStaffDataSource.text_view_staff = "{{ trans('hr.View staff information') }}";
			// text of 'update staff'
			vueStaffDataSource.text_update_staff = "{{ trans('hr.Update staff information') }}";
			// text to show 'download failed'
			vueStaffDataSource.text_attachment_download_failed = "{{ trans('messages.Attachment download failed') }}";
			// selection of country
			vueStaffDataSource.selection_country = {!! json_encode(\App\Helpers\CountryHelper::getAllCountryOptions()) !!};
			// callback for update
			vueStaffDataSource.updateCallback = function(staff) {
				refreshTableWithStaff(staff, false);
			};
			// callback for insert
			vueStaffDataSource.insertCallback = function(staff) {
				refreshTableWithStaff(staff, true);
			};
		});
	</script>
@endif

@if ($controlSwitch['staff-modal'])
	<script type="text/javascript" src="{{ asset('js/embedded_modal/staff_form.js') }}"></script>
@endif

@endsection
