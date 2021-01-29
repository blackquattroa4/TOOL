@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
    <div class="col-md-10 col-md-offset-1">
			<!-- role form modal -->
		@if ($controlSwitch['role-modal'])
			@include(prioritizeCustomizedTemplateOverDefault('embedded_modal.role_form'))
		@endif

		@if (Auth::user()->can('sy-list'))
      <div id="rowwindow" class="panel panel-default">
        <div class="panel-heading">
					<table width='100%'>
						<tr>
							<td><h4>{{ trans('tool.Role Management') }}</h4></td>
							<td>
								<div class="pull-right">
								@permission('role-create')
									<a class="btn btn-primary" onclick="createRoleInModal()">{{ trans('tool.Create New Role') }}</a>
								@endpermission
								</div>
							</td>
						</tr>
					</table>
				</div>

        <div class="panel-body">
					<table class="table table-bordered" id="roletable">
						<tr>
							<th>{{ trans('tool.Name') }}</th>
							<th>{{ trans('tool.Description') }}</th>
							<th>{{ trans('tool.Action') }}</th>
						</tr>
					@foreach ($roles as $key => $role)
						<tr id="role-{{ $role->id }}">
							<td>{{ $role->display_name }}</td>
							<td>{{ $role->description }}</td>
							<td style="width:76px">
								<button class="btn btn-info btn-xs" onclick="viewRoleInModal({{ $role->id }})" title="{{ trans('forms.Show') }}">
									<i class="fa fa-eye" aria-hidden="true"></i>
								</button>
							@permission('role-edit')
								<button class="btn btn-info btn-xs" onclick="updateRoleInModal({{ $role->id }})" title="{{ trans('forms.Edit') }}">
									<i class="fa fa-pencil-square-o" aria-hidden="true"></i>
								</button>
							@endpermission
							</td>
						</tr>
					@endforeach
					</table>
					{!! $roles->render() !!}
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

@if ($controlSwitch['role-modal'])
	<script type="text/javascript">
		function vueRoleFormDataSource() {
			// function that holds data
		}

		$(document).ready(function() {
			// text to create roles
			vueRoleFormDataSource.text_create_role = "{{ trans('tool.Create New Role') }}";
			// text to update roles
			vueRoleFormDataSource.text_update_role = "{{ trans('tool.Edit Role') }}";
			// text to show roles
			vueRoleFormDataSource.text_view_role = "{{ trans('tool.Show Role') }}";
			// button to create
			vueRoleFormDataSource.button_create = "<i class=\"fa fa-btn fa-floppy-o\"></i>" + "{{ trans('forms.Create') }}";
			// button to update
			vueRoleFormDataSource.button_update = "<i class=\"fa fa-btn fa-pencil-square-o\"></i>" + "{{ trans('forms.Update') }}";
			// insert callback
			vueRoleFormDataSource.insertCallback = function(role) {
				$('<tr id="role-' + role.id + '"></tr>').insertAfter($('table#roletable tr:nth-child(1)'));
				$('table#roletable tr#role-' + role.id).append('<td>' + role.display + '</td>');
				$('table#roletable tr#role-' + role.id).append('<td>' + role.description + '</td>');
				$('table#roletable tr#role-' + role.id).append('<td>' + (role.can_view ? ("<a class=\"btn btn-info btn-xs\" onclick=\"viewRoleInModal(" + role.id + ")\" title=\"{{ trans('forms.Show') }}\"><span class=\"fa fa-eye\"></span></a>&nbsp;") : "") + (role.can_edit ? ("<a class=\"btn btn-info btn-xs\" onclick=\"updateRoleInModal(" + role.id + ")\" title=\"{{ trans('forms.Show') }}\"><span class=\"fa fa-pencil-square-o\"></span></a>&nbsp;") : "") + '</td>');
			};
			//update callback
			vueRoleFormDataSource.updateCallback = function(role) {
				$('table#roletable tr#role-' + role.id + ' td:nth-child(1)').html(role.display);
				$('table#roletable tr#role-' + role.id + ' td:nth-child(2)').html(role.description);
				$('table#roletable tr#role-' + role.id + ' td:nth-child(3)').html("");
				if (role.can_view) {
					$('table#roletable tr#role-' + role.id + ' td:nth-child(3)').append("<a class=\"btn btn-info btn-xs\" onclick=\"viewRoleInModal(" + role.id + ")\" title=\"{{ trans('forms.Show') }}\"><span class=\"fa fa-eye\"></span></a>&nbsp;");
				}
				if (role.can_edit) {
					$('table#roletable tr#role-' + role.id + ' td:nth-child(3)').append("<a class=\"btn btn-info btn-xs\" onclick=\"updateRoleInModal(" + role.id + ")\" title=\"{{ trans('forms.Show') }}\"><span class=\"fa fa-pencil-square-o\"></span></a>&nbsp;");
				}
			};
		});
	</script>
@endif

@if ($controlSwitch['role-modal'])
	<script type="text/javascript" src="{{ asset('js/embedded_modal/role_form.js') }}"></script>
@endif

@endsection
