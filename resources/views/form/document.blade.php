@extends('layouts.app')

@section('additional-style')
<style>
	label.form-check-label {
		vertical-align: top;
		margin-top: 9px;
	}

	table.permission-table {
		width: 100%;
	}
</style>
@endsection

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<div class="panel panel-default">
				<div class="panel-heading">{{ $source['title'] }}</div>
				<div class="panel-body">
					<form class="form-horizontal" role="form" enctype="multipart/form-data" method="POST" action="{{ $source['post_url'] }}">
						{{ csrf_field() }}

						<div class="form-group">
							<label for="title" class="col-md-3 control-label">{{ trans('document.Title') }}</label>

							<div class="col-md-7{{ $errors->has('title') ? ' has-error' : '' }}">
							@if ($readonly)
								<input id="title" type="text" class="form-control" name="title" value="{{ old('reference') }}" readonly>
							@else
								<input id="title" type="text" class="form-control" name="title" value="{{ old('reference') }}" >
							@endif

								@if ($errors->has('title'))
									<span class="help-block">
										<strong>{{ $errors->first('title') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group">
							<label for="description" class="col-md-3 control-label">{{ trans('document.Description') }}</label>

							<div class="col-md-7{{ $errors->has('description') ? ' has-error' : '' }}">
							@if ($readonly)
								<textarea id="description" col="50" type="text" class="form-control" name="description" disabled>{{ old('description') }}</textarea>
							@else
								<textarea id="description" col="50" type="text" class="form-control" name="description">{{ old('description') }}</textarea>
							@endif

								@if ($errors->has('description'))
									<span class="help-block">
										<strong>{{ $errors->first('description') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group">
							<label for="thefile" class="col-md-3 control-label">{{ trans('document.File') }}</label>

							<div class="col-md-7{{ $errors->has('thefile') ? ' has-error' : '' }}">
								<label class="btn btn-info" for="thefile">
								@if ($readonly)
									<a href="{{ url('/document/download/'.old('id')) }}">
										<span id="download-button[{{ old('id') }}]">{{ old('filename') }}</span>
									</a>
								@else
									<!-- assuming value='C:\fakepath\filename' -->
									<input id="thefile" name="thefile" type="file" style="display:none;" onchange="$('#upload-selector-label').html( ($(this).val().substring($(this).val().lastIndexOf( '\\' ) + 1)) );" />
									<span id="upload-selector-label" >{{ trans('tool.Browse file') }}</span>
								@endif
								</label>

							@if ($errors->has('thefile'))
								<span class="help-block">
									<strong>{{ $errors->first('thefile') }}</strong>
								</span>
							@endif
							</div>
						</div>

						<div class="form-group">
							<label for="permissions" class="col-md-3 control-label">{{ trans('document.Permissions') }}</label>
							<div class="col-md-7{{ $errors->has('permissions') ? ' has-error' : '' }}">
								<p>
									<a id="permission_display" class="btn btn-info" data-toggle="collapse" href="#permissionDiv" role="button" aria-expanded="false" aria-controls="collapseExample">{{ trans('forms.Show') }}&emsp;<span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span></a>
								</p>
								<div class="collapse" id="permissionDiv">
									<div class="card card-body">
										<div class="panel panel-default">
											<div class="panel-heading">
												{{ trans('messages.Role') }}
											</div>
											<div class="panel-body">
												<table class="permission-table">
													<th>
														<td></td>
														<td>{{ trans('forms.Read') }}</td>
														<td>{{ trans('forms.Update') }}</td>
														<td>{{ trans('forms.Delete') }}</td>
													</th>
												@foreach ($roles as $idx => $name)
													<tr class="form-check">
														<td>
															<label class="form-check-label" for="permission['role'][{{ $idx }}]">{{ $name }}</label>
														</td>
														<td>&emsp;
														</td>
														<td>
															<input class="form-check-input" style="width:30px;height:30px;" type="checkbox" title="{{ trans('forms.Read') }}" id="permission[roles][{{ $idx }}][read]" name="permission[roles][{{ $idx }}][read]" {{ old('permission.roles.'.$idx.'.read') ? ' checked' : '' }}{{ $readonly ? ' disabled' : '' }}>
														</td>
														<td>
															<input class="form-check-input" style="width:30px;height:30px;" type="checkbox" title="{{ trans('forms.Update') }}" id="permission[roles][{{ $idx }}][update]" name="permission[roles][{{ $idx }}][update]" {{ old('permission.roles.'.$idx.'.update') ? ' checked' : '' }}{{ $readonly ? ' disabled' : ''}}>
														</td>
														<td>
															<input class="form-check-input" style="width:30px;height:30px;" type="checkbox" title="{{ trans('forms.Delete') }}" id="permission[roles][{{ $idx }}][delete]" name="permission[roles][{{ $idx }}][delete]" {{ old('permission.roles.'.$idx.'.delete') ? ' checked' : '' }}{{ $readonly ? ' disabled' : ''}}>
														</td>
													</tr>
												@endforeach
												</table>
											</div>
										</div>

										<div class="panel panel-default">
											<div class="panel-heading">
												{{ trans('messages.User') }}
											</div>
											<div class="panel-body">
												<table class="permission-table">
													<th>
														<td></td>
														<td>{{ trans('forms.Read') }}</td>
														<td>{{ trans('forms.Update') }}</td>
														<td>{{ trans('forms.Delete') }}</td>
													</th>
												@foreach ($users as $idx => $name)
													<tr class="form-check">
														<td>
															<label class="form-check-label" for="permission['role'][{{ $idx }}]">{{ $name }}</label>
														</td>
														<td>&emsp;
														</td>
														<td>
															<input class="form-check-input" style="width:30px;height:30px;" type="checkbox" title="{{ trans('forms.Read') }}" id="permission[users][{{ $idx }}][read]" name="permission[users][{{ $idx }}][read]" {{ old('permission.users.'.$idx.'.read') ? ' checked' : '' }} {{ ($readonly || ($idx == $creator_id)) ? ' disabled' : '' }}>
														</td>
														<td>
															<input class="form-check-input" style="width:30px;height:30px;" type="checkbox" title="{{ trans('forms.Update') }}" id="permission[users][{{ $idx }}][update]" name="permission[users][{{ $idx }}][update]" {{ old('permission.users.'.$idx.'.update') ? ' checked' : '' }} {{ ($readonly || ($idx == $creator_id)) ? ' disabled' : '' }}>
														</td>
														<td>
															<input class="form-check-input" style="width:30px;height:30px;" type="checkbox" title="{{ trans('forms.Delete') }}" id="permission[users][{{ $idx }}][delete]" name="permission[users][{{ $idx }}][delete]" {{ old('permission.users.'.$idx.'.delete') ? ' checked' : '' }} {{ ($readonly || ($idx == $creator_id)) ? ' disabled' : '' }}>
														</td>
													</tr>
												@endforeach
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="form-group">
						@if (is_array($source['action']))
							<div class="col-md-{{ 10-2*count($source['action']) }} col-md-offset-{{ 2*count($source['action']) }}">
							</div>
							@foreach ($source['action'] as $keyAction => $oneAction)
							<div class="col-md-2">
								<button type="submit" id="submit" name="submit" class="btn btn-primary" value="{{ $keyAction }}">
									<i class="fa fa-btn fa-floppy-o"></i> {{ $oneAction }}
								</button>
							</div>
							@endforeach
						@else
							<div class="col-md-2 col-md-offset-8">
								<button type="submit" class="btn btn-primary">
									<i class="fa fa-btn fa-floppy-o"></i> {{ $source['action'] }}
								</button>
							</div>
						@endif
						</div>
					</form>
				</div>
			</div>

		@if ($readonly)
			<div class="panel panel-default">
				<div class="panel-heading">{{ trans('document.Past versions') }}</div>
				<div class="panel-body">
				@if ($pastVersions->count() > 0)
					<table id="doctable" class="table table-striped table-bordered" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th>{{ trans('document.Title') }}</th>
								<th>{{ trans('document.Description') }}</th>
								<th>{{ trans('document.Timestamp') }}</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>{{ trans('document.Title') }}</th>
								<th>{{ trans('document.Description') }}</th>
								<th>{{ trans('document.Timestamp') }}</th>
							</tr>
						</tfoot>
						<tbody>
						@foreach ($pastVersions as $version)
								<tr>
									<td><a href="{{ url('/document/download/'.$version['id']) }}">{{ $version['title'] }}</a>&emsp;(v{{ $version['version'] }})</td>
									<td>{!! str_replace("\n", '<br>', $version['notes']) !!}</td>
									<td>{{ \App\Helpers\DateHelper::dbToGuiDate($version['created_at']) }} {{ $version['created_at']->format('H:iA') }}</td>
								</tr>
						@endforeach
						</tbody>
					</table>
				@else
					<p>{{ trans('document.No previous version') }}</p>
				@endif
				</div>
			</div>
		@endif
		</div>
	@if (count($history) > 0)
		<div class="col-md-8 col-md-offset-2">
			<div class="panel panel-default">
				<div class="panel-heading">{{ trans('document.History') }}</div>
				<div class="panel-body">
				</div>
			</div>
		</div>
	@endif
	</div>
</div>
@endsection

@section('post-content')
	<script type="text/javascript">
		$('#permissionDiv').on('hidden.bs.collapse', function () {
			$('#permission_display').html('{{ trans('forms.Show') }}&emsp;<span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span>');
		});

		$('#permissionDiv').on('show.bs.collapse', function () {
			$('#permission_display').html('{{ trans('forms.Hide') }}&emsp;<span class="glyphicon glyphicon-triangle-top" aria-hidden="true"></span>');
		});
	</script>
@endsection
