@extends('layouts.app')

@section('additional-style')

@endsection

@section('content')
<div id="vue_container" class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">

			<div class="panel panel-default">
				<div class="panel-heading">
					<h4>{{ trans('forms.Inventory alert') }}</h4>
				</div>
				<div class="panel-body">
					<form class="form-horizontal" role="form" method="POST" action="">
						{{ csrf_field() }}

						<div v-for="(id, index) in form.line" class="form-group detail-line">

							<input id="line[]" type="hidden" name="line[]" v-bind:value="id" ></input>

							<div class="col-md-3 col-md-offset-1" v-bind:class="{ 'has-error' : 'unique_tradable.'+index in errors }">
								{{ trans('forms.Product') }}
								<select id="unique_tradable[]" class="form-control" name="unique_tradable[]" v-model="form.unique_tradable[index]">
                  <option value="0">{{ trans("forms.All products") }}</option>
                @foreach (\App\UniqueTradable::getActiveProducts('sku', 'asc') as $uniqueTradable)
                  <option value="{{ $uniqueTradable->id }}" >{{ $uniqueTradable->sku }}</option>
                @endforeach
                </select>
								<span v-if="'unique_tradable.'+index in errors" class="help-block">
									<strong>@{{ errors['unique_tradable.'+index][0] }}</strong>
								</span>
							</div>

              <div class="col-md-2" v-bind:class="{ 'has-error' : 'lower_limit.'+index in errors }">
								{{ trans('forms.Lower limit') }}
                <input id="lower_limit[]" type="number" class="form-control text-right" name="lower_limit[]" min="0" v-model="form.lower_limit[index]">
								<span v-if="'lower_limit.'+index in errors" class="help-block">
									<strong>@{{ errors['lower_limit.'+index][0] }}</strong>
								</span>
              </div>

              <div class="col-md-2" v-bind:class="{ 'has-error' : 'upper_limit.'+index in errors }">
								{{ trans('forms.Upper limit') }}
                <input id="upper_limit[]" type="number" class="form-control text-right" name="upper_limit[]" min="0" v-model="form.upper_limit[index]">
								<span v-if="'upper_limit.'+index in errors" class="help-block">
									<strong>@{{ errors['upper_limit.'+index][0] }}</strong>
								</span>
              </div>

              <div class="col-md-1 col-md-offset-2" v-bind:class="{ 'has-error' : 'valid.'+index in errors }">
								{{ trans('status.Valid') }}
								<input id="valid[]" type="hidden" name="valid[]" v-model="form.valid[index]" >
								<input id="_valid[]" type="checkbox" class="form-control" name="_valid[]" v-model="form.valid[index]" >
								<span v-if="'valid.'+index in errors" class="help-block">
									<strong>@{{ errors['valid.'+index][0] }}</strong>
								</span>
							</div>

              <div class="col-md-3 col-md-offset-2" v-bind:class="{ 'has-error' : 'location.'+index in errors }">
								{{ trans('forms.Location') }}
								<select id="location[]" class="form-control" name="location[]" v-model="form.location[index]">
                  <option value="0">{{ trans("forms.All locations") }}</option>
                @foreach (\App\Location::getActiveWarehouses('name', 'asc') as $location)
                  <option value="{{ $location->id }}" >{{ $location->name }}</option>
                @endforeach
                </select>
								<span v-if="'location.'+index in errors" class="help-block">
									<strong>@{{ errors['location.'+index][0] }}</strong>
								</span>
							</div>

						</div>

						<div class="form-group">
							<div class="col-md-2">
								<button type="button" class="btn btn-info" onclick="return addNewLine();" >+&nbsp;{{ trans('forms.Add line') }}</button>
							</div>
							<div class="col-md-2 col-md-offset-8">
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

@section('post-content')
	<script type="text/javascript">

		var vueContainer = null;

		function addNewLine() {
			vueContainer.form.line.push(0);
			vueContainer.form.unique_tradable.push(0);
			vueContainer.form.location.push(0);
			vueContainer.form.lower_limit.push(0);
			vueContainer.form.upper_limit.push(1);
			vueContainer.form.valid.push(1);
		}

		$(document).ready(function() {
			vueContainer = new Vue({
				el : '#vue_container',
			@php
				$oldInput = session()->getOldInput();
				array_walk($oldInput['valid'], function(&$element, $index) {
					$element = in_array($element, [1, true, "1", "true"], true);
				});
			@endphp
				data : {
					form : {
						line : {!! json_encode($oldInput['line']) !!},
						unique_tradable : {!! json_encode($oldInput['unique_tradable']) !!},
						location : {!! json_encode($oldInput['location']) !!},
						lower_limit : {!! json_encode($oldInput['lower_limit']) !!},
						upper_limit : {!! json_encode($oldInput['upper_limit']) !!},
						valid : {!! json_encode($oldInput['valid']) !!},
					},
					errors : {!! json_encode($errors->toArray()) !!}
				}
			});
		});
	</script>
@endsection
