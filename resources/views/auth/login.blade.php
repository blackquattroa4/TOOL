@extends('layouts.app')

@section('content')
<br/><br/>
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">
					<table width="100%"><tr>
						<td>Login</td>
						<td style="text-align:right">
							<a href="{{ url('/register') }}">Register</a>
						</td>
					</tr></table>
				</div>

                <div class="panel-body">
                    <form class="form-horizontal" method="POST" action="{{ url('/login') }}">
                        {{ csrf_field() }}

						<p class="col-md-12" style="text-align:center">
							<font size="+3">
								<b>T</b>racker <b>O</b>f <b>O</b>rder & <b>L</b>ogistics</font>
						</p>
						<br/>

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email" class="col-md-4 control-label">E-Mail Address</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus>

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

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <div class="checkbox">
                                    <label class="col-md-7">
                                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Remember Me</label>
                                    <button class="btn btn-primary col-md-5" onclick="$(this).closest('form').attr('action', window.location.href); $(this).closest('form').submit();">
                                      <i class="fa fa-btn fa-sign-in"></i> Login</button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <a class="btn btn-link" href="{{ route('password.request') }}">Forgot Your Password?</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
