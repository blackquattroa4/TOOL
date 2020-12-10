@extends('layouts.app')

@section('content')
<div id="vue_container" class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">

			<div class="panel panel-default">
				<div class="panel-heading">
					<h4>{{ trans('forms.Expense approval rule') }}</h4>
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
								@foreach ($entities as $entity)
									<option value="{{ $entity->id }}" >{{ $entity->name }}&emsp;({{ $entity->code }})</option>
								@endforeach
								</select>
								<span v-if="'entity.'+index in errors" class="help-block">
									<strong>@{{ errors['entity.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-3" v-bind:class="{ 'has-error' : 'threshold.'+index in errors }">
								{{ trans('finance.Amount') }}
								<input id="threshold[]" style="text-align:right;" type="number" class="form-control" min="0" step="{{ $currency['min'] }}" v-model="form.threshold[index]" name="threshold[]" {{ $readonly ? "disabled" : "" }}>
								<span v-if="'threshold.'+index in errors" class="help-block">
									<strong>@{{ errors['threshold.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-1 col-md-offset-2" v-bind:class="{ 'has-error' : 'valid.'+index in errors }">
								{{ trans('status.Valid') }}
								<input id="valid[]" type="hidden" name="valid[]" v-model="form.valid[index]" {{ $readonly ? "disabled" : "" }}>
								<input id="_valid[]" type="checkbox" class="form-control" name="_valid[]" v-model="form.valid[index]" {{ $readonly ? "disabled" : "" }}>
								<span v-if="'valid.'+index in errors" class="help-block">
									<strong>@{{ errors['valid.'+index][0] }}</strong>
								</span>
							</div>

							<div class="clearfix"></div>

							<div class="col-md-3 col-md-offset-2" v-bind:class="{ 'has-error' : 'approver.'+index in errors }">
								{{ trans('forms.Staff') }}
								<select id="approver[]" class="form-control" name="approver[]" v-model="form.approver[index]" {{ $readonly ? "disabled" : "" }}>
								@foreach ($employees as $employee)
									<option value="{{ $employee->id }}" >{{ $employee->name }}</option>
								@endforeach
								</select>
								<span v-if="'approver.'+index in errors" class="help-block">
									<strong>@{{ errors['approver.'+index][0] }}</strong>
								</span>
							</div>

						</div>

					@if (!$readonly)
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
			vueContainer.form.entity.push(0);
			vueContainer.form.approver.push({{ $employees->first()->id }});
			vueContainer.form.threshold.push(0);
			vueContainer.form.valid.push(1);
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
						entity : {!! json_encode($oldInput['entity']) !!},
						approver : {!! json_encode($oldInput['approver']) !!},
						threshold : {!! json_encode($oldInput['threshold']) !!},
						valid : {!! json_encode($oldInput['valid']) !!}
					},
					errors : {!! json_encode($errors->toArray()) !!}
				}
			});
		});
	</script>
@endsection
