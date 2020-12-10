<?php

namespace App\Http\Controllers;

use App;
use App\Helpers\DateHelper;
use App\Helpers\HistoryHelper;
use App\Helpers\ParameterHelper;
use App\Helpers\QuantityHelper;
use App\Http\Requests;
use App\Location;
use App\Http\Resources\WarehouseHeader as WarehouseHeaderResource;
use App\Serial;
use App\TaxableEntity;
use App\Tradable;
use App\UniqueTradable;
use App\User;
use App\WarehouseDetail;
use App\WarehouseHeader;
use App\WarehouseHistory;
use App\WarehouseBin;
use App\WarehouseBinTransaction;
use Auth;
use DB;
use NumberFormatter;
use Session;
use Validator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use App\Validations\WarehouseBinAvailabilityValidation;
use App\Validations\WarehouseBinQuantityValidation;

class WarehouseOrderController extends Controller
{
	public function createOrder(Request $request, $order_type, $entity_id)
	{
		$entity = TaxableEntity::find($entity_id);

		// load warehouse-detail; errors imply redirect back, flashing input removes old value
		if (!Session::has('alert-danger') && !Session::has('alert-warning') && !Session::has('errors')) {
			$request->session()->flashInput(WarehouseHeader::generateEmptyInput($entity));
		}

		return view()->first(generateTemplateCandidates('form.outsourced_warehouse_order_quantity'),
				[
						'readonly' => false,
						'detail_required' => true,
						'quantity_formatter' => QuantityHelper::getHtmlAttribute(),
						'source' => [
								'title' => trans('warehouse.Process order'),
								'post_url' =>  '/' . $request->path(),
								'type' => $order_type,
								'action' => trans('forms.Submit'),
								'address' => $entity->shippingAddress,
								'bins' => WarehouseBin::getBinIndexedByUniqueTradableAndLocation($order_type),
						]
				]);
	}

	public function createOrderPost(Request $request, $order_type, $entity_id)
	{
		$isDeliver = in_array($order_type, ['deliver']);

		if ($isDeliver) {
			Validator::extend('bin_quantity', WarehouseBinQuantityValidation::class);
			Validator::extend('bin_availability', WarehouseBinAvailabilityValidation::class);
		}

		$validator = Validator::make($request->all(), [
			//'via' => 'required|string',
			//'reference' => 'required|string',
			'process_date' => 'required',
			'location' => 'required|numeric',
			'address' => 'required|numeric',
			'quantity.*' => 'required|numeric|min:1' . ($isDeliver ? '|bin_quantity:product_id.*,bin.*' : ''),
			'bin.*' => 'required|numeric|min:1' . ($isDeliver ? '|bin_availability:product_id.*' : ''),
		]);

		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return redirect('/' . $request->path())
					->with('alert-warning', trans('messages.Please correct all errors'))
					->withErrors($validator) // send back all errors to the form
					->withInput($request->all()); // send back the input so we can repopulate the form
		}

		$warehouseHeaderObj = null;

 		$request->merge([
			'type' => $order_type,
			'entity' => $entity_id,
		]);

		try {
			DB::transaction(function() use ($request, &$warehouseHeaderObj) {
				$warehouseHeaderObj = WarehouseHeader::initialize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", "#".$warehouseHeaderObj->title, trans('warehouse.Order ### processed.')));
	}

	public function loadOrderAjax(Request $request)
	{
		if ($request->has('id')) {
			$warehouseHeaderObj = WarehouseHeader::find($request->input('id'));
			if ($warehouseHeaderObj) {
				$warehouseDetailCollection = $warehouseHeaderObj->detail->load('uniqueTradable', 'binTransactions')->toArray();

				return response()->json([
					'success' => true,
					'data' => [
						'id' => $warehouseHeaderObj->id,
						'title' => WarehouseHeader::$document_title[$warehouseHeaderObj->type] . " #" . $warehouseHeaderObj->title,
						'action' => trans('forms.View PDF'),
						'csrf' => csrf_token(),
						// 'post_url' => '/warehouse/vieworder/' . $request->input('id'),
						'addresses' => TaxableEntity::getJsonShippingAddressesIndexedByEntity(),
						'increment' => $warehouseHeaderObj->title,
						'type' => $warehouseHeaderObj->type,
						'reference' => $warehouseHeaderObj->reference,
						'process_date' => DateHelper::dbToGuiDate($warehouseHeaderObj->order_date),
						'staff' => $warehouseHeaderObj->staff->name,
						'via' => $warehouseHeaderObj->via,
						'location' => $warehouseHeaderObj->shipping_location_id,
						'entity' => $warehouseHeaderObj->external_entity_id,
						'address' => $warehouseHeaderObj->external_address_id,
						'product_id' => array_column($warehouseDetailCollection, 'unique_tradable_id'),
						'sku' => array_column(array_column($warehouseDetailCollection, 'unique_tradable'), "sku"),
						'description' => array_column($warehouseDetailCollection, 'description'),
						'quantity' => array_map(function($element) { return sprintf(env('APP_QUANTITY_FORMAT'), $element); },
								array_column($warehouseDetailCollection, 'processed_quantity')),
						// 'bin' => array_column(array_column(array_column($warehouseDetailCollection, 'bin_transactions'), 0), "bin_id")
						'bin' => array_map(function ($item) { return count($item) ? $item[0]["bin_id"] : 0; },
							array_column($warehouseDetailCollection, 'bin_transactions'))
					]
				]);
			}
		}

		$entity = TaxableEntity::whereNotIn('type', ['self','employee'])->where('active', 1)->first();

		return response()->json([
			'success' => true,
			'data' => [
				'id' => 0,
				'title' => '',
				'action' => trans('forms.Submit'),
				'csrf' => csrf_token(),
				// 'post_url' => '',
				'addresses' => TaxableEntity::getJsonShippingAddressesIndexedByEntity(),
				'increment' => '?????',
				'type' => '',
				'reference' => '',
				'process_date' => DateHelper::dbToGuiDate(date("Y-m-d")),
				'staff' => Auth::user()->name,
				'via' => '',
				'location' => Location::getActiveWarehouses('name', 'asc')->first()->id,
				'entity' => $entity->id,
				'address' => $entity->defaultShippingAddress[0]->id,
				'product_id' => [],
				'sku' => [],
				'description' => [],
				'quantity' => [],
				'bin' => []
			]
		]);
	}

	// this is to load warehouse-order for use of other department
	public function loadWarehouseOrderAjax(Request $request, $id)
	{
		if ($id) {
			$warehouseHeaderObj = WarehouseHeader::find($id);
			$warehouseDetailCollection = $warehouseHeaderObj->detail;

			return response()->json([
				'success' => true,
				'data' => [
					'csrf' => csrf_token(),
					'history' => array_map(function($elem) {
							$timeElem = explode(" ", $elem['updated_at']);
							$timeString = DateHelper::dbToGuiDate($timeElem[0]) . " " . date("g:iA", strtotime($elem['updated_at']));
							return sprintf(trans('messages.%1$s %2$s at %3$s'), $elem['staff']['name'], trans('action.'.$elem['process_status']), $timeString);
						}, $warehouseHeaderObj->history()->with('staff')->orderBy('created_at', 'desc')->get()->toArray()),
					'id' => $warehouseHeaderObj->id,
					'increment' => $warehouseHeaderObj->title,
					'type' => $warehouseHeaderObj->type,
					'status' => $warehouseHeaderObj->status,
					'reference' => $warehouseHeaderObj->reference,
					'process_date' => DateHelper::dbToGuiDate($warehouseHeaderObj->order_date),
					'staff' => $warehouseHeaderObj->internal_contact_id,
					'via' => $warehouseHeaderObj->via,
					'location' => $warehouseHeaderObj->shipping_location_id,
					'entity' => $warehouseHeaderObj->external_entity_id,
					'address' => $warehouseHeaderObj->external_address_id,
					'notes' => $warehouseHeaderObj->notes,
					'internal_notes' => $warehouseHeaderObj->internal_notes,
					'line' => $warehouseDetailCollection->pluck('id'),
					'product' => $warehouseDetailCollection->pluck('unique_tradable_id'),
					'description' => $warehouseDetailCollection->pluck('description'),
					'quantity' => $warehouseDetailCollection->map(function ($item) { return sprintf(env('APP_QUANTITY_FORMAT'), $item->expected_quantity - $item->processed_quantity); }),
				]
			]);
		}

		return response()->json([
			'success' => true,
			'data' => [
				'csrf' => csrf_token(),
				'history' => [ ],
				'id' => 0,
				'increment' => '?????',
				'type' => '',
				'status' => '',
				'reference' => '',
				'process_date' => DateHelper::dbToGuiDate(date("Y-m-d")),
				'staff' => Auth::user()->id,
				'via' => '',
				'location' => 0,
				'entity' => 0,
				'address' => 0,
				'notes' => '',
				'internal_notes' => '',
				'line' => [],
				'product' => [],
				'description' => [],
				'quantity' => []
			]
		]);
	}

	public function createOrderPostAjax(Request $request)
	{
		// all Ajax controller does not register with session-history
		// removal no longer needed since this controller is in web-ajax group
		// $this->removeFromHistory();

		$isDeliver = in_array($request->input('type'), ['deliver']);

		if ($isDeliver) {
			Validator::extend('bin_quantity', WarehouseBinQuantityValidation::class);
			Validator::extend('bin_availability', WarehouseBinAvailabilityValidation::class);
		}

		$validator = Validator::make($request->all(), [
			'via' => 'required|string',
			'reference' => 'required|string',
			'process_date' => 'required',
			'location' => 'required|numeric',
			'address' => 'required|numeric',
			'quantity.*' => 'required|numeric|min:1' . ($isDeliver ? '|bin_quantity:product_id.*,bin.*' : ''),
			'bin.*' => 'required|numeric|min:1' . ($isDeliver ? '|bin_availability:product_id.*' : ''),
		]);

		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return response()->json([ 'success' => false, 'errors' => $validator->errors() ]);
		}

		$warehouseHeaderObj = null;

		try {
			DB::transaction(function() use ($request, &$warehouseHeaderObj) {
				$warehouseHeaderObj = WarehouseHeader::initialize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => [
				'order' => new WarehouseHeaderResource($warehouseHeaderObj),
				'inventory' => $warehouseHeaderObj->generateInventoryUpdate(),
				'bin_availability' => WarehouseBin::getBinIndexedByUniqueTradableAndLocation("deliver"),
			] ]);
	}

	public function viewOrder($id, Request $request)
	{
		$order = WarehouseHeader::find($id);
		if (!$order) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$order['title'], trans('warehouse.Order ### can not be viewed')));
		}

		$entityCode = $order->externalEntity['code'];
		$document = $order['title'];
		$contact = $order->staff['name'];
		$location = $order->location['name'];
		$processDate = DateHelper::dbToGuiDate($order['order_date']);
		$reference = $order['reference'];
		$via = $order['via'];
		$address = $order->logisticAddress;
		$address = $address['street'] . "\n" . $address['city'] . "\n" . $address['state'] . " " . $address['zipcode'] . "\n" . $address['country'];

		$line = array();
		$item = array();
		$description = array();
		$expectedQuantity = array();
		$processQuantity = array();
		$serial = array();

		foreach ($order->detail as $oneEntry) {
			$line[] = $oneEntry['id'];
			$item[] = $oneEntry->uniqueTradable['sku'];
			$description[] = $oneEntry->uniqueTradable['description'];
			$expectedQuantity[] = $oneEntry->expected_quantity;
			$processQuantity[] = sprintf(env("APP_QUANTITY_FORMAT"), $oneEntry->processed_quantity);
			$serial[] = $oneEntry->getSerialNumbers('html');
		}

		// no need to check error-redirect since this is read only
		$request->session()->flashInput([
			'processQuantity' => $processQuantity,
		]);

		return view()->first(generateTemplateCandidates('form.warehouse_order_quantity_only'),
					array(
						'readonly' => true,
						'source' => array(
							'title' => trans('warehouse.View order'),
							'post_url' => '/' . $request->path(),
							'type' => $order['type'],
							'history' => $order->history()->orderBy('created_at', 'desc')->get(),
							'action' => trans('forms.View PDF')
						),
						'ext_entity' => $entityCode,
						'document' => $document,
						'process_date' => $processDate,
						'contact' => $contact,
						'location' => $location,
						'reference' => $reference,
						'address' => $address,
						'via' => $via,
						'quantity_formatter' => QuantityHelper::getHtmlAttribute(),
						'line' => $line,
						'product' => $item,
						'description' => $description,
						'expectQuantity' => $expectedQuantity,
						'serial' => $serial,
					)
				);
	}

	public function printOrder($id, Request $request)
	{
		$order = WarehouseHeader::find($id);

		$pdf = $order->generatePDF("\App\WarehouseFormPdf");

		$pdf->Output("Warehouse order #".$order->title.".pdf", "D");
	}

	public function downloadOrderSerialPdf($id, Request $request)
	{
		$order = WarehouseHeader::find($id);

		$pdf = $order->generatePDF("\App\WarehouseSerialPdf");

		$pdf->Output("Serial of warehouse order #".$order->title.".pdf", "D");
	}

	public function scanOrder($id, Request $request)
	{
		$order = WarehouseHeader::find($id);
		if (!$order || $order->isNotOpen()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#$id", "warehouse.Order ### can not be processed"));
		}

		$products = [];
		foreach (Tradable::getCurrentProducts('sku', 'asc') as $oneEntry) {
			$products[$oneEntry->id] = [
					'sku' => $oneEntry->uniqueTradable->sku,
					'pattern' => $oneEntry->serial_pattern,
				];
		}

		// no need to check error-redirect since POST does not validate
		$request->session()->flashInput([
				'increment' => $order['title'],
				'inputdate' => DateHelper::dbToGuiDate($order['order_date']),
				'location' => $order['shipping_location_id'],
				'reference' => $order['reference'],
				'address' => $order['external_address_id'],
				'notes' => $order['internal_notes'],
			]);

		return view()->first(generateTemplateCandidates("form.warehouse_order_scan"), [
				'readonly' => true,
				'source' => [
					'post_url' => "/" . $request->path(),
				],
				'location' => Location::getActiveWarehouses('name', 'asc'),
				'addresses' => $order->externalEntity->shippingAddress,
				'products' => $products,
			]);
	}

	public function scanOrderPost($id, Request $request)
	{
		$order = WarehouseHeader::find($id);
		if (!$order || $order->isNotOpen()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$order['title'], trans('warehouse.Order ### can not be processed')));
		}

		$vacating = in_array($order->type, ['relocate', 'transfer', 'deliver']);
		$occupying = in_array($order->type, ['receive', 'relocate', 'transfer']);

		try {
			DB::transaction(function() use ($order, $request, $vacating, $occupying) {
				$scanDate = DateHelper::guiToDbDate($request->input('inputdate'));

				$totalScanned = 0;

				foreach ($request->input('serial') as $productId => $serials) {
					$detail = ($productId > 0) ? $order->detail()->where('unique_tradable_id',Tradable::find($productId)->unique_tradable_id)->first() : null;
					$processedProductId = ($productId > 0) ? $productId : Tradable::where('stockable', 1)->first()->id;
					foreach ($serials as $oneSerial) {
						Serial::create([
								'serial' => $oneSerial,
								'src_table' => $detail ? 'warehouse_details' : 'warehouse_headers',
								'src_id' => $detail ? $detail->id : $order->id,
								'tradable_id' => $processedProductId,
								'pallet_id' => 0,
								'carton_id' => 0,
								'warranty_from' => $scanDate,
							]);
						if ($vacating) {
							DB::update(
								"UPDATE bin_serial SET occupied_until = utc_timestamp() WHERE bin_id IN (SELECT id FROM warehouse_bins WHERE valid = 1 AND location_id = ?) AND tradable_id = ? AND serial = ? AND occupied_until IS NULL",
								[
									$order->shipping_location_id ,
									$processedProductId,
									$oneSerial
								]
							);
						}
						if ($occupying && $request->input('bins.'.$oneSerial)) {
							DB::insert(
								"INSERT INTO bin_serial (bin_id, tradable_id, serial, occupied_since) VALUES (?, ?, ?, utc_timestamp())",
								[
									$request->input('bins.'.$oneSerial),
									$processedProductId,
									$oneSerial,
								]
							);
						}

						$totalScanned++;
					}
				}

				if ($totalScanned > 0) {
					WarehouseHistory::create([
							'src' => 'warehouse_headers',
							'src_id' => $order['id'],
							'staff_id' => auth()->user()['id'],
							'machine' => $request->ip(),
							'process_status' => 'scanned',
							'notes' => '',
						]);
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", "#".$order->title, trans('warehouse.Order ### scanned.')));
	}

	public function updateReceiveOrder($id, Request $request)
	{
		$order = WarehouseHeader::find($id);
		if (($order['type'] != 'receive') || $order->isNotOpen()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$order['title'], trans('warehouse.Order ### can not be processed')));
		}

		$entityCode = $order->externalEntity['code'];
		$document = $order['title'];
		$contact = $order->staff['name'];
		$location = $order->location['name'];
		$processDate = DateHelper::dbToGuiDate($order['order_date']);
		$reference = $order['reference'];
		$via = $order['via'];
		$address = $order->logisticAddress;
		$address = $address['street'] . "\n" . $address['city'] . "\n" . $address['state'] . " " . $address['zipcode'] . "\n" . $address['country'];

		$line = array();
		$item = array();
		$description = array();
		$expectedQuantity = array();
		$processQuantity = array();

		foreach ($order->detail as $oneEntry) {
			$line[] = $oneEntry->id;
			$item[] = $oneEntry->uniqueTradable['sku'];
			$description[] = $oneEntry->uniqueTradable['description'];
			$expectedQuantity[] = $oneEntry->expected_quantity - $oneEntry->processed_quantity;
			$totalScanned = Serial::where([['src_table', 'warehouse_details'], ['src_id', $oneEntry->id]])->count();
			$processQuantity[] = ($totalScanned > $oneEntry->processed_quantity) ? ($totalScanned - $oneEntry->processed_quantity) : 0;
		}

		// no need to check error-redirect since POST does not validate
		$request->session()->flashInput([
			'processQuantity' => $processQuantity,
		]);

		return view()->first(generateTemplateCandidates('form.warehouse_order_quantity_only'),
					array(
						'readonly' => false,
						'source' => array(
							'title' => trans('forms.Receive order'),
							'post_url' => '/' . $request->path(),
							'type' => 'receive',
							'history' => $order->history()->orderBy('created_at', 'desc')->get(),
							'action' => trans('forms.Update')
						),
						'ext_entity' => $entityCode,
						'document' => $document,
						'process_date' => $processDate,
						'contact' => $contact,
						'location' => $location,
						'reference' => $reference,
						'address' => $address,
						'via' => $via,
						'quantity_formatter' => QuantityHelper::getHtmlAttribute(),
						'line' => $line,
						'product' => $item,
						'description' => $description,
						'expectQuantity' => $expectedQuantity
						)
					);
	}

	public function updateTransferOrder($id, Request $request)
	{
		$order = WarehouseHeader::find($id);
		if (($order['type'] != 'transfer') || $order->isNotOpen()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$order['title'], trans('warehouse.Order ### can not be processed')));
		}

		$entityCode = $order->externalEntity()['code'];
		$document = $order['title'];
		$contact = $order->staff['name'];
		$location = $order->location['name'];
		$processDate = $order['order_date'];
		$reference = $order['reference'];
		$via = $order['via'];
		$address = $order->logisticAddress();
		$address = $address['street'] . "\n" . $address['city'] . "\n" . $address['state'] . " " . $address['zipcode'] . "\n" . $address['country'];

		$line = array();
		$item = array();
		$description = array();
		$expectedQuantity = array();
		$processQuantity = array();

		foreach ($order->detail as $oneEntry) {
			$line[] = $oneEntry->id;
			$item[] = $oneEntry->uniqueTradable()['sku'];
			$description[] = $oneEntry->uniqueTradable()['description'];
			$expectedQuantity[] = $oneEntry->expected_quantity - $oneEntry->processed_quantity;
			$processQuantity[] = 0;
		}

		// no need to check error-redirect since POST does not validate
		$request->session()->flashInput([
			'processQuantity' => $processQuantity,
		]);

		return view()->first(generateTemplateCandidates('form.warehouse_order_quantity_only'),
					array(
						'readonly' => false,
						'source' => array(
							'title' => trans('forms.Receive order'),
							'post_url' => '/' . $request->path(),
							'type' => 'receive',
							'history' => $order->history()->orderBy('created_at', 'desc')->get(),
							'action' => trans('forms.Update')
						),
						'ext_entity' => $entityCode,
						'document' => $document,
						'process_date' => $processDate,
						'contact' => $contact,
						'location' => $location,
						'reference' => $reference,
						'address' => $address,
						'via' => $via,
						'quantity_formatter' => QuantityHelper::getHtmlAttribute(),
						'line' => $line,
						'product' => $item,
						'description' => $description,
						'expectQuantity' => $expectedQuantity
						)
					);
	}

	public function updateDeliverOrder($id, Request $request)
	{
		$order = WarehouseHeader::find($id);
		if (($order['type'] != 'deliver') || $order->isNotOpen()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$order['title'], trans('warehouse.Order ### can not be processed')));
		}

		$entityCode = $order->externalEntity['code'];
		$document = $order['title'];
		$contact = $order->staff['name'];
		$location = $order->location['name'];
		$processDate = DateHelper::dbToGuiDate($order['order_date']);
		$reference = $order['reference'];
		$via = $order['via'];
		$address = $order->logisticAddress;
		$address = $address['street'] . "\n" . $address['city'] . "\n" . $address['state'] . " " . $address['zipcode'] . "\n" . $address['country'];

		$line = array();
		$item = array();
		$description = array();
		$expectedQuantity = array();
		$processQuantity = array();

		foreach ($order->detail as $oneEntry) {
			$line[] = $oneEntry->id;
			$item[] = $oneEntry->uniqueTradable['sku'];
			$description[] = $oneEntry->uniqueTradable['description'];
			$expectedQuantity[] = $oneEntry->expected_quantity - $oneEntry->processed_quantity;
			$totalScanned = Serial::where([['src_table', 'warehouse_details'], ['src_id', $oneEntry->id]])->count();
			$processQuantity[] = ($totalScanned > $oneEntry->processed_quantity) ? ($totalScanned - $oneEntry->processed_quantity) : 0;
		}

		// no need to check error-redirect since POST does not validate
		$request->session()->flashInput([
			'processQuantity' => $processQuantity,
		]);

		return view()->first(generateTemplateCandidates('form.warehouse_order_quantity_only'),
					array(
						'readonly' => false,
						'source' => array(
							'title' => trans('forms.Ship order'),
							'post_url' => '/' . $request->path(),
							'type' => 'deliver',
							'history' => $order->history()->orderBy('created_at', 'desc')->get(),
							'action' => trans('forms.Update')
						),
						'ext_entity' => $entityCode,
						'document' => $document,
						'process_date' => $processDate,
						'contact' => $contact,
						'location' => $location,
						'reference' => $reference,
						'address' => $address,
						'via' => $via,
						'quantity_formatter' => QuantityHelper::getHtmlAttribute(),
						'line' => $line,
						'product' => $item,
						'description' => $description,
						'expectQuantity' => $expectedQuantity
						)
					);
	}

	public function updateOrderPost($id, Request $request)
	{
		$order = WarehouseHeader::find($id);
		if (!$order || $order->isNotOpen()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$order['title'], trans('warehouse.Order ### can not be processed')));
		}

		try {
			DB::transaction(function() use ($request, $order) {
				if (array_sum($request->input('processQuantity')) > 0) {
					WarehouseHistory::create([
							'src' => 'warehouse_headers',
							'src_id' => $order['id'],
							'staff_id' => auth()->user()['id'],
							'machine' => $request->ip(),
							'process_status' => 'processed',
							'notes' => '',
						]);
				}

				foreach ($request->input('processQuantity') as $idx => $qty) {
					if ($qty > 0) {
						$detail = WarehouseDetail::find($request->input('line')[$idx]);
						$detail->update([
								'processed_quantity' => $detail['processed_quantity'] + $qty,
							]);
						WarehouseHistory::create([
								'src' => 'warehouse_details',
								'src_id' => $detail['id'],
								'staff_id' => auth()->user()['id'],
								'machine' => $request->ip(),
								'process_status' => 'updated',
								'notes' => $qty . ' processed',
							]);
					}
				}
				event(new \App\Events\WarehouseUpsertEvent($order));
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", "#".$order->title, trans('warehouse.Order ### processed.')));
	}

	public function viewSingleSerialHistory(Request $reqeuest)
	{
		return view()->first(generateTemplateCandidates('warehouse.serial'));
	}

	public function viewSingleSerialHistoryAjax(Request $request)
	{
		// all Ajax controller does not register with session-history
		// removal no longer needed since this controller is in web-ajax group
		// $this->removeFromHistory();

		$status = [
				'receive' => trans('warehouse.received'),
				'relocate' => trans('warehouse.relocated'),
				'transfer' => trans('warehouse.transfer'),
				'deliver' => trans('warehouse.delivered'),
			];

		$serial = $request->input('serial');
		$result = [];

		foreach (Serial::where('serial', $serial)->orderBy('id', 'desc')->get() as $record) {
			$obj = $record->source;
			$warehouseHeader = ($obj instanceof WarehouseHeader) ? $obj : $obj->warehouseHeader;
			$srcOrder = $warehouseHeader->source;
			$result[] = sprintf(trans('warehouse.%1$s in warehouse order #%2$s(%3$s) at %4$s'),
													$status[$warehouseHeader->type],
													"<a href=\"" . url('warehouse/vieworder/' . $warehouseHeader->id) . "\" target=\"_blank\">" . $warehouseHeader->title . "</a>",
													"<a href=\"" . $srcOrder->getUrl() . "\" target=\"_blank\">" . $warehouseHeader->reference . "</a>",
													DateHelper::dbToGuiDate($warehouseHeader->created_at->format("Y-m-d")) . " " . $warehouseHeader->created_at->format("g:iA"));
		}

		if (count($result) == 0) {
			$result[] = trans('forms.No result found');
		}

		return json_encode($result);
	}

	public function voidOrderPostAjax(Request $request)
	{
		// all Ajax controller does not register with session-history
		// removal no longer needed since this controller is in web-ajax group
		// $this->removeFromHistory();

		$warehouseHeaderObj = WarehouseHeader::find($request->input('id'));

		try {
			DB::transaction(function() use ($request, $warehouseHeaderObj) {
				$warehouseHeaderObj->void($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => [
				'order' => new WarehouseHeaderResource($warehouseHeaderObj),
				'inventory' => $warehouseHeaderObj->generateInventoryUpdate(),
				'bin_availability' => WarehouseBin::getBinIndexedByUniqueTradableAndLocation("deliver"),
			]]);
	}

	public function processWarehouseOrderAjax(Request $request, $id) {
		$warehouseHeaderObj = WarehouseHeader::find($id);

		try {
			DB::transaction(function() use ($request, $warehouseHeaderObj) {
				$warehouseHeaderObj->process($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => [ 'entry' => new WarehouseHeaderResource($warehouseHeaderObj) ] ]);
	}

	public function processOrderPostAjax(Request $request) {

		$warehouseHeaderObj = WarehouseHeader::find($request->input('id'));

		$isDeliver = in_array($warehouseHeaderObj->type, ['deliver']);

		if ($isDeliver) {
			Validator::extend('bin_quantity', WarehouseBinQuantityValidation::class);
			Validator::extend('bin_availability', WarehouseBinAvailabilityValidation::class);
		}

		$arraySize = max(count($request->input('product_id') ?? []), count($request->input('quantity') ?? []), count($request->input('bin') ?? []));

		$validator = Validator::make($request->all(), [
			'product_id' => 'required|array|size:' . $arraySize,
			'quantity' => 'required|array|size:' . $arraySize,
			'bin' => 'required|array|size:' . $arraySize,
			'product_id.*' => 'required|numeric',
			'quantity.*' => 'required|numeric|min:0' . ($isDeliver ? '|bin_quantity:product_id.*,bin.*' : ''),
			'bin.*' => 'required|numeric' . ($isDeliver ? '|bin_availability:product_id.*' : ''),
		]);

		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			$errors = $validator->errors();
			foreach ([ "product_id", "quantity", "bin" ] as $field) {
				if ($errors->has($field)) {
					foreach (range(0, $arraySize-1) as $idx) {
						$validator->getMessageBag()->add($field . '.' . $idx, $errors->get($field)[0]);
					}
				}
			}
			return response()->json([ 'success' => false, 'errors' => $errors ]);
		}

		// prepare submitted quantity indexed by unique_id
		$processedUniqueTradables = [ ];
		// foreach ($request->input('product_id') as $idx => $tradableId) {
		foreach ($request->input('product_id') as $idx => $uniqueTradableId) {
			// $uniqueTradableId = Tradable::find($tradableId)->uniqueTradable->id;
			if (array_key_exists($uniqueTradableId, $processedUniqueTradables)) {
				$processedUniqueTradables[$uniqueTradableId]['total'] += $request->input('quantity')[$idx];
				array_push($processedUniqueTradables[$uniqueTradableId]['lines'], $idx);
			} else {
				$processedUniqueTradables[$uniqueTradableId] = [
					'total' => $request->input('quantity')[$idx],
					'lines' => [ $idx ],
				];
			}
		}

		// prepare balance quantity (from detail) indexed by unique_id
		$expectedUniqueTradables = [ ];
		foreach ($warehouseHeaderObj->detail as $detailLine) {
			if (array_key_exists($detailLine->unique_tradable_id, $expectedUniqueTradables)) {
				$expectedUniqueTradables[$detailLine->unique_tradable_id] += $detailLine->expected_quantity - $detailLine->processed_quantity;
			} else {
				$expectedUniqueTradables[$detailLine->unique_tradable_id] = $detailLine->expected_quantity - $detailLine->processed_quantity;
			}
		}

		$errors = [ ];
		// if some items were never expected
		$unexpectedUniqueTradableIds = array_diff(array_keys($processedUniqueTradables), array_keys($expectedUniqueTradables));
		if (count($unexpectedUniqueTradableIds)) {
			foreach ($unexpectedUniqueTradableIds as $unexpectedUniqueTradableId) {
				foreach ($processedUniqueTradables[$unexpectedUniqueTradableId]['lines'] as $oneLineId) {
					$errors['sku.' . $oneLineId ][] = trans('messages.Item unexpected');
				}
			}
		}

		// validate first, make sure quantity > 0 & quantity < (expected - processed)
		foreach (array_intersect(array_keys($processedUniqueTradables), array_keys($expectedUniqueTradables)) as $theId) {
			if ($expectedUniqueTradables[$theId] < $processedUniqueTradables[$theId]['total']) {
				foreach ($processedUniqueTradables[$theId]['lines'] as $oneLineId) {
					$errors['quantity.' . $oneLineId ][] = trans('messages.Item over-prepared');
				}
			}
		}

		if (count($errors)) {
			return response()->json([ 'success' => false, 'errors' => $errors ]);
		}

		try {
			DB::transaction(function() use ($request, $warehouseHeaderObj) {
				$warehouseHeaderObj->prepare($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => [
				'order' => new WarehouseHeaderResource($warehouseHeaderObj),
				'inventory' => $warehouseHeaderObj->generateInventoryUpdate(),
				'bin_availability' => WarehouseBin::getBinIndexedByUniqueTradableAndLocation("deliver"),
			]]);
	}
}
