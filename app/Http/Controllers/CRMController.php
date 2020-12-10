<?php

namespace App\Http\Controllers;

use App;
use App\Address;
use App\ChartAccount;
use App\ExpenseHeader;
use App\Helpers\InventoryHelper;
use App\Helpers\ParameterHelper;
use App\Http\Requests;
use App\Http\Resources\SalesHeader as SalesHeaderResource;
use App\RmaHeader;
use App\SalesHeader;
use App\TaxableEntity;
use App\TransactableHeader;
use App\WarehouseHeader;
use App\User;
use Auth;
use DB;
use NumberFormatter;
use Validator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;

class CRMController extends Controller
{
	public function index()
	{
		$switch = [
			'sales-order-modal' => auth()->user()->can(['so-create', 'so-edit', 'so-view', 'sr-create', 'sr-edit', 'sr-view']),
			'sales-quote-modal' => auth()->user()->can(['sq-create', 'sq-edit', 'sq-view']),
			'warehouse-order-modal' => auth()->user()->can(['wo-create', 'wo-edit', 'wo-view']),
			'entity-modal' => auth()->user()->can(['customer-create', 'customer-edit']),
			'tradable-transaction-modal' => auth()->user()->can(['wo-create', 'wo-edit', 'wo-view', 'wo-process']),
			'charge-modal' => auth()->user()->can(['ex-create', 'ex-edit', 'ex-view', 'ex-process']),
			'transactable-modal' => auth()->user()->can(['ar-view', 'rar-view']),
			'rma-modal' => auth()->user()->can(['rma-view', 'rma-create', 'rma-edit', 'rma-process']),
			'customer-stats-modal' => auth()->user()->can('customer-report'),

			'customer-table' => auth()->user()->can('customer-list'),
			'quote-table' => auth()->user()->can('sq-list'),
			'order-table' => auth()->user()->can(['so-list', 'sr-list']),
			'outbound-table' => auth()->user()->can('wo-list'),
			'charge-table' => auth()->user()->can('ex-list'),
			'transactable-table' => auth()->user()->can(['ar-list', 'rar-list']),
			'rma-table' => auth()->user()->can('rma-list'),
			'inventory-table' => auth()->user()->can('wo-list'),

			'create-customer-button' => auth()->user()->can('customer-create'),
			'create-quote-button' => auth()->user()->can('sq-create'),
			'create-order-button' => auth()->user()->can('so-create'),
			'create-return-button' => auth()->user()->can('sr-create'),
			'create-charge-button' => auth()->user()->can('ex-create'),
			'create-rma-button' => auth()->user()->can('rma-create'),
			'data-export-button' => auth()->user()->can(['so-list', 'sr-list'], true),
			'sales-restriction-button' => auth()->user()->can(['so-list', 'so-create', 'so-edit', 'so-view'], true),
		];

		$level2Switch = [
			'create-order-return-button' => $switch['create-order-button'] || $switch['create-return-button'],

			'customer-window' => $switch['customer-table'] || $switch['create-customer-button'],
			'quote-window' => $switch['quote-table'] || $switch['create-quote-button'],
			'order-window' => $switch['order-table'] || $switch['create-order-return-button'],
			'transactable-window' => $switch['transactable-table'],
			// 'inbound-booking-window' => $switch['inbound-table'] || $switch['create-booking-button'],
			'warehouse-order-window' => $switch['outbound-table'] || $switch['create-picking-button'],
			'chargeback-window' => $switch['charge-table'] || $switch['create-charge-button'],
			'inventory-window' => $switch['inventory-table'],
			'rma-window' => $switch['rma-table'] || $switch['create-rma-button'],
			'report-window' => $switch['data-export-button'],
			'tool-window' => $switch['sales-restriction-button'],

			'entity-template' => $switch['customer-table'] || $switch['entity-modal'],
			'quote-template' => $switch['quote-table'] || $switch['sales-quote-modal'],
			'order-template' => $switch['order-table'] || $switch['sales-order-modal'],
			'transactable-template' => $switch['transactable-table'],
			'warehouse-order-template' => $switch['outbound-table'] || $switch['warehouse-order-modal'],
			'charge-template' => $switch['charge-table'] || $switch['charge-modal'],
			'inventory-template' => $switch['inventory-table'],
			'rma-template' => $switch['rma-table'] || $switch['create-rma-button'],
		];

		return view()->first(generateTemplateCandidates('crm.list'), [
				'controlSwitch' => array_merge($switch, $level2Switch)
			]);

	}

	public function reserveTransactableTitle($id, Request $request)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		$salesObj = SalesHeader::find($id);
		if (empty($salesObj->reserved_receivable_title))
		{
			try {
				$title = ParameterHelper::getNextSequence('transaction_number');
				DB::transaction(function() use ($salesObj, $title) {
					$salesObj->update([
							'reserved_receivable_title' => $title,
						]);
				});
			} catch (\Exception $e) {
				$registration = recordAndReportProblem($e);
				return response()->json(['success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
			}
		}

		return response()->json(['success' => true, 'data' => new SalesHeaderResource($salesObj) ]);
	}

	// get sales quote
	public function getDashboardQuoteAjax(Request $request)
	{
		$quotes = SalesHeader::where('type', 'quote')->get();

		return response()->json([ 'success' => true, 'data' => SalesHeaderResource::collection($quotes) ]);
	}

	// get sales order/return
	public function getDashboardOrderAjax(Request $request)
	{
		$types = [ ];
		if (auth()->user()->can('so-list')) { array_push($types, "order"); }
		if (auth()->user()->can('sr-list')) { array_push($types, "return"); }
		$orders = SalesHeader::whereIn('type', $types)->get();
		return response()->json([ 'success' => true, 'data' => SalesHeaderResource::collection($orders) ]);
	}

	// get charge/expense from customer only
	public function getDashboardChargeAjax(Request $request)
	{
		$expenses = ExpenseHeader::select('expense_headers.*')
					->join('taxable_entities', 'taxable_entities.id', '=', 'expense_headers.entity_id')
					->where('taxable_entities.type', 'customer')->get();

		return response()->json([ 'success' => true, 'data' => ExpenseHeaderResource::collection($expenses) ]);
	}

}
