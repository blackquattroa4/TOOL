@extends('layouts.app')

@section('content')
<div id="vue_container" class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">

			<div class="panel panel-default">
				<div class="panel-heading">
					<h4>{{ trans('forms.Manage recurring charge') }}</h4>
				</div>
				<div class="panel-body">
					<form class="form-horizontal" role="form" method="POST" action="">
						{{ csrf_field() }}

						<div v-for="(id, index) in form.line" class="form-group detail-line">

							<input id="line[]" type="hidden" name="line[]" v-model="id" ></input>

							<div class="col-md-3 col-md-offset-1" v-bind:class="{ 'has-error' : 'tradable.'+index in errors }">
								{{ trans('forms.Item') }}
								<select id="tradable[]" class="form-control" name="tradable[]" v-model="form.tradable[index]" {{ $readonly ? "disabled" : "" }}>
								@foreach ($tradables as $tradable)
									<option value="{{ $tradable->id }}" >{{ $tradable->sku }}</option>
								@endforeach
								</select>
								<span v-if="'tradable.'+index in errors" class="help-block">
									<strong>@{{ errors['tradable.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-4" v-bind:class="{ 'has-error' : 'entity.'+index in errors }">
								{{ trans('forms.Entity') }}
								<select id="entity[]" class="form-control" name="entity[]" v-model="form.entity[index]" {{ $readonly ? "disabled" : "" }}>
								@foreach ($entities as $entity)
									<option value="{{ $entity->id }}" >{{ $entity->code }}&emsp;({{ $entity->name }})</option>
								@endforeach
								</select>
								<span v-if="'entity.'+index in errors" class="help-block">
									<strong>@{{ errors['entity.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-1 col-md-offset-2" v-bind:class="{ 'has-error' : 'valid.'+index in errors }">
								{{ trans('status.Valid') }}
								<input id="valid[]" type="hidden" name="valid[]" v-model="form.valid[index]" >
								<input id="_valid[]" type="checkbox" class="form-control" name="_valid[]" v-model="form.valid[index]" {{ $readonly ? "disabled" : "" }}>
								<span v-if="'valid.'+index in errors" class="help-block">
									<strong>@{{ errors['valid.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-4 col-md-offset-2" v-bind:class="{ 'has-error' : 'notes.'+index in errors }">
								{{ trans('forms.Notes') }}
								<input id="notes[]" type="text" class="form-control" name="notes[]" v-model="form.notes[index]" >
								<span v-if="'notes.'+index in errors" class="help-block">
									<strong>@{{ errors['notes.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-2" v-bind:class="{ 'has-error' : 'numeral.'+index in errors }">
								{{ trans('finance.Frequency') }}
								<input id="numeral[]" style="text-align:right;" type="number" class="form-control" name="numeral[]" min="1" v-model="form.numeral[index]">
								<span v-if="'numeral.'+index in errors" class="help-block">
									<strong>@{{ errors['numeral.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-2" v-bind:class="{ 'has-error' : 'period.'+index in errors }">
								&nbsp;
								<select id="period[]" class="form-control" name="period[]" v-model="form.period[index]">
								@foreach ([ 'days' => trans('finance.days'), 'weeks' => trans('finance.weeks'), 'months' => trans('finance.months'), 'years' => trans('finance.years') ] as $value => $display)
									<option value="{{ $value }}" >{{ $display }}</option>
								@endforeach
								</select>
								<span v-if="'period.'+index in errors" class="help-block">
									<strong>@{{ errors['period.'+index][0] }}</strong>
								</span>
							</div>
						</div>

					@if (!$readonly)
						<div class="form-group">
							<div class="col-md-2">
								<button type="button" class="btn btn-info" onclick="addNewLine()" >
									+&nbsp;{{ trans('forms.Add line') }}
								</button>
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
			vueContainer.form.tradable.push({{ $tradables->first()->id }});
			vueContainer.form.entity.push({{ $entities->first()->id }});
			vueContainer.form.valid.push(1);
			vueContainer.form.notes.push('');
			vueContainer.form.numeral.push(1);
			vueContainer.form.period.push('days');
		}
	@endif

		$(document).ready(function() {
			vueContainer = new Vue({
				el : '#vue_container',
				data : {
				@php
					$oldInput = session()->getOldInput();
					array_walk($oldInput['valid'], function(&$element, $index) {
						$element = in_array($element, [1, true, "1", "true"], true);
					});
				@endphp
					form : {
						line : {!! json_encode($oldInput['line']) !!},
						tradable : {!! json_encode($oldInput['tradable']) !!},
						entity : {!! json_encode($oldInput['entity']) !!},
						valid : {!! json_encode($oldInput['valid']) !!},
						notes : {!! json_encode($oldInput['notes']) !!},
						numeral : {!! json_encode($oldInput['numeral']) !!},
						period : {!! json_encode($oldInput['period']) !!},
					},
					errors : {!! json_encode($errors->toArray()) !!}
				}
			});
		});
	</script>
@endsection
