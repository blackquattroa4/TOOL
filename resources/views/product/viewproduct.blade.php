@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<!-- fill in content here -->
			<div id="supplierwindow" class="panel panel-default">
				<div class="panel-heading">
					<table width='100%'>
						<tr>
							<td><h4>{{ trans('product.View product') }}&emsp;({{ $product->uniqueTradable->sku }})</h4></td>
							<td align='right'></td>
						</tr>
					</table>
				</div>

				<div class="panel-body">
					<ul class="nav nav-tabs">
						<li><a id="basic-info" data-toggle="tab" href="#basic">{{ trans("product.Basic information") }}</a></li>
						<li><a data-toggle="tab" href="#notice">{{ trans("product.Product update notice") }}</a></li>
						<li><a data-toggle="tab" href="#faq">{{ trans("product.Product FAQ") }}</a></li>
						<li><a data-toggle="tab" href="#runrate">{{ trans("product.Analysis") }}</a></li>
					</ul>
					<div class="tab-content aging-inventory">
						<div id="basic" class="tab-pane fade">
							<div style="margin-top:20px;">
								<!-- basic information -->
								<div class="form-horizontal" role="form" >
									<div class="form-group{{ $errors->has('model') ? ' has-error' : '' }}">
										<label for="model" class="col-md-4 control-label">{{ trans('forms.Model') }}&nbsp;/&nbsp;{{ trans('forms.SKU') }}</label>

										<div class="col-md-6">
											<input id="model" type="text" class="form-control" name="model" value="{{ $product->uniqueTradable->sku }}" readonly>
										</div>
									</div>

									<div class="form-group{{ $errors->has('description') ? ' has-error' : '' }}">
										<label for="description" class="col-md-4 control-label">{{ trans('forms.Description') }}</label>

										<div class="col-md-6">
											<input id="description" type="text" class="form-control" name="description" value="{{ $product->uniqueTradable->description }}" readonly>
										</div>
									</div>

									<div class="form-group{{ $errors->has('productid') ? ' has-error' : '' }}">
										<label for="productid" class="col-md-4 control-label">UPC&nbsp;/&nbsp;EAN</label>

										<div class="col-md-6">
											<input id="productid" type="text" class="form-control" name="productid" value="{{ $product->uniqueTradable->product_id }}" readonly>
										</div>
									</div>

									<div class="form-group{{ $errors->has('phaseout') ? ' has-error' : '' }}">
										<label for="phaseout" class="col-md-4 control-label">{{ trans('forms.Phasing-out') }}</label>

										<div class="col-md-1">
											<input id="phaseout" type="checkbox" class="form-control" name="phaseout" onclick="return false " {{ $product->uniqueTradable->phasing_out ? " checked" : "" }}>
										</div>
									</div>

									<div class="form-group{{ $errors->has('itemtype') ? ' has-error' : '' }}">
										<label for="itemtype" class="col-md-4 control-label">{{ trans('forms.Item type') }}</label>

										<label for="itemtype" class="col-md-2 control-label">{{ trans('forms.Stockable') }}</label>
										<div class="col-md-1">
											<input id="itemtype" type="radio" class="form-control" name="itemtype" value="stockable" onclick="return false;" {{ $product->uniqueTradable->stockable ? " checked" : "" }}>
										</div>
										<label for="itemtype" class="col-md-2 control-label">{{ trans('forms.Expendable') }}</label>
										<div class="col-md-1">
											<input id="itemtype" type="radio" class="form-control" name="itemtype" value="expendable" onclick="return false;" {{ $product->uniqueTradable->expendable ? " checked" : "" }}>
										</div>
									</div>

									<div class="form-group{{ $errors->has('forecast') ? ' has-error' : '' }}">
										<label for="phaseout" class="col-md-4 control-label">{{ trans('forms.Forecastable') }}</label>

										<div class="col-md-1">
											<input id="forecast" type="checkbox" class="form-control" name="forecast" onclick="return false;" {{ $product->uniqueTradable->forecastable ? " checked" : ""}}>
										</div>
									</div>

									<div class="form-group{{ $errors->has('active') ? ' has-error' : '' }}">
										<label for="active" class="col-md-4 control-label">{{ trans('forms.Active') }}</label>

										<div class="col-md-1">
											<input id="active" type="checkbox" class="form-control" name="active" onclick="return false;" {{ $product->current ? " checked" : ""}}>
										</div>
									</div>

									<div class="form-group{{ $errors->has('serial_pattern') ? ' has-error' : '' }}">
										<label for="contact" class="col-md-4 control-label">{{ trans('forms.Serial pattern') }}</label>

										<div class="col-md-6">
											<input id="serial_pattern" type="text" class="form-control" name="serial_pattern" placeholder="regular expression of serial number" value="{{ $product->serial_pattern }}" readonly>
										</div>
									</div>

									<div class="form-group{{ $errors->has('supplier') ? ' has-error' : '' }}">
										<label for="email" class="col-md-4 control-label">{{ trans('forms.Supplier') }}</label>

										<div class="col-md-6">
											<input id="supplier" class="form-control" name="supplier" value="{{ $product->supplier->name }}" readonly>
										</div>
									</div>

									<div class="form-group{{ $errors->has('unit_dimension') ? ' has-error' : '' }}">
										<label for="phone" class="col-md-4 control-label">{{ trans('forms.Unit dimension') }}</label>

										<div class="col-md-2">
											<input id="unit_length" type="number" min="0.01" step="0.01" class="form-control" name="unit_length" value="{{ $product->unit_length }}" readonly>{{ $misc['length'] }}
										</div>

										<div class="col-md-2">
											<input id="unit_width" type="number" min="0.01" step="0.01" class="form-control" name="unit_width" value="{{ $product->unit_width }}" readonly>{{ $misc['length'] }}
										</div>

										<div class="col-md-2">
											<input id="unit_height" type="number" min="0.01" step="0.01" class="form-control" name="unit_height" placeholder="height" value="{{ $product->unit_height }}" readonly>{{ $misc['length'] }}
										</div>
									</div>

									<div class="form-group{{ $errors->has('unit_weight') ? ' has-error' : '' }}">
										<label for="unit_weight" class="col-md-4 control-label">{{ trans('forms.Unit weight') }}</label>

										<div class="col-md-3">
											<input id="unit_weight" type="number" min="0.01" step="0.01" class="form-control" name="unit_weight" value="{{ $product->unit_weight }}" readonly>{{ $misc['weight'] }}
										</div>
									</div>

									<div class="form-group{{ $errors->has('per_carton') ? ' has-error' : '' }}">
										<label for="per_carton" class="col-md-4 control-label">{{ trans('forms.Unit per carton') }}</label>

										<div class="col-md-3">
											<input id="per_carton" type="number" min="1" step="1" class="form-control" name="per_carton" value="{{ $product->unit_per_carton }}" readonly>
										</div>
									</div>

									<div class="form-group{{ $errors->has('carton_dimension') ? ' has-error' : '' }}">
										<label for="phone" class="col-md-4 control-label">{{ trans('forms.Unit dimension') }}</label>

										<div class="col-md-2">
											<input id="carton_length" type="number" min="0.01" step="0.01" class="form-control" name="carton_length" placeholder="length" value="{{ $product->carton_length }}" readonly>{{ $misc['length'] }}
										</div>

										<div class="col-md-2">
											<input id="carton_width" type="number" min="0.01" step="0.01" class="form-control" name="carton_width" placeholder="width" value="{{ $product->carton_width }}" readonly>{{ $misc['length'] }}
										</div>

										<div class="col-md-2">
											<input id="carton_height" type="number" min="0.01" step="0.01" class="form-control" name="carton_height" placeholder="height" value="{{ $product->carton_height }}" readonly>{{ $misc['length'] }}
										</div>
									</div>

									<div class="form-group{{ $errors->has('carton_weight') ? ' has-error' : '' }}">
										<label for="carton_weight" class="col-md-4 control-label">{{ trans('forms.Unit weight') }}</label>

										<div class="col-md-3">
											<input id="carton_weight" type="number" min="0.01" step="0.01" class="form-control" name="carton_weight" value="{{ $product->carton_weight }}" readonly>{{ $misc['weight'] }}
										</div>
									</div>

									<div class="form-group{{ $errors->has('per_pallet') ? ' has-error' : '' }}">
										<label for="per_pallet" class="col-md-4 control-label">{{ trans('forms.Carton per pallet') }}</label>

										<div class="col-md-3">
											<input id="per_pallet" type="number" min="1" step="1" class="form-control" name="per_pallet" value="{{ $product->carton_per_pallet }}" readonly>
										</div>
									</div>

									<div class="form-group{{ $errors->has('lead_day') ? ' has-error' : '' }}">
										<label for="lead_day" class="col-md-4 control-label">{{ trans('forms.Lead days') }}</label>

										<div class="col-md-3">
											<input id="lead_day" type="number" min="1" step="1" class="form-control" name="lead_day" value="{{ $product->lead_days }}" readonly>
										</div>
									</div>

									<div class="form-group{{ $errors->has('content') ? ' has-error' : '' }}">
										<label for="content" class="col-md-4 control-label">{{ trans('forms.Content') }}</label>

										<div class="col-md-6">
											<textarea id="content" class="form-control" name="content" rows=4 readonly>{{ $product->content }}</textarea>
										</div>
									</div>

									<div class="form-group{{ $errors->has('country') ? ' has-error' : '' }}">
										<label for="country" class="col-md-4 control-label">{{ trans('forms.Manufacture origin') }}</label>

										<div class="col-md-6">
											<input id="country" class="form-control" name="country" value="{{ $product->manufacture_origin }}" readonly></input>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div id="notice" class="tab-pane fade">
							<div style="margin-top:20px;">
								<table id="updatetable" class="table table-striped table-bordered" cellspacing="0" width="100%">
									<thead>
										<tr>
											<th>{{ trans('product.Summary') }}</th>
											<th>{{ trans('product.Issue date') }}</th>
											<th>{{ trans('product.Staff') }}</th>
											<th>{{ trans('product.Affected product') }}</th>
											<th></th>
										</tr>
									</thead>
									<tfoot>
										<tr>
											<th>{{ trans('product.Summary') }}</th>
											<th>{{ trans('product.Issue date') }}</th>
											<th>{{ trans('product.Staff') }}</th>
											<th>{{ trans('product.Affected product') }}</th>
											<th></th>
										</tr>
									</tfoot>
									<tbody>
										@foreach ($notice as $oneNotice)
											<tr>
												<td>{{ $oneNotice['summary'] }}</td>
												<td>{{ $oneNotice['date'] }}</td>
												<td>{{ $oneNotice['staff'] }}</td>
												<td>
													@foreach ($oneNotice['products'] as $idx => $sku)
														<span class="label label-info"><a style="color:white;" href="{{ url('/product/viewproduct/'.$idx) }}">{{ $sku }}</a></span>&nbsp;
													@endforeach
												</td>
												<td>
													@if ($oneNotice['can_view'])
														<a class='btn btn-info btn-xs' title="{{ trans('forms.View') }}" href="{{ url('document/view/' . $oneNotice['document_id']) }}"><i class="fa fa-eye" aria-hidden="true"></i></a>
													@endif
												</td>
											</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
						<div id="faq" class="tab-pane fade">
							<div style="margin-top:20px;">
								<table id="faqtable" class="table table-striped table-bordered" cellspacing="0" width="100%">
									<thead>
										<tr>
											<th>{{ trans('product.Question') }}</th>
											<th>{{ trans('product.Issue date') }}</th>
											<th>{{ trans('product.Staff') }}</th>
											<th>{{ trans('product.Affected product') }}</th>
											<th></th>
										</tr>
									</thead>
									<tfoot>
										<tr>
											<th>{{ trans('product.Question') }}</th>
											<th>{{ trans('product.Issue date') }}</th>
											<th>{{ trans('product.Staff') }}</th>
											<th>{{ trans('product.Affected product') }}</th>
											<th></th>
										</tr>
									</tfoot>
									<tbody>
										@foreach ($faq as $oneEntry)
											<tr>
												<td>{{ $oneEntry['question'] }}</td>
												<td>{{ $oneEntry['date'] }}</td>
												<td>{{ $oneEntry['staff'] }}</td>
												<td>
												@foreach ($oneEntry['products'] as $idx => $sku)
													<span class="label label-info"><a style="color:white;" href="{{ url('/product/viewproduct/'.$idx) }}">{{ $sku }}</a></span>&nbsp;
												@endforeach
												</td>
												<td>
													@if ($oneEntry['can_view'])
														<a class='btn btn-info btn-xs' title="{{ trans('forms.View') }}" href="{{ url('document/view/' . $oneEntry['document_id']) }}"><i class="fa fa-eye" aria-hidden="true"></i></a>
													@endif
												</td>
											</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
						<div id="runrate" class="tab-pane fade">
							<div style="margin-top:20px;">
								<div class="alert alert-warning" role="alert">
  								<strong>Warning!</strong> Analysis tool is to be designed/implemented.
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@endsection

@section('post-content')
	<script>
		$(document).ready(function() {
			$('a#basic-info').trigger('click');
			$('#updatetable').DataTable({ "order": [[ 1, 'desc' ]], "pageLength": 25, "columnDefs": [{ "orderable": false, "targets": 4 }] });
			$('#faqtable').DataTable({ "order": [[ 1, 'desc' ]], "pageLength": 25, "columnDefs": [{ "orderable": false, "targets": 4 }] });
		});
	</script>
@endsection
