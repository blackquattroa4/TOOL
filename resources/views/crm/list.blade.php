@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
		<!-- sales-quote form modal -->
	@if ($controlSwitch['sales-quote-modal'])
		@include(prioritizeCustomizedTemplateOverDefault('embedded_modal.sales_quote'))
	@endif

		<!-- sales-entry form modal -->
	@if ($controlSwitch['sales-order-modal'])
		@include(prioritizeCustomizedTemplateOverDefault('embedded_modal.sales_order'))
	@endif

		<!-- transactable form modal -->
	@if ($controlSwitch['transactable-modal'])
		@include(prioritizeCustomizedTemplateOverDefault('embedded_modal.transactable_form'))
	@endif

		<!-- warehouse-form modal -->
	@if ($controlSwitch['warehouse-order-modal'])
		@include(prioritizeCustomizedTemplateOverDefault('embedded_modal.pregenerated_warehouse_order'))
	@endif

		<!-- taxable-entity modal -->
	@if ($controlSwitch['entity-modal'])
		@include(prioritizeCustomizedTemplateOverDefault('embedded_modal.taxable_entity'))
	@endif

		<!-- tradable-transaction modal -->
	@if ($controlSwitch['tradable-transaction-modal'])
		@include(prioritizeCustomizedTemplateOverDefault('embedded_modal.tradable_transaction'))
	@endif

		<!-- charge-entry modal -->
	@if ($controlSwitch['charge-modal'])
		@include(prioritizeCustomizedTemplateOverDefault('embedded_modal.charge_entry'))
	@endif

	<!-- customer-stats modal -->
	@if ($controlSwitch['customer-stats-modal'])
		@include(prioritizeCustomizedTemplateOverDefault('embedded_modal.customer_stats'))
	@endif

		@if ($controlSwitch['customer-window'])
			<div id="customerwindow" class="panel panel-default">
				<div class="panel-heading">
					<table width='100%'>
						<tr>
							<td><h4>{{ trans('crm.Customer') }}</h4></td>
							<td align='right'>
							@if ($controlSwitch['create-customer-button'])
								<input type="button" class="btn btn-primary" onclick="createTaxableEntityInModal('customer')" value="{{ trans('crm.New customer') }}"/>
							@endif
							</td>
						</tr>
					</table>
				</div>

				<div class="panel-body">
					<table id="customertable" class="table table-striped table-bordered" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th>{{ trans('crm.Name') }}</th>
								<th>{{ trans('crm.Code') }}</th>
								<th>{{ trans('crm.Contact') }}</th>
								<th>{{ trans('crm.Region') }}</th>
								<th>{{ trans('crm.Outstanding order') }}</th>
								<th></th>
								<th></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>{{ trans('crm.Name') }}</th>
								<th>{{ trans('crm.Code') }}</th>
								<th>{{ trans('crm.Contact') }}</th>
								<th>{{ trans('crm.Region') }}</th>
								<th>{{ trans('crm.Outstanding order') }}</th>
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
								<td></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		@endif

		@if ($controlSwitch['quote-window'])
			<div id="quotewindow" class="panel panel-default">
				<div class="panel-heading">
					<table width='100%'>
						<tr>
							<td><h4>{{ trans('crm.Sales quote') }}</h4></td>
							<td align='right'>
								<!-- <input type="button" class="btn btn-primary" onclick="window.location.href='{{ url('/crm/createquote') }}'" value="{{ trans('crm.New quote') }}"/> -->
							@if ($controlSwitch['create-quote-button'])
								<button type="button" class="btn btn-primary" onclick="createSalesQuoteInModal()" >{{ trans('crm.New quote') }}</button>
							@endif
							</td>
						</tr>
					</table>
				</div>

				<div class="panel-body">
					<table id="quotetable" class="table table-striped table-bordered" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th>{{ trans('crm.Sales quote') }}</th>
								<th>{{ trans('crm.Customer') }}</th>
								<th>{{ trans('crm.Input date') }}</th>
								<th>{{ trans('crm.Contact') }}</th>
								<th>{{ trans('crm.Staff') }}</th>
								<th>{{ trans('crm.Items') }}</th>
								<th></th>
								<th></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>{{ trans('crm.Sales quote') }}</th>
								<th>{{ trans('crm.Customer') }}</th>
								<th>{{ trans('crm.Input date') }}</th>
								<th>{{ trans('crm.Contact') }}</th>
								<th>{{ trans('crm.Staff') }}</th>
								<th>{{ trans('crm.Items') }}</th>
								<th></th>
								<th></th>
							</tr>
						</tfoot>
						<tbody>
							<tr>
								<td data-order="000000"></td>
								<td></td>
								<td data-order="1970-01-01"></td>
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

		@if ($controlSwitch['order-window'])
			<div id="orderwindow" class="panel panel-default">
				<div class="panel-heading">
					<table width='100%'>
						<tr>
							<td><h4>{{ trans('crm.Sales order/return') }}</h4></td>
							<td align='right'>
								<!-- <input type="button" class="btn btn-primary" onclick="window.location.href='{{ url('/crm/createorderreturn') }}'" value="{{ trans('crm.New order/return') }}"/> -->
							@if ($controlSwitch['create-order-return-button'])
								<div class="btn-group">
									<a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#" >
										{{ trans('vrm.New entry') }}
										<span class="caret"></span>
									</a>
									<ul class="dropdown-menu">
									@if ($controlSwitch['create-order-button'])
								    <li>
											<a onclick="createSalesEntryInModal('order')">{{ trans('crm.New order')}}</a>
										</li>
									@endif
									@if ($controlSwitch['create-return-button'])
								    <li>
											<a onclick="createSalesEntryInModal('return')">{{ trans('crm.New return') }}</a>
										</li>
									@endif
									</ul>
								</div>
							@endif
							</td>
						</tr>
					</table>
				</div>

				<div class="panel-body">
					<table id="orderreturntable" class="table table-striped table-bordered" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th>{{ trans('crm.Sales order/return') }}</th>
								<th>{{ trans('crm.Customer') }}</th>
								<th>{{ trans('crm.Delivery date') }}</th>
								<th>{{ trans('crm.Staff') }}</th>
								<th>{{ trans('crm.Balance') }}</th>
								<th>{{ trans('crm.Total') }}</th>
								<th></th>
								<th></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>{{ trans('crm.Sales order/return') }}</th>
								<th>{{ trans('crm.Customer') }}</th>
								<th>{{ trans('crm.Delivery date') }}</th>
								<th>{{ trans('crm.Staff') }}</th>
								<th>{{ trans('crm.Balance') }}</th>
								<th>{{ trans('crm.Total') }}</th>
								<th></th>
								<th></th>
							</tr>
						</tfoot>
						<tbody>
							<tr>
								<td data-order="000000"></td>
								<td></td>
								<td data-order="1970-01-01"></td>
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

		@if ($controlSwitch['warehouse-order-window'])
			<div id="workorderwindow" class="panel panel-default">
				<div class="panel-heading">
					<table width='100%'>
						<tr>
							<td><h4>{{ trans('crm.Work order') }}</h4></td>
							<td align='right'><!-- <input type="button" class="btn btn-primary" onclick="window.location.href='{{ url('/crm/createworkorder') }}'" value="{{ trans('crm.New work order') }}"/> --></td>
						</tr>
					</table>
				</div>

				<div class="panel-body">
					<table id="workordertable" class="table table-striped table-bordered" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th>{{ trans('crm.Document') }}</th>
								<th>{{ trans('crm.Sales order') }}</th>
								<th>{{ trans('crm.Customer') }}</th>
								<th>{{ trans('crm.Operator') }}</th>
								<th>{{ trans('crm.Incur date') }}</th>
								<th>{{ trans('crm.Total') }}</th>
								<th></th>
								<th></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>{{ trans('crm.Document') }}</th>
								<th>{{ trans('crm.Sales order') }}</th>
								<th>{{ trans('crm.Customer') }}</th>
								<th>{{ trans('crm.Operator') }}</th>
								<th>{{ trans('crm.Incur date') }}</th>
								<th>{{ trans('crm.Total') }}</th>
								<th></th>
								<th></th>
							</tr>
						</tfoot>
						<tbody>
							<tr>
								<td data-order="000000"></td>
								<td></td>
								<td></td>
								<td></td>
								<td data-order="1970-01-01"></td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		@endif

		@if ($controlSwitch['transactable-window'])
			<div id="invoicewindow" class="panel panel-default">
				<div class="panel-heading">
					<table width='100%'>
						<tr>
							<td><h4>{{ trans('crm.Invoice/Credit') }}</h4></td>
							<td align='right'><!-- <input type="button" class="btn btn-primary" onclick="window.location.href='{{ url('/crm/createworkorder') }}'" value="{{ trans('crm.New invoice/credit') }}"/> --></td>
						</tr>
					</table>
				</div>

				<div class="panel-body">
					<table id="invoicetable" class="table table-striped table-bordered" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th>{{ trans('crm.Document') }}</th>
								<th>{{ trans('crm.Customer') }}</th>
								<th>{{ trans('crm.Incur date') }}</th>
								<th>{{ trans('crm.Due date') }}</th>
								<th>{{ trans('crm.Total') }}</th>
								<th>{{ trans('crm.Balance') }}</th>
								<th></th>
								<th></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>{{ trans('crm.Document') }}</th>
								<th>{{ trans('crm.Customer') }}</th>
								<th>{{ trans('crm.Incur date') }}</th>
								<th>{{ trans('crm.Due date') }}</th>
								<th>{{ trans('crm.Total') }}</th>
								<th>{{ trans('crm.Balance') }}</th>
								<th></th>
								<th></th>
							</tr>
						</tfoot>
						<tbody>
							<tr>
								<td data-order="000000"></td>
								<td></td>
								<td data-order="1970-01-01"></td>
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

		@if ($controlSwitch['chargeback-window'])
			<div id="chargebackwindow" class="panel panel-default">
				<div class="panel-heading">
					<table width='100%'>
						<tr>
							<td><h4>{{ trans('crm.Customer chargeback') }}</h4></td>
							<td align='right'>
							@if ($controlSwitch['create-charge-button'])
								<input type="button" class="btn btn-primary" onclick="createChargeEntryInModal()" value="{{ trans('crm.New chargeback') }}"/>
							@endif
							</td>
						</tr>
					</table>
				</div>

				<div class="panel-body">
					<table id="chargebacktable" class="table table-striped table-bordered" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th>{{ trans('crm.Document') }}</th>
								<th>{{ trans('crm.Customer') }}</th>
								<th>{{ trans('crm.Incur date') }}</th>
								<th>{{ trans('crm.Total') }}</th>
								<th>{{ trans('crm.Status') }}</th>
								<th></th>
								<th></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>{{ trans('crm.Document') }}</th>
								<th>{{ trans('crm.Customer') }}</th>
								<th>{{ trans('crm.Incur date') }}</th>
								<th>{{ trans('crm.Total') }}</th>
								<th>{{ trans('crm.Status') }}</th>
								<th></th>
								<th></th>
							</tr>
						</tfoot>
						<tbody>
							<tr>
								<td data-order="000000"></td>
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

		@if ($controlSwitch['inventory-window'])
			<div id="inventorywindow" class="panel panel-default">
				<div class="panel-heading">
					<table width='100%'>
						<tr>
							<td><h4>{{ trans('warehouse.Inventory') }}</h4></td>
							<td align='right'>
							</td>
						</tr>
					</table>
				</div>

				<div class="panel-body">
					<table id="inventorytable" class="table table-striped table-bordered" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th>{{ trans('forms.Location') }}</th>
								<th>{{ trans('forms.SKU') }}</th>
								<th>{{ trans('forms.Balance') }}</th>
								<th></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>{{ trans('forms.Location') }}</th>
								<th>{{ trans('forms.SKU') }}</th>
								<th>{{ trans('forms.Balance') }}</th>
								<th></th>
							</tr>
						</tfoot>
						<tbody>
							<tr>
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

		@if ($controlSwitch['report-window'])
			<div id="reportwindow" class="panel panel-default">
				<div class="panel-heading"><h4>{{ trans('messages.Report') }}</h4></div>

				<div class="panel-body">
				@if (true)
					<a class="btn btn-sq-lg btn-info" disabled><span class="fa fa-2x fa-question-circle"></span><br/>{!! str_replace(" ", "<br/>", trans("crm.View forecast")) !!}</a>
				@endif
				@if (true)
					<a class="btn btn-sq-lg btn-info" disabled><span class="fa fa-2x fa-question-circle"></span><br/>{!! str_replace(" ", "<br/>", trans("crm.Export data")) !!}</a>
				@endif
				</div>
			</div>
		@endif

		@if ($controlSwitch['tool-window'])
			<div id="toolwindow" class="panel panel-default">
				<div class="panel-heading"><h4>{{ trans('messages.Tool') }}</h4></div>

				<div class="panel-body">
				@if ($controlSwitch['sales-restriction-button'])
					<a class="btn btn-sq-lg btn-info" href="{{ url('/system/sales-restriction') }}"><span class="fa fa-2x fa-question-circle"></span><br/>{!! str_replace(" ", "<br/>", trans("crm.Manage restriction")) !!}</a>
				@endif
				@if (true)
					<a class="btn btn-sq-lg btn-info" disabled><span class="fa fa-2x fa-question-circle"></span><br/>{!! str_replace(" ", "<br/>", trans("crm.Forecast sales")) !!}</a>
				@endif
				</div>
			</div>
		@endif

		</div>
	</div>
</div>

<ul id="contextMenu" class="dropdown-menu" role="menu" style="display:none" >
	<li style="text-align:center;"><b>{{ trans('messages.Quick scroll menu') }}</b></li>
	<li class="divider"></li>
@if ($controlSwitch['customer-window'])
	<li><a tabindex="-1" onclick="$('html,body').animate({scrollTop:$('#customerwindow').offset().top}, 500);">{{ trans('crm.Customer') }}</a></li>
@endif
@if ($controlSwitch['quote-window'])
	<li><a tabindex="-1" onclick="$('html,body').animate({scrollTop:$('#quotewindow').offset().top}, 500);">{{ trans('crm.Sales quote') }}</a></li>
@endif
@if ($controlSwitch['order-window'])
	<li><a tabindex="-1" onclick="$('html,body').animate({scrollTop:$('#orderwindow').offset().top}, 500);">{{ trans('crm.Sales order/return') }}</a></li>
@endif
@if ($controlSwitch['warehouse-order-window'])
	<li><a tabindex="-1" onclick="$('html,body').animate({scrollTop:$('#workorderwindow').offset().top}, 500);">{{ trans('crm.Work order') }}</a></li>
@endif
@if ($controlSwitch['transactable-window'])
	<li><a tabindex="-1" onclick="$('html,body').animate({scrollTop:$('#invoicewindow').offset().top}, 500);">{{ trans('crm.Invoice/Credit') }}</a></li>
@endif
@if ($controlSwitch['chargeback-window'])
	<li><a tabindex="-1" onclick="$('html,body').animate({scrollTop:$('#chargebackwindow').offset().top}, 500);">{{ trans('crm.Customer chargeback') }}</a></li>
@endif
@if ($controlSwitch['inventory-window'])
	<li><a tabindex="-1" onclick="$('html,body').animate({scrollTop:$('#inventorywindow').offset().top}, 500);">{{ trans('warehouse.Inventory') }}</a></li>
@endif
@if ($controlSwitch['report-window'])
	<li><a tabindex="-1" onclick="$('html,body').animate({scrollTop:$('#reportwindow').offset().top}, 500);">{{ trans('messages.Report') }}</a></li>
@endif
@if ($controlSwitch['tool-window'])
	<li><a tabindex="-1" onclick="$('html,body').animate({scrollTop:$('#toolwindow').offset().top}, 500);">{{ trans('messages.Tool') }}</a></li>
@endif
	<li><a tabindex="-1" onclick="$('html,body').animate({scrollTop:$('.container').offset().top}, 500);">{{ trans('messages.Return to top') }}<i style="padding-left:1em;" class="fa fa-arrow-up" aria-hidden="true"></i></a></li>
</ul>
@endsection

@section('post-content')

@if (!App::environment('local'))
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js" charset="utf-8"></script>
@else
	<script type="text/javascript" src="{{ asset('external/ajax/libs/Chart.js/2.7.1/Chart.min.js') }}"></script>
@endif

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

	<script src="{{ asset('external/imagebox/js/jquery.imagemodal.js') }}"></script>

@if ($controlSwitch['entity-template'])
	<script id="entity-template" type="text/x-custom-template">
		<tr id="entity-{id}">
			<td><p style="padding-right:2em;display:inline;">{name}</p>
				<i data-condition="{is_active}" class="fa fa-heartbeat" aria-hidden="true" title="{{ trans('status.Active') }}"></i>
			</td>
			<td>{code}</td>
			<td>{contact}</td>
			<td>{region}</td>
			<td class="text-right">{outstanding}</td>
			<td>{search-key}</td>
			<td>
				<button data-condition="{can_view}" class='btn btn-info btn-xs' title="{{ trans('forms.View') }}" onclick="viewTaxableEntityInModal({id})"><i class="fa fa-eye" aria-hidden="true"></i></button>
				<button data-condition="{can_edit}" class='btn btn-info btn-xs' title="{{ trans('forms.Edit') }}" onclick="updateTaxableEntityInModal({id})"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
				<button data-condition="{can_view}" class='btn btn-info btn-xs' title="{{ trans('forms.View') }}" onclick="loadCustomerStats({id})"><i class="fa fa-line-chart" aria-hidden="true"></i></button>
			</td>
		</tr>
	</script>
@endif

@if ($controlSwitch['quote-template'])
	<script id="quote-template" type="text/x-custom-template">
		<tr id="quote-{id}">
			<td data-order="{increment}"><p style="padding-right:2em;display:inline;">{increment}</p>
				<i data-condition="{is_approved}" class="fa fa-check-square-o" aria-hidden="true" title="{{ trans('status.Approved') }}"></i>
				<i data-condition="{is_valid}" class="fa fa-check-circle" aria-hidden="true" title="{{ trans('status.Valid') }}"></i>
				<i data-condition="!{is_valid}" class="fa fa-times-circle" aria-hidden="true" title="{{ trans('status.Invalid') }}"></i>
			</td>
			<td>{customer}</td>
			<td data-order="{input_date}">{input_date_display}</td>
			<td>{contact}</td>
			<td>{staff}</td>
			<td class="text-right">{items}</td>
			<td>{search-key}</td>
			<td>
				<button data-condition="{can_view}" class='btn btn-info btn-xs' title="{{ trans('forms.View') }}" onclick="viewSalesQuoteInModal({id})"><i class="fa fa-eye" aria-hidden="true"></i></button>
				<button data-condition="{can_edit}" class='btn btn-info btn-xs' title="{{ trans('forms.Edit') }}" onclick="updateSalesQuoteInModal({id})"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
				<button data-condition="{need_approve}" class='btn btn-info btn-xs' title="{{ trans('forms.Approve') }}" onclick="approveSalesQuoteInModal({id})"><i class="fa fa-check-square-o" aria-hidden="true"></i></button>
			</td>
		</tr>
	</script>
@endif

@if ($controlSwitch['order-template'])
	<script id="order-template" type="text/x-custom-template">
		<tr id="sales-{id}">
			<td data-condition="'{reserved_receivable_title}' == ''" data-order="{increment}" id="reserve-order-{id}" >
				<p data-condition="'{type}' == 'order'" style="padding-right:1em;display:inline;">{increment}</p>
				<p data-condition="'{type}' == 'return'" style="padding-right:1em;display:inline;">R{increment}</p>
				<i data-condition="{is_approved}" class="fa fa-check-square-o" aria-hidden="true" title="{{ trans('status.Approved') }}"></i>
				<i data-condition="{is_prepared}" class="fa fa-thumbs-o-up" aria-hidden="true" title="{{ trans('status.Prepared') }}"></i>
				<i data-condition="{is_shipped}" class="fa fa-truck" aria-hidden="true" title="{{ trans('status.Shipped') }}"></i>
				<i data-condition="{is_closed}" class="fa fa-archive" aria-hidden="true" title="{{ trans('status.Closed') }}"></i>
			</td>
			<td data-condition="'{reserved_receivable_title}' != ''" data-order="{increment}" id="reserve-order-{id}" title="{{ trans('crm.Reserved receivable title') . " #" }} {reserved_receivable_title}" >
				<p data-condition="'{type}' == 'order'" style="padding-right:1em;display:inline;">{increment}</p>
				<p data-condition="'{type}' == 'return'" style="padding-right:1em;display:inline;">R{increment}</p>
				<i data-condition="{is_approved}" class="fa fa-check-square-o" aria-hidden="true" title="{{ trans('status.Approved') }}"></i>
				<i data-condition="{is_prepared}" class="fa fa-thumbs-o-up" aria-hidden="true" title="{{ trans('status.Prepared') }}"></i>
				<i data-condition="{is_shipped}" class="fa fa-truck" aria-hidden="true" title="{{ trans('status.Shipped') }}"></i>
				<i data-condition="{is_closed}" class="fa fa-archive" aria-hidden="true" title="{{ trans('status.Closed') }}"></i>
			</td>
			<td>{customer}</td>
			<td data-order="{delivery_date}">{delivery_date_display}</td>
			<td>{staff}</td>
			<td class="text-right" title="{balance}">{percent}</td>
			<td class="text-right">{total}</td>
			<td>{search-key}</td>
			<td>
				<button data-condition="{can_view}" class='btn btn-info btn-xs' title="{{ trans('forms.View') }}" onclick="viewSalesEntryInModal({id})"><i class="fa fa-eye" aria-hidden="true"></i></button>
				<button data-condition="{can_edit}" class='btn btn-info btn-xs' title="{{ trans('forms.Edit') }}" onclick="updateSalesEntryInModal({id})"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
				<button data-condition="{can_reserve}" id="reserve-order-{id}" title="{{ trans('crm.Reserve receivable title') }}" class='btn btn-info btn-xs' onclick="salesReservePostAjax({id})"><i class="fa fa-clipboard" aria-hidden="true"></i></button>
				<button data-condition="{need_approve}" class='btn btn-info btn-xs' title="{{ trans('forms.Approve') }}" onclick="approveSalesEntryInModal({id})"><i class="fa fa-check-square-o" aria-hidden="true"></i></button>
			</td>
		</tr>
	</script>
@endif

@if ($controlSwitch['warehouse-order-template'])
	<script id="warehouse-order-template" type="text/x-custom-template">
		<tr id="warehouse-{id}">
			<td data-order="{title}">{title}</td>
			<td>{source}</td>
			<td>{entity}</td>
			<td>{operator}</td>
			<td data-order="{delivery_date}">{delivery_date_display}</td>
			<td class="text-right">{items}</td>
			<td>{search-key}</td>
			<td>
				<button data-condition="{can_view}" class='btn btn-info btn-xs' title="{{ trans('forms.View') }}" onclick="viewWarehouseOrderInModal({id})"><i class="fa fa-eye" aria-hidden="true"></i></button>
			</td>
		</tr>
	</script>
@endif

@if ($controlSwitch['transactable-template'])
	<script id="transactable-template" type="text/x-custom-template">
		<tr id="transactable-{id}">
			<td data-order="{title}"><p style="padding-right:1em;display:inline;">{title}</p>
				<i data-condition="{is_closed}" class="fa fa-archive" aria-hidden="true" title="{{ trans('status.Closed') }}"></i>
				<i data-condition="{is_void}" class="fa fa-ban" aria-hidden="true" title="{{ trans('status.Void') }}"></i>
			</td>
			<td>{entity}</td>
			<td data-order="{incur_date}">{incur_date_display}</td>
			<td data-condition="{is_pastdue}" data-order="{due_date}" class="text-danger">{due_date_display}</td>
			<td data-condition="!{is_pastdue}" data-order="{due_date}" class="text-danger">{due_date_display}</td>
			<td class="text-right">{total}</td>
			<td data-condition="{is_pastdue}" class="text-right text-danger">{balance}</td>
			<td data-condition="!{is_pastdue}" class="text-right">{balance}</td>
			<td>{search-key}</td>
			<td>
				<button data-condition="{can_view}" class='btn btn-info btn-xs' title="{{ trans('forms.View') }}" onclick="viewTransactableEntryInModal({id})"><i class="fa fa-eye" aria-hidden="true"></i></button>
			</td>
		</tr>
	</script>
@endif

@if ($controlSwitch['charge-template'])
	<script id="charge-template" type="text/x-custom-template">
		<tr id="charge-{id}">
			<td data-order="{title}">{title}</td>
			<td>{entity}</td>
			<td data-order="{incur_date}">{incur_date_display}</td>
			<td class="text-right">{total}</td>
			<td>{status}</td>
			<td>{search-key}</td>
			<td>
				<button data-condition="{can_view}" class='btn btn-info btn-xs' title="{{ trans('forms.View') }}" onclick="viewChargeEntryInModal({id})"><i class="fa fa-eye" aria-hidden="true"></i></button>
				<button data-condition="{can_edit}" class='btn btn-info btn-xs' title="{{ trans('forms.Edit') }}" onclick="updateChargeEntryInModal({id})"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
				<button data-condition="{can_submit}" class='btn btn-info btn-xs' title="{{ trans('forms.Submit') }}" onclick="submitChargeEntryInModal({id})"><i class="fa fa-file-text" aria-hidden="true"></i></button>
				<button data-condition="{can_retract}" class='btn btn-info btn-xs' title="{{ trans('forms.Retract') }}" onclick="retractChargeEntryInModal({id})"><i class="fa fa-times" aria-hidden="true"></i></button>
				<button data-condition="{can_approve}" class='btn btn-info btn-xs' title="{{ trans('forms.Approve') }}" onclick="approveChargeEntryInModal({id})"><i class="fa fa-check-square-o" aria-hidden="true"></i></button>
			</td>
		</tr>
	</script>
@endif

@if ($controlSwitch['inventory-template'])
	<script id="inventory-template" type="text/x-custom-template">
		<tr id="tradable-{location_id}-{sku_id}">
			<td>{location}</td>
			<td>{sku}</td>
			<td class="text-right">{balance}</td>
			<td>
				<button data-condition="{can_view}" class='btn btn-info btn-xs' title="{{ trans('forms.View') }}" onclick="viewTradableTransactionsInModal({location_id}, {entity_id}, {sku_id})"><i class="fa fa-eye" aria-hidden="true"></i></button>
			</td>
		</tr>
	</script>
@endif

@if ($controlSwitch['customer-window'])
	<script type="text/javascript">
		$(document).ready(function() {
			$('#customertable').DataTable(Object.assign(
				dataTableDefaultConfig('{{ trans('forms.Refresh/Reload') }}', 'GET', '/dashboard/entity/customer/ajax', { }, '#customertable', '#entity-template', '{{ trans('tool.Enter search keyword') }}', '{{ \App\TaxableEntity::generateSearchTips("\\n") }}', {{ env('DATATABLE_DEFAULT_ROWS', 10) }}),
				{
					order : [[ 0, 'asc' ]],
					columnDefs : [{ orderable : false, targets : 6 }, { visible : false, targets : 5 }]
				}
			)).on('preXhr.dt', function(e, settings) {
				showDataTableProcessingTransient('#customertable');
			});
		} );
	</script>
@endif

@if ($controlSwitch['quote-window'])
	<script type="text/javascript">
		$(document).ready(function() {
			$('#quotetable').DataTable(Object.assign(
				dataTableDefaultConfig('{{ trans('forms.Refresh/Reload') }}', 'GET', '/dashboard/sales-quote/ajax', { }, '#quotetable', '#quote-template', '{{ trans('tool.Enter search keyword') }}', '{{ \App\SalesHeader::generateSearchTips("\\n") }}', {{ env('DATATABLE_DEFAULT_ROWS', 10) }}),
				{
					order : [[ 2, 'desc' ]],
					columnDefs : [{ orderable : false, targets : 7 }, { visible : false, targets : 6 }]
				}
			)).on('preXhr.dt', function(e, settings) {
				showDataTableProcessingTransient('#quotetable');
			});
		} );
	</script>
@endif

@if ($controlSwitch['order-window'])
	<script type="text/javascript">
		$(document).ready(function() {
			$('#orderreturntable').DataTable(Object.assign(
				dataTableDefaultConfig('{{ trans('forms.Refresh/Reload') }}', 'GET', '/dashboard/sales-order/ajax', { }, '#orderreturntable', '#order-template', '{{ trans('tool.Enter search keyword') }}', '{{ \App\SalesHeader::generateSearchTips("\\n") }}', {{ env('DATATABLE_DEFAULT_ROWS', 10) }}),
				{
					order : [[ 2, 'desc' ]],
					columnDefs : [{ orderable : false, targets : 7 }, { visible : false, targets : 6 }]
				}
			)).on('preXhr.dt', function(e, settings) {
				showDataTableProcessingTransient('#orderreturntable');
			});
		} );
	</script>
@endif

@if ($controlSwitch['warehouse-order-window'])
	<script type="text/javascript">
		$(document).ready(function() {
			$('#workordertable').DataTable(Object.assign(
				dataTableDefaultConfig('{{ trans('forms.Refresh/Reload') }}', 'GET', '/dashboard/warehouse-order/from-sales/ajax', { }, '#workordertable', '#warehouse-order-template', '{{ trans('tool.Enter search keyword') }}', '{{ \App\WarehouseHeader::generateSearchTips("\\n") }}', {{ env('DATATABLE_DEFAULT_ROWS', 10) }}),
				{
					order : [[ 0, 'desc' ]],
					columnDefs : [{ orderable : false, targets : 7 }, { visible : false, targets : 6 }]
				}
			)).on('preXhr.dt', function(e, settings) {
				showDataTableProcessingTransient('#workordertable');
			});
		} );
	</script>
@endif

@if ($controlSwitch['transactable-window'])
	<script type="text/javascript">
		$(document).ready(function() {
			$('#invoicetable').DataTable(Object.assign(
				dataTableDefaultConfig('{{ trans('forms.Refresh/Reload') }}', 'GET', '/dashboard/transactable/receivable/ajax', { }, '#invoicetable', '#transactable-template', '{{ trans('tool.Enter search keyword') }}', '{{ \App\TransactableHeader::generateSearchTips("\\n") }}', {{ env('DATATABLE_DEFAULT_ROWS', 10) }}),
				{
					order : [[ 2, 'desc' ]],
					columnDefs : [{ orderable : false, targets : 7 }, { visible : false, targets : 6 }]
				}
			)).on('preXhr.dt', function(e, settings) {
				showDataTableProcessingTransient('#invoicetable');
			});
		} );
	</script>
@endif

@if ($controlSwitch['chargeback-window'])
	<script type="text/javascript">
		$(document).ready(function() {
			$('#chargebacktable').DataTable(Object.assign(
				dataTableDefaultConfig('{{ trans('forms.Refresh/Reload') }}', 'GET', '/dashboard/expense/customer/ajax', { }, '#chargebacktable', '#charge-template', '{{ trans('tool.Enter search keyword') }}', '{{ \App\ExpenseHeader::generateSearchTips("\\n") }}', {{ env('DATATABLE_DEFAULT_ROWS', 10) }}),
				{
					order : [[ 2, 'desc' ]],
					columnDefs : [{ orderable : false, targets : 6 }, { visible : false, targets : 5 }]
				}
			)).on('preXhr.dt', function(e, settings) {
				showDataTableProcessingTransient('#chargebacktable');
			});
		} );
	</script>
@endif

@if ($controlSwitch['inventory-window'])
	<script type="text/javascript">
		$(document).ready(function() {
			$('#inventorytable').DataTable(Object.assign(
				dataTableDefaultConfig('{{ trans('forms.Refresh/Reload') }}', 'GET', '/dashboard/inventory/ajax', { }, '#inventorytable', '#inventory-template', '{{ trans('tool.Enter search keyword') }}', '', {{ env('DATATABLE_DEFAULT_ROWS', 10) }}),
				{
					order : [[ 1, 'desc' ]],
					columnDefs : [{ orderable : false, targets : 3 }]
				}
			)).on('preXhr.dt', function(e, settings) {
				showDataTableProcessingTransient('#inventorytable');
			});
		} );
	</script>
@endif

@if (false)
	<script type="text/javascript">
		$(document).ready(function() {
			$('#allocationtable').DataTable(Object.assign(
				dataTableDefaultConfig('{{ trans('forms.Refresh/Reload') }}', 'GET', 'url', { }, '#allocationtable', '#templateSelector', '{{ trans('tool.Enter search keyword') }}', '', {{ env('DATATABLE_DEFAULT_ROWS', 10) }}),
				{
					order : [[ 0, 'desc' ]],
					columnDefs : []
				}
			)).on('preXhr.dt', function(e, settings) {
				showDataTableProcessingTransient('#allocationtable');
			});
		} );
	</script>
@endif

@if ($controlSwitch['quote-template'])
	<script type="text/javascript">
		function refreshTableWithSalesQuote(quote, is_new) {
			let content = populateHtmlTemplateWithData($('#quote-template').html().toString(), quote);
			if (!is_new && $('#quotetable').DataTable().row('#quote-' + quote['id']).length) {
				$('#quotetable').DataTable().row('#quote-' + quote['id']).remove();
			}
			$('#quotetable').DataTable().row.add($(content)).draw();
		}
	</script>
@endif

@if ($controlSwitch['order-template'])
	<script type="text/javascript">
		function refreshTableWithSalesEntry(entry, is_new) {
			let content = populateHtmlTemplateWithData($('#order-template').html().toString(), entry);
			if (!is_new && $('#orderreturntable').DataTable().row('#sales-' + entry['id']).length) {
				$('#orderreturntable').DataTable().row('#sales-' + entry['id']).remove();
			}
			$('#orderreturntable').DataTable().row.add($(content)).draw();
		}
	</script>
@endif

@if ($controlSwitch['entity-template'])
	<script type="text/javascript">
		function refreshTableWithEntity(entity, is_new) {
			let content = populateHtmlTemplateWithData($('#entity-template').html().toString(), entity);

			if (!is_new && $('#customertable').DataTable().row('#entity-' + entity['id']).length) {
				$('#customertable').DataTable().row('#entity-' + entity['id']).remove();
			}
			$('#customertable').DataTable().row.add($(content)).draw();
		}
	</script>
@endif

@if ($controlSwitch['charge-template'])
	<script type="text/javascript">
		function refreshTableWithCharge(charge, is_new) {
			let content = populateHtmlTemplateWithData($('#charge-template').html().toString(), charge);
			if (!is_new && $('#chargebacktable').DataTable().row('#charge-' + charge['id']).length) {
				$('#chargebacktable').DataTable().row('#charge-' + charge['id']).remove();
			}
			$('#chargebacktable').DataTable().row.add($(content)).draw();
		}
	</script>
@endif

@if ($controlSwitch['sales-quote-modal'])
	<script type="text/javascript">
		function vueSalesQuoteDataSource() {
			// function that holds data
		}

		$(document).ready(function() {
			// current user id
			vueSalesQuoteDataSource.current_user_id = {{ auth()->user()->id }};
			// display text of 'View PDF'
			vueSalesQuoteDataSource.button_pdf = "<i class=\"fa fa-btn fa-file-pdf-o\"></i>" + "{{ trans('forms.View PDF') }}";
			// display text of 'Create'
			vueSalesQuoteDataSource.button_submit = "<i class=\"fa fa-btn fa-file-text\"></i>" + "{{ trans('forms.Submit') }}";
			// display text of 'Update'
			vueSalesQuoteDataSource.button_update = "<i class=\"fa fa-btn fa-pencil-square-o\"></i>" + "{{ trans('forms.Update') }}";
			// display text of 'Approve'
			vueSalesQuoteDataSource.button_approve = "<i class=\"fa fa-btn fa-check-square-o\"></i>" + "{{ trans('forms.Approve') }}";
			// display text of 'Reject'
			vueSalesQuoteDataSource.button_reject = "<i class=\"fa fa-btn fa-thumbs-o-down\"></i>" + "{{ trans('forms.Disapprove') }}";
			// display text of 'Void'
			vueSalesQuoteDataSource.button_void = "<i class=\"fa fa-btn fa-ban\"></i>" + "{{ trans('forms.Void') }}";
			// selection of entity
			vueSalesQuoteDataSource.selection_entity = {!! json_encode(\App\TaxableEntity::getCustomersWithProduct('code', 'asc')->mapWithKeys(function ($item) { $fmtr = $item->currency->getFormat(true); return [ $item->id => [ 'display' => $item->code . '  (' . $item->name . ')', 'currency' => $item->currency_id, 'payment' => $item->payment_term_id, 'contact' => $item->contact->last()->id, 'regex' => $fmtr['regex'], 'symbol' => $fmtr['symbol'], 'fdigit' => $fmtr['fdigit'], 'min' => $fmtr['min'], 'icon' => $fmtr['icon'] ] ]; })->toArray()) !!};
			// selection of payment
			vueSalesQuoteDataSource.selection_payment = {!! json_encode(\App\PaymentTerm::orderBy('grace_days', 'asc')->pluck('symbol', 'id')->toArray()) !!};
			// selection of contact
			vueSalesQuoteDataSource.selection_contact = {!! json_encode(\App\User::getUsersIndexedByEntity(['supplier','customer','employee'])) !!};
			// selection of staff
			vueSalesQuoteDataSource.selection_staff = {!! json_encode(\App\User::getAllStaff('name', 'asc')->pluck('name', 'id')->toArray()) !!};
			// selection of product
			vueSalesQuoteDataSource.selection_product = {!! json_encode(\App\UniqueTradable::getUniqueTradableIndexedByCustomerEntity()) !!};
			// selection of currency
			vueSalesQuoteDataSource.selection_currency = {!! json_encode(\App\Currency::orderBy('symbol', 'asc')->get()->mapWithKeys(function ($item) { return [ $item->id => array_merge($item->getFormat(true), [ 'description' => $item->description ]) ]; })->toArray()) !!};
			// display text of 'View PDF'
			vueSalesQuoteDataSource.text_new_entry = "{{ trans('crm.New entry') }}";
			// text of today's date
			vueSalesQuoteDataSource.text_today = "{{ \App\Helpers\DateHelper::dbToGuiDate(date("Y-m-d")) }}";
			// text of 'Create sales quote'
			vueSalesQuoteDataSource.text_create_sales_quote = "{{ trans('crm.New quote') }}";
			// text of 'View sales quote'
			vueSalesQuoteDataSource.text_view_sales_quote = "{{ trans('crm.View quote') }}";
			// text of 'Update sales quote'
			vueSalesQuoteDataSource.text_update_sales_quote = "{{ trans('crm.Update quote') }}";
			// text of 'Approve sales quote'
			vueSalesQuoteDataSource.text_approve_sales_quote = "{{ trans('crm.Approve quote') }}";
			// callback function when new order is submitted
			vueSalesQuoteDataSource.insertCallback = function(data) {
				refreshTableWithSalesQuote(data, true);
			};
			// callback function when new order is updated
			vueSalesQuoteDataSource.updateCallback = function(data) {
				refreshTableWithSalesQuote(data, false);
			}
		});
	</script>
@endif

@if ($controlSwitch['sales-order-modal'])
	<script type="text/javascript">
		function vueSalesEntryDataSource() {
			// function that holds data
		}

		$(document).ready(function() {
			// current user id
			vueSalesEntryDataSource.current_user_id = {{ auth()->user()->id }};
			// display text of 'View PDF'
			vueSalesEntryDataSource.button_pdf = "<i class=\"fa fa-btn fa-file-pdf-o\"></i>" + "{{ trans('forms.View PDF') }}";
			// display text of 'Create'
			vueSalesEntryDataSource.button_submit = "<i class=\"fa fa-btn fa-file-text\"></i>" + "{{ trans('forms.Submit') }}";
			// display text of 'Update'
			vueSalesEntryDataSource.button_update = "<i class=\"fa fa-btn fa-pencil-square-o\"></i>" + "{{ trans('forms.Update') }}";
			// display text of 'Approve'
			vueSalesEntryDataSource.button_approve = "<i class=\"fa fa-btn fa-check-square-o\"></i>" + "{{ trans('forms.Approve') }}";
			// display text of 'Reject'
			vueSalesEntryDataSource.button_reject = "<i class=\"fa fa-btn fa-thumbs-o-down\"></i>" + "{{ trans('forms.Disapprove') }}";
			// display text of 'Void'
			vueSalesEntryDataSource.button_void = "<i class=\"fa fa-btn fa-ban\"></i>" + "{{ trans('forms.Void') }}";
			// selection of entity
			vueSalesEntryDataSource.selection_entity = {!! json_encode(\App\TaxableEntity::getCustomersWithProduct('code', 'asc')->mapWithKeys(function ($item) { $fmtr = $item->currency->getFormat(true); return [ $item->id => [ 'display' => $item->code . '  (' . $item->name . ')', 'currency' => $item->currency_id, 'payment' => $item->payment_term_id, 'regex' => $fmtr['regex'], 'symbol' => $fmtr['symbol'], 'fdigit' => $fmtr['fdigit'], 'min' => $fmtr['min'], 'icon' => $fmtr['icon'] ] ]; })->toArray()) !!};
			// selection of payment
			vueSalesEntryDataSource.selection_payment = {!! json_encode(\App\PaymentTerm::orderBy('grace_days', 'asc')->pluck('symbol', 'id')->toArray()) !!};
			// selection of contact
			vueSalesEntryDataSource.selection_contact = {!! json_encode(\App\User::getUsersIndexedByEntity(['supplier','customer','employee'])) !!};
			// selection of staff
			vueSalesEntryDataSource.selection_staff = {!! json_encode(\App\User::getAllStaff('name', 'asc')->pluck('name', 'id')->toArray()) !!};
			// selection of product
			vueSalesEntryDataSource.selection_product = {!! json_encode(\App\UniqueTradable::getUniqueTradableIndexedByCustomerEntity()) !!};
			// selection of warehouse
			vueSalesEntryDataSource.selection_warehouse = {!! json_encode(\App\Location::getWarehouses('name', 'asc')->toArray()) !!};
			// selection of currency
			vueSalesEntryDataSource.selection_currency = {!! json_encode(\App\Currency::orderBy('symbol', 'asc')->get()->mapWithKeys(function ($item) { return [ $item->id => $item->getFormat(true) ]; })->toArray()) !!};
			// selection of billing address
			vueSalesEntryDataSource.selection_billing = {!! json_encode(\App\Address::getAddressesIndexedByEntity(['supplier','customer','employee'], 'billing')) !!};
			// selection of shipping address
			vueSalesEntryDataSource.selection_shipping = {!! json_encode(\App\Address::getAddressesIndexedByEntity(['supplier','customer','employee'], 'shipping')) !!};
			// display text of 'View PDF'
			vueSalesEntryDataSource.text_new_entry = "{{ trans('crm.New entry') }}";
			// text of today's date
			vueSalesEntryDataSource.text_today = "{{ \App\Helpers\DateHelper::dbToGuiDate(date("Y-m-d")) }}";
			// text of 'Create sales order'
			vueSalesEntryDataSource.text_create_sales_order = "{{ trans('crm.New order') }}";
			// text of 'Create sales return'
			vueSalesEntryDataSource.text_create_sales_return = "{{ trans('crm.New return') }}";
			// text of 'View sales order'
			vueSalesEntryDataSource.text_view_sales_order = "{{ trans('crm.View order') }}";
			// text of 'View salee return'
			vueSalesEntryDataSource.text_view_sales_return = "{{ trans('crm.View return') }}";
			// text of 'Update sales order'
			vueSalesEntryDataSource.text_update_sales_order = "{{ trans('crm.Update order') }}";
			// text of 'Update sales return'
			vueSalesEntryDataSource.text_update_sales_return = "{{ trans('crm.Update return') }}";
			// text of 'Approve sales order'
			vueSalesEntryDataSource.text_approve_sales_order = "{{ trans('crm.Approve order') }}";
			// text of 'Approve sales return'
			vueSalesEntryDataSource.text_approve_sales_return = "{{ trans('crm.Approve return') }}";
			// callback function when new order is submitted
			vueSalesEntryDataSource.insertCallback = function(data) {
				refreshTableWithSalesEntry(data, true);
			};
			// callback function when new order is updated
			vueSalesEntryDataSource.updateCallback = function(data) {
				refreshTableWithSalesEntry(data, false);
			}
		});
	</script>
@endif

@if ($controlSwitch['transactable-modal'])
	<script type="text/javascript">
		function vueTransactableDataSource() {
			// function that holds data
		}

		$(document).ready(function() {
			// selection of client entities
			vueTransactableDataSource.selection_receivable = {!! json_encode(\App\TaxableEntity::getReceivableEntities('code', 'asc')->mapWithKeys(function ($item) { return [ $item->id => $item->code . '  (' . $item->name . ')' ]; })->toArray()) !!};
			// selection of supplying entities
			vueTransactableDataSource.selection_payable = {!! json_encode(\App\TaxableEntity::getPayableEntities('code', 'asc')->mapWithKeys(function ($item) { return [ $item->id => $item->code . '  (' . $item->name . ')' ]; })->toArray()) !!};
			// selection of payment terms
			vueTransactableDataSource.selection_payment = {!! json_encode(\App\PaymentTerm::orderBy('grace_days', 'asc')->pluck('symbol', 'id')->toArray()) !!};
			// selection of contact
			vueTransactableDataSource.selection_contact = {!! json_encode(\App\User::getUsersIndexedByEntity(['supplier', 'customer'])) !!};
			// selection of staff
			vueTransactableDataSource.selection_staff = {!! json_encode(\App\User::getAllStaff('name', 'asc')->pluck('name', 'id')->toArray()) !!};
			// selection of currency
			vueTransactableDataSource.selection_currency = {!! json_encode(\App\Currency::orderBy('symbol', 'asc')->pluck('symbol', 'id')->toArray()) !!};
			// selection of product
			vueTransactableDataSource.selection_item = {!! json_encode(\App\UniqueTradable::orderBy('sku', 'asc')->get()->pluck('sku', 'id')->toArray()) !!};
			// selection of billing-address
			vueTransactableDataSource.selection_billing = {!! json_encode(\App\Address::getAddressesIndexedByEntity(['supplier','customer', 'employee'], 'billing')) !!};
			// selection of shipping-address
			vueTransactableDataSource.selection_shipping = {!! json_encode(\App\Address::getAddressesIndexedByEntity(['supplier','customer', 'employee'], 'shipping')) !!};
			// button of 'View'
			vueTransactableDataSource.button_view = "<i class=\"fa fa-btn fa-file-pdf-o\"></i>" + "{{ trans('forms.View PDF') }}";
			// button of 'Void'
			vueTransactableDataSource.button_void = "<i class=\"fa fa-btn fa-ban\"></i>" + "{{ trans('forms.Void') }}";
			// text of 'View receivable'
			vueTransactableDataSource.text_view_receivable = "{{ trans('finance.View receivable') }}";
			// text of 'View payable'
			vueTransactableDataSource.text_view_payable = "{{ trans('finance.View payable') }}";
			// text of 'View receivable'
			vueTransactableDataSource.text_void_receivable = "{{ trans('finance.Void receivable') }}";
			// text of 'View payable'
			vueTransactableDataSource.text_void_payable = "{{ trans('finance.Void payable') }}";
			// callback function when new order is submitted
			// vueTransactableDataSource.insertCallback = null;
			// callback function when order is updated
			// vueTransactableDataSource.updateCallback = null;
		});
	</script>
@endif

@if ($controlSwitch['warehouse-order-modal'])
	<script type="text/javascript">
		function vueWarehouseOrderDataSource() {
			// function that holds data
		}

		$(document).ready(function() {
			// text for 'View order'
			vueWarehouseOrderDataSource.text_view_warehouse_order = "{{ trans('warehouse.View order') }}";
			// text for 'Process order'
			vueWarehouseOrderDataSource.text_process_warehouse_order = "{{ trans('warehouse.Process order') }}";
			// text for 'View PDF' button
			vueWarehouseOrderDataSource.button_pdf = "<i class=\"fa fa-btn fa-file-pdf-o\"></i>" + "{{ trans('forms.View PDF') }}";
			// text for 'Update' button
			vueWarehouseOrderDataSource.button_update = "<i class=\"fa fa-btn fa-pencil-square-o\"></i>" + "{{ trans('forms.Update') }}";
			// selection of shipping address
			vueWarehouseOrderDataSource.selection_shipping = {!! json_encode(\App\Address::getAddressesIndexedByEntity(['supplier','customer','employee','self'], 'shipping')) !!};
			// selection of staff
			vueWarehouseOrderDataSource.selection_staff = {!! json_encode(\App\User::getAllStaff('name', 'asc')->pluck('name', 'id')->toArray()) !!};
			// selection of warehouse
			vueWarehouseOrderDataSource.selection_warehouse = {!! json_encode(\App\Location::getWarehouses('name', 'asc')->toArray()) !!};
			// selection of entity
			vueWarehouseOrderDataSource.selection_entity = {!! json_encode(\App\TaxableEntity::orderBy('code', 'asc')->get()->mapWithKeys(function ($item) { $fmtr = $item->currency->getFormat(true); return [ $item->id => [ 'display' => $item->code . '  (' . $item->name . ')', 'currency' => $item->currency_id, 'payment' => $item->payment_term_id, 'regex' => $fmtr['regex'], 'symbol' => $fmtr['symbol'], 'fdigit' => $fmtr['fdigit'], 'min' => $fmtr['min'] ] ]; })->toArray()) !!};
			// selection of product
			vueWarehouseOrderDataSource.selection_product = {!! json_encode(\App\UniqueTradable::getProducts('sku', 'asc')->mapWithKeys(function ($item) { return [ $item->id => [ 'sku' => $item->sku, 'description' => $item->description ]]; })) !!};
			// callback function when order is updated
			// vueWarehouseOrderDataSource.updateCallback = null;
		});
	</script>
@endif

@if ($controlSwitch['entity-modal'])
	<script type="text/javascript">
		function vueTaxableEntityDataSource() {
			// function that holds data
		}

		$(document).ready(function() {
			// text for button 'create'
			vueTaxableEntityDataSource.button_create = "<i class=\"fa fa-btn fa-floppy-o\"></i>" + "{{ trans('forms.Create') }}";
			// text for button 'update'
			vueTaxableEntityDataSource.button_update = "<i class=\"fa fa-btn fa-pencil-square-o\"></i>" + "{{ trans('forms.Update') }}";
			// selection of country location
			vueTaxableEntityDataSource.selection_country = {!! json_encode(\App\Helpers\CountryHelper::getAllCountryOptions()) !!};
			// selection of payment term
			vueTaxableEntityDataSource.selection_payment = {!! json_encode(\App\PaymentTerm::orderBy('grace_days', 'asc')->get()->mapWithKeys(function ($item) { return [ $item->id => [ 'symbol' => $item->symbol, 'description' => $item->description ]]; })->toArray()) !!};
			// selection of currency
			vueTaxableEntityDataSource.selection_currency =  {!! json_encode(\App\Currency::orderBy('symbol', 'asc')->get()->mapWithKeys(function ($item) { return [$item->id => [ 'symbol' => $item->symbol, 'description' => $item->description ]]; })->toArray()) !!};
			// text of 'Create customer'
			vueTaxableEntityDataSource.text_create_customer = "{{ trans('crm.New customer') }}";
			// text of 'View customer'
			vueTaxableEntityDataSource.text_view_customer = "{{ trans('crm.View customer') }}";
			// text of 'Update customer'
			vueTaxableEntityDataSource.text_update_customer = "{{ trans('crm.Update customer') }}";
			// callback function when new entity is entered
			vueTaxableEntityDataSource.insertCallback = function(data) {
				refreshTableWithEntity(data, true);
			};
			// callback function when existing entity is updated
			vueTaxableEntityDataSource.updateCallback = function(data) {
				refreshTableWithEntity(data, false);
			};
		});
	</script>
@endif

@if ($controlSwitch['charge-modal'])
	<script type="text/javascript">
		function vueChargeDataSource() {
			// function that holds global variables
		}

		$(document).ready(function() {
			// text of date of today
			vueChargeDataSource.text_today = "{{ \App\Helpers\DateHelper::dbToGuiDate(date("Y-m-d")) }}";
			// default taxable entity id
			vueChargeDataSource.id_default_entity = {{ auth()->user()->entity->id }};
			// current user id
			vueChargeDataSource.id_current_user = {{ auth()->user()->id }};
			// text of 'browse file'
			vueChargeDataSource.text_browse_file = "{{ trans('tool.Browse file') }}";
			// text of 'attachment download failed'
			vueChargeDataSource.text_attachment_download_failed = "{{ trans('messages.Attachment download failed') }}";
			// text of 'upload file'
			vueChargeDataSource.text_upload_file = "{{ trans('tool.Upload file') }}";
			// text of 'view file'
			vueChargeDataSource.text_view_file = "{{ trans('tool.View file') }}";
			// text of 'new charge'
			vueChargeDataSource.text_new_charge = "{{ trans('finance.New charge') }}";
			// text of 'view charge'
			vueChargeDataSource.text_view_charge = "{{ trans('finance.View charge') }}";
			// text of 'update charge'
			vueChargeDataSource.text_update_charge = "{{ trans('finance.Update charge') }}";
			// text of 'submit charge'
			vueChargeDataSource.text_submit_charge = "{{ trans('finance.Submit charge') }}";
			// text of 'retract charge'
			vueChargeDataSource.text_retract_charge = "{{ trans('finance.Retract charge') }}";
			// text of 'approve charge'
			vueChargeDataSource.text_approve_charge = "{{ trans('finance.Approve charge') }}";
			// button of 'New charge'
			vueChargeDataSource.button_create = "<i class=\"fa fa-btn fa-floppy-o\"></i>" + "{{ trans('forms.Create') }}";
			// button of 'Update charge'
			vueChargeDataSource.button_update = "<i class=\"fa fa-btn fa-pencil-square-o\"></i>" + "{{ trans('forms.Update') }}";
			// button of 'Submit charge'
			vueChargeDataSource.button_submit = "<i class=\"fa fa-btn fa-file-text\"></i>" + "{{ trans('forms.Submit') }}";
			// button of 'Retract charge'
			vueChargeDataSource.button_retract = "<i class=\"fa fa-btn fa-times\"></i>" + "{{ trans('forms.Retract') }}";
			// button of 'Approve charge'
			vueChargeDataSource.button_approve = "<i class=\"fa fa-btn fa-check-square-o\"></i>" + "{{ trans('forms.Approve') }}";
			// button of 'disapprove charge'
			vueChargeDataSource.button_reject = "<i class=\"fa fa-btn fa-thumbs-o-down\"></i>" + "{{ trans('forms.Disapprove') }}";
			// selection of entity
			vueChargeDataSource.selection_entity = {!! json_encode(\App\TaxableEntity::getActiveCustomers('code', 'asc')->mapWithKeys(function ($item) { return [ $item->id => $item->code . '  (' . $item->name . ')' ]; })->toArray()) !!};
			// selection of staff
			vueChargeDataSource.selection_staff = {!! json_encode(\App\User::getActiveStaff('name', 'asc')->pluck('name', 'id')); !!};
			// selection of currency
			vueChargeDataSource.selection_currency = {!! json_encode(\App\Currency::getActiveCurrencies('symbol', 'asc')->mapWithKeys(function ($item) { return [ $item->id => $item->symbol . '  (' . $item->description . ')' ]; })->toArray()); !!};
			// selection of expenditure item
			vueChargeDataSource.selection_item = {!! json_encode(\App\UniqueTradable::getActiveExpenditures('sku', 'asc')->pluck('sku', 'id')) !!};
			// callback function after update
			vueChargeDataSource.updateCallback = function (charge) {
				refreshTableWithCharge(charge, false);
			};
			// callback function after insert
			vueChargeDataSource.insertCallback = function (charge) {
				refreshTableWithCharge(charge, true);
			};
		});
	</script>
@endif

@if ($controlSwitch['tradable-transaction-modal'])
	<script type="text/javascript">
		function vueTradableTransactionDataSource() {
			// function that holds global variables
		}

		$(document).ready(function() {
			// text for button 'create'
			vueTradableTransactionDataSource.button_load_more_transaction = "{{ trans('forms.Load more transactions') }}";
		});
	</script>
@endif

@if ($controlSwitch['customer-stats-modal'])
	<script type="text/javascript">
		function vueCustomerStatsDataSource() {
			// function that holds global variables
		}

		$(document).ready(function() {
			// text
			vueCustomerStatsDataSource.text_performance = "{{ trans('finance.Performance') }}";
			vueCustomerStatsDataSource.text_month = "{{ trans('tool.month') }}";
			vueCustomerStatsDataSource.text_amount = "{{ trans('finance.Amount') }}";
			vueCustomerStatsDataSource.text_runrate = "{{ trans('finance.Runrate') }}";
			vueCustomerStatsDataSource.text_volume = "{{ trans('finance.Volume') }}";
			vueCustomerStatsDataSource.text_chart_error = "{{ trans('crm.Performance statistics cannot be generated') }}";
		});
	</script>
@endif

@if ($controlSwitch['sales-quote-modal'])
	<script type="text/javascript" src="{{ asset('js/embedded_modal/sales_quote.js') }}"></script>
@endif

@if ($controlSwitch['sales-order-modal'])
	<script type="text/javascript" src="{{ asset('js/embedded_modal/sales_order.js') }}"></script>
@endif

@if ($controlSwitch['transactable-modal'])
	<script type="text/javascript" src="{{ asset('js/embedded_modal/transactable_form.js') }}"></script>
@endif

@if ($controlSwitch['warehouse-order-modal'])
	<script type="text/javascript" src="{{ asset('js/embedded_modal/pregenerated_warehouse_order.js') }}"></script>
@endif

@if ($controlSwitch['entity-modal'])
	<script type="text/javascript" src="{{ asset('js/embedded_modal/taxable_entity.js') }}"></script>
@endif

@if ($controlSwitch['tradable-transaction-modal'])
	<script type="text/javascript" src="{{ asset('js/embedded_modal/tradable_transaction.js') }}"></script>
@endif

@if ($controlSwitch['charge-modal'])
	<script type="text/javascript" src="{{ asset('js/embedded_modal/charge_entry.js') }}"></script>
@endif

@if ($controlSwitch['customer-stats-modal'])
	<script type="text/javascript" src="{{ asset('js/embedded_modal/customer_stats.js') }}"></script>
@endif

@endsection
