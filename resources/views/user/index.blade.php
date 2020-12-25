@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
    <div class="col-md-10 col-md-offset-1">
			<!-- user form modal -->
		@if ($controlSwitch['user-modal'])
			@include(prioritizeCustomizedTemplateOverDefault('embedded_modal.user_form'))
		@endif

		@if (Auth::user()->can('user-list'))
      <div id="rowwindow" class="panel panel-default">
        <div class="panel-heading">
					<table width='100%'>
						<tr>
							<td><h4>{{ trans('tool.Users Management') }}</h4></td>
							<td>
								<div class="pull-right">
								@if (Auth::user()->can('user-create'))
									<a class="btn btn-primary" onclick="createUserInModal()">{{ trans('tool.Create new user') }}</a>
								@endif
								</div>
							</td>
						</tr>
					</table>
				</div>

				<div class="panel-body">
					<table class="table table-bordered" id="usertable">
						<tr>
							<th>{{ trans('tool.Name') }}</th>
							<th>{{ trans('tool.Email') }}</th>
							<th>{{ trans('tool.Roles') }}</th>
							<th>{{ trans('tool.Action') }}</th>
						</tr>
					@foreach ($data as $key => $user)
						<tr id="user-{{ $user->id }}">
							<td>{{ $user->name }}</td>
							<td>{{ $user->email }}</td>
							<td>
						@if(!empty($user->roles))
							@foreach($user->roles as $v)
								<label style="margin-right:3px;" class="label label-success">{{ $v->display_name }}</label>
							@endforeach
						@endif
							</td>
							<td style="width:70px">
							@if (Auth::user()->can('user-view'))
								<button class="btn btn-info btn-xs" onclick="viewUserInModal({{ $user->id }})" title="{{ trans('forms.Show') }}">
									<i class="fa fa-eye" aria-hidden="true"></i>
								</button>
							@endif
							@if (Auth::user()->can('user-edit'))
								<button class="btn btn-info btn-xs" onclick="updateUserInModal({{ $user->id }})" title="{{ trans('forms.Edit') }}">
									<i class="fa fa-pencil-square-o"></i>
								</button>
							@endif
							</td>
						</tr>
					@endforeach
					</table>
					{!! $data->render() !!}
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

	<script type="text/javascript">
	@if ($controlSwitch['user-modal'])
		function vueUserFormDataSource() {
			// function that holds data
		}

		$(document).ready(function() {
			// text to create user
			vueUserFormDataSource.text_create_user = "{{ trans('tool.Create new user') }}";
			// text to update user
			vueUserFormDataSource.text_update_user = "{{ trans('tool.Edit user') }}";
			// text to show user
			vueUserFormDataSource.text_view_user = "{{ trans('tool.Show user') }}";
			// button to create
			vueUserFormDataSource.button_create = "<i class=\"fa fa-btn fa-floppy-o\"></i>" + "{{ trans('forms.Create') }}";
			// button to update
			vueUserFormDataSource.button_update = "<i class=\"fa fa-btn fa-pencil-square-o\"></i>" + "{{ trans('forms.Update') }}";
			// insert callback
			vueUserFormDataSource.insertCallback = function(user) {
				$('<tr id="user-' + user.id + '"></tr>').insertAfter($('table#usertable tr:nth-child(1)'));
				$('table#usertable tr#user-' + user.id).append('<td>' + user.name + '</td>');
				$('table#usertable tr#user-' + user.id).append('<td>' + user.email + '</td>');
				$('table#usertable tr#user-' + user.id).append('<td>' + user.roles.map(x => '<label class="label label-success">' + x + '</label>').join('&nbsp;') + '</td>');
				$('table#usertable tr#user-' + user.id).append('<td>' + (user.can_view ? ("<a class=\"btn btn-info btn-xs\" onclick=\"viewUserInModal(" + user.id + ")\" title=\"{{ trans('forms.Show') }}\"><span class=\"fa fa-eye\"></span></a>&nbsp;") : "") + (user.can_edit ? ("<a class=\"btn btn-info btn-xs\" onclick=\"updateUserInModal(" + user.id + ")\" title=\"{{ trans('forms.Show') }}\"><span class=\"fa fa-pencil-square-o\"></span></a>&nbsp;") : "") + '</td>');
			}
			//update callback
			vueUserFormDataSource.updateCallback = function(user) {
				$('table#usertable tr#user-' + user.id + ' td:nth-child(1)').html(user.name);
				$('table#usertable tr#user-' + user.id + ' td:nth-child(2)').html(user.email);
				$('table#usertable tr#user-' + user.id + ' td:nth-child(3)').html(user.roles.map(x => '<label class="label label-success">' + x + '</label>').join('&nbsp;'));
				$('table#usertable tr#user-' + user.id + ' td:nth-child(4)').html("");
				if (user.can_view) {
					$('table#usertable tr#user-' + user.id + ' td:nth-child(4)').append("<a class=\"btn btn-info btn-xs\" onclick=\"viewUserInModal(" + user.id + ")\" title=\"{{ trans('forms.Show') }}\"><span class=\"fa fa-eye\"></span></a>&nbsp;");
				}
				if (user.can_edit) {
					$('table#usertable tr#user-' + user.id + ' td:nth-child(4)').append("<a class=\"btn btn-info btn-xs\" onclick=\"updateUserInModal(" + user.id + ")\" title=\"{{ trans('forms.Show') }}\"><span class=\"fa fa-pencil-square-o\"></span></a>&nbsp;");
				}
			};
		});
	@endif
	</script>

	@if ($controlSwitch['user-modal'])
		<script type="text/javascript" src="{{ asset('js/embedded_modal/user_form.js') }}"></script>
	@endif

@endsection
