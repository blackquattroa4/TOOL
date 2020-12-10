@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<div class="panel panel-default">
				<div class="panel-heading">{{ trans('messages.Update profile') }}</div>
				<div class="panel-body">
					<form class="form-horizontal" role="form" method="POST" action="{{ url('/profile/update') }}">
						{{ csrf_field() }}

						<div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
							<label for="name" class="col-md-4 control-label">{{ trans('messages.Name') }}</label>

							<div class="col-md-6">
								<input id="name" type="text" class="form-control" name="name" value="{{ $user['name'] }}">

								@if ($errors->has('name'))
									<span class="help-block">
										<strong>{{ $errors->first('name') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
							<label for="email" class="col-md-4 control-label">{{ trans('messages.E-mail address') }}</label>

							<div class="col-md-6">
								<input id="email" type="text" class="form-control" name="email" value="{{ $user['email'] }}">

								@if ($errors->has('email'))
									<span class="help-block">
										<strong>{{ $errors->first('email') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('email_pswd') ? ' has-error' : '' }}">
							<label for="email_pswd" class="col-md-4 control-label">{{ trans('messages.E-mail password') }}</label>

							<div class="col-md-6">
								<input id="email_pswd" type="text" class="form-control" name="email_pswd" value="{{ $user['email_password'] }}">

								@if ($errors->has('email_pswd'))
									<span class="help-block">
										<strong>{{ $errors->first('email_pswd') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="well">
							<legend>&emsp;{{ trans('messages.Incoming') }}</legend>
							<div class="form-group{{ $errors->has('imap_server') ? ' has-error' : '' }}">
								<label for="imap_server" class="col-md-4 control-label">{{ trans('messages.E-mail server') }}</label>

								<div class="col-md-6">
									<input id="imap_server" type="text" class="form-control" name="imap_server" value="{{ $user['imap_server'] }}">

									@if ($errors->has('imap_server'))
										<span class="help-block">
											<strong>{{ $errors->first('imap_server') }}</strong>
										</span>
									@endif
								</div>
							</div>

							<div class="form-group{{ $errors->has('imap_port') ? ' has-error' : '' }}">
								<label for="imap_port" class="col-md-4 control-label">{{ trans('messages.E-mail port') }}</label>

								<div class="col-md-6">
									<input id="imap_port" type="number" class="form-control" min="1" step="1" max="65535" name="imap_port" value="{{ $user['imap_port'] }}">

									@if ($errors->has('imap_port'))
										<span class="help-block">
											<strong>{{ $errors->first('imap_port') }}</strong>
										</span>
									@endif
								</div>
							</div>

							<div class="form-group{{ $errors->has('imap_protocol') ? ' has-error' : '' }}">
								<label for="imap_protocol" class="col-md-4 control-label">{{ trans('messages.E-mail protocol') }}</label>

								<div class="col-md-6">
									<select id="imap_protocol" class="form-control" name="imap_protocol" >
									@foreach (["IMAP" => "imap", "IMAP2" => "imap2", "IMAP2 BIS" => "imap2bis", "IMAP4" => "imap4", "IMAP4 V1" => "imap4rev1", "POP3" => "pop3", "NNTP" => "nntp"] as $display => $val)
										<option value="{{ $val }}" {{ ($user['imap_protocol']==$val) ? "selected" : "" }}>{{ $display }}</option>
									@endforeach
									</select>

									@if ($errors->has('imap_protocol'))
										<span class="help-block">
											<strong>{{ $errors->first('imap_protocol') }}</strong>
										</span>
									@endif
								</div>
							</div>

							<div class="form-group{{ $errors->has('imap_encryption') ? ' has-error' : '' }}">
								<label for="imap_encryption" class="col-md-4 control-label">{{ trans('messages.E-mail encryption') }}</label>

								<div class="col-md-6">
									<select id="imap_encryption" class="form-control" name="imap_encryption" >
									@foreach (["SSL" => "ssl", "TLS" => "tls", "no TLS" => "notls"] as $display => $val)
										<option value="{{ $val }}" {{ ($user['imap_encryption']==$val) ? "selected" : "" }}>{{ $display }}</option>
									@endforeach
									</select>

									@if ($errors->has('imap_encryption'))
										<span class="help-block">
											<strong>{{ $errors->first('imap_encryption') }}</strong>
										</span>
									@endif
								</div>
							</div>
						</div>

						<div class="well">
							<legend>&emsp;{{ trans('messages.Outgoing') }}</legend>
							<div class="form-group{{ $errors->has('smtp_server') ? ' has-error' : '' }}">
								<label for="smtp_server" class="col-md-4 control-label">{{ trans('messages.E-mail server') }}</label>

								<div class="col-md-6">
									<input id="smtp_server" type="text" class="form-control" name="smtp_server" value="{{ $user['smtp_server'] }}">

									@if ($errors->has('smtp_server'))
										<span class="help-block">
											<strong>{{ $errors->first('smtp_server') }}</strong>
										</span>
									@endif
								</div>
							</div>

							<div class="form-group{{ $errors->has('smtp_port') ? ' has-error' : '' }}">
								<label for="smtp_port" class="col-md-4 control-label">{{ trans('messages.E-mail port') }}</label>

								<div class="col-md-6">
									<input id="smtp_port" type="number" class="form-control" min="1" step="1" max="65535" name="smtp_port" value="{{ $user['smtp_port'] }}">

									@if ($errors->has('smtp_port'))
										<span class="help-block">
											<strong>{{ $errors->first('smtp_port') }}</strong>
										</span>
									@endif
								</div>
							</div>

							<div class="form-group{{ $errors->has('smtp_protocol') ? ' has-error' : '' }}">
								<label for="smtp_protocol" class="col-md-4 control-label">{{ trans('messages.E-mail protocol') }}</label>

								<div class="col-md-6">
									<select id="smtp_protocol" class="form-control" name="smtp_protocol" >
									@foreach (["SMTP" => "smtp"] as $display => $val)
										<option value="{{ $val }}" {{ ($user['smtp_protocol']==$val) ? "selected" : "" }}>{{ $display }}</option>
									@endforeach
									</select>

									@if ($errors->has('smtp_protocol'))
										<span class="help-block">
											<strong>{{ $errors->first('smtp_protocol') }}</strong>
										</span>
									@endif
								</div>
							</div>

							<div class="form-group{{ $errors->has('smtp_encryption') ? ' has-error' : '' }}">
								<label for="smtp_encryption" class="col-md-4 control-label">{{ trans('messages.E-mail encryption') }}</label>

								<div class="col-md-6">
									<select id="smtp_encryption" class="form-control" name="smtp_encryption" >
									@foreach (["SSL" => "ssl", "TLS" => "tls", "no TLS" => "notls"] as $display => $val)
										<option value="{{ $val }}" {{ ($user['smtp_encryption']==$val) ? "selected" : "" }}>{{ $display }}</option>
									@endforeach
									</select>

									@if ($errors->has('smtp_encryption'))
										<span class="help-block">
											<strong>{{ $errors->first('smtp_encryption') }}</strong>
										</span>
									@endif
								</div>
							</div>
						</div>

						<div class="form-group{{ $errors->has('phone') ? ' has-error' : '' }}">
							<label for="email" class="col-md-4 control-label">{{ trans('messages.Contact phone') }}</label>

							<div class="col-md-6">
								<input id="phone" type="phone" class="form-control" name="phone" value="{{ $user['phone'] }}">

								@if ($errors->has('phone'))
									<span class="help-block">
										<strong>{{ $errors->first('phone') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('street') ? ' has-error' : '' }}">
							<label for="street" class="col-md-4 control-label">{{ trans('messages.Street address') }}</label>

							<div class="col-md-6">
								<input id="street" type="street" class="form-control" name="street" value="{{ $user['street'] }}">

								@if ($errors->has('street'))
									<span class="help-block">
										<strong>{{ $errors->first('street') }}</strong>
									</span>
								@endif
							</div>
							<div class="col-md-2">
								<input id="unit" type="unit" class="form-control" name="unit" value="{{ $user['unit'] }}">

								@if ($errors->has('unit'))
									<span class="help-block">
										<strong>{{ $errors->first('unit') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('city') ? ' has-error' : '' }}">
							<label for="city" class="col-md-4 control-label">{{ trans('messages.City') }}</label>

							<div class="col-md-6">
								<input id="city" type="city" class="form-control" name="city" value="{{ $user['city'] }}">

								@if ($errors->has('city'))
									<span class="help-block">
										<strong>{{ $errors->first('city') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('district') ? ' has-error' : '' }}">
							<label for="phone" class="col-md-4 control-label">{{ trans('messages.District') }}</label>

							<div class="col-md-6">
								<input id="district" type="district" class="form-control" name="district" value="{{ $user['district'] }}">

								@if ($errors->has('district'))
									<span class="help-block">
										<strong>{{ $errors->first('district') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('state') ? ' has-error' : '' }}">
							<label for="city" class="col-md-4 control-label">{{ trans('messages.State') }}</label>

							<div class="col-md-6">
								<input id="state" type="state" class="form-control" name="state" value="{{ $user['state'] }}">

								@if ($errors->has('state'))
									<span class="help-block">
										<strong>{{ $errors->first('state') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('country') ? ' has-error' : '' }}">
							<label for="country" class="col-md-4 control-label">{{ trans('messages.Country') }}</label>

							<div class="col-md-6">
								<select id="country" type="country" class="form-control" name="country" >
								@foreach ($country as $abbr => $fullname)
									<option value="{{ $abbr }}" {{ ($user['country'] == $abbr) ? "selected" : ""}}>{{ $abbr }}&emsp;{{ $fullname }}</option>
								@endforeach
								</select>

								@if ($errors->has('country'))
									<span class="help-block">
										<strong>{{ $errors->first('country') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('zipcode') ? ' has-error' : '' }}">
							<label for="zipcode" class="col-md-4 control-label">{{ trans('messages.Zipcode') }}</label>

							<div class="col-md-6">
								<input id="zipcode" type="zipcode" class="form-control" name="zipcode" value="{{ $user['zipcode'] }}">

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
									<i class="fa fa-btn fa-floppy-o"></i> {{ trans('forms.Update') }}
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
