

<div class="modal fade" id="embeddedTaxableEntityModal" tabindex="-1" role="dialog" aria-labelledby="embeddedTaxableEntityModalLabel" data-backdrop="static" data-keyboard="false" aria-hidden="false">

	<!-- the form -->
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <font size="4" style="padding-right:30px;">@{{ modal.title }}</font>
        <span v-if="'general' in errors" v-bind:class="{ 'text-danger' : 'general' in errors }" >
          <strong>@{{ errors['general'][0] }}</strong>
        </span>
      </div>
      <div style="height: 70vh; overflow-y: scroll;" class="modal-body">

				<form class="form-horizontal" role="form" method="POST" >

					<input type="hidden" name="_token" v-model="modal.csrf">

					<input type="hidden" id="type" name="type" v-bind:value="form.type" />

					<div class="form-group" v-bind:class="{ 'has-error' : 'code' in errors }">
						<label for="code" class="col-md-4 control-label">{{ trans('forms.Code') }}</label>

						<div class="col-md-6">
							<input id="code" type="text" class="form-control" name="code" v-model="form.code" v-bind:readonly="form.id > 0">
							<span v-if="'code' in errors" class="help-block">
								<strong>@{{ errors['code'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'name' in errors }">
						<label for="name" class="col-md-4 control-label">{{ trans('forms.Name') }}</label>

						<div class="col-md-6">
							<input id="name" type="text" class="form-control" name="name" v-model="form.name" v-bind:readonly="modal.readonly">
							<span v-if="'name' in errors" class="help-block">
								<strong>@{{ errors['name'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'active' in errors }">
						<label for="active" class="col-md-4 control-label">{{ trans('forms.Active') }}</label>

						<div class="col-md-1">
							<input id="active" type="hidden" name="active" v-model="form.active">
							<input id="_active" type="checkbox" class="form-control" name="_active" v-model="form.active" v-bind:disabled="modal.readonly">
							<span v-if="'active' in errors" class="help-block">
								<strong>@{{ errors['active'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'contact' in errors }">
						<label for="contact" class="col-md-4 control-label">{{ trans('forms.Contact') }}</label>

						<div class="col-md-6">
							<input id="contact" type="contact" class="form-control" name="contact" placeholder="primary contact" v-model="form.contact" v-bind:readonly="modal.readonly">
							<span v-if="'contact' in errors" class="help-block">
								<strong>@{{ errors['contact'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'email' in errors }">
						<label for="email" class="col-md-4 control-label">{{ trans('forms.E-mail address') }}</label>

						<div class="col-md-6">
							<input id="email" type="email" class="form-control" name="email" v-model="form.email" v-bind:readonly="modal.readonly">
							<span v-if="'email' in errors" class="help-block">
								<strong>@{{ errors['email'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'phone' in errors }">
						<label for="phone" class="col-md-4 control-label">{{ trans('forms.Phone') }}</label>

						<div class="col-md-6">
							<input id="phone" type="phone" class="form-control" name="phone" v-model="form.phone" v-bind:readonly="modal.readonly">
							<span v-if="'phone' in errors" class="help-block">
								<strong>@{{ errors['phone'][0] }}</strong>
							</span>
  					</div>
  				</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'bstreet' in errors }">
						<label for="bstreet" class="col-md-4 control-label">{{ trans('forms.Billing address') }}</label>

						<div class="col-md-6">
							<input id="bstreet" type="street" class="form-control" name="bstreet" placeholder="street" v-model="form.bstreet" v-bind:readonly="modal.readonly">
							<span v-if="'bstreet' in errors" class="help-block">
								<strong>@{{ errors['bstreet'][0] }}</strong>
							</span>
						</div>

						<div class="col-md-2">
							<input id="bunit" type="unit" class="form-control" name="bunit" placeholder="unit" v-model="form.bunit" v-bind:readonly="modal.readonly">
							<span v-if="'bunit' in errors" class="help-block">
								<strong>@{{ errors['bunit'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'bdistrict' in errors }">
						<label for="bdistrict" class="col-md-4 control-label"></label>

						<div class="col-md-6">
							<input id="bdistrict" type="district" class="form-control" name="bdistrict" placeholder="district" v-model="form.bdistrict" v-bind:readonly="modal.readonly">
							<span v-if="'bdistrict' in errors" class="help-block">
								<strong>@{{ errors['bdistrict'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'bcity' in errors }">
						<label for="bcity" class="col-md-4 control-label"></label>

						<div class="col-md-6">
							<input id="bcity" type="city" class="form-control" name="bcity" placeholder="city" v-model="form.bcity" v-bind:readonly="modal.readonly">
							<span v-if="'bcity' in errors" class="help-block">
								<strong>@{{ errors['bcity'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'bstate' in errors }">
						<label for="bstate" class="col-md-4 control-label"></label>

						<div class="col-md-6">
							<input id="bstate" type="state" class="form-control" name="bstate" placeholder="state" v-model="form.bstate" v-bind:readonly="modal.readonly">
							<span v-if="'bstate' in errors" class="help-block">
								<strong>@{{ errors['bstate'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'bcountry' in errors }">
						<label for="bcountry" class="col-md-4 control-label"></label>

						<div class="col-md-6">
							<select id="bcountry" type="country" class="form-control" name="bcountry" v-model="form.bcountry" v-bind:readonly="modal.readonly" v-bind:disabled="modal.readonly">
								<option v-for="(display, index) in modal.country" v-bind:value="index" >@{{ index }}&emsp;@{{ display }}</option>
							</select>
							<span v-if="'bcountry' in errors" class="help-block">
								<strong>@{{ errors['bcountry'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'bzipcode' in errors }">
						<label for="bzipcode" class="col-md-4 control-label"></label>

						<div class="col-md-6">
							<input id="bzipcode" type="zipcode" class="form-control" name="bzipcode" placeholder="zipcode" v-model="form.bzipcode" v-bind:readonly="modal.readonly">
							<span v-if="'bzipcode' in errors" class="help-block">
								<strong>@{{ errors['bzipcode'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'sstreet' in errors }">
						<label for="sstreet" class="col-md-4 control-label">{{ trans('forms.Shipping address') }}</label>

						<div class="col-md-6">
							<input id="sstreet" type="street" class="form-control" name="sstreet" placeholder="street" v-model="form.sstreet" v-bind:readonly="modal.readonly">
							<span v-if="'sstreet' in errors" class="help-block">
								<strong>@{{ errors['sstreet'][0] }}</strong>
							</span>
						</div>
						<div class="col-md-2">
							<input id="sunit" type="unit" class="form-control" name="sunit" placeholder="unit" v-model="form.sunit" v-bind:readonly="modal.readonly">
							<span v-if="'sunit' in errors" class="help-block">
								<strong>@{{ errors['sunit'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'sdistrict' in errors }">
						<label for="sdistrict" class="col-md-4 control-label"></label>

						<div class="col-md-6">
							<input id="sdistrict" type="district" class="form-control" name="sdistrict" placeholder="district" v-model="form.sdistrict" v-bind:readonly="modal.readonly">
							<span v-if="'sdistrict' in errors" class="help-block">
								<strong>@{{ errors['sdistrict'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'scity' in errors }">
						<label for="scity" class="col-md-4 control-label"></label>

						<div class="col-md-6">
							<input id="scity" type="city" class="form-control" name="scity" placeholder="city" v-model="form.scity" v-bind:readonly="modal.readonly">
							<span v-if="'scity' in errors" class="help-block">
								<strong>@{{ errors['scity'][0] }}</strong>
							</span>
						</div>
					</div>

		      <div class="form-group" v-bind:class="{ 'has-error' : 'sstate' in errors }">
						<label for="sstate" class="col-md-4 control-label"></label>

						<div class="col-md-6">
							<input id="sstate" type="state" class="form-control" name="sstate" placeholder="state" v-model="form.sstate" v-bind:readonly="modal.readonly">
							<span v-if="'sstate' in errors" class="help-block">
								<strong>@{{ errors['sstate'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group" v-bind:class="{ 'has-error' : 'scountry' in errors }">
						<label for="scountry" class="col-md-4 control-label"></label>

						<div class="col-md-6">
							<select id="scountry" type="country" class="form-control" name="scountry" v-model="form.scountry" v-bind:readonly="modal.readonly" v-bind:disabled="modal.readonly">
								<option v-for="(display, index) in modal.country" v-bind:value="index" >@{{ index }}&emsp;@{{ display }}</option>
							</select>
							<span v-if="'scountry' in errors" class="help-block">
								<strong>@{{ errors['scountry'][0] }}</strong>
							</span>
						</div>
					</div>

		      <div class="form-group" v-bind:class="{ 'has-error' : 'szipcode' in errors }">
						<label for="szipcode" class="col-md-4 control-label"></label>

						<div class="col-md-6">
							<input id="szipcode" type="zipcode" class="form-control" name="szipcode" placeholder="zipcode" v-model="form.szipcode" v-bind:readonly="modal.readonly">
							<span v-if="'szipcode' in errors" class="help-block">
								<strong>@{{ errors['szipcode'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group" v-bind:model="{ 'has-error' : 'payment' in errors }">
						<label for="payment" class="col-md-4 control-label">{{ trans('forms.Payment') }}</label>

						<div class="col-md-6">
							<select id="payment" class="form-control" name="payment" v-model="form.payment" v-bind:readonly="modal.readonly" v-bind:disabled="modal.readonly">
								<option v-for="(display, index) in modal.payment" v-bind:value="index" >@{{ display.symbol }}&emsp;@{{ display.description }}</option>
							</select>
							<span v-if="'payment' in errors" class="help-block">
								<strong>@{{ errors['payment'][0] }}</strong>
							</span>
						</div>
					</div>

					<div class="form-group{{ $errors->has('currency') ? ' has-error' : '' }}">
            <label for="currency" class="col-md-4 control-label">{{ trans('forms.Currency') }}</label>

						<div class="col-md-6">
							<select id="currency" class="form-control" name="currency" v-model="form.currency" v-bind:readonly="modal.readonly" v-bind:disabled="modal.readonly">
								<option v-for="(display, index) in modal.currency" v-bind:value="index" >@{{ display.symbol }}&emsp;@{{ display.description }}</option>
							</select>
							<span v-if="'currency' in errors" class="help-block">
								<strong>@{{ errors['currency'][0] }}</strong>
							</span>
						</div>
					</div>

				</form>
			</div>

      <div v-if="Object.keys(modal.action).length > 0" class="modal-footer">
				<div class="form-group">
					<div class="col-md-12">
						<button v-for="(callback, display) in modal.action" type="button" class="btn btn-primary pull-right" style="margin-left:3px;" v-on:click="window[callback]()" v-html="display">
						</button>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>