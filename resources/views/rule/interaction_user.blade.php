@extends('layouts.app')

@section('content')
<div id="vue_container" class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">

			<div class="panel panel-default">
				<div class="panel-heading">
					<h4>{{ trans('forms.Interaction user rule') }}</h4>
				</div>
				<div class="panel-body">
					<form class="form-horizontal" role="form" method="POST" action="">
						{{ csrf_field() }}

						<div v-for="(id, index) in form.line" class="form-group detail-line">

							<input id="line[]" type="hidden" name="line[]" v-model="id" ></input>

							<div class="col-md-3 col-md-offset-1" v-bind:class="{ 'has-error' : 'originator.'+index in errors }">
								{{ trans('forms.Originator') }}
								<select id="originator[]" class="form-control" name="originator[]" v-model="form.originator[index]" {{ $readonly ? "disabled" : "" }}>
									<option value="0">{{ trans('forms.Anyone') }}</option>
								@foreach ($employees as $employee)
									<option value="{{ $employee->id }}" >{{ $employee->name }}</option>
								@endforeach
								</select>
								<span v-if="'originator.'+index in errors" class="help-block">
									<strong>@{{ errors['originator.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-3" v-bind:class="{ 'has-error' : 'participant.'+index in errors }">
								{{ trans('forms.Participant') }}
								<select id="participant[]" class="form-control" name="participant[]" v-model="form.participant[index]" {{ $readonly ? "disabled" : "" }}>
								@foreach ($employees as $employee)
									<option value="{{ $employee->id }}">{{ $employee->name }}</option>
								@endforeach
								</select>
								<span v-if="'participant.'+index in errors" class="help-block">
									<strong>@{{ errors['participant.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-2" v-bind:class="{ 'has-error' : 'role.'+index in errors }">
								{{ trans('forms.Role') }}
								<select id="role[]" class="form-control" name="role[]" v-model="form.role[index]" {{ $readonly ? "disabled" : "" }}>
								@foreach([ 'requestee' => 'Requestee', 'participant' => 'Participant'] as $key => $display)
									<option value="{{ $key }}" >{{ trans('forms.' . $display) }}</option>
								@endforeach
								</select>
								<span v-if="'role.'+index in errors" class="help-block">
									<strong>@{{ errors['role.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-1 col-md-offset-1" v-bind:class="{ 'has-error' : 'valid.'+index in errors }">
								{{ trans('status.Valid') }}
								<input id="valid[]" type="hidden" name="valid[]" v-model="form.valid[index]">
								<input id="_valid[]" type="checkbox" class="form-control" name="_valid[]" v-model="form.valid[index]" {{ $readonly ? "disabled" : "" }}>
							</div>
							<span v-if="'valid.'+index in errors" class="help-block">
								<strong>@{{ errors['valid.'+index][0] }}</strong>
							</span>
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
			vueContainer.form.originator.push(0);
			vueContainer.form.participant.push({{ $employee->first()->id }});
			vueContainer.form.role.push('requestee');
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
						originator : {!! json_encode($oldInput['originator']) !!},
						participant : {!! json_encode($oldInput['participant']) !!},
						role : {!! json_encode($oldInput['role']) !!},
						valid : {!! json_encode($oldInput['valid']) !!},
					},
					errors : {!! json_encode($errors->toArray()) !!}
				}
			});
		});
	</script>
@endsection
