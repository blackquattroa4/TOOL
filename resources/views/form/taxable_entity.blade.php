@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<div class="panel panel-default">
				<div class="panel-heading">{{ $source['title'] }}</div>
				<div class="panel-body">
					<form class="form-horizontal" role="form" method="POST" action="{{ $source['post_url'] }}">
						{{ csrf_field() }}

						<input type="hidden" id="type" name="type" value="{{ $source['type'] }}" />
						
						<div class="form-group{{ $errors->has('code') ? ' has-error' : '' }}">
							<label for="code" class="col-md-4 control-label">{{ trans('forms.Code') }}</label>

							<div class="col-md-6">
								<input id="code" type="text" class="form-control" name="code" value="{{ old('code') }}" {{ $read['code'] ? "readonly" : "" }}>

								@if ($errors->has('code'))
									<span class="help-block">
										<strong>{{ $errors->first('code') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
							<label for="name" class="col-md-4 control-label">{{ trans('forms.Name') }}</label>

							<div class="col-md-6">
								<input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}">

								@if ($errors->has('name'))
									<span class="help-block">
										<strong>{{ $errors->first('name') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('active') ? ' has-error' : '' }}">
							<label for="active" class="col-md-4 control-label">{{ trans('forms.Active') }}</label>

							<div class="col-md-1">
								<input id="active" type="checkbox" class="form-control" name="active" {{ old('active') ? "checked" : ""}}>

								@if ($errors->has('active'))
									<span class="help-block">
										<strong>{{ $errors->first('active') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('contact') ? ' has-error' : '' }}">
							<label for="contact" class="col-md-4 control-label">{{ trans('forms.Contact') }}</label>

							<div class="col-md-6">
								<input id="contact" type="contact" class="form-control" name="contact" placeholder="primary contact" value="{{ old('contact') }}">

								@if ($errors->has('contact'))
									<span class="help-block">
										<strong>{{ $errors->first('contact') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
							<label for="email" class="col-md-4 control-label">{{ trans('forms.E-mail address') }}</label>

							<div class="col-md-6">
								<input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}">

								@if ($errors->has('email'))
									<span class="help-block">
										<strong>{{ $errors->first('email') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('phone') ? ' has-error' : '' }}">
							<label for="phone" class="col-md-4 control-label">{{ trans('forms.Phone') }}</label>

							<div class="col-md-6">
								<input id="phone" type="phone" class="form-control" name="phone" value="{{ old('phone') }}">

								@if ($errors->has('phone'))
									<span class="help-block">
										<strong>{{ $errors->first('phone') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('bstreet') ? ' has-error' : '' }}">
							<label for="bstreet" class="col-md-4 control-label">{{ trans('forms.Billing address') }}</label>

							<div class="col-md-6">
								<input id="bstreet" type="street" class="form-control" name="bstreet" placeholder="street" value="{{ old('bstreet') }}">

								@if ($errors->has('bstreet'))
									<span class="help-block">
										<strong>{{ $errors->first('bstreet') }}</strong>
									</span>
								@endif
							</div>
							<div class="col-md-2">
								<input id="bunit" type="unit" class="form-control" name="bunit" placeholder="unit" value="{{ old('bunit') }}">

								@if ($errors->has('bunit'))
									<span class="help-block">
										<strong>{{ $errors->first('bunit') }}</strong>
									</span>
								@endif
							</div>
						</div>
						
						<div class="form-group{{ $errors->has('bdistrict') ? ' has-error' : '' }}">
							<label for="bdistrict" class="col-md-4 control-label"></label>

							<div class="col-md-6">
								<input id="bdistrict" type="district" class="form-control" name="bdistrict" placeholder="district" value="{{ old('bdistrict') }}">

								@if ($errors->has('bdistrict'))
									<span class="help-block">
										<strong>{{ $errors->first('bdistrict') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('bcity') ? ' has-error' : '' }}">
							<label for="bcity" class="col-md-4 control-label"></label>

							<div class="col-md-6">
								<input id="bcity" type="city" class="form-control" name="bcity" placeholder="city" value="{{ old('bcity') }}">

								@if ($errors->has('bcity'))
									<span class="help-block">
										<strong>{{ $errors->first('bcity') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('bstate') ? ' has-error' : '' }}">
							<label for="bstate" class="col-md-4 control-label"></label>

							<div class="col-md-6">
								<input id="bstate" type="state" class="form-control" name="bstate" placeholder="state" value="{{ old('bstate') }}">

								@if ($errors->has('bstate'))
									<span class="help-block">
										<strong>{{ $errors->first('bstate') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('bcountry') ? ' has-error' : '' }}">
							<label for="bcountry" class="col-md-4 control-label"></label>

							<div class="col-md-6">
								<select id="bcountry" type="country" class="form-control" name="bcountry" >
								@foreach ($country as $abbr => $fullname)
									<option value="{{ $abbr }}" {{ (old('bcountry') == $abbr) ? "selected" : ""}}>{{ $abbr }}&emsp;{{ $fullname }}</option>
								@endforeach
								</select>

								@if ($errors->has('bcountry'))
									<span class="help-block">
										<strong>{{ $errors->first('bcountry') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('bzipcode') ? ' has-error' : '' }}">
							<label for="bzipcode" class="col-md-4 control-label"></label>

							<div class="col-md-6">
								<input id="bzipcode" type="zipcode" class="form-control" name="bzipcode" placeholder="zipcode" value="{{ old('bzipcode') }}">

								@if ($errors->has('bzipcode'))
									<span class="help-block">
										<strong>{{ $errors->first('bzipcode') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('sstreet') ? ' has-error' : '' }}">
							<label for="sstreet" class="col-md-4 control-label">{{ trans('forms.Shipping address') }}</label>

							<div class="col-md-6">
								<input id="sstreet" type="street" class="form-control" name="sstreet" placeholder="street" value="{{ old('sstreet') }}">

								@if ($errors->has('sstreet'))
									<span class="help-block">
										<strong>{{ $errors->first('sstreet') }}</strong>
									</span>
								@endif
							</div>
							<div class="col-md-2">
								<input id="sunit" type="unit" class="form-control" name="sunit" placeholder="unit" value="{{ old('sunit') }}">

								@if ($errors->has('sunit'))
									<span class="help-block">
										<strong>{{ $errors->first('sunit') }}</strong>
									</span>
								@endif
							</div>
						</div>
						
						<div class="form-group{{ $errors->has('sdistrict') ? ' has-error' : '' }}">
							<label for="sdistrict" class="col-md-4 control-label"></label>

							<div class="col-md-6">
								<input id="sdistrict" type="district" class="form-control" name="sdistrict" placeholder="district" value="{{ old('sdistrict') }}">

								@if ($errors->has('sdistrict'))
									<span class="help-block">
										<strong>{{ $errors->first('sdistrict') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('scity') ? ' has-error' : '' }}">
							<label for="scity" class="col-md-4 control-label"></label>

							<div class="col-md-6">
								<input id="scity" type="city" class="form-control" name="scity" placeholder="city" value="{{ old('scity') }}">

								@if ($errors->has('scity'))
									<span class="help-block">
										<strong>{{ $errors->first('scity') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('sstate') ? ' has-error' : '' }}">
							<label for="sstate" class="col-md-4 control-label"></label>

							<div class="col-md-6">
								<input id="sstate" type="state" class="form-control" name="sstate" placeholder="state" value="{{ old('sstate') }}">

								@if ($errors->has('sstate'))
									<span class="help-block">
										<strong>{{ $errors->first('sstate') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('scountry') ? ' has-error' : '' }}">
							<label for="scountry" class="col-md-4 control-label"></label>

							<div class="col-md-6">
								<select id="scountry" type="country" class="form-control" name="scountry" >
								@foreach ($country as $abbr => $fullname)
									<option value="{{ $abbr }}" {{ (old('scountry') == $abbr) ? "selected" : ""}}>{{ $abbr }}&emsp;{{ $fullname }}</option>
								@endforeach
								</select>

								@if ($errors->has('scountry'))
									<span class="help-block">
										<strong>{{ $errors->first('scountry') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('szipcode') ? ' has-error' : '' }}">
							<label for="szipcode" class="col-md-4 control-label"></label>

							<div class="col-md-6">
								<input id="szipcode" type="zipcode" class="form-control" name="szipcode" placeholder="zipcode" value="{{ old('szipcode') }}">

								@if ($errors->has('szipcode'))
									<span class="help-block">
										<strong>{{ $errors->first('szipcode') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('payment') ? ' has-error' : '' }}">
							<label for="payment" class="col-md-4 control-label">{{ trans('forms.Payment') }}</label>

							<div class="col-md-6">
								<select id="payment" class="form-control" name="payment" >
								@foreach ($payment as $useless => $display)
									<option value="{{ $display['id'] }}" {{ (old('payment') == $display['id']) ? "selected" : ""}}>{{ $display['symbol'] }}&emsp;{{ $display['description'] }}</option>
								@endforeach
								</select>

								@if ($errors->has('currency'))
									<span class="help-block">
										<strong>{{ $errors->first('currency') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group{{ $errors->has('currency') ? ' has-error' : '' }}">
							<label for="currency" class="col-md-4 control-label">{{ trans('forms.Currency') }}</label>

							<div class="col-md-6">
								<select id="currency" class="form-control" name="currency" >
								@foreach ($currency as $useless => $display)
									<option value="{{ $display['id'] }}" {{ (old('currency') == $display['id']) ? "selected" : ""}}>{{ $display['symbol'] }}&emsp;{{ $display['description'] }}</option>
								@endforeach
								</select>

								@if ($errors->has('currency'))
									<span class="help-block">
										<strong>{{ $errors->first('currency') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group">
							<div class="col-md-6 col-md-offset-4">
								<button type="submit" class="btn btn-primary">
									<i class="fa fa-btn fa-floppy-o"></i> {{ $source['action'] }}
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
