@extends('layouts.app')

@section('content')
<br/><br/>
<div class="container">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<div class="panel panel-default">
				<div class="panel-heading">
					<table width="100%"><tr>
						<td>Register</td>
						<td style="text-align:right"><a href="{{ route('login') }}">Login</a></td>
					</tr></table>
				</div>

				<div class="panel-body">
					<form class="form-horizontal" method="POST" action="{{ route('register') }}">
						{{ csrf_field() }}

						<div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
							<label for="name" class="col-md-4 control-label">Name</label>

							<div class="col-md-6">
								<input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" required autofocus>

								@if ($errors->has('name'))
									<span class="help-block">
										<strong>{{ $errors->first('name') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
							<label for="email" class="col-md-4 control-label">E-Mail Address</label>

							<div class="col-md-6">
								<input id="email" type="email" class="form-control" name="email" placeholder="this will be used at login" value="{{ old('email') }}" required>

								@if ($errors->has('email'))
									<span class="help-block">
										<strong>{{ $errors->first('email') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
							<label for="password" class="col-md-4 control-label">Password</label>

							<div class="col-md-6">
								<input id="password" type="password" class="form-control" name="password" required>

								@if ($errors->has('password'))
									<span class="help-block">
										<strong>{{ $errors->first('password') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
							<label for="password-confirm" class="col-md-4 control-label">Confirm Password</label>

							<div class="col-md-6">
								<input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>

								@if ($errors->has('password_confirmation'))
									<span class="help-block">
										<strong>{{ $errors->first('password_confirmation') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('phone') ? ' has-error' : '' }}">
							<label for="phone" class="col-md-4 control-label">Phone</label>

							<div class="col-md-6">
								<input id="phone" type="phone" class="form-control" name="phone" value="{{ old('phone') }}">

								@if ($errors->has('phone'))
									<span class="help-block">
										<strong>{{ $errors->first('phone') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('street') ? ' has-error' : '' }}">
							<label for="phone" class="col-md-4 control-label">Address</label>

							<div class="col-md-6">
								<input id="street" type="street" class="form-control" name="street" placeholder="street" value="{{ old('street') }}">

								@if ($errors->has('street'))
									<span class="help-block">
										<strong>{{ $errors->first('street') }}</strong>
									</span>
								@endif
							</div>
							<div class="col-md-2">
								<input id="unit" type="unit" class="form-control" name="unit" placeholder="unit" value="{{ old('unit') }}">

								@if ($errors->has('unit'))
									<span class="help-block">
										<strong>{{ $errors->first('unit') }}</strong>
									</span>
								@endif
							</div>
						</div>
						
						<div class="form-group{{ $errors->has('district') ? ' has-error' : '' }}">
							<label for="phone" class="col-md-4 control-label"></label>

							<div class="col-md-6">
								<input id="district" type="district" class="form-control" name="district" placeholder="district" value="{{ old('district') }}">

								@if ($errors->has('district'))
									<span class="help-block">
										<strong>{{ $errors->first('district') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('city') ? ' has-error' : '' }}">
							<label for="phone" class="col-md-4 control-label"></label>

							<div class="col-md-6">
								<input id="city" type="city" class="form-control" name="city" placeholder="city" value="{{ old('city') }}">

								@if ($errors->has('city'))
									<span class="help-block">
										<strong>{{ $errors->first('city') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('state') ? ' has-error' : '' }}">
							<label for="phone" class="col-md-4 control-label"></label>

							<div class="col-md-6">
								<input id="state" type="state" class="form-control" name="state" placeholder="state" value="{{ old('state') }}">

								@if ($errors->has('state'))
									<span class="help-block">
										<strong>{{ $errors->first('state') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('country') ? ' has-error' : '' }}">
							<label for="country" class="col-md-4 control-label"></label>

							<div class="col-md-6">
								<select id="country" type="country" class="form-control" name="country" >
									<option value="CA" {{ (old('country') == 'CA') ? "selected" : ""}}>Canada</option>
									<option value="CN" {{ (old('country') == 'CN') ? "selected" : ""}}>China</option>
									<option value="MX" {{ (old('country') == 'MX') ? "selected" : ""}}>Mexico</option>
									<option value="TW" {{ (old('country') == 'TW') ? "selected" : ""}}>Taiwan</option>
									<option value="US" {{ (old('country') == 'US') ? "selected" : ""}}>United States</option>
								</select>

								@if ($errors->has('country'))
									<span class="help-block">
										<strong>{{ $errors->first('country') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('zipcode') ? ' has-error' : '' }}">
							<label for="zipcode" class="col-md-4 control-label"></label>

							<div class="col-md-6">
								<input id="zipcode" type="zipcode" class="form-control" name="zipcode" placeholder="zipcode" value="{{ old('zipcode') }}">

								@if ($errors->has('zipcode'))
									<span class="help-block">
										<strong>{{ $errors->first('zipcode') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group">
							<div class="col-md-6 col-md-offset-4">
								<button type="submit" class="btn btn-primary">
									<i class="fa fa-btn fa-user"></i> Register
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
