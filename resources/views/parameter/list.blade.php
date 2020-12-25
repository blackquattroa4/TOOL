@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">

			<!-- parameter modal -->
		@if ($controlSwitch['parameter-modal'])
			@include(prioritizeCustomizedTemplateOverDefault('embedded_modal.parameter_form'))
		@endif

		@if ($controlSwitch['parameter-window'])
			<div id="rowwindow" class="panel panel-default">
				<div class="panel-heading">
					<table width='100%'>
						<tr>
							<td><h4>{{ trans('tool.System Parameter Management') }}</h4></td>
							<td>
								<div class="pull-right">
								@if ($controlSwitch['create-parameter-button'])
									<a class="btn btn-success" onclick="createParameterInModal()">{{ trans('tool.Create New Parameter') }}</a>
								@endif
								</div>
							</td>
						</tr>
					</table>
				</div>

				<div class="panel-body">
					<table id="parametertable" class="table table-bordered">
						<thead>
							<tr>
								<th>{{ trans('forms.Key') }}</th>
								<th>{{ trans('forms.Value') }}</th>
								<th>{{ trans('tool.Action') }}</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td></td>
								<td></td>
								<td></td>
							</tr>
						</tbody>
						<tfoot>
							<tr>
								<th>{{ trans('forms.Key') }}</th>
								<th>{{ trans('forms.Value') }}</th>
								<th>{{ trans('tool.Action') }}</th>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		@endif
		</div>
	</div>
</div>
@endsection

@section('post-content')

	<script type="text/javascript" src="{{ asset('js/DataTableHelper.js') }}" ></script>

	<script type="text/javascript" src="{{ asset('js/HtmlTemplateHelper.js') }}" ></script>

@if ($controlSwitch['parameter-window'])
	<script id="parameter-template" type="text/x-custom-template">
		<tr id="parameter-{id}">
			<td>{key}</td>
			<td>{value}</td>
			<td style="width:30px;">
				<button data-condition="{can_edit}" class="btn btn-info btn-xs" onclick="updateParameterInModal({id})" title="{{ trans('forms.Update') }}">
					<i class="fa fa-pencil-square-o" aria-hidden="true"></i>
				</button>
			</td>
		</tr>
	</script>
@endif

@if ($controlSwitch['parameter-table'])
	<script type="text/javascript">
		$(document).ready(function() {
			$('#parametertable').DataTable(Object.assign(
				dataTableDefaultConfig('{{ trans('forms.Refresh/Reload') }}', 'GET', '/system/parameter/ajax', { }, '#parametertable', '#parameter-template', '{{ trans('tool.Enter search keyword') }}', '', {{ env('DATATABLE_DEFAULT_ROWS', 10) }}),
				{
					order : [[ 1, 'asc' ]],
					columnDefs : [{ orderable : false, targets : 1 }, { orderable : false, targets : 2 }]
				}
			)).on('preXhr.dt', function(e, settings) {
				showDataTableProcessingTransient('#parametertable');
			});
		} );
	</script>
@endif

@if ($controlSwitch['parameter-modal'])
	<script type="text/javascript">
		function refreshTableWithParameter(parameter, is_new) {
			let content = populateHtmlTemplateWithData($('#parameter-template').html().toString(), parameter);

			if (!is_new && $('#parametertable').DataTable().row('#parameter-' + parameter['id']).length) {
				$('#parametertable').DataTable().row('#parameter-' + parameter['id']).remove();
			}
			$('#parametertable').DataTable().row.add($(content)).draw();
		}
	</script>
@endif

@if ($controlSwitch['parameter-modal'])
	<script type="text/javascript">
		function vueParameterDataSource() {
			// function that holds global variables
		}

		$(document).ready(function () {
			// title of 'create parameter'
			vueParameterDataSource.text_create_parameter = '{{ trans('messages.Create parameter') }}';
			// title of 'update parameter'
			vueParameterDataSource.text_update_parameter = '{{ trans('messages.Edit parameter') }}';
			// literal of 'create' button
			vueParameterDataSource.button_create = "<i class=\"fa fa-btn fa-floppy-o\"></i>" + "{{ trans('forms.Create') }}";
			// literal of 'update' button
			vueParameterDataSource.button_update = "<i class=\"fa fa-btn fa-pencil-square-o\"></i>" + "{{ trans('forms.Update') }}";
			// callback function for insert
			vueParameterDataSource.insertCallback = function (parameter) {
				refreshTableWithParameter(parameter, true);
			};
			// callback function for update
			vueParameterDataSource.updateCallback = function (parameter) {
				refreshTableWithParameter(parameter, false);
			};
		})
	</script>
@endif

@if ($controlSwitch['parameter-modal'])
	<script type="text/javascript" src="{{ asset('js/embedded_modal/parameter_form.js') }}"></script>
@endif

@endsection
