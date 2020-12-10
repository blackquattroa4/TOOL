<?php

namespace App\Http\Controllers;

use App;
use App\Address;
use App\ChartAccount;
use App\Currency;
use App\ExpenseHeader;
use App\Helpers\InventoryHelper;
use App\Http\Requests;
use App\Http\Resources\PurchaseHeader as PurchaseHeaderResource;
use App\Location;
use App\Parameter;
use App\PaymentTerm;
use App\PurchaseDetail;
use App\PurchaseHistory;
use App\PurchaseHeader;
use App\TaxableEntity;
use App\User;
use App\WarehouseHeader;
use Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use DB;
use NumberFormatter;
use Validator;

class VRMController extends Controller
{
	public function index()
	{

		$switch = [
			'purchase-order-modal' => auth()->user()->can(['po-create', 'po-edit', 'po-view', 'pr-create', 'pr-edit', 'pr-view']),
			'purchase-quote-modal' => auth()->user()->can(['pq-create', 'pq-edit', 'pq-view']),
			'warehouse-order-modal' => auth()->user()->can(['wo-create', 'wo-edit', 'wo-view']),
			'entity-modal' => auth()->user()->can(['supplier-create', 'supplier-edit']),
			'warehouse-transaction-modal' => auth()->user()->can(['wo-view']),
			'charge-modal' => auth()->user()->can(['ex-create', 'ex-edit', 'ex-view', 'ex-process']),
			'supplier-stats-modal' => auth()->user()->can('supplier-report'),

			'supplier-table' => auth()->user()->can('supplier-list'),
			'quote-table' => auth()->user()->can('pq-list'),
			'order-table' => auth()->user()->can(['po-list', 'pr-list']),
			'inbound-table' => auth()->user()->can('wo-list'),
			'outbound-table' => auth()->user()->can('wo-list'),
			'charge-table' => auth()->user()->can('ex-list'),
			'inventory-table' => auth()->user()->can('wo-list'),

			'create-supplier-button' => auth()->user()->can('supplier-create'),
			'create-quote-button' => auth()->user()->can('pq-create'),
			'create-order-button' => auth()->user()->can('po-create'),
			'create-return-button' => auth()->user()->can('pr-create'),
			'create-booking-button' => auth()->user()->can('wo-create'),
			'create-picking-button' => auth()->user()->can('wo-create'),
			'create-charge-button' => auth()->user()->can('ex-create'),
			'data-export-button' => auth()->user()->can(['po-list', 'pr-list'], true),
			'inventory-alert-button' => auth()->user()->can(['po-list', 'pr-list'], true),
		];

		$level2Switch = [
			'create-order-return-button' => $switch['create-order-button'] || $switch['create-return-button'],

			'supplier-window' => $switch['supplier-table'] || $switch['create-supplier-button'],
			'quote-window' => $switch['quote-table'] || $switch['create-quote-button'],
			'order-window' => $switch['order-table'] || $switch['create-order-return-button'],
			'inbound-booking-window' => $switch['inbound-table'] || $switch['create-booking-button'],
			'outbound-booking-window' => $switch['outbound-table'] || $switch['create-picking-button'],
			'charge-window' => $switch['charge-table'] || $switch['create-charge-button'],
			'inventory-window' => $switch['inventory-table'],
			'report-window' => $switch['data-export-button'],
			'tool-window' => $switch['inventory-alert-button'],

			'entity-template' => $switch['supplier-table'] || $switch['entity-modal'],
			'quote-template' => $switch['quote-table'] || $switch['purchase-quote-modal'],
			'order-template' => $switch['order-table'] || $switch['purchase-order-modal'],
			'warehouse-entry-template' => $switch['inbound-table'] || $switch['outbound-table'] || $switch['warehouse-order-modal'],
			'charge-template' => $switch['charge-table'] || $switch['charge-modal'],
			'inventory-template' => $switch['inventory-table'],
		];

		return view()->first(generateTemplateCandidates('vrm.list'), [
				'controlSwitch' => array_merge($switch, $level2Switch)
			]);

	}

	// get purchase quote
	public function getDashboardQuoteAjax(Request $request)
	{
		$quotes = PurchaseHeader::where('type', 'quote')->get();

		return response()->json([ 'success' => true, 'data' => PurchaseHeaderResource::collection($quotes) ]);
	}

	// get purchase order/return
	public function getDashboardOrderAjax(Request $request)
	{
		$types = [ ];
		if (auth()->user()->can('po-list')) { array_push($types, "order"); }
		if (auth()->user()->can('pr-list')) { array_push($types, "return"); }
		$orders = PurchaseHeader::whereIn('type', $types)->get();
		return response()->json([ 'success' => true, 'data' => PurchaseHeaderResource::collection($orders) ]);
	}

	// get charge/expense from supplier only
	public function getDashboardChargeAjax(Request $request)
	{
		$expenses = ExpenseHeader::select('expense_headers.*')
					->join('taxable_entities', 'taxable_entities.id', '=', 'expense_headers.entity_id')
					->where('taxable_entities.type', 'supplier')->get();

		return response()->json([ 'success' => true, 'data' => ExpenseHeaderResource::collection($expenses) ]);
	}
}
