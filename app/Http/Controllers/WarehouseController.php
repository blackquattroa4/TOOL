<?php

namespace App\Http\Controllers;

use App;
use App\Address;
use App\ChartAccount;
use App\Currency;
use App\Helpers\DateHelper;
use App\Helpers\BarcodeHelper;
use App\Helpers\HistoryHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\QuantityHelper;
use App\Http\Requests;
use App\Http\Resources\WarehouseHeader as WarehouseHeaderResource;
use App\InventoryPdf;
use App\Location;
use App\Parameter;
use App\PaymentTerm;
use App\SalesDetail;
use App\SalesHistory;
use App\SalesHeader;
use App\PurchaseDetail;
use App\PurchaseHistory;
use App\PurchaseHeader;
use App\TaxableEntity;
use App\UniqueTradable;
use App\User;
use App\WarehouseHeader;
use App\LabelAvery5160Pdf;
use App\LabelAvery5163Pdf;
use App\LabelAvery5167Pdf;
use App\WarehouseBin;
use Auth;
use DB;
use NumberFormatter;
use Session;
use Validator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use App\Validations\WarehouseBinDuplicationValidation;

class WarehouseController extends Controller
{
	public function index()
	{
		$switch = [
			'warehouse-order-modal' => auth()->user()->can(['wo-create', 'wo-edit', 'wo-process', 'wo-view']),
			'warehouse-transaction-modal' => auth()->user()->can('wo-view'),
			'create-order-button' => auth()->user()->can('wo-create'),
			'warehouse-order-table' => auth()->user()->can('wo-list'),
			'inventory-table' => auth()->user()->can('wo-list'),
			'generate-label-button' => auth()->user()->can('wo-process'),
			'manage-bin-button' => auth()->user()->can('wo-process'),
			'inventory-report' => auth()->user()->can('wo-list'),
			'aging-report' => auth()->user()->can('wo-list'),
		];

		$level2Switch = [
			'warehouse-order-window' => $switch['warehouse-order-table'] || $switch['create-order-button'],
			'inventory-window' => $switch['inventory-table'],
			'report-window' => $switch['inventory-report'] || $switch['aging-report'],
			'tool-window' => $switch['generate-label-button'] || $switch['manage-bin-button'],
			'warehouse-entry-template' => $switch['warehouse-order-table'],
			'inventory-template' => $switch['inventory-table'],
		];

		return view()->first(generateTemplateCandidates('warehouse.list'), [
				'controlSwitch' => array_merge($switch, $level2Switch)
			]);
	}

	public function getDashboardOrderAjax(Request $request, $type)
	{
		switch ($type) {
			case 'inbound':
				$orders = WarehouseHeader::where('type', 'receive')->orderBy('title', 'desc')->get();
				break;
			case 'outbound':
				$orders = WarehouseHeader::where('type', 'deliver')->orderBy('title', 'desc')->get();
				break;
			case 'from-sales':
				$orders = WarehouseHeader::where('src', 'sales_headers')->orderBy('title', 'desc')->get();
				break;
			default:
				$orders = collect([]);
				break;
		}

		return response()->json([ 'success' => true, 'data' => WarehouseHeaderResource::collection($orders) ]);
	}

	public function getDashboardInventoryAjax(Request $request)
	{
		$canView = auth()->user()->can('wo-view');

		$inventory = array_map(function($item) use ($canView) {
				$bins = UniqueTradable::find($item['sku_id'])->getWarehouseBins($item['location_id']);
				$item['balance'] = sprintf(env('APP_QUANTITY_FORMAT'), $item['balance']);
				$item['bins'] = $bins;
				$item['bins_string'] = implode(", ", $bins);
				$item['can_view'] = $canView;
				return $item;
			},
			InventoryHelper::getWarehouseInventory(date("Y-m-d"), null, TaxableEntity::theCompany()));

		return response()->json([ 'success' => true, 'data' => $inventory ]);
	}

	public function viewTransactions($location, $sku)
	{
		return view()->first(generateTemplateCandidates('warehouse.transactions'), [
							'locations' => Location::getActiveWarehouses('name', 'asc'),
							'skus' => UniqueTradable::getProducts('sku', 'asc'),
							'selected_location' => $location,
							'selected_sku' => $sku
						]);
	}

	public function viewTransactionsAjax(Request $request)
	{
		// all Ajax controller does not register with session-history
		// removal no longer needed since this controller is in web-ajax group
		// $this->removeFromHistory();

		$locationId = $request->input('location');
		$skuId = $request->input('sku');
		$offset = $request->input('offset');
		$count = $request->input('count');

		$result = array();

		foreach (DB::select("SELECT * FROM (SELECT (@id:=@id + 1) AS idx, t0.id, t0.quantity, warehouse_headers.title as `source`, warehouse_headers.reference as `notes`, t0.created_at, (@sum:=@sum + t0.quantity) AS sum FROM warehouse_bin_transactions t0 LEFT JOIN tradables ON tradables.id = t0.tradable_id LEFT JOIN warehouse_details ON warehouse_details.id = t0.warehouse_detail_id LEFT JOIN warehouse_headers ON warehouse_headers.id = warehouse_details.header_id LEFT JOIN warehouse_bins on warehouse_bins.id = t0.bin_id CROSS JOIN (SELECT @sum:=0) table1 CROSS JOIN (SELECT @id:=0) table2 WHERE t0.valid = 1 AND warehouse_bins.location_id = " . $locationId . " AND tradables.unique_tradable_id = " . $skuId . " ORDER BY t0.created_at) AS t1 ORDER BY t1.idx DESC LIMIT " . $count . " OFFSET " . $offset) as $transaction) {
			$result[] = [
					'id' => $transaction->id,
					'date' => DateHelper::dbToGuiDate($transaction->created_at),
					'quantity' => sprintf(env('APP_QUANTITY_FORMAT'), $transaction->quantity),
					'source' => $transaction->source,
					'notes' => $transaction->notes,
					'balance' => $transaction->sum,
				];
		}
		return json_encode([
			'text' => [
				'load_more_transactions' => trans("forms.Load more transactions"),
			],
			'data' => $result
		]);
	}

	public function getInventoryAjax(Request $request)
	{
		$fmtr = new \NumberFormatter( TaxableEntity::theCompany()->currency->getFormat()['regex'], \NumberFormatter::CURRENCY );

		$endDate = DateHelper::guiToDbDate($request->input('date'));

		$result = [];

		// pull inventory data
		foreach (DB::select("select locations.id as location_id, locations.name, unique_tradables.id as sku_id, unique_tradables.sku, sum(quantity) as quantity, sum(quantity*unit_cost) as amount from tradable_transactions left join locations on locations.id = tradable_transactions.location_id left join unique_tradables on unique_tradables.id = tradable_transactions.unique_tradable_id where tradable_transactions.valid = 1 and tradable_transactions.owner_entity_id = " . TaxableEntity::theCompany()->id . " and tradable_transactions.created_at < '" . $endDate . " 23:59:59' group by locations.id, locations.name, sku_id, unique_tradables.sku") as $record) {
			if (!isset($result[$record->location_id])) {
				$result[$record->location_id] = [
						'title' => $record->name,
						'items' => [],
					];
			}
			$result[$record->location_id]['items'][$record->sku_id] = [
							'title' => $record->sku,
							'quantity' => sprintf(env("APP_QUANTITY_FORMAT"), $record->quantity),
							'amount' => $fmtr->format($record->amount),
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

		return view()->first(generateTemplateCandidates('warehouse.aging_inventory'));
	}

	public function getInventoryAgingAjax(Request $request)
	{
		// all Ajax controller does not register with session-history
		// removal no longer needed since this controller is in web-ajax group
		// $this->removeFromHistory();

		$endDate = DateHelper::guiToDbDate($request->input('date'));

		$result = [];

		// pull inventory data
		foreach (DB::select("select locations.id as location_id, locations.name, unique_tradables.id as sku_id, unique_tradables.sku, sum(quantity) as quantity from warehouse_bin_transactions left join warehouse_bins on warehouse_bin_transactions.bin_id = warehouse_bins.id left join locations on locations.id = warehouse_bins.location_id left join tradables on tradables.id = warehouse_bin_transactions.tradable_id left join unique_tradables on unique_tradables.id = tradables.unique_tradable_id where warehouse_bin_transactions.valid = 1 and warehouse_bin_transactions.created_at < '" . $endDate . " 23:59:59' group by locations.id, locations.name, sku_id, unique_tradables.sku") as $record) {
			if (!isset($result['location-'.$record->location_id])) {
				$result['location-'.$record->location_id] = [
						'title' => $record->name,
						'items' => [],
					];
			}
			$age = UniqueTradable::find($record->sku_id)->getWarehouseAging($record->quantity, $record->location_id, $endDate, TaxableEntity::theCompany()->id);
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

	public function printInventory(Request $request)
	{
		// file-download related controller does not register with session-history
		$this->removeFromHistory();

		$endDate = DateHelper::guiToDbDate($request->input('date'));

		$result = InventoryHelper::getWarehouseInventory($endDate, null);

		foreach ($result as $idx => $content) {
			$result[$idx]['balance'] = sprintf(env('APP_QUANTITY_FORMAT'), $result[$idx]['balance']);
		}

		$pdf = new InventoryPdf([
				'title' => "Inventory as of " . $endDate,
				'data' => $result,
			]);

		$pdf->Output("Inventory as of ".$endDate.".pdf", "D");
	}

	public function printLabel(Request $request)
	{
		return view()->first(generateTemplateCandidates('warehouse.label'));
	}

	public function printUpcLabel(Request $request)
	{
		$template = "\\App\\".$request->input('template');
		$quantity = $request->input('quantity');
		$format = $request->input('format');
		$text = $request->input('text');
		$upc = $request->input('upc');

		// generate PDF and pass back
		$serial_image_array = [];
		try {
			for ($a = 0, $b = $quantity; $a < $b; $a++) {
				switch ($format) {
				case 'code128':
					$serial_image_array[] = BarcodeHelper::generateBarcode128($upc, 1, 30, $text);
					break;
				case 'upca':
					$serial_image_array[] = BarcodeHelper::generateBarcodeUPCA($upc, 2, 17, $text);
					break;
				default:
					$image = imagecreate(100, 50);
					$background = imagecolorallocate( $image, 255, 255, 255 );
					$text_color = imagecolorallocate( $image, 0, 0, 0 );
					imagestring($image, 5, 0, 0, $upc, $text_color);
					$filename = tempnam(sys_get_temp_dir(), 'none_');
					imagegif($image, $filename);
					$serial_image_array[] = $filename;
					imagecolordeallocate( $image, $background );
					imagecolordeallocate( $image, $text_color );
					imagedestroy( $image );
					break;
				}
			}
		} catch (\Exception $e) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', trans('messages.Error generating PDF'));
		}

		$pdf = new $template($serial_image_array);
		$pdf->generatePdf();
		$pdf->Output("UPC ".$upc." label.pdf", "D");
	}

	public function printSerialLabel(Request $request)
	{
		$template = "\\App\\".$request->input('template');
		$text = $request->input('text');
		$start = $request->input('start');
		$end = $request->input('end');

		// generate PDF and pass back
		$serial_image_array = [];
		try {
			if (strlen($start) === strlen($end)) {
				$idx = 1;
				$len = strlen($start);
				while ((strncasecmp($start, $end, $idx) === 0) && ($idx <= $len)) {
					$idx++;
				}
				if ($idx < $len) {
					$idx--;
					$prefix = substr($start, 0, $idx);
					if (strcasecmp($start, $end) < 0) {
						$beginNum = intval(substr($start, $idx));
						$endNum = intval(substr($end, $idx));
					} else {
						$beginNum = intval(substr($end, $idx));
						$endNum = intval(substr($start, $idx));
					}
					$digit = strlen(substr($start, $idx));

					for ($a = $beginNum; $a <= $endNum; $a++) {
						$serial_image_array[] = BarcodeHelper::generateBarcode128(($prefix . sprintf("%0" . $digit . "d", $a)), 1, 30, $text);
					}
				}
			}
		} catch (\Exception $e) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', trans('messages.Error generating PDF'));
		}

		$pdf = new $template($serial_image_array);
		$pdf->generatePdf();
		$pdf->Output("Serial ".$start." to ".$end." label.pdf", "D");
	}

	public function printCartonLabel(Request $request)
	{
		$template = "\\App\\".$request->input('template');
		$quantity = $request->input('quantity');
		$format = $request->input('format');
		$text = $request->input('text');
		$upc = $request->input('upc');
		$start = $request->input('start');
		$end = $request->input('end');
		$background = $request->file('background');

		// generate PDF and pass back
		$serial_image_array = [];
		try {
			if (strlen($start) === strlen($end)) {
				$idx = 1;
				$len = strlen($start);
				while ((strncasecmp($start, $end, $idx) === 0) && ($idx <= $len)) {
					$idx++;
				}
				if ($idx < $len) {
					$idx--;
					$prefix = substr($start, 0, $idx);
					$beginNum = 0;
					$endNum = 0;
					if (strcasecmp($start, $end) < 0) {
						$beginNum = intval(substr($start, $idx));
						$endNum = intval(substr($end, $idx));
					} else {
						$beginNum = intval(substr($end, $idx));
						$endNum = intval(substr($start, $idx));
					}
					$digit = strlen(substr($start, $idx));
					for ($a = $beginNum; $a <= $endNum; $a+=$quantity) {
						//$pdf->serial_image_array[] = generateBarcode128(($prefix . sprintf("%0" . $digit . "d", $a)), 1, 30);
						$img = null;
						if (!empty($background)) {
							$img = imagecreatefromjpeg($background);
						} else {
							$img = imagecreate(300, 300);
						}
						// background color has to be allocated first!
						$colorBackground = imagecolorallocate( $img, 255, 255, 255 );
						$colorBlack = imagecolorallocate($img, 0, 0, 0);
						// create graphical representation of product name
						imagestring($img, 16, 0, 0, "Item", $colorBlack);
						imagestring($img, 16, 70, 0, $text, $colorBlack);
						// create graphical representation of UPC code
						imagestring($img, 16, 0, 20, "UPC", $colorBlack);
						$upcBarcodeFile = BarcodeHelper::generateBarcodeUPCA($upc, 2, 17);
						imagecopy($img, imagecreatefromgif($upcBarcodeFile), 70, 20, 0, 0, 224, 53);
						// create graphical representation of quantity
						imagestring($img, 16, 0, 80, "Qty", $colorBlack);
						imagestring($img, 16, 70, 80, $quantity . " pcs", $colorBlack);
						// create graphical representation of beginning/ending serial
						imagestring($img, 16, 0, 100, "Serial", $colorBlack);
						$s1BarcodeFile = BarcodeHelper::generateBarcode128(($prefix . sprintf("%0" . $digit . "d", $a)), 1, 30);
						$s2BarcodeFile = BarcodeHelper::generateBarcode128(($prefix . sprintf("%0" . $digit . "d", $a+$quantity-1)), 1, 30);
						imagecopy($img, imagecreatefromgif($s1BarcodeFile), 70, 100, 0, 0, 136, 49);
						imagecopy($img, imagecreatefromgif($s2BarcodeFile), 70, 160, 0, 0, 136, 49);

						$filename = tempnam(sys_get_temp_dir(), 'none_');
						imagegif($img, $filename);
						$serial_image_array[] = $filename;
						imagecolordeallocate( $img, $colorBlack );
						imagecolordeallocate( $img, $colorBackground );
						imagedestroy( $img );
					}
				}
			}
		} catch (\Exception $e) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', trans('messages.Error generating PDF'));
		}

		$pdf = new $template($serial_image_array);
		$pdf->generatePdf();
		$pdf->Output("Carton ".$text." ".$start." to ".$end." label.pdf", "D");
	}

	public static function generateBinRange($exp)
	{
		$sections = array_filter(array_reverse(preg_split("/(\[[0-9]+\-[0-9]+\])/i", $exp, -1, PREG_SPLIT_DELIM_CAPTURE)));

		$result = [ "" ];

		foreach ($sections as $section) {
			if (preg_match("/(\[[0-9]+\-[0-9]+\])/i", $section)) {
				$subRange = array_values(array_filter(preg_split("/[\[\-\]]/i", $section)));
				$subRange = range($subRange[0], $subRange[1]);
				$newRange = [];
				foreach ($subRange as $subElement) {
					foreach ($result as $element) {
						$newRange[] = $subElement . $element;
					}
				}
				$result = $newRange;
			} else {
				foreach ($result as $id => $element) {
					$result[$id] = $section . $result[$id];
				}
			}
		}

		return $result;
	}

	public function printBinLabel(Request $request)
	{
		$template = "\\App\\".$request->input('template');
		$binRange = self::generateBinRange($request->input('bin_regex'));

		// generate PDF and pass back
		$label_image_array = [];
		try {
			foreach ($binRange as $binLocation) {
				$img = imagecreate(200, 200);
				$colorBackground = imagecolorallocate( $img, 255, 255, 255 );
				$colorBlack = imagecolorallocate($img, 0, 0, 0);
				// create graphical representation of product name
				$binBarcodeFile = BarcodeHelper::generateBarcode128($binLocation, 1, 90);
				$dimensionInfo = getimagesize($binBarcodeFile);
				imagecopy($img, imagerotate(imagecreatefromgif($binBarcodeFile), 90, 0), 10, 30, 0, 0, $dimensionInfo[1], $dimensionInfo[0]);

				$filename = tempnam(sys_get_temp_dir(), 'none_');
				imagegif($img, $filename);
				$label_image_array[] = $filename;
				imagecolordeallocate( $img, $colorBlack );
				imagecolordeallocate( $img, $colorBackground );
				imagedestroy( $img );
			}
		} catch (\Exception $e) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', trans('messages.Error generating PDF'));
		}

		$pdf = new $template($label_image_array);
		$pdf->generatePdf();
		$pdf->Output("Bin label.pdf", "D");
	}

	public function consignmentInventoryAjax(Request $request)
	{
		// all Ajax controller does not register with session-history
		// removal no longer needed since this controller is in web-ajax group
		// $this->removeFromHistory();

		$supplierId = $request->input('supplier');
		$locationId = $request->input('location');

		// use average cost for all existing consignment inventory
		$result = DB::select("select tradables.id, unique_tradables.sku, (select ifnull(sum(quantity*unit_cost), 0)/ifnull(sum(quantity), 1) from tradable_transactions where valid = 1 and owner_entity_id = " . $supplierId . " and location_id = " . $locationId . " and unique_tradable_id = unique_tradables.id) as cost, (select ifnull(sum(quantity), 0) from tradable_transactions where valid = 1 and owner_entity_id = " . $supplierId . " and location_id = " . $locationId . " and unique_tradable_id = unique_tradables.id) as sum from tradables left join unique_tradables on unique_tradables.id = tradables.unique_tradable_id where tradables.supplier_entity_id = " . $supplierId );

		return json_encode($result);
	}

	public function showBin(Request $request)
	{
		// load bin detail; errors imply redirect back, flashing input removes old value
		if (!Session::has('alert-danger') && !Session::has('alert-warning') && !Session::has('errors')) {
			$bins = WarehouseBin::all()->toArray();
			$request->session()->flashInput([
					'line' => array_column($bins, 'id'),
					'location' => array_column($bins, 'location_id'),
					'name' => array_column($bins, 'name'),
					'valid' => array_column($bins, 'valid'),
				]);
		}

		return view()->first(generateTemplateCandidates('warehouse.bins'), [
			'readonly' => false,
		]);
	}

	public function updateBin(Request $request)
	{
		// run the validation rules on the inputs from the form
		Validator::extend('bin_duplication', WarehouseBinDuplicationValidation::class);
		$validator = Validator::make($request->all(), [
			'location.*' => 'numeric',
			'name.*' => 'required|alpha_num|bin_duplication:location.*',
		]);

		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return redirect('/' . $request->path())
					->with('alert-warning', trans('messages.Please correct all errors'))
					->withErrors($validator) // send back all errors to the login form
					->withInput($request->all()); // send back the input (not the password) so that we can repopulate the form
		}

		try {
			DB::transaction(function() use ($request) {
				foreach ($request->input('line') as $index => $id) {
					if ($id) {
						// update
						$bin = warehouseBin::find($id);
						$bin->location_id = $request->input('location')[$index];
						$bin->name = $request->input('name')[$index];
						$bin->valid = in_array($request->input('valid')[$index], ["1", "true"]);
						$bin->save();
					} else {
						// create
						warehouseBin::create([
							'location_id' => $request->input('location')[$index],
							'name' => $request->input('name')[$index],
							'valid' => in_array($request->input('valid')[$index], ["1", "true"]),
						]);
					}
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(1))->with('alert-success', trans('messages.Warehouse bin updated'));
	}
}
