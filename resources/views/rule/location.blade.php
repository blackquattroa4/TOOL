@extends('layouts.app')

@section('content')
<div id="vue_container" class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">

			<div class="panel panel-default">
				<div class="panel-heading">
					<h4>{{ trans('forms.Location') }}</h4>
				</div>
				<div class="panel-body">
					<form class="form-horizontal" role="form" method="POST" action="">
						{{ csrf_field() }}

						<div v-for="(value, index) in form.locations.id" class="form-group detail-line">

							<input id="id[]" type="hidden" name="id[]" v-model="form.locations.id[index]" ></input>

							<div class="col-md-3 col-md-offset-1" v-bind:class="{ 'has-error' : 'name.'+index in errors }">
								{{ trans('forms.Name') }}
                <input type="text" id="name[]" name="name[]" class="form-control" v-model="form.locations.name[index]" ></input>
                <span v-if="'name.'+index in errors" class="help-block">
                  <strong>@{{ errors['name.'+index][0] }}</strong>
                </span>
							</div>

              <div class="col-md-2" v-bind:class="{ 'has-error' : 'type.'+index in errors }">
								{{ trans('forms.Type') }}
                <select id="type[]" style="text-align:right;" class="form-control" name="type[]" v-model="form.locations.type[index]">
									<option v-for="type in types" v-bind:value="type.key" >@{{ type.value }}</option>
								</select>
                <span v-if="'type.'+index in errors" class="help-block">
                  <strong>@{{ errors['type.'+index][0] }}</strong>
                </span>
							</div>

							<div class="col-md-3" v-bind:class="{ 'has-error' : 'contact_id.'+index in errors }">
								{{ trans('forms.Contact') }}
                <select id="contact_id[]" style="text-align:right;" class="form-control" name="contact_id[]" v-model="form.locations.contact_id[index]">
									<option v-for="employee in employees" v-bind:value="employee.id" >@{{ employee.name }}</option>
								</select>
                <span v-if="'contact_id.'+index in errors" class="help-block">
                  <strong>@{{ errors['contact_id.'+index][0] }}</strong>
                </span>
							</div>

              <div class="col-md-1 col-md-offset-1" v-bind:class="{ 'has-error' : 'active.'+index in errors }">
								{{ trans('status.Active') }}
								<input type="hidden" id="active[]" name="active[]" v-model="form.locations.active[index]"></input>
                <input type="checkbox" id="_active[]" name="_active[]" class="form-control" v-model="form.locations.active[index]" ></input>
                <span v-if="'active.'+index in errors" class="help-block">
                  <strong>@{{ errors['active.'+index][0] }}</strong>
                </span>
							</div>

              <div class="clearfix"></div>

              <div class="col-md-offset-1" v-bind:class="{ 'col-md-7' : form.locations.address_id[index] != 0, 'col-md-2' : form.locations.address_id[index] == 0, 'has-error' : 'address_id.'+index in errors }">
                {{ trans('forms.Shipping address') }}
                <select id="address_id[]" style="text-align:right;" class="form-control" name="address_id[]" v-model="form.locations.address_id[index]">
									<option value="0">{{ trans('forms.New address') }}</option>
									<option v-for="address in addresses" v-bind:value="address.id" >@{{ address.street + ", " + address.city + " " + address.state + " " + address.zipcode + " " + address.country }}</option>
								</select>
                <span v-if="'address_id.'+index in errors" class="help-block">
                  <strong>@{{ errors['address_id.'+index][0] }}</strong>
                </span>
              </div>

							<div class="col-md-4 " v-bind:class="{ 'hidden' : form.locations.address_id[index] != 0, 'has-error' : 'street.'+index in errors }">
                {{ trans('messages.Street address') }}
								<input type="text" id="street[]" name="street[]" class="form-control" v-model="form.locations.street[index]"></input>
								<span v-if="'street.'+index in errors" class="help-block">
                  <strong>@{{ errors['street.'+index][0] }}</strong>
                </span>
							</div>

							<div class="col-md-1 " v-bind:class="{ 'hidden' : form.locations.address_id[index] != 0, 'has-error' : 'unit.'+index in errors }">
                {{ trans('messages.Unit') }}
								<input type="text" id="unit[]" name="unit[]" class="form-control" v-model="form.locations.unit[index]"></input>
								<span v-if="'unit.'+index in errors" class="help-block">
                  <strong>@{{ errors['unit.'+index][0] }}</strong>
                </span>
							</div>

							<div class="col-md-2 " v-bind:class="{ 'hidden' : form.locations.address_id[index] != 0, 'has-error' : 'district.'+index in errors }">
                {{ trans('messages.District') }}
								<input type="text" id="district[]" name="district[]" class="form-control" v-model="form.locations.district[index]"></input>
								<span v-if="'district.'+index in errors" class="help-block">
                  <strong>@{{ errors['district.'+index][0] }}</strong>
                </span>
							</div>

							<div class="clearfix"></div>

							<div class="col-md-3 col-md-offset-1" v-bind:class="{ 'hidden' : form.locations.address_id[index] != 0, 'has-error' : 'city.'+index in errors }">
                {{ trans('messages.City') }}
								<input type="text" id="city[]" name="city[]" class="form-control" v-model="form.locations.city[index]"></input>
								<span v-if="'city.'+index in errors" class="help-block">
                  <strong>@{{ errors['city.'+index][0] }}</strong>
                </span>
							</div>

							<div class="col-md-2 " v-bind:class="{ 'hidden' : form.locations.address_id[index] != 0, 'has-error' : 'state.'+index in errors }">
                {{ trans('messages.State') }}
								<input type="text" id="state[]" name="state[]" class="form-control" v-model="form.locations.state[index]"></input>
								<span v-if="'state.'+index in errors" class="help-block">
                  <strong>@{{ errors['state.'+index][0] }}</strong>
                </span>
							</div>

							<div class="col-md-2 " v-bind:class="{ 'hidden' : form.locations.address_id[index] != 0, 'has-error' : 'country.'+index in errors }">
                {{ trans('messages.Country') }}
								<select id="country[]" name="country[]" class="form-control" v-model="form.locations.country[index]">
								@foreach (\App\Helpers\CountryHelper::getAllCountryOptions() as $value => $display)
									<option value="{{ $value }}">{{ $display }}</option>
								@endforeach
								</select>
								<span v-if="'country.'+index in errors" class="help-block">
                  <strong>@{{ errors['country.'+index][0] }}</strong>
                </span>
							</div>

							<div class="col-md-2 " v-bind:class="{ 'hidden' : form.locations.address_id[index] != 0, 'has-error' : 'zipcode.'+index in errors }">
                {{ trans('messages.Zipcode') }}
								<input type="text" id="zipcode[]" name="zipcode[]" class="form-control" v-model="form.locations.zipcode[index]"></input>
								<span v-if="'zipcode.'+index in errors" class="help-block">
                  <strong>@{{ errors['zipcode.'+index][0] }}</strong>
                </span>
							</div>

						</div>

					@if (!$readonly)
						<div class="form-group">
							<div class="col-md-2">
								<button type="button" class="btn btn-info" onclick="addNewLine();">+&nbsp;{{ trans("forms.Add") }}</button>
							</div>
							<div class="col-md-2 col-md-offset-8">
								<button type="submit" class="btn btn-primary">
									<i class="fa fa-btn fa-floppy-o"></i> {{ trans('forms.Update') }}
								</button>
							</div>
						</div>
					@endif

					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('post-content')

	<script type="text/javascript">

    var vueContainer = null;

		function addNewLine()
		{
			vueContainer.form.locations.id.push(0);
			vueContainer.form.locations.name.push('');
			vueContainer.form.locations.type.push('{{ $types[0]['key'] }}');
			vueContainer.form.locations.contact_id.push('{{ $employees->pluck('id')[0] }}');
			vueContainer.form.locations.address_id.push(0);
			vueContainer.form.locations.active.push(1);
			vueContainer.form.locations.street.push('');
			vueContainer.form.locations.unit.push('');
			vueContainer.form.locations.district.push('');
			vueContainer.form.locations.city.push('');
			vueContainer.form.locations.state.push('');
			vueContainer.form.locations.country.push('{{ array_keys(\App\Helpers\CountryHelper::getAllCountryOptions())[0] }}');
			vueContainer.form.locations.zipcode.push('');
		}

		$(document).ready(function() {

      vueContainer = new Vue({
				 el: '#vue_container',
			 @php
			 	 // transform data for Vue.
			 	 $oldInput = session()->getOldInput();
				 array_walk($oldInput['active'], function(&$element, $index) {
					 $element = in_array($element, [1, true, "1", "true"], true);
				 });
			 @endphp
				 data: {
					 	form : {
              locations : {
								id : {!! json_encode($oldInput['id']) !!},
								name : {!! json_encode($oldInput['name']) !!},
								type : {!! json_encode($oldInput['type']) !!},
								contact_id : {!! json_encode($oldInput['contact_id']) !!},
								address_id : {!! json_encode($oldInput['address_id']) !!},
								active : {!! json_encode($oldInput['active']) !!},
								street : {!! json_encode($oldInput['street']) !!},
								unit : {!! json_encode($oldInput['unit']) !!},
								district : {!! json_encode($oldInput['district']) !!},
								city : {!! json_encode($oldInput['city']) !!},
								state : {!! json_encode($oldInput['state']) !!},
								country : {!! json_encode($oldInput['country']) !!},
								zipcode : {!! json_encode($oldInput['zipcode']) !!},
							},
            },
            types : {!! json_encode($types) !!},
            employees : {!! json_encode($employees) !!},
						addresses : {!! json_encode($addresses) !!},
						errors : {!! json_encode($errors->toArray()) !!},
				 },
				 // mounted: function() {
				 // }
			});
		});
	</script>
@endsection
