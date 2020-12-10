@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
		@if (isset($source['history']) && count($source['history']))
			<!-- history modal -->
			<div class="modal fade" id="historyModal" tabindex="-1" role="dialog" aria-labelledby="historyModalLabel" data-backdrop="static" aria-hidden="false">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
							<h4 class="modal-title" id="deleteModalLabel">{{ trans('forms.History') }}</h4>
						</div>
						<div class="modal-body">
						@foreach ($source['history'] as $oneLine)
							<p>{{ sprintf(trans('messages.%1$s %2$s at %3$s'), $oneLine->staff['name'], trans('action.'.$oneLine['process_status']), \App\Helpers\DateHelper::dbToGuiDate($oneLine['updated_at']->format("Y-m-d")) . " " . $oneLine['updated_at']->format("g:iA")) }}</p>
						@endforeach
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans("forms.Close") }}</button>
						</div>
					</div>
				</div>
			</div>
		@endif

			<div class="panel panel-default">
				<div class="panel-heading">
					<table width="100%">
						<tr>
							<td>{{ $source['title'] }}</td>
							<td>
							@if (isset($source['history']) && count($source['history']))
								<a href="#" data-toggle="modal" data-target="#historyModal"><span class="fa fa-2x fa-history pull-right"></span></a>
							@endif
							</td>
						</tr>
					</table>
				</div>
				<div class="panel-body">
					<form class="form-horizontal" role="form" method="POST" action="{{ $source['post_url'] }}">
						{{ csrf_field() }}

						<input type="hidden" id="type" name="type" value="{{ $source['type'] }}" />

						<div class="form-group">
							<label for="increment" class="col-md-2 control-label">{{ trans('forms.Document') }}</label>

							<div class="col-md-3{{ $errors->has('increment') ? ' has-error' : '' }}">
								<input id="increment" type="text" class="form-control" name="increment" value="{{ $document }}" readonly>

								@if ($errors->has('increment'))
									<span class="help-block">
										<strong>{{ $errors->first('increment') }}</strong>
									</span>
								@endif
							</div>

							<label for="reference" class="col-md-2 control-label">{{ trans('forms.Reference') }}</label>

							<div class="col-md-4{{ $errors->has('reference') ? ' has-error' : '' }}">
								<input id="reference" type="text" class="form-control" name="reference" value="{{ $reference }}" readonly>

								@if ($errors->has('reference'))
									<span class="help-block">
										<strong>{{ $errors->first('reference') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group">
							<label for="pdate" class="col-md-2 control-label">{{ trans('forms.Date') }}</label>

							<div class="col-md-3{{ $errors->has('pdate') ? ' has-error' : '' }}">
								<input id="pdate" type="text" class="form-control" name="pdate" value="{{ $process_date }}" readonly>

								@if ($errors->has('pdate'))
									<span class="help-block">
										<strong>{{ $errors->first('pdate') }}</strong>
									</span>
								@endif
							</div>

							<label for="staff" class="col-md-2 control-label">{{ trans('forms.Staff') }}</label>

							<div class="col-md-4{{ $errors->has('staff') ? ' has-error' : '' }}">
								<input id="staff" type="text" class="form-control" name="staff" value="{{ $contact }}" readonly>

								@if ($errors->has('staff'))
									<span class="help-block">
										<strong>{{ $errors->first('staff') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group">
							<label for="via" class="col-md-2 control-label">{{ trans('forms.Via') }}</label>

							<div class="col-md-3{{ $errors->has('via') ? ' has-error' : '' }}">
								<input id="via" type="text" class="form-control" name="via" value="{{ $via }}" readonly>

								@if ($errors->has('via'))
									<span class="help-block">
										<strong>{{ $errors->first('via') }}</strong>
									</span>
								@endif
							</div>

							<label for="location" class="col-md-2 control-label">{{ trans('forms.Warehouse') }}</label>

							<div class="col-md-4{{ $errors->has('location') ? ' has-error' : '' }}">
								<input id="location" type="text" class="form-control" name="location" value="{{ $location }}" readonly>

								@if ($errors->has('location'))
									<span class="help-block">
										<strong>{{ $errors->first('location') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<div class="form-group">
							<label for="entity" class="col-md-2 control-label">{{ trans('forms.Entity') }}</label>

							<div class="col-md-3{{ $errors->has('entity') ? ' has-error' : '' }}">
								<input id="entity" type="text" class="form-control" name="entity" value="{{ $ext_entity }}" readonly>

								@if ($errors->has('entity'))
									<span class="help-block">
										<strong>{{ $errors->first('entity') }}</strong>
									</span>
								@endif
							</div>

							<label for="address" class="col-md-2 control-label">{{ trans('forms.External address') }}</label>

							<div class="col-md-4{{ $errors->has('address') ? ' has-error' : '' }}">
								<textarea id="address" rows="4" class="form-control" name="address" readonly>{{ $address }}</textarea>

								@if ($errors->has('address'))
									<span class="help-block">
										<strong>{{ $errors->first('address') }}</strong>
									</span>
								@endif
							</div>
						</div>

						<hr />

					@foreach ( old('processQuantity') as $id => $product_id )
						<div class="form-group{{ $errors->has('product['.$id.']') ? ' has-error' : '' }} detail-line">
							<input id="line[{{ $id }}]" type="hidden" name="line[{{ $id }}]" value="{{ $line[$id] }}" ></input>
							<div class="col-md-4">
								{{ trans('forms.Item') }}<input id="product[{{ $id }}]" type="text" class="form-control" name="product[{{ $id }}]" value="{{ $product[$id] }}" readonly>

								@if ($errors->has('product['.$id.']'))
									<span class="help-block">
										<strong>{{ $errors->first('product['.$id.']') }}</strong>
									</span>
								@endif
							</div>

							<div class="col-md-7">
								{{ trans('forms.Description') }}<input id="description[{{ $id }}]" type="text" class="form-control" name="description[{{ $id }}]" value="{{ $description[$id] }}" readonly>

								@if ($errors->has('description['.$id.']'))
									<span class="help-block">
										<strong>{{ $errors->first('description['.$id.']') }}</strong>
									</span>
								@endif
							</div>

							<div class="col-md-8">
								@if (isset($serial[$id]) && (!empty($serial[$id])))
									&nbsp;<br><a id="show-serial-detail-{{ $id }}" style="padding-left:10%;" data-toggle="collapse" href="#serial-detail-{{ $id }}" onclick="$(this).css('display', 'none')" aria-expanded="false">{{ trans('forms.Show serial') }}</a>
									<div class="collapse" id="serial-detail-{{ $id }}">
										<div class="well">
											{!! $serial[$id] !!}
											<a class="pull-right" data-toggle="collapse" href="#serial-detail-{{ $id }}" onclick="$('#show-serial-detail-{{ $id }}').css('display', '')" aria-expanded="false">{{ trans('forms.Hide serial') }}</a>
	  								</div>
									</div>
								@endif
							</div>

							<div class="col-md-2">
								{{ trans('forms.Ordered') }}<input id="quantity[{{ $id }}]" type="text" style="text-align:right;" class="form-control" name="expectQuantity[{{ $id }}]" value="{{ sprintf(env("APP_QUANTITY_FORMAT"), $expectQuantity[$id]) }}" readonly>

								@if ($errors->has('expectQuantity['.$id.']'))
									<span class="help-block">
										<strong>{{ $errors->first('expectQuantity['.$id.']') }}</strong>
									</span>
								@endif
							</div>

							<div class="col-md-2">
								{{ trans('forms.Processed') }}
								@if ($readonly)
									<input id="processQuantity[{{ $id }}]" style="text-align:right;" min="{{ $quantity_formatter['zero'] }}" step="{{ $quantity_formatter['step'] }}" max="{{ sprintf(env("APP_QUANTITY_FORMAT"), $expectQuantity[$id]) }}" class="form-control" name="processQuantity[{{ $id }}]" value="{{ old('processQuantity')[$id] }}" readonly>
								@else
									<input id="processQuantity[{{ $id }}]" type="number" style="text-align:right;" min="{{ $quantity_formatter['zero'] }}" step="{{ $quantity_formatter['step'] }}" max="{{ sprintf(env("APP_QUANTITY_FORMAT"), $expectQuantity[$id]) }}" class="form-control" name="processQuantity[{{ $id }}]" value="{{ old('processQuantity')[$id] }}" >
								@endif

								@if ($errors->has('processQuantity['.$id.']'))
									<span class="help-block">
										<strong>{{ $errors->first('processQuantity['.$id.']') }}</strong>
									</span>
								@endif
							</div>

						</div>
					@endforeach

						<div class="form-group">
							<div class="col-md-2 col-md-offset-10">
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

@section('post-content')
@endsection
