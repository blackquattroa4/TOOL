@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
	    <div class="col-md-10 col-md-offset-1">
            <div id="rowwindow" class="panel panel-default">
                <div class="panel-heading">
					<table width='100%'>
						<tr>
							<td><h4>{{ trans('tool.Create new user') }}</h4></td>
							<td></td>
						</tr>
					</table>
				</div>

				<div class="panel-body">
					{!! Form::open(array('route' => 'user.store','method'=>'POST')) !!}
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-12">
							<div class="form-group{{ $errors->has('name') ? ' has-error' : ''}}">
								<strong>{{ trans('tool.Name') }}</strong>
								{!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
								@if ($errors->has('name'))
									<span class="help-block">
										<strong>{{ $errors->first('name') }}</strong>
									</span>
								@endif
							</div>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-12">
							<div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
								<strong>{{ trans('tool.Email') }}</strong>
								{!! Form::text('email', null, array('placeholder' => 'Email','class' => 'form-control')) !!}
								@if ($errors->has('email'))
									<span class="help-block">
										<strong>{{ $errors->first('email') }}</strong>
									</span>
								@endif
							</div>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-12">
							<div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
								<strong>{{ trans('tool.Password') }}</strong>
								{!! Form::password('password', array('placeholder' => 'Password','class' => 'form-control')) !!}
								@if ($errors->has('password'))
									<span class="help-block">
										<strong>{{ $errors->first('password') }}</strong>
									</span>
								@endif
							</div>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-12">
							<div class="form-group{{ $errors->has('confirm-password') ? ' has-error' : '' }}">
								<strong>{{ trans('tool.Confirm Password') }}</strong>
								{!! Form::password('confirm-password', array('placeholder' => 'Confirm Password','class' => 'form-control')) !!}
								@if ($errors->has('confirm-password'))
									<span class="help-block">
										<strong>{{ $errors->first('confirm-password') }}</strong>
									</span>
								@endif
							</div>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-12">
							<div class="form-group{{ $errors->has('roles') ? ' has-error' : '' }}">
								<strong>{{ trans('tool.Role') }}</strong>
								{!! Form::select('roles[]', $roles,[], array('class' => 'form-control','multiple')) !!}
								@if ($errors->has('roles'))
									<span class="help-block">
										<strong>{{ $errors->first('roles') }}</strong>
									</span>
								@endif
							</div>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-12 text-center">
							<button type="submit" class="btn btn-primary pull-right">{{ trans('forms.Submit') }}</button>
						</div>
					</div>
					{!! Form::close() !!}
				</div>
			</div>
	    </div>
	</div>
</div>

@endsection
