@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
	    <div class="col-md-10 col-md-offset-1">
            <div id="rowwindow" class="panel panel-default">
                <div class="panel-heading">
					<table width='100%'>
						<tr>
							<td><h4>{{ trans('tool.Show user') }}</h4></td>
							<td></td>
						</tr>
					</table>
				</div>

				<div class="panel-body">
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-12">
							<div class="form-group">
								<strong>{{ trans('tool.Name') }}</strong>
								{{ $user->name }}
							</div>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-12">
							<div class="form-group">
								<strong>{{ trans('tool.Email') }}</strong>
								{{ $user->email }}
							</div>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-12">
							<div class="form-group">
								<strong>{{ trans('tool.Roles') }}</strong>
						@if(!empty($user->roles))
							@foreach($user->roles as $v)
								<label class="label label-success">{{ $v->display_name }}</label>
							@endforeach
						@endif
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@endsection
