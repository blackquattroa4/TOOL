@extends('layouts.app')
 
@section('content')
<div class="container">
	<div class="row">
	    <div class="col-md-10 col-md-offset-1">
            <div id="rowwindow" class="panel panel-default">
                <div class="panel-heading">
					<table width='100%'>
						<tr>
							<td><h4>Create New User</h4></td>
							<td></td>
						</tr>
					</table>
				</div>
				
				<div class="panel-body">
					{!! Form::open(array('route' => 'users.store','method'=>'POST')) !!}
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-12">
							<div class="form-group">
								<strong>Name:</strong>
								{!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
							</div>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-12">
							<div class="form-group">
								<strong>Email:</strong>
								{!! Form::text('email', null, array('placeholder' => 'Email','class' => 'form-control')) !!}
							</div>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-12">
							<div class="form-group">
								<strong>Password:</strong>
								{!! Form::password('password', array('placeholder' => 'Password','class' => 'form-control')) !!}
							</div>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-12">
							<div class="form-group">
								<strong>Confirm Password:</strong>
								{!! Form::password('confirm-password', array('placeholder' => 'Confirm Password','class' => 'form-control')) !!}
							</div>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-12">
							<div class="form-group">
								<strong>Role:</strong>
								{!! Form::select('roles[]', $roles,[], array('class' => 'form-control','multiple')) !!}
							</div>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-12 text-center">
							<button type="submit" class="btn btn-primary pull-right">Submit</button>
						</div>
					</div>
					{!! Form::close() !!}
				</div>
			</div>
	    </div>
	</div>
</div>

@endsection
