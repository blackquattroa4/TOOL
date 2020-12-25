@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">

			<!-- document modal -->
		@if ($controlSwitch['document-modal'])
			@include(prioritizeCustomizedTemplateOverDefault('embedded_modal.document_form'))
		@endif

			<!-- document-delete modal -->
			<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" data-backdrop="static" aria-hidden="false">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
							<h4 class="modal-title" id="deleteModalLabel">{{ trans('document.Delete document') }}</h4>
						</div>
						<div class="modal-body">
							<h4>{{ trans('document.Please confirm deletion') }}</h4>
							<!-- <h4>{{ trans('document.Enter code to confirm') }}</h4> -->
							<!-- <img style="display:inline;" height="30px;" src="" /> -->
							<input type="hidden" id="documentDeleteId" name="documentDeleteId" value="" />
							<!-- <input type="text" class="form-control" id="deleteConfirmation" name="documentConfirmation" value="" /> -->
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans("forms.Cancel") }}</button>
							<button type="button" class="btn btn-primary" onclick="confirmDelete()">{{ trans("forms.Confirm") }}</button>
						</div>
					</div>
				</div>
			</div>

		@if ($controlSwitch['document-window'])
			<div id="docwindow" class="panel panel-default">
				<div class="panel-heading">
					<table width='100%'>
						<tr>
							<td><h4>{{ trans('document.Document') }}</h4></td>
							<td align='right'>
							@if ($controlSwitch['create-document-button'])
								<input type="button" class="btn btn-primary" value="{{ trans('document.New document') }}" onclick="createDocumentInModal();" />
							@endif
							</td>
						</tr>
					</table>
				</div>

				<div class="panel-body">
					<table id="doctable" class="table table-striped table-bordered" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th>{{ trans('document.Title') }}</th>
								<th>{{ trans('document.Description') }}</th>
								<th>{{ trans('document.Timestamp') }}</th>
								<th></th>
								<th></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>{{ trans('document.Title') }}</th>
								<th>{{ trans('document.Description') }}</th>
								<th>{{ trans('document.Timestamp') }}</th>
								<th></th>
								<th></th>
							</tr>
						</tfoot>
						<tbody>
							<!-- body generated by ajax; need an initial stuffer row  in order to get 'data-order' attribute active -->
							<tr>
								<td></td>
								<td></td>
								<td data-order="1970-01-01"></td>
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
	<li><a tabindex="-1" onclick="$('html,body').animate({scrollTop:$('#docwindow').offset().top}, 500);">{{ trans('document.Document') }}</a></li>
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

	<script type="text/javascript" src="{{ asset('js/DataTableHelper.js') }}" ></script>

	<script type="text/javascript" src="{{ asset('js/HtmlTemplateHelper.js') }}" ></script>

	<script id="document-template" type="text/x-custom-template">
		<tr id="document-{id}">
			<td>{title}  (v{version})</td>
			<td>{description}</td>
			<td data-order="{create_date}">{create_date_display}</td>
			<td>{search-key}</td>
			<td style="width:110px">
				<button data-condition="{can_view}" class='btn btn-info btn-xs' title="{{ trans('forms.Download') }}" onclick="window.location.href='/document/download/{id}';"><i class="fa fa-download" aria-hidden="true"></i></button>
				<button data-condition="{can_view}" class='btn btn-info btn-xs' title="{{ trans('forms.View') }}" onclick="viewDocumentInModal({id})"><i class="fa fa-eye" aria-hidden="true"></i></button>
				<button data-condition="{can_update}" class='btn btn-info btn-xs' title="{{ trans('forms.Update') }}" onclick="updateDocumentInModal({id})"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
				<button data-condition="{can_delete}" class='btn btn-info btn-xs' title="{{ trans('forms.Delete') }}" onclick="deleteDocumentInModal({id})"><i class="fa fa-times" aria-hidden="true"></i></button>
			</td>
		</tr>
	</script>

	<script type="text/javascript">
		$(document).ready(function() {
			$('#doctable').DataTable(Object.assign(
				dataTableDefaultConfig('{{ trans('forms.Refresh/Reload') }}', 'GET', '/dashboard/document/ajax', { }, '#doctable', '#document-template', '{{ trans('tool.Enter search keyword') }}', '', {{ env('DATATABLE_DEFAULT_ROWS', 10) }}),
				{
					order : [[ 2, 'desc' ]],
					columnDefs : [{ orderable : false, targets : 4 }, { visible : false, targets : 3 }]
				}
			)).on('preXhr.dt', function(e, settings) {
				showDataTableProcessingTransient('#doctable');
			});
		} );

		function refreshTableWithDocument(docs) {
			if ((docs['old'] != null) && $('#doctable').DataTable().row('#document-' + docs['old']['id']).length) {
				$('#doctable').DataTable().row('#document-' + docs['old']['id']).remove();
			}
			if (docs['new'] != null) {
				let content = populateHtmlTemplateWithData($("#document-template").html().toString(), docs['new']);
				$('#doctable').DataTable().row.add($(content));
			}
			$('#doctable').DataTable().draw();
		}

		function vueDocumentDataSource() {
			// function that holds global variables
		}

		$(document).ready(function() {
			// text to modal title
			vueDocumentDataSource.text_browse_file = "{{ trans('tool.Browse file') }}";
			vueDocumentDataSource.text_create_document = "{{ trans('document.New document') }}";
			vueDocumentDataSource.text_update_document = "{{ trans('document.Update document') }}";
			vueDocumentDataSource.text_view_document = "{{ trans('document.View document') }}";
			vueDocumentDataSource.text_delete_document = "{{ trans('document.Delete document') }}";
			// button of "Create"
			vueDocumentDataSource.button_create = "<i class=\"fa fa-btn fa-floppy-o\"></i>" + "{{ trans('forms.Create') }}";
			// button of "Update"
			vueDocumentDataSource.button_update = "<i class=\"fa fa-btn fa-pencil-square-o\"></i>" + "{{ trans('forms.Update') }}";
			// button of "Delete"
			vueDocumentDataSource.button_delete = "<i class=\"fa fa-btn fa-trash-o\"></i>" + "{{ trans('forms.Delete') }}";
			// callback function after insert/update/delete
			vueDocumentDataSource.refreshCallback = function (data) {
				// update customer table
				refreshTableWithDocument(data);
			}
		});

	</script>

	<script type="text/javascript" src="{{ asset('js/embedded_modal/document_form.js') }}" ></script>

@endsection
