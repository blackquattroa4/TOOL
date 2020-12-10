@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
	    <div class="col-md-10 col-md-offset-1">
            <div id="rowwindow" class="panel panel-default">
                <div class="panel-heading">
					<table width='100%'>
						<tr>
							<td><h4>{{ trans('tool.Create New Role') }}</h4></td>
							<td></td>
						</tr>
					</table>
				</div>

				<div class="panel-body">
					{!! Form::open(array('route' => 'role.store','method'=>'POST')) !!}
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-12">
							<div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
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
							<div class="form-group{{ $errors->has('display_name') ? ' has-error' : ''}}">
								<strong>{{ trans('tool.Display Name') }}</strong>
								{!! Form::text('display_name', null, array('placeholder' => 'Display Name','class' => 'form-control')) !!}
								@if ($errors->has('display_name'))
									<span class="help-block">
										<strong>{{ $errors->first('display_name') }}</strong>
									</span>
								@endif
							</div>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-12">
							<div class="form-group{{ $errors->has('description') ? ' has-error' : '' }}">
								<strong>{{ trans('tool.Description') }}</strong>
								{!! Form::textarea('description', null, array('placeholder' => 'Description','class' => 'form-control','style'=>'height:100px')) !!}
								@if ($errors->has('description'))
									<span class="help-block">
										<strong>{{ $errors->first('description') }}</strong>
									</span>
								@endif
							</div>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-12">
							<div class="form-group{{ $errors->has('permission') ? ' has-error' : '' }}">
								<strong>{{ trans('tool.Permission') }}</strong>
								<br/>
								@foreach($permission as $value)
								<div class="col-md-12">
									{{ Form::checkbox('permission[]', $value->id, false, array('class' => 'name', 'style' => 'width:25px;height:25px;')) }}
									<span style="padding-left:5px;vertical-align:super;font-size:16px;">{{ $value->display_name }}</span>
								</div>
								@endforeach
								@if ($errors->has('permission'))
									<span class="help-block">
										<strong>{{ $errors->first('permission') }}</strong>
									</span>
								@endif
							</div>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-12">
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
