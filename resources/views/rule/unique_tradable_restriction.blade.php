@extends('layouts.app')

@section('additional-style')
@endsection

@section('content')
<div id="vue_container" class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">

			<div class="panel panel-default">
				<div class="panel-heading">
					<h4>{{ trans('forms.Sales restriction') }}</h4>
				</div>
				<div class="panel-body">
					<form class="form-horizontal" role="form" method="POST" action="">
						{{ csrf_field() }}

						<div v-for="(id, index) in form.line" class="form-group detail-line">

							<input id="line[]" type="hidden" name="line[]" v-model="id" ></input>

							<div class="col-md-4 col-md-offset-1" v-bind:class="{ 'has-error' : 'entity.'+index in errors }">
								{{ trans('forms.Entity') }}
								<select id="entity[]" class="form-control" name="entity[]" v-model="form.entity[index]" {{ $readonly ? "disabled" : "" }}>
									<option value="0" >{{ trans('forms.All') }}</option>
								@foreach ($customers as $customer)
									<option value="{{ $customer->id }}" >{{ $customer->name }}&emsp;({{ $customer->code }})</option>
								@endforeach
								</select>
								<span v-if="'entity.'+index in errors" class="help-block">
									<strong>@{{ errors['entity.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-2" v-bind:class="{ 'has-error' : 'filter.'+index in errors }">
								{{ trans('forms.Filter') }}<br>
								<select id="filter[]" name="filter[]" class="form-control" v-model="form.filter[index]">
								@foreach (['include' => trans('forms.Include'), 'exclude' => trans('forms.Exclude') ] as $value => $display)
									<option value="{{ $value }}">{{ $display }}</option>
								@endforeach
								</select>
								<span v-if="'filter.'+index in errors" class="help-block">
									<strong>@{{ errors['filter.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-3" v-bind:class="{ 'has-error' : 'unique_tradable.'+index in errors }">
								{{ trans('forms.Product') }}
								<select id="unique_tradable[]" class="form-control" name="unique_tradable[]" v-model="form.unique_tradable[index]" {{ $readonly ? "disabled" : "" }}>
								@foreach ($tradables as $tradable)
									<option value="{{ $tradable->id }}" >{{ $tradable->sku }}</option>
								@endforeach
								</select>
								<span v-if="'unique_tradable.'+index in errors" class="help-block">
									<strong>@{{ errors['unique_tradable.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-1" v-bind:class="{ 'has-error' : 'valid.'+index in errors }">
								{{ trans('status.Valid') }}
								<input id="valid[]" type="hidden" name="valid[]" v-model="form.valid[index]" {{ $readonly ? "disabled" : "" }}>
								<input id="_valid[]" type="checkbox" class="form-control" name="_valid[]" v-model="form.valid[index]" {{ $readonly ? "disabled" : "" }}>
								<span v-if="'valid.'+index in errors" class="help-block">
									<strong>@{{ errors['valid.'+index][0] }}</strong>
								</span>
							</div>

						</div>

					@if (!$readonly)
						<div class="form-group">
							<div class="col-md-2">
								<button type="button" class="btn btn-info" onclick="return addNewLine();">+&nbsp;{{ trans('forms.Add line') }}</button>
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

	@if (!$readonly)
		function addNewLine() {
			vueContainer.form.line.push(0);
			vueContainer.form.unique_tradable.push({{ $tradables->first()->id }});
			vueContainer.form.entity.push(0);
			vueContainer.form.filter.push('include');
			vueContainer.form.valid.push(1);
		}
	@endif

		$(document).ready(function() {
			vueContainer = new Vue({
				el : '#vue_container',
				data : {
				@php
					$oldInput = session()->getOldInput();
				@endphp
					form : {
						line : {!! json_encode($oldInput['line']) !!},
						unique_tradable : {!! json_encode($oldInput['unique_tradable']) !!},
						entity : {!! json_encode($oldInput['entity']) !!},
						filter : {!! json_encode($oldInput['filter']) !!},
						valid : {!! json_encode($oldInput['valid']) !!}
					},
					errors : {!! json_encode($errors->toArray()) !!}
				},
				// mounted: function() {
				// }
			});
		});
	</script>
@endsection
