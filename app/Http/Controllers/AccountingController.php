<?php

namespace App\Http\Controllers;

use App;
use App\ChartAccount;
use App\Currency;
use App\ExpenseHeader;
use App\Helpers\DateHelper;
use App\Helpers\HistoryHelper;
use App\Helpers\InventoryHelper;
use App\Http\Requests;
use App\InventoryPdf;
use App\Location;
use App\Parameter;
use App\PurchaseHeader;
use App\SalesHeader;
use App\TaxableEntity;
use App\Tradable;
use App\TradableTransaction;
use App\TransactableHeader;
use App\UniqueTradable;
use App\User;
use Auth;
use DB;
use NumberFormatter;
use Illuminate\Http\Request;

class AccountingController extends Controller
{
	public function index()
	{
		$switch = [
			// modal related
			'transactable-modal' => auth()->user()->can(['ar-view', 'ar-edit', 'rar-view', 'rar-edit', 'ap-view', 'ap-edit', 'rap-view', 'rap-edit']),
			'purchase-process-modal' => auth()->user()->can(['ap-create', 'rap-create']),
			'sales-process-modal' => auth()->user()->can(['ar-create', 'rar-create']),
			'taxable-entity-modal' => auth()->user()->can(['customer-create', 'customer-edit', 'supplier-create', 'supplier-edit']),
			'taccount-transaction-modal' => auth()->user()->can(['acct-view']),
			'taccount-reconciliation-modal' => auth()->user()->can(['ar-process', 'ap-process', 'rar-process', 'rap-process']),
			'tradable-transaction-modal' => auth()->user()->can(['wo-create', 'wo-edit', 'wo-view', 'wo-process']),
			'charge-entry-modal' => auth()->user()->can(['ex-create', 'ex-edit', 'ex-view']),
			'income-receipt-modal' => auth()->user()->can(['ar-process', 'rar-process']),
			'payment-disbursement-modal' => auth()->user()->can(['ap-process', 'rap-process']),
			'taccount-form-modal' => auth()->user()->can(['acct-view', 'acct-edit']),
			'receivable-table' => auth()->user()->can(['ar-list', 'rar-list']),
			'create-customer-button' => auth()->user()->can('customer-create'),
			'customer-table' => auth()->user()->can('customer-list'),
			'payable-table' => auth()->user()->can(['ap-list', 'rap-list']),
			'create-supplier-button' => auth()->user()->can('supplier-create'),
			'supplier-table' => auth()->user()->can('supplier-list'),
			'create-expense-button' => auth()->user()->can('ex-create'),
			'expense-table' => auth()->user()->can('ex-list'),
			'inventory-table' => auth()->user()->can('wo-list'),
			'taccount-table' => auth()->user()->can('acct-list'),

			// other actions
			'customer-upsert' => auth()->user()->can(['customer-create', 'customer-edit']),
			'supplier-upsert' => auth()->user()->can(['supplier-create', 'supplier-edit']),

			'inventory-report' => auth()->user()->can(['wo-list', 'wo-view'], true),

			'adjust-inventory-button' => auth()->user()->can(['ex-create', 'ar-create'], true),
			'create-account-button' => auth()->user()->can('acct-edit'),  // this covers create/edit
			'adjust-consignment-button' => auth()->user()->can(['ar-create', 'rar-create', 'ap-create', 'rap-create', 'ex-create']),
			'adjust-cogs-button' => auth()->user()->can(['ex-create', 'ar-create'], true),
		];

		$level2Switch = [
			'receivable-window' => $switch['sales-process-modal'] || $switch['receivable-table'],
			'customer-window' => $switch['create-customer-button'] || $switch['customer-table'],
			'payable-window' => $switch['purchase-process-modal'] || $switch['payable-table'],
			'supplier-window' => $switch['create-supplier-button'] || $switch['supplier-table'],
			'expense-window' => $switch['create-expense-button'] || $switch['expense-table'],
			'inventory-window' => $switch['inventory-table'],
			'taccount-window' => $switch['taccount-table'],
			'report-window' => $switch['inventory-report'],
			'tool-window' => $switch['adjust-inventory-button'] || $switch['create-expense-button'],

			'entity-template' => $switch['customer-table'] || $switch['customer-upsert'] || $switch['supplier-table'] || $switch['supplier-upsert'],
			'transactable-template' => $switch['receivable-table'] || $switch['sales-process-modal'] || $switch['payable-table'] || $switch['purchase-process-modal'],
			'expense-template' => $switch['expense-table'] || $switch['expense-upsert'],
			'inventory-entry-template' => $switch['inventory-table'],
			'taccount-template' => $switch['taccount-table'] || $switch['create-account-button'],
		];

		return view()->first(generateTemplateCandidates('accounting.list'), [
				'controlSwitch' => array_merge($switch, $level2Switch),
			]);
	}

	public function printInventory(Request $request)
	{
		// file-download related controller does not register with session-history
		$this->removeFromHistory();

		$endDate = DateHelper::guiToDbDate($request->input('date'));

		$result = InventoryHelper::getAccountingInventory($endDate, null, TaxableEntity::theCompany());

		foreach ($result as $idx => $content) {
			$result[$idx]['balance'] = sprintf(env('APP_QUANTITY_FORMAT'), $result[$idx]['balance']);
		}

		$pdf = new InventoryPdf([
				'title' => "Inventory as of " . $endDate,
				'data' => $result,
			]);

		$pdf->Output("Inventory as of ".$endDate.".pdf", "D");
	}

	public function adjustConsignment()
	{
		$locations = Location::getActiveWarehouses('name', 'asc');
		$suppliers = TaxableEntity::getActiveSuppliers('name', 'asc');
		$products = Tradable::getCurrentProducts('sku', 'asc');
		$currency = TaxableEntity::theCompany()->currency->getFormat(true);

		return view()->first(generateTemplateCandidates('form.inventory_quantity'), [
				'locations' => $locations,
				'suppliers' => $suppliers,
				'products' => $products,
				'currency' => $currency,
			]);
	}

	public function adjustConsignmentPost(Request $request)
	{
		$supplierId = $request->input('entity');
		$locationId = $request->input('location');
		$date = DateHelper::guiToDbDate($request->input('recorddate'));
		$notes = $request->input('notes');

		try {
			DB::transaction(function() use ($request, $locationId, $supplierId, $notes) {
				foreach ($request->input('quantity') as $tradableId => $quantity) {
					if ($quantity != "0") {
						$transaction = TradableTransaction::create([
								'unique_tradable_id' => Tradable::find($tradableId)->unique_tradable_id,
								'location_id' => $locationId,
								'owner_entity_id' => $supplierId,
								'quantity' => $quantity,
								'unit_cost' => $request->input('cost')[$tradableId],
								'src_table' => '',
								'src_id' => 0,
								'valid' => 1,
								'notes' => $notes,
								'created_at' => $date . ' 00:00:00',
							]);
						event(new \App\Events\InventoryUpdateEvent($transaction));
					}
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', trans('accounting.Consignment inventory adjusted'));
	}

	public function viewTradableTransactions(Request $request, $location, $entity, $sku)
	{
		return view()->first(generateTemplateCandidates('accounting.tradable_transactions'), [
							'locations' => Location::getActiveWarehouses('name', 'asc'),
							'entities' => InventoryHelper::getInventoryOwners(),
							'skus' => UniqueTradable::getProducts('sku', 'asc'),
							'selected_location' => $location,
							'selected_entity' => $entity,
							'selected_sku' => $sku
						]);
	}

	public function getDashboardInventoryAjax(Request $request)
	{
		$canView = auth()->user()->can('wo-view');

		$inventory = array_map(function($item) use ($canView) {
				$item['balance'] = sprintf(env('APP_QUANTITY_FORMAT'), $item['balance']);
				$item['can_view'] = $canView;
				return $item;
			},
			InventoryHelper::getAccountingInventory(date("Y-m-d"), null, TaxableEntity::theCompany()));

		return response()->json([ 'success' => true, 'data' => $inventory ]);
	}

	public function viewTradableTransactionsAjax(Request $request)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		$locationId = $request->input('location');
		$entityId = $request->input('owner');
		$skuId = $request->input('sku');
		$offset = $request->input('offset');
		$count = $request->input('count');

		$result = array();

		foreach (DB::select("select * from (select (@id := @id + 1) as idx, t0.id, t0.notes, transactable_headers.title as source, transactable_headers.src_table as src_table, transactable_headers.src_id as src_id, t0.quantity, t0.created_at, (@sum := @sum + t0.quantity) as sum from tradable_transactions t0 left join transactable_details on transactable_details.id = t0.src_id left join transactable_headers on transactable_details.transactable_header_id = transactable_headers.id cross join (select @sum := 0) table1 cross join (select @id := 0) table2 where t0.valid = 1 and t0.location_id=$locationId and t0.owner_entity_id=$entityId and t0.unique_tradable_id=$skuId order by t0.created_at) as t1 order by t1.idx desc limit $count offset $offset") as $transaction) {
			$source = "";
			switch ($transaction->src_table) {
				case 'sales_headers':
					$salesHeader = SalesHeader::find($transaction->src_id);
					switch ($salesHeader['type']) {
						case 'order':
							$source = " (SO#" . $salesHeader['title'] . ")";
							break;
						case 'return':
							$source = " (SR#" . $salesHeader['title'] . ")";
							break;
					}
					break;
				case 'purchase_headers':
					$purchaseHeader = PurchaseHeader::find($transaction->src_id);
					switch ($purchaseHeader['type']) {
						case 'order':
							$source = " (PO#" . $purchaseHeader['title'] . ")";
							break;
						case 'return':
							$source = " (PR#" . $purchaseHeader['title'] . ")";
							break;
					}
					break;
			}
			$result[] = [
					'id' => $transaction->id,
					'date' => $transaction->created_at,
					'quantity' => sprintf(env('APP_QUANTITY_FORMAT'), $transaction->quantity),
					'source' => $transaction->source . $source,
					'notes' => $transaction->notes,
					'balance' => $transaction->sum,
				];
		}
		return json_encode($result);
	}

	public function getInventoryAging(Request $request)
	{
		// flus end date; no need to check error-redirect since there's no validation
		$request->session()->flashInput([
			'enddate' => date('m/d/Y'),
		]);

		return view()->first(generateTemplateCandidates('accounting.aging_inventory'));
	}

	public function getInventoryAgingAjax(Request $request)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		$endDate = DateHelper::guiToDbDate($request->input('date'));

		$result = [];

		// pull inventory data
		foreach (DB::select("select locations.id as location_id, locations.name, unique_tradables.id as sku_id, unique_tradables.sku, sum(quantity) as quantity, sum(quantity*unit_cost) as amount from tradable_transactions left join locations on locations.id = tradable_transactions.location_id left join unique_tradables on unique_tradables.id = tradable_transactions.unique_tradable_id where tradable_transactions.valid = 1 and tradable_transactions.owner_entity_id and tradable_transactions.created_at < '" . $endDate . " 23:59:59' group by locations.id, locations.name, sku_id, unique_tradables.sku") as $record) {
			if (!isset($result['location-'.$record->location_id])) {
				$result['location-'.$record->location_id] = [
						'title' => $record->name,
						'items' => [],
					];
			}
			$age = UniqueTradable::find($record->sku_id)->getAging($record->quantity, $record->location_id, $endDate, TaxableEntity::theCompany()->id);
			$result['location-'.$record->location_id]['items'][] = [
							'title' => $record->sku,
							'quantity' => sprintf(env("APP_QUANTITY_FORMAT"), $record->quantity),
							'days' => $age['days'],
							'slug' => str_slug('location-'.$record->location_id.'-'.$record->sku),
							'batches' => $age['batches'],
						];

		}

		return json_encode($result);
	}

}
