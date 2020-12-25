@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">

			<!-- product modal -->
		@if ($controlSwitch['product-modal'])
			@include(prioritizeCustomizedTemplateOverDefault('embedded_modal.tradable_form'))
		@endif

			<!-- product-analysis modal -->
		@if ($controlSwitch['product-analysis-modal'])
			@include(prioritizeCustomizedTemplateOverDefault('embedded_modal.tradable_analysis'))
		@endif

			<!-- document modal -->
		@if ($controlSwitch['document-modal'])
			@include(prioritizeCustomizedTemplateOverDefault('embedded_modal.document_form'))
		@endif

			<!-- product-notice modal -->
		@if ($controlSwitch['product-notice-modal'])
			@include(prioritizeCustomizedTemplateOverDefault('embedded_modal.tradable_notice'))
		@endif

			<!-- product-faq modal -->
		@if ($controlSwitch['product-faq-modal'])
			@include(prioritizeCustomizedTemplateOverDefault('embedded_modal.tradable_faq'))
		@endif

		@if ($controlSwitch['product-window'])
			<div id="productwindow" class="panel panel-default">
				<div class="panel-heading">
					<table width='100%'>
						<tr>
							<td><h4>{{ trans('product.Product') }}</h4></td>
							<td align='right'>
							@if ($controlSwitch['create-product-button'])
								<input type="button" class="btn btn-primary" onclick="createTradableInModal()" value="{{ trans('product.New product') }}"/>
							@endif
							</td>
						</tr>
					</table>
				</div>

				<div class="panel-body">
					<table id="producttable" class="table table-striped table-bordered" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th>{{ trans('forms.SKU') }}</th>
								<th>UPC&nbsp;/&nbsp;EAN</th>
								<th>{{ trans('product.Description') }}</th>
								<th>{{ trans('product.Supplier') }}</th>
								<th></th>
								<th></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>{{ trans('forms.SKU') }}</th>
								<th>UPC&nbsp;/&nbsp;EAN</th>
								<th>{{ trans('product.Description') }}</th>
								<th>{{ trans('product.Supplier') }}</th>
								<th></th>
								<th></th>
							</tr>
						</tfoot>
						<tbody>
							<tr>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		@endif

		@if ($controlSwitch['notice-window'])
			<div id="updatewindow" class="panel panel-default">
				<div class="panel-heading">
					<table width='100%'>
						<tr>
							<td><h4>{{ trans('product.Product update notice') }}</h4></td>
							<td align='right'>
							@if ($controlSwitch['create-notice-button'])
								<input type="button" class="btn btn-primary" onclick="createTradableNoticeInModal()" value="{{ trans('product.New notice') }}"/>
							@endif
							</td>
						</tr>
					</table>
				</div>

				<div class="panel-body">
					<table id="updatetable" class="table table-striped table-bordered" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th>{{ trans('product.Summary') }}</th>
								<th>{{ trans('product.Issue date') }}</th>
								<th>{{ trans('product.Staff') }}</th>
								<th>{{ trans('product.Affected product') }}</th>
								<th></th>
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
								<th></th>
							</tr>
						</tfoot>
						<tbody>
							<tr>
								<td></td>
								<td data-order="1970-01-01"></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		@endif

		@if ($controlSwitch['faq-window'])
			<div id="faqwindow" class="panel panel-default">
				<div class="panel-heading">
					<table width='100%'>
						<tr>
							<td><h4>{{ trans('product.Product FAQ') }}</h4></td>
							<td align='right'>
							@if ($controlSwitch['create-faq-button'])
								<input type="button" class="btn btn-primary" onclick="createTradableFaqInModal()" value="{{ trans('product.New FAQ') }}"/>
							@endif
							</td>
						</tr>
					</table>
				</div>

				<div class="panel-body">
					<table id="faqtable" class="table table-striped table-bordered" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th>{{ trans('product.Question') }}</th>
								<th>{{ trans('product.Issue date') }}</th>
								<th>{{ trans('product.Staff') }}</th>
								<th>{{ trans('product.Affected product') }}</th>
								<th></th>
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
								<th></th>
							</tr>
						</tfoot>
						<tbody>
							<tr>
								<td></td>
								<td data-order="1970-01-01"></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		@endif

		@if (false)
			<div id="testwindow" class="panel panel-default">
				<div class="panel-heading">
					<table width='100%'>
						<tr>
							<td><h4>{{ trans('product.Test record') }}</h4></td>
							<td align='right'><input type="button" class="btn btn-primary" onclick="window.location.href='{{ url('/product/createrecord') }}'" value="{{ trans('product.New record') }}"/></td>
						</tr>
					</table>
				</div>

				<div class="panel-body">
					<table id="testtable" class="table table-striped table-bordered" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th>{{ trans('product.Test date') }}</th>
								<th>{{ trans('product.Staff') }}</th>
								<th>{{ trans('product.Affected product') }}</th>
								<th>{{ trans('product.Summary') }}</th>
								<th></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>{{ trans('product.Test date') }}</th>
								<th>{{ trans('product.Staff') }}</th>
								<th>{{ trans('product.Affected product') }}</th>
								<th>{{ trans('product.Summary') }}</th>
								<th></th>
							</tr>
						</tfoot>
						<tbody>
							<tr>
								<td data-order="1970-01-01"></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		@endif

		</div>
	</div>
</div>

<ul id="contextMenu" class="dropdown-menu" role="menu" style="display:none" >
	<li style="text-align:center;"><b>{{ trans('messages.Quick scroll menu') }}</b></li>
	<li class="divider"></li>
@if ($controlSwitch['product-window'])
	<li><a tabindex="-1" onclick="$('html,body').animate({scrollTop:$('#productwindow').offset().top}, 500);">{{ trans('product.Product') }}</a></li>
@endif
@if ($controlSwitch['notice-window'])
	<li><a tabindex="-1" onclick="$('html,body').animate({scrollTop:$('#updatewindow').offset().top}, 500);">{{ trans('product.Product update') }}</a></li>
@endif
@if ($controlSwitch['faq-window'])
	<li><a tabindex="-1" onclick="$('html,body').animate({scrollTop:$('#faqwindow').offset().top}, 500);">{{ trans('product.Product FAQ') }}</a></li>
@endif
@if (false)
	<li><a tabindex="-1" onclick="$('html,body').animate({scrollTop:$('#testwindow').offset().top}, 500);">{{ trans('product.Test record') }}</a></li>
@endif
	<li><a tabindex="-1" onclick="$('html,body').animate({scrollTop:$('.container').offset().top}, 500);">{{ trans('messages.Return to top') }}<i style="padding-left:1em;" class="fa fa-arrow-up" aria-hidden="true"></i></a></li>
</ul>
@endsection

@section('post-content')

	<script type="text/javascript" src="{{ asset('js/ContextMenu.js') }}"></script>

	<script type="text/javascript">

		$(".container").contextMenu({
			menuSelector: "#contextMenu",
			/*
			menuSelected: function (invokedOn, selectedMenu) {
				var msg = "You selected the menu item '" + selectedMenu.text() + "' on the value '" + invokedOn.text() + "'";
				alert(msg);
			}
			*/
		});
	</script>

	<script type="text/javascript" src="{{ asset('js/DataTableHelper.js') }}" ></script>

	<script type="text/javascript" src="{{ asset('js/HtmlTemplateHelper.js') }}" ></script>

@if ($controlSwitch['product-template'])
	<script id="product-template" type="text/x-custom-template">
		<tr id="product-{id}">
			<td><p style="padding-right:2em;display:inline;">{sku}</p>
				<i data-condition="{is_current}" class="fa fa-heartbeat" aria-hidden="true" title="{{ trans('status.Active') }}"></i>
				<i data-condition="{is_stockable}" class="fa fa-cubes" aria-hidden="true" title="{{ trans('status.Stockable') }}"></i>
				<i data-condition="{is_expendable}" class="fa fa-money" aria-hidden="true" title="{{ trans('status.Expendable') }}"></i>
			</td>
			<td>{product_id}</td>
			<td title="{description}" style="width:25%;text-overflow:ellipsis;">{description}</td>
			<td>{supplier}</td>
			<td>{search-key}</td>
			<td style="width:82px">
				<button data-condition="{can_view}" class='btn btn-info btn-xs' title="{{ trans('forms.View') }}" onclick="viewTradableInModal({id})"><i class="fa fa-eye" aria-hidden="true"></i></button>
				<button data-condition="{can_edit}" class='btn btn-info btn-xs' title="{{ trans('forms.Edit') }}" onclick="updateTradableInModal({id})"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
				<button data-condition="{can_view}" class='btn btn-info btn-xs' title="{{ trans('product.Analysis') }}" onclick="analyzeTradableInModal({id})"><i class="fa fa-pie-chart" aria-hidden="true"></i></button>
			</td>
		</tr>
	</script>
@endif

@if ($controlSwitch['notice-template'])
	<script id="notice-template" type="text/x-custom-template">
		<tr id="notice-{id}">
			<td>{summary}</td>
			<td data-order="{date}">{date_display}</td>
			<td>{staff}</td>
			<td style="width:50%;text-overflow:ellipsis;">
				{skus}
			</td>
			<td>{search-key}</td>
			<td style="width:55px">
					<a data-condition="{can_view}" class='btn btn-info btn-xs' title="{{ trans('forms.View') }}" onclick="viewDocumentInModal({document_id})"><i class="fa fa-eye" aria-hidden="true"></i></a>
					<a data-condition="{can_edit}" class='btn btn-info btn-xs' title="{{ trans('forms.Update') }}" onclick="updateTradableNoticeInModal({id})"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
			</td>
		</tr>
	</script>
@endif

@if ($controlSwitch['faq-template'])
	<script id="faq-template" type="text/x-custom-template">
		<tr id="faq-{id}">
			<td style="width:25%;text-overflow:ellipsis;">{question}</td>
			<td data-order="{date}">{date_display}</td>
			<td>{staff}</td>
			<td style="width:40%;text-overflow:ellipsis;">
				{skus}
			</td>
			<td>{search-key}</td>
			<td style="width:55px">
				<a data-condition="{can_view}" class='btn btn-info btn-xs' title="{{ trans('forms.View') }}" onclick="viewDocumentInModal({document_id})"><i class="fa fa-eye" aria-hidden="true"></i></a>
				<a data-condition="{can_edit}" class='btn btn-info btn-xs' title="{{ trans('forms.Update') }}" onclick="updateTradableFaqInModal({id})"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
			</td>
		</tr>
	</script>
@endif


@if ($controlSwitch['product-table'])
	<script type="text/javascript">
		$(document).ready(function() {
			$('#producttable').DataTable(Object.assign(
				dataTableDefaultConfig('{{ trans('forms.Refresh/Reload') }}', 'GET', '/dashboard/tradable/ajax', { }, '#producttable', '#product-template', '{{ trans('tool.Enter search keyword') }}', '{{ \App\Tradable::generateSearchTips("\\n") }}', {{ env('DATATABLE_DEFAULT_ROWS', 10) }}),
				{
					order : [[ 0, 'asc' ]],
					columnDefs : [{ orderable : false, targets : 5 }, { visible : false, targets : 4 }]
				}
			)).on('preXhr.dt', function(e, settings) {
				showDataTableProcessingTransient('#producttable');
			});
		} );
	</script>
@endif

@if ($controlSwitch['notice-table'])
	<script type="text/javascript">
		$(document).ready(function() {
			$('#updatetable').DataTable(Object.assign(
				dataTableDefaultConfig('{{ trans('forms.Refresh/Reload') }}', 'GET', '/dashboard/tradable-update/ajax', { }, '#updatetable', '#notice-template', '{{ trans('tool.Enter search keyword') }}', '', {{ env('DATATABLE_DEFAULT_ROWS', 10) }}),
				{
					order : [[ 1, 'desc' ]],
					columnDefs : [{ orderable : false, targets : 5 }, { visible : false, targets : 4 }]
				}
			)).on('preXhr.dt', function(e, settings) {
				showDataTableProcessingTransient('#updatetable');
			});
		} );
	</script>
@endif

@if ($controlSwitch['faq-table'])
	<script type="text/javascript">
		$(document).ready(function() {
			$('#faqtable').DataTable(Object.assign(
				dataTableDefaultConfig('{{ trans('forms.Refresh/Reload') }}', 'GET', '/dashboard/tradable-faq/ajax', { }, '#faqtable', '#faq-template', '{{ trans('tool.Enter search keyword') }}', '', {{ env('DATATABLE_DEFAULT_ROWS', 10) }}),
				{
					order : [[ 1, 'desc' ]],
					columnDefs : [{ orderable : false, targets : 5 }, { visible : false, targets : 4 }]
				}
			)).on('preXhr.dt', function(e, settings) {
				showDataTableProcessingTransient('#faqtable');
			});
		} );
	</script>
@endif

@if (false)
	<script type="text/javascript">
		$(document).ready(function() {
			$('#testtable').DataTable(Object.assign(
				dataTableDefaultConfig('{{ trans('forms.Refresh/Reload') }}', 'GET', 'url', { }, '#testtable', '#templateSelector', '{{ trans('tool.Enter search keyword') }}', '', {{ env('DATATABLE_DEFAULT_ROWS', 10) }}),
				{
					order : [[ 0, 'desc' ]],
					columnDefs : [{ orderable : false, targets : 4 }]
				}
			)).on('preXhr.dt', function(e, settings) {
				showDataTableProcessingTransient('#testtable');
			});
		} );
	</script>
@endif

@if ($controlSwitch['product-modal'])
	<script type="text/javascript">
		function refreshTableWithTradable(tradable, is_new) {
			let content = populateHtmlTemplateWithData($("#product-template").html().toString(), tradable);

			if (!is_new && $('#producttable').DataTable().row('#product-' + tradable['id']).length) {
				$('#producttable').DataTable().row('#product-' + tradable['id']).remove();
			}
			$('#producttable').DataTable().row.add($(content)).draw();
		}
	</script>
@endif

@if ($controlSwitch['product-notice-modal'])
	<script type="text/javascript">
		function refreshTableWithNotice(notice, is_new) {
			let content = populateHtmlTemplateWithData($("#notice-template").html().toString(), notice);

			if (!is_new && $('#updatetable').DataTable().row('#notice-' + notice['id']).length) {
				$('#updatetable').DataTable().row('#notice-' + notice['id']).remove();
			}
			$('#updatetable').DataTable().row.add($(content)).draw();
		}
	</script>
@endif

@if ($controlSwitch['product-faq-modal'])
	<script type="text/javascript">
		function refreshTableWithFaq(faq, is_new) {
			let content = populateHtmlTemplateWithData($("#faq-template").html().toString(), faq);

			if (!is_new && $('#faqtable').DataTable().row('#faq-' + faq['id']).length) {
				$('#faqtable').DataTable().row('#faq-' + faq['id']).remove();
			}
			$('#faqtable').DataTable().row.add($(content)).draw();
		}
	</script>
@endif

@if ($controlSwitch['document-modal'])
	<script type="text/javascript">
		function vueDocumentDataSource() {
			// function that holds global variables
		}

		$(document).ready(function() {
			// text to modal title
			vueDocumentDataSource.text_create_document = "{{ trans('document.New document') }}";
			vueDocumentDataSource.text_update_document = "{{ trans('document.Update document') }}";
			vueDocumentDataSource.text_view_document = "{{ trans('document.View document') }}";
			vueDocumentDataSource.text_delete_document = "{{ trans('document.Delete document') }}";
			// button of "Create"
			vueDocumentDataSource.button_create = "<i class=\"fa fa-btn fa-floppy-o\"></i>" + "{{ trans('forms.Create') }}";
			// button of "Update"
			vueDocumentDataSource.button_update = "<i class=\"fa fa-btn fa-pencil-square-o\"></i>" + "{{ trans('forms.Update') }}";
			// button of "Delete"
			vueDocumentDataSource.button_delete = "<i class=\"fa fa-btn fa-trash-o\"></i>" + "{{ trans('forms.Delete') }}";
		});
	</script>
@endif

@if ($controlSwitch['product-modal'])
	<script type="text/javascript">
		function vueTradableFormDataSource() {
			// function that holds variable
		}

		$(document).ready(function() {
			// text for 'create product'
			vueTradableFormDataSource.text_create_tradable = "{{ trans('product.New product') }}";
			// text for 'view product'
			vueTradableFormDataSource.text_view_tradable = "{{ trans('product.View product') }}";
			// text for 'update product'
			vueTradableFormDataSource.text_update_tradable = "{{ trans('product.Update product') }}";
			// button for 'create'
			vueTradableFormDataSource.button_create = "<i class=\"fa fa-btn fa-floppy-o\"></i>" + "{{ trans('forms.Create') }}";
			// button for 'update'
			vueTradableFormDataSource.button_update = "<i class=\"fa fa-btn fa-pencil-square-o\"></i>" + "{{ trans('forms.Update') }}";;
			// selection of suppliers
			vueTradableFormDataSource.selection_supplier = {!! json_encode(\App\TaxableEntity::getSuppliers('code', 'asc')->mapWithKeys(function ($item) { return [ $item->id => [ 'id' => $item->id, 'display' => $item->code . '  (' . $item->name . ')' ]]; })->toArray()) !!};
			// selection of inventory-account
			vueTradableFormDataSource.selection_account = {!! \App\ChartAccount::where([ 'active' => 1 ])->where('type', 'expense')->orderBy('account')->get()->mapWithKeys(function ($item) { return [ $item->id => [ 'id' => $item->id, 'account' => $item->account, 'description' => $item->description ]]; }); !!};
			// selection of manufacture origin
			vueTradableFormDataSource.selection_country = {!! json_encode(\App\Helpers\CountryHelper::getAllCountryOptions()) !!};
			// length-unit
			vueTradableFormDataSource.text_length_unit = "{{ \App\Measurement::where([['conversion_ratio', '=', '1'], ['active', '=', '1'], ['type', '=', 'length']])->first()['symbol'] }}";
			// weight-unit
			vueTradableFormDataSource.text_weight_unit = "{{ \App\Measurement::where([['conversion_ratio', '=', '1'], ['active', '=', '1'], ['type', '=', 'weight']])->first()['symbol'] }}";
			// callback for insert
			vueTradableFormDataSource.insertCallback = function(tradable) {
				refreshTableWithTradable(tradable, true);
			};
			// callback for update
			vueTradableFormDataSource.updateCallback = function(tradable) {
				refreshTableWithTradable(tradable, false);
			};
		});
	</script>
@endif

@if ($controlSwitch['product-notice-modal'])
	<script type="text/javascript">
		function vueTradableNoticeDataSource() {
			// function that holds variable
		}

		$(document).ready(function() {
			// text for 'create product'
			vueTradableNoticeDataSource.text_create_notice = "{{ trans('product.New notice') }}";
			// text for 'update product'
			vueTradableNoticeDataSource.text_update_notice = "{{ trans('product.Product update notice') }}";
			// text for 'browse file'
			vueTradableNoticeDataSource.text_browse_file = "{{ trans('tool.Browse file') }}";
			// button for 'create'
			vueTradableNoticeDataSource.button_create = "<i class=\"fa fa-btn fa-floppy-o\"></i>" + "{{ trans('forms.Create') }}";
			// button for 'update'
			vueTradableNoticeDataSource.button_update = "<i class=\"fa fa-btn fa-pencil-square-o\"></i>" + "{{ trans('forms.Update') }}";
			// selection of tradables
			vueTradableNoticeDataSource.selection_tradable = {!! json_encode(\App\Tradable::select('tradables.id', 'unique_tradables.sku', 'taxable_entities.code')->leftjoin('unique_tradables', 'tradables.unique_tradable_id', '=', 'unique_tradables.id')->leftjoin('taxable_entities', 'taxable_entities.id', '=', 'tradables.supplier_entity_id')->where('unique_tradables.stockable', '1')->orderBy('unique_tradables.sku', 'asc')->get()->toArray()) !!};
			// callback for insert
			vueTradableNoticeDataSource.insertCallback = function(notice) {
				refreshTableWithNotice(notice, true);
			};
			// callback for update
			vueTradableNoticeDataSource.updateCallback = function(notice) {
				refreshTableWithNotice(notice, false);
			};
		});
	</script>
@endif

@if ($controlSwitch['product-faq-modal'])
	<script type="text/javascript">
		function vueTradableFaqDataSource() {
			// function that holds variable
		}

		$(document).ready(function() {
			// text for 'create product'
			vueTradableFaqDataSource.text_create_faq = "{{ trans('product.New FAQ') }}";
			// text for 'update product'
			vueTradableFaqDataSource.text_update_faq = "{{ trans('product.Update FAQ') }}";
			// text for 'browse file'
			vueTradableFaqDataSource.text_browse_file = "{{ trans('tool.Browse file') }}";
			// button for 'create'
			vueTradableFaqDataSource.button_create = "<i class=\"fa fa-btn fa-floppy-o\"></i>" + "{{ trans('forms.Create') }}";
			// button for 'update'
			vueTradableFaqDataSource.button_update = "<i class=\"fa fa-btn fa-pencil-square-o\"></i>" + "{{ trans('forms.Update') }}";
			// selection of tradables
			vueTradableFaqDataSource.selection_tradable = {!! json_encode(\App\Tradable::select('tradables.id', 'unique_tradables.sku', 'taxable_entities.code')->leftjoin('unique_tradables', 'tradables.unique_tradable_id', '=', 'unique_tradables.id')->leftjoin('taxable_entities', 'taxable_entities.id', '=', 'tradables.supplier_entity_id')->where('unique_tradables.stockable', '1')->orderBy('unique_tradables.sku', 'asc')->get()->toArray()) !!};
			// callback for insert
			vueTradableFaqDataSource.insertCallback = function(faq) {
				refreshTableWithFaq(faq, true);
			};
			// callback for update
			vueTradableFaqDataSource.updateCallback = function(faq) {
				refreshTableWithFaq(faq, false);
			};
		});
	</script>
@endif

@if ($controlSwitch['product-analysis-modal'])
	<script type="text/javascript">
		function vueTradableAnalysisDataSource() {
			// function that holds variable
		}

		$(document).ready(function() {
			// text for 'create product'
			vueTradableAnalysisDataSource.text_analyze_product = "{{ trans('product.Analyze product') }}";
		});
	</script>
@endif

@if ($controlSwitch['product-modal'])
	<script type="text/javascript" src="{{ asset('js/embedded_modal/tradable_form.js') }}"></script>
@endif

@if ($controlSwitch['product-analysis-modal'])
	<script type="text/javascript" src="{{ asset('js/embedded_modal/tradable_analysis.js') }}"></script>
@endif

@if ($controlSwitch['document-modal'])
	<script type="text/javascript" src="{{ asset('js/embedded_modal/document_form.js') }}"></script>
@endif

@if ($controlSwitch['product-faq-modal'])
	<script type="text/javascript" src="{{ asset('js/embedded_modal/tradable_faq.js') }}"></script>
@endif

@if ($controlSwitch['product-notice-modal'])
	<script type="text/javascript" src="{{ asset('js/embedded_modal/tradable_notice.js') }}"></script>
@endif

@endsection
