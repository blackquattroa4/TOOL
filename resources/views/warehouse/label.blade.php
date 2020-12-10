@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4>{{ trans("forms.UPC")}}</h4>
				</div>
				<div class="panel-body">

					<form id="upc_form" class="form-horizontal" role="form" method="POST" action="{{ url('warehouse/label/upc') }}" >
						{{ csrf_field() }}

						<div class="form-group">
							<div class="col-md-4">
								{{ trans('forms.Template') }}
								<select id="template" name="template" class="form-control" >
									<option value="LabelAvery5160Pdf">Avery Denison 5160</option>
									<option value="LabelAvery5163Pdf">Avery Denison 5163</option>
									<option value="LabelAvery5167Pdf" selected>Avery Denison 5167</option>
								</select>
							</div>

							<div class="col-md-2">
								{{ trans('forms.Quantity') }}
								<input id="quantity" style="text-align:right;" type="number" name="quantity" class="form-control" min="1" step="1" value="1" ></input>
							</div>

							<div class="col-md-2">
								{{ trans('forms.Format') }}
								<select id="format" name="format" class="form-control" >
									<option value="none">{{ trans('forms.None') }}</option>
									<option value="upca" selected>UPC-A</option>
								</select>
							</div>
						</div>

						<div class="form-group">
							<div class="col-md-4">
								{{ trans('forms.Text') }}
								<input id="text" type="text" name="text" class="form-control" placeholder="{{ trans('warehouse.Text above barcode') }}" value="" ></input>
							</div>
							<div class="col-md-4">
								{{ trans('forms.UPC') }}
								<input id="upc" type="text" name="upc" class="form-control" placeholder="{{ trans('forms.UPC') }}" value="" ></input>
							</div>

							<div class="col-md-4">
								&emsp;<br>  <!-- this is to help alignmnet -->
								<button id="print_upc" type="submit" class="btn btn-primary pull-right">{{ trans('forms.View PDF')}}</button>
							</div>

						</div>
					</form>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4>{{ trans("forms.Serial")}}</h4>
				</div>
				<div class="panel-body">
					<form id="serial_form" class="form-horizontal" role="form" method="POST" action="{{ url('warehouse/label/serial') }}" >
						{{ csrf_field() }}

						<div class="form-group">
							<div class="col-md-4">
								{{ trans('forms.Template') }}
								<select id="template" name="template" class="form-control" >
									<option value="LabelAvery5160Pdf">Avery Denison 5160</option>
									<option value="LabelAvery5163Pdf">Avery Denison 5163</option>
									<option value="LabelAvery5167Pdf" selected>Avery Denison 5167</option>
								</select>
							</div>
						</div>

						<div class="form-group">
							<div class="col-md-4">
								{{ trans('forms.Text') }}
								<input id="text" type="text" name="text" class="form-control" placeholder="{{ trans('warehouse.Text above barcode') }}" value="" ></input>
							</div>
						</div>

						<div class="form-group">
							<div class="col-md-4">
								{{ trans('forms.Serial') }} ({{ trans('forms.In-range')}})
								<input id="start" type="text" name="start" class="form-control" placeholder="{{ trans('warehouse.Enter beginning serial number') }}" value="" ></input>
							</div>
							<div class="col-md-4">
								&emsp;<br>
								<input id="end" type="text" name="end" class="form-control" placeholder="{{ trans('warehouse.Enter ending serial number') }}" value="" ></input>
							</div>

							<div class="col-md-4">
								&emsp;<br>  <!-- this is to help alignmnet -->
								<button id="print_serial" type="submit" class="btn btn-primary pull-right">{{ trans('forms.View PDF')}}</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4>{{ trans("forms.Carton")}}</h4>
				</div>
				<div class="panel-body">

					<form id="carton_form" class="form-horizontal" role="form" method="POST" action="{{ url('warehouse/label/carton') }}" >
						{{ csrf_field() }}

						<div class="form-group">
							<div class="col-md-4">
								{{ trans('forms.Template') }}
								<select id="template" name="template" class="form-control" >
									<option value="LabelAvery5168Pdf">Avery Denison 5168</option>
								</select>
							</div>

							<div class="col-md-2">
								{{ trans('forms.Per-carton') }}
								<input id="quantity" style="text-align:right;" type="number" name="quantity" class="form-control" min="1" step="1" value="1" ></input>
							</div>

							<div class="col-md-2">
								{{ trans('forms.Format') }}
								<select id="format" name="format" class="form-control" >
									<option value="none">{{ trans('forms.None') }}</option>
									<option value="upca" selected>UPC-A</option>
								</select>
							</div>
						</div>

						<div class="form-group">
							<div class="col-md-4">
								{{ trans('forms.Text') }}
								<input id="text" type="text" name="text" class="form-control" placeholder="{{ trans('warehouse.Text above barcode') }}" value="" ></input>
							</div>
							<div class="col-md-4">
								{{ trans('forms.UPC') }}
								<input id="upc" type="text" name="upc" class="form-control" placeholder="{{ trans('forms.UPC') }}" value="" ></input>
							</div>
						</div>

						<div class="form-group">
							<div class="col-md-4">
								{{ trans('forms.Serial') }} ({{ trans('forms.In-range')}})
								<input id="start" type="text" name="start" class="form-control" placeholder="{{ trans('warehouse.Enter beginning serial number') }}" value="" ></input>
							</div>
							<div class="col-md-4">
								&emsp;<br>
								<input id="end" type="text" name="end" class="form-control" placeholder="{{ trans('warehouse.Enter ending serial number') }}" value="" ></input>
							</div>

							<div class="col-md-4">
								&emsp;<br>  <!-- this is to help alignmnet -->
								<button id="print_carton" type="submit" class="btn btn-primary pull-right">{{ trans('forms.View PDF')}}</button>
							</div>

						</div>
					</form>

				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4>{{ trans("forms.Bin")}}</h4>
				</div>
				<div class="panel-body">
					<form id="bin_form" class="form-horizontal" role="form" method="POST" action="{{ url('warehouse/label/bin') }}" >
						{{ csrf_field() }}

						<div class="form-group">
							<div class="col-md-4">
								{{ trans('forms.Template') }}
								<select id="template" name="template" class="form-control" >
									<option value="LabelAvery5168Pdf">Avery Denison 5168</option>
								</select>
							</div>

							<div class="col-md-5">
								{{ trans('forms.Bin') }}
								<input id="bin_regex" type="text" name="bin_regex" class="form-control" placeholder="Rack[1-10]Bin[1-20]" value="" ></input>
							</div>

							<div class="col-md-3">
								&emsp;<br>  <!-- this is to help alignmnet -->
								<button id="print_bin" type="submit" class="btn btn-primary pull-right">{{ trans('forms.View PDF')}}</button>
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
