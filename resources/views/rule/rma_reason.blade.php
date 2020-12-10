@extends('layouts.app')

@section('additional-style')

@endsection

@section('content')
<div id="vue_container" class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">

			<div class="panel panel-default">
				<div class="panel-heading">
					<h4>{{ trans('forms.RMA reason') }}</h4>
				</div>
				<div class="panel-body">
					<form class="form-horizontal" role="form" method="POST" action="">
						{{ csrf_field() }}

						<div v-for="(lineId, index) in form.line" class="form-group detail-line">

							<input id="line[]" type="hidden" name="line[]" v-model="lineId" ></input>

							<div class="col-md-4 col-md-offset-1" v-bind:class="{ 'has-error' : 'summary.'+index in errors }">
								{{ trans('forms.Mnemonic') }}
								<input id="summary[]" class="form-control" name="summary[]" v-model="form.summary[index]">
								<span v-if="'summary.'+index in errors" class="help-block">
									<strong>@{{ errors['summary.'+index][0] }}</strong>
								</span>
							</div>

							<div class="col-md-6" v-bind:class="{ 'has-error' : 'description.'+index in errors }">
								{{ trans('forms.Description') }}
								<input id="description[]" class="form-control" name="description[]" v-model="form.description[index]">
								<span v-if="'description.'+index in errors" class="help-block">
									<strong>@{{ errors['description.'+index][0] }}</strong>
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
			vueContainer.form.summary.push('');
			vueContainer.form.description.push('');
		}

		$(document).ready(function() {
			vueContainer = new Vue({
				el : '#vue_container',
				data : {
				@php
					$oldInput = session()->getOldInput();
				@endphp
					form : {
						line : {!! json_encode($oldInput['line']) !!},
						summary : {!! json_encode($oldInput['summary']) !!},
						description : {!! json_encode($oldInput['description']) !!}
					},
					errors : {!! json_encode($errors->toArray()) !!}
				}
			});
		});
	</script>
@endsection
