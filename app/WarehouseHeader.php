<?php

namespace App;

use App\Helpers\DateHelper;
use App\Helpers\ParameterHelper;
use Auth;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class WarehouseHeader extends Model
{

	public static $document_title = null;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'title', 'type', 'palletized', 'internal_contact_id', 'shipping_location_id', 'reference', 'external_entity_id', 'external_address_id', 'status', 'via', 'order_date','src', 'src_id', 'notes', 'internal_notes',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

	CONST NOT_CLOSED_STATUS = [ 'open' ];

	// extending default Laravel model constructor
	public function __construct(array $attributes = array())
	{
		parent::__construct($attributes);

		if (!self::$document_title) {
			self::$document_title = [
				"receive" => trans('warehouse.Incoming order'),
				"relocate" => trans('warehouse.Relocate order'),
				"transfer" => trans('warehouse.Transfer order'),
				"deliver" => trans('warehouse.Outgoing order'),
			];
		}
	}

	protected static function boot()
  {
    parent::boot();

		// scope objects are in designated-period or objects are not-closed
		if ($timeWindow = env('TIME_WINDOW')) {
	    static::addGlobalScope('currentFiscal', function (Builder $builder) use ($timeWindow) {
	      $builder->where(function ($query) use ($timeWindow) {
					if ($timeWindow == 'current') {
						$query->orWhere('warehouse_headers.order_date', '>=', date("Y-01-01"))
									->orWhereIn('warehouse_headers.status', static::NOT_CLOSED_STATUS);
					} else if (strtotime(date("Y-m-01") . " " . env('TIME_WINDOW'))) {
						$query->orWhere('warehouse_headers.order_date', '>=', date("Y-m-d", strtotime(date("Y-m-01") . " " . $timeWindow)))
									->orWhereIn('warehouse_headers.status', static::NOT_CLOSED_STATUS);
					}
				});
	    });
		}
  }

	/*
	 * flash(pre-fill) old input into option array.  This is for create function.
	 */
	public static function generateEmptyInput($entity)
	{
		return [
			'increment' => '????',
			'reference' => '',
			'process_date' => DateHelper::dbToGuiDate(date("Y-m-d")),
			'staff' => Auth::user()->name,
			'via' => '',
			'location' => Location::getActiveWarehouses('name', 'asc')->first()->id,
			'entity' => $entity->code . ' - ' . $entity->name,
			'address' => $entity->defaultShippingAddress[0]->id,
			'product_id' => [ ],
			'sku' => [ ],
			'description' => [ ],
			'quantity' => [ ],
			'bin' => [ ],
		];
	}

	/*
	 * flash(pre-fill) old input into option array.  This is for update/view/approve/process function.
	 */
	public function generateArrayForOldInput()
	{
	}

	public function isNotFromPurchase()
	{
		return ($this->src != 'purchase');
	}

	public function isFromPurchase()
	{
		return ($this->src == 'purchase');
	}

	public function isNotFromSales()
	{
		return ($this->src != 'sales');
	}

	public function isFromSales()
	{
		return ($this->src == 'sales');
	}

	public function isNotReceive()
	{
		return ($this->type != 'receive');
	}

	public function isReceive()
	{
		return ($this->type == 'receive');
	}

	public function isNotRelocate()
	{
		return ($this->type != 'relocate');
	}

	public function isRelocate()
	{
		return ($this->type == 'relocate');
	}

	public function isNotTransfer()
	{
		return ($this->type != 'transfer');
	}

	public function isTransfer()
	{
		return ($this->type == 'transfer');
	}

	public function isNotDeliver()
	{
		return ($this->type != 'deliver');
	}

	public function isDeliver()
	{
		return ($this->type == 'deliver');
	}

	public function isOpen()
	{
		return ($this->status == 'open');
	}

	public function isNotOpen()
	{
		return in_array($this->status, ['void', 'closed']);
	}

	public function externalEntity()
	{
		return $this->belongsTo('\App\TaxableEntity', 'external_entity_id');
	}

	public function staff()
	{
		return $this->belongsTo('\App\User', 'internal_contact_id');
	}

	public function location()
	{
		return $this->belongsTo('\App\Location', 'shipping_location_id');
	}

	public function logisticAddress()
	{
		return $this->belongsTo('\App\Address', 'external_address_id');
	}

	public function detail()
	{
		return $this->hasMany('\App\WarehouseDetail', 'header_id');
	}

	public function quantityCount()
	{
		return array_reduce($this->detail->toArray(), function ($carry, $item) {
			return $carry += $item['expected_quantity'];
		}, 0);
	}

	public function balanceCount()
	{
		return array_reduce($this->detail->toArray(), function ($carry, $item) {
			return $carry += ($item['expected_quantity'] - $item['processed_quantity']);
		}, 0);
	}

	// this function serves as example of how to construct complicated relationship
	public function binTransactions()
	{
		// WarehouseHeader -1-to-many- WarehouseDetail -1-to-1- WarehouseBinTransaction
		$transactions = WarehouseBinTransaction
				::join('warehouse_details', 'warehouse_bin_transactions.warehouse_detail_id', '=', 'warehouse_details.id')
				->join('warehouse_headers', 'warehouse_details.header_id', '=', 'warehouse_headers.id');

		$hasMany = new \Illuminate\Database\Eloquent\Relations\HasMany($transactions, $this, 'warehouse_headers.id', 'id');

		return $hasMany;
	}

	public function bins()
	{
		// WarehouseHeader -1-to-many- WarehouseDetail -1-to-1- WarehouseBin
		$bins = WarehouseBin
				::join('warehouse_bin_transactions', 'warehouse_bin_transactions.bin_id', '=', 'warehouse_bins.id')
				->join('warehouse_details', 'warehouse_bin_transactions.warehouse_detail_id', '=', 'warehouse_details.id')
				->join('warehouse_headers', 'warehouse_details.header_id', '=', 'warehouse_headers.id');

		$hasMany = new \Illuminate\Database\Eloquent\Relations\HasMany($bins, $this, 'warehouse_headers.id', 'id');

		return $hasMany;
	}

	public function generateInventoryUpdate()
	{
		$details = $this->detail()->with('uniqueTradable')->get();
		$result = [];
		foreach ($details as $detail) {
			if (!array_key_exists($detail->unique_tradable_id, $result)) {
				$balance_raw = $detail->uniqueTradable->getWarehouseInventory(gmdate("Y-m-d"), $this->location->id);
				$bins = $detail->uniqueTradable->getWarehouseBins($this->location->id);
				$result[] = [
					"location_id" => $this->location->id,
					"location" => $this->location->name,
					"sku_id" => $detail->unique_tradable_id,
					"sku" => $detail->uniqueTradable->sku,
					"balance" => sprintf(env('APP_QUANTITY_FORMAT'), $balance_raw),
					"bins" => $bins,
					"bins_string" => implode(", ", $bins),
					"can_view" => auth()->user()->can('wo-view'),
				];
			}
		}
		return $result;
	}

	private static function getPurchaseLocation($order)
	{
		if (get_class($order) != 'App\PurchaseHeader') {
			return 0;
		}

		$locations = [];
		foreach ($order->purchaseDetail as $detail) {
			if (!in_array($detail['receiving_location_id'], $locations)) {
				$locations[] = $detail['receiving_location_id'];
			}
		}
		return $locations[0];
	}

	public static function initialize($request)
	{
		$order_src = 'na';

		$warehouseHeaderObj = self::create([
			'title' => ParameterHelper::getNextSequence('warehouse_order_number'),
			'type' => $request->input('type'),
			'palletized' => false,
			'internal_contact_id' => Auth::user()->id,
			'shipping_location_id' => $request->input('location'),
			'reference' => $request->input('reference'),
			'external_entity_id' => $request->input('entity'),
			'external_address_id' => $request->input('address'),
			'status' => 'open',
			'via' => $request->input('via'),
			'order_date' => DateHelper::guiToDbDate($request->input('process_date')),
			'src' => $order_src,
			'src_id' => 0,
			'notes' => '',
			'internal_notes' => '',
		]);
		WarehouseHistory::create([
				'src' => 'warehouse_headers',
				'src_id' => $warehouseHeaderObj->id,
				'staff_id' => Auth::user()->id,
				'machine' => $request->ip(),
				'process_status' => 'created',
				'notes' => '',
			]);

		foreach ($request->input('product_id') as $lineId => $uniqueTradableId) {
			$uniqueTradable = UniqueTradable::find($uniqueTradableId);
			$warehouseDetailObj = WarehouseDetail::create([
					'header_id' => $warehouseHeaderObj->id,
					'src_table' => 'na',
					'src_id' => 0,
					'unique_tradable_id' => $uniqueTradable->id,
					'expected_quantity' => $request->input('quantity')[$lineId],
					'processed_quantity' => $request->input('quantity')[$lineId],
					'description' => $request->input('description')[$lineId],
					'status' => 'open',
					'notes' => '',
				]);

			WarehouseHistory::create([
					'src' => 'warehouse_details',
					'src_id' => $warehouseDetailObj->id,
					'staff_id' => Auth::user()->id,
					'machine' => $request->ip(),
					'process_status' => 'created',
					'notes' => '',
				]);

			switch ($request->input('type')) {
				case 'receive':
					WarehouseBinTransaction::create([
						'warehouse_detail_id' => $warehouseDetailObj->id,
						'bin_id' => $request->input('bin')[$lineId],
						'tradable_id' => $uniqueTradable->tradableByEntity($request->input('entity'))->id,
						'quantity' => $request->input('quantity')[$lineId],
						'valid' => 1,
					]);
					break;
				case 'deliver':
					$binId = $request->input('bin')[$lineId];
					foreach (WarehouseBin::find($binId)->getTradableBatches($uniqueTradable, $request->input('quantity')[$lineId]) as $batch) {
						WarehouseBinTransaction::create([
							'warehouse_detail_id' => $warehouseDetailObj->id,
							'bin_id' => $binId,
							'tradable_id' => $batch['tradable_id'],
							'quantity' => 0 - $batch['quantity'],
							'valid' => 1,
						]);
					}
					break;
				default:
					break;
			}

			WarehouseHistory::create([
					'src' => 'warehouse_details',
					'src_id' => $warehouseDetailObj->id,
					'staff_id' => Auth::user()->id,
					'machine' => $request->ip(),
					'process_status' => 'processed',
					'notes' => '',
				]);
		}

		WarehouseHistory::create([
				'src' => 'warehouse_headers',
				'src_id' => $warehouseHeaderObj->id,
				'staff_id' => Auth::user()->id,
				'machine' => $request->ip(),
				'process_status' => 'processed',
				'notes' => '',
			]);

		event(new \App\Events\WarehouseUpsertEvent($warehouseHeaderObj));

		return $warehouseHeaderObj;
	}

	public static function synchronize($order, $ipAddress)
	{
		$src_table = null;
		$internal_contact_id = 0;
		$shipping_location_id = 0;
		$reference = "";
		$internal_notes = "";
		$details = null;
		$palletized = 0;
		//$detailClassName = null;
		switch (get_class($order)) {
			case 'App\PurchaseHeader':
				$src_table = 'purchase_headers';
				$internal_contact_id = $order['purchase_id'];
				$reference = "P" . substr(strtoupper($order['type']), 0, 1) . "#" . $order['title'];
				$shipping_location_id = self::getPurchaseLocation($order);
				$internal_notes = $order['internal_notes'];
				$details = $order->purchaseDetail;
				//$detailClassName = "PurchaseDetail";
				$palletized = 0;
				break;
			case 'App\SalesHeader':
				$src_table = 'sales_headers';
				$internal_contact_id = $order['sales_id'];
				$reference = "S" . substr(strtoupper($order['type']), 0, 1) . "#" . $order['title'];
				$shipping_location_id = $order['shipping_location_id'];
				$internal_notes = "";
				$details = $order->salesDetail;
				//$detailClassName = "SalesDetail";
				$palletized = $order->palletized;
				break;
		}

		if ($warehouseOrder = WarehouseHeader::where('src', $src_table)->where('src_id', $order['id'])->first()) {
			// synchronize order with existing warehouse order
			$warehouseOrder->update([
					//'title' => ParameterHelper::getNextSequence('warehouse_order_number'),
					//'type' => $order->translateWarehouseOrderType(),
					'palletized' => $palletized,
					'internal_contact_id' => $internal_contact_id,
					'shipping_location_id' => $shipping_location_id,
					'reference' => $reference,
					//'external_entity_id' => $order['entity_id'],
					'external_address_id' => $order['shipping_address_id'],
					'status' => $order['status'],
					'via' => $order['via'],
					//'order_date' => date("Y-m-d"),
					//'src' => $src_table,
					//'src_id' => $order['id'],
					'notes' => $order['notes'],
					'internal_notes' => $internal_notes,
				]);

			WarehouseHistory::create([
					'src' => 'warehouse_headers',
					'src_id' => $warehouseOrder['id'],
					'staff_id' => auth()->user()['id'],
					'machine' => $ipAddress,
					'process_status' => 'updated',
					'notes' => '',
				]);

			// process each existing detail
			foreach ($details as $detail) {
				if ($warehouseDetail = WarehouseDetail::where('src_table', $detail->getTable())->where('src_id', $detail['id'])->first()) {
					$warehouseDetail->update([
							//'header_id' => $warehouseOrder['id'],
							//'unique_tradable_id' => $detail['unique_tradable_id'],
							'expected_quantity' => $detail['ordered_quantity'],
							//'processed_quantity' => 0,
							'description' => $detail['description'],
							'status' => $detail['status'],
							'notes' => $detail['notes'],
						]);

					WarehouseHistory::create([
							'src' => 'warehouse_details',
							'src_id' => $warehouseDetail['id'],
							'staff_id' => auth()->user()['id'],
							'machine' => $ipAddress,
							'process_status' => 'updated',
							'notes' => '',
						]);
				} else {
					$warehouseDetail = WarehouseDetail::create([
							'header_id' => $warehouseOrder['id'],
							'src_table' => $detail->getTable(),
							'src_id' => $detail['id'],
							'unique_tradable_id' => $detail['unique_tradable_id'],
							'expected_quantity' => $detail['ordered_quantity'],
							'processed_quantity' => $detail['shipped_quantity'],
							'description' => $detail['description'],
							'status' => 'open',
							'notes' => $detail['notes'],
						]);

					WarehouseHistory::create([
							'src' => 'warehouse_details',
							'src_id' => $warehouseDetail['id'],
							'staff_id' => auth()->user()['id'],
							'machine' => $ipAddress,
							'process_status' => 'created',
							'notes' => '',
						]);
				}
			}
		} else {
			// create warehouse order
			$warehouseOrder = WarehouseHeader::create([
					'title' => ParameterHelper::getNextSequence('warehouse_order_number'),
					'type' => $order->translateWarehouseOrderType(),
					'palletized' => $palletized,
					'internal_contact_id' => $internal_contact_id,
					'shipping_location_id' => $shipping_location_id,
					'reference' => $reference,
					'external_entity_id' => $order['entity_id'],
					'external_address_id' => $order['shipping_address_id'],
					'status' => 'open',
					'via' => $order['via'],
					'order_date' => date("Y-m-d"),
					'src' => $src_table,
					'src_id' => $order['id'],
					'notes' => $order['notes'],
					'internal_notes' => $internal_notes,
				]);

			WarehouseHistory::create([
					'src' => 'warehouse_headers',
					'src_id' => $warehouseOrder['id'],
					'staff_id' => auth()->user()['id'],
					'machine' => $ipAddress,
					'process_status' => 'created',
					'notes' => '',
				]);

			foreach ($details as $detail) {
				$warehouseDetail = WarehouseDetail::create([
						'header_id' => $warehouseOrder['id'],
						'src_table' => $detail->getTable(),
						'src_id' => $detail['id'],
						'unique_tradable_id' => $detail['unique_tradable_id'],
						'expected_quantity' => $detail['ordered_quantity'],
						'processed_quantity' => $detail['shipped_quantity'],
						'description' => $detail['description'],
						'status' => 'open',
						'notes' => $detail['notes'],
					]);

				WarehouseHistory::create([
						'src' => 'warehouse_details',
						'src_id' => $warehouseDetail['id'],
						'staff_id' => auth()->user()['id'],
						'machine' => $ipAddress,
						'process_status' => 'created',
						'notes' => '',
					]);
			}
		}

		event(new \App\Events\WarehouseUpsertEvent($warehouseOrder));

		return $warehouseOrder;
	}

	public function void(Request $request)
	{
		$this->update([ 'status' => 'void' ]);
		WarehouseHistory::create([
			'src' => 'warehouse_headers',
			'src_id' => $this->id,
			'staff_id' => auth()->user()['id'],
			'machine' => $request->ip(),
			'process_status' => 'void',
			'notes' => '',
		]);
		foreach ($this->detail as $detail) {
			$detail->update([ 'status' => 'void' ]);
			WarehouseHistory::create([
				'src' => 'warehouse_details',
				'src_id' => $detail->id,
				'staff_id' => auth()->user()['id'],
				'machine' => $request->ip(),
				'process_status' => 'void',
				'notes' => '',
			]);
		}
		$this->binTransactions()->update([ 'valid' => 0 ]);

		event(new \App\Events\WarehouseUpsertEvent($this));
	}

	public function process(Request $request)
	{
		foreach ($request->input('line') as $index => $detailId) {
			$detail = $this->detail()->where('id', $detailId)->first();
			$detail->update([ 'processed_quantity' => $detail->processed_quantity + $request->input('processing')[$index] ]);
			WarehouseHistory::create([
				'src' => 'warehouse_details',
				'src_id' => $detailId,
				'staff_id' => auth()->user()['id'],
				'machine' => $request->ip(),
				'process_status' => 'processed',
				'notes' => '',
			]);
			if ($detail->expected_quantity == $detail->processed_quantity) {
				$detail->update([ 'status' => 'closed' ]);
				WarehouseHistory::create([
					'src' => 'warehouse_details',
					'src_id' => $detailId,
					'staff_id' => auth()->user()['id'],
					'machine' => $request->ip(),
					'process_status' => 'closed',
					'notes' => '',
				]);
			}
		}
		WarehouseHistory::create([
			'src' => 'warehouse_headers',
			'src_id' => $this->id,
			'staff_id' => auth()->user()['id'],
			'machine' => $request->ip(),
			'process_status' => 'processed',
			'notes' => '',
		]);
		$balance = $this->detail->reduce(function ($carry, $item) { return $carry + $item->expected_quantity - $item->processed_quantity; }, 0);
		if ($balance == 0) {
			$this->update([ 'status' => 'closed' ]);
			WarehouseHistory::create([
				'src' => 'warehouse_headers',
				'src_id' => $this->id,
				'staff_id' => auth()->user()['id'],
				'machine' => $request->ip(),
				'process_status' => 'closed',
				'notes' => '',
			]);
		}

		event(new \App\Events\WarehouseUpsertEvent($this));
	}

	// this function is for warehouse to prepare order
	public function prepare(Request $request)
	{
		// prepare submitted quantity indexed by unique_id
		$processedUniqueTradables = [ ];
		foreach ($request->input('product_id') as $idx => $uniqueTradableId) {
			if ($request->input('quantity')[$idx]) {
				if (array_key_exists($uniqueTradableId, $processedUniqueTradables)) {
					array_push($processedUniqueTradables[$uniqueTradableId], [
						'quantity' => $request->input('quantity')[$idx],
						'bin_id' => $request->input('bin')[$idx],
					]);
				} else {
					$processedUniqueTradables[$uniqueTradableId] = [
						[
							'quantity' => $request->input('quantity')[$idx],
							'bin_id' => $request->input('bin')[$idx],
						]
					];
				}
			}
		}

		// if there's at least 1 item
		if (count($processedUniqueTradables)) {
			WarehouseHistory::create([
					'src' => 'warehouse_headers',
					'src_id' => $this->id,
					'staff_id' => auth()->user()->id,
					'machine' => $request->ip(),
					'process_status' => 'processed',
					'notes' => '',
				]);
		}

		// go through each detail and increment processed_quantity according to submission
		foreach ($this->detail as $oneDetail) {
			$processedBalance = array_reduce($processedUniqueTradables[$oneDetail->unique_tradable_id], function($carry, $item) { return $carry += $item['quantity']; }, 0);
			if (isset($processedUniqueTradables[$oneDetail->unique_tradable_id]) && $processedBalance) {
				$detailHistoryContent = [
					'src' => 'warehouse_details',
					'src_id' => $oneDetail->id,
					'staff_id' => auth()->user()->id,
					'machine' => $request->ip(),
					'process_status' => 'updated',
				];
				// the line can be partially fulfilled.  We need to consider balance vs submission quantity
				$balance = $oneDetail->expected_quantity - $oneDetail->processed_quantity;
				if ($processedBalance < $balance) {
					// balance is greater than submission total, go through buffer and transcribe everything to WarehouseBinTransaction
					foreach ($processedUniqueTradables[$oneDetail->unique_tradable_id] as $bufferKey => $buffer) {
						$oneDetail->recordBinTransaction($buffer['bin_id'], $buffer['quantity']);
					}
					$oneDetail->processed_quantity += $processedBalance;
					$detailHistoryContent = array_merge(
							$detailHistoryContent,
							[ 'notes' => $processedBalance . ' unit processed' ]
						);
					unset($processedUniqueTradables[$oneDetail->unique_tradable_id]);
				} else {
					// submission is greater than line balance, go through buffer and transcribe quantity only up-to submission quantity
					$decrementCounter = $balance;
					foreach ($processedUniqueTradables[$oneDetail->unique_tradable_id] as $bufferKey => $buffer) {
						if ($decrementCounter > 0) {
							if ($decrementCounter > $buffer['quantity']) {
								$oneDetail->recordBinTransaction($buffer['bin_id'], $buffer['quantity']);
								$decrementCounter -= $buffer['quantity'];
								unset($processedUniqueTradables[$oneDetail->unique_tradable_id]);
							} else {
								$oneDetail->recordBinTransaction($buffer['bin_id'], $decrementCounter);
								$decrementCounter -= 0;
								$processedUniqueTradables[$oneDetail->unique_tradable_id][$bufferKey]['quantity'] -= $decrementCounter;
							}
						} else {
							break;
						}
					}
					$oneDetail->processed_quantity += $balance;
					$detailHistoryContent = array_merge(
							$detailHistoryContent,
							[ 'notes' => $balance . ' unit processed' ]
						);
				}
				$oneDetail->save();
				WarehouseHistory::create($detailHistoryContent);
			}
		}

		event(new \App\Events\WarehouseUpsertEvent($this));
	}

	CONST QUANTITY_EXPRESSION_IN_PDF = [
		"\\App\\WarehouseFormPdf" => "\$tempData['quantity'] = \$detail->expected_quantity - \$detail->processed_quantity;\$tempData['bins'] = \$detail->getBinLocations();",
		"\\App\\WarehouseSerialPdf" => "\$tempData['quantity'] = \$detail->processed_quantity - 0;\$tempData['serial'] = \$detail->getSerialNumbers('array');",
	];

	public function generatePDF($formName)
	{
		$companyAddress = TaxableEntity::theCompany()->defaultBillingAddress[0];
		$externalAddress = $this->logisticAddress;
		$staff = $this->staff;
		$warehouse = $this->shippingLocation;
		$details = [];
		foreach ($this->detail as $detail) {
			$tempData = [
					'sku' => $detail->uniqueTradable->sku,
					'description' => $detail->uniqueTradable->description,
				];
			eval(self::QUANTITY_EXPRESSION_IN_PDF[$formName]);
			$details[] = $tempData;
		}

		$data = [
			'increment' => $this->title,
			'type' => $this->type,
			'date' => DateHelper::dbToGuiDate($this->order_date),
			'via' => $this->via,
			'reference' => $this->reference,
			'staff' => $staff->name,
			'company_address' => $companyAddress->street.(empty($companyAddress->unit) ? "" : (" ".$companyAddress->unit))." ".$companyAddress->city.(empty($companyAddress->district) ? "" : (" ".$companyAddress->district))." ".$companyAddress->state." ".$companyAddress->country." ".$companyAddress->zipcode,
			'external_address' => $externalAddress->street.(empty($externalAddress->unit) ? "" : (" ".$externalAddress->unit))." ".$externalAddress->city.(empty($externalAddress->district) ? "" : (" ".$externalAddress->district))." ".$externalAddress->state." ".$externalAddress->country." ".$externalAddress->zipcode,
			'notes' => $this->internal_notes,
			'detail' => $details,
		];

		$pdf = new $formName($data);

		return $pdf;
	}

	public function history()
	{
		return $this->hasMany('\App\WarehouseHistory', 'src_id')->where('src', 'warehouse_headers');
	}

	public function source()
	{
		switch ($this->src) {
			case 'purchase_headers':
				return $this->belongsTo('\App\PurchaseHeader', 'src_id')->withoutGlobalScope('currentFiscal');
			case 'sales_headers':
				return $this->belongsTo('\App\SalesHeader', 'src_id')->withoutGlobalScope('currentFiscal');
			default:
				return $this->belongsTo('\App\WarehouseHeader', '')->withDefault();  // return empty relationship
		}
		return $this->belongsTo('\App\WarehouseHeader', '')->withDefault();  // return empty relationship
	}

	// If new keyword is added, make sure generateSearchTips() is updated as well
	public function generateSearchAttribute()
	{
		$result = [];

		array_push($result, $this->type);
		array_push($result, $this->status);
		if (substr($this->order_date, 0, 4) == date("Y")) {
			array_push($result, 'thisyear');
		}
		if (substr($this->order_date, 0, 7) == date("Y-m")) {
			array_push($result, 'thismonth');
		}
		if (substr($this->order_date, 0, 7) == date("Y-m", strtotime("-1 month"))) {
			array_push($result, 'lastmonth');
		}

		return $result;
	}

	// If new keyword is added, make sure generateSearchAttribute() is updated as well
	public static function generateSearchTips($delimiter)
	{
    return implode($delimiter, [
				str_pad('receive', 15) . trans('tool.Search receive order'),
				str_pad('relocate', 15) . trans('tool.Search relocate order'),
				str_pad('transfer', 15) . trans('tool.Search transfer order'),
				str_pad('deliver', 15) . trans('tool.Search deliver order'),
				str_pad('open', 15) . trans('tool.Search open warehouse order'),
				str_pad('void', 15) . trans('tool.Search void warehouse order'),
				str_pad('closed', 15) . trans('tool.Search closed warehouse order'),
				str_pad('thisyear', 15) . trans('tool.Search from this year'),
				str_pad('thismonth', 15) . trans('tool.Search from this month'),
				str_pad('lastmonth', 15) . trans('tool.Search from last month'),
			]);
	}
}
