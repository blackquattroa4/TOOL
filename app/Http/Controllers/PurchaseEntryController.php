<?php

namespace App\Http\Controllers;

use App\ExpenseHeader;
use App\Helpers\DateHelper;
use App\PurchaseHeader;
use App\Http\Resources\PurchaseHeader as PurchaseHeaderResource;
use App\TransactableHeader;
use App\Http\Resources\TransactableHeader as TransactableHeaderResource;
use App\Rules\PurchaseDetailQuantityRestriction;
use App\TaxableEntity;
use DB;
use Illuminate\Http\Request;
use Validator;

class PurchaseEntryController extends Controller
{
  // validation rules for 'create', 'update'
  CONST VALIDATION_RULES = [
    'reference' => 'required',
    'incoterm' => 'required',
    'inputdate' => 'required',
    'ivcost.*' => "required|numeric",
    'unitprice.*' => "required|numeric",
    'quantity.*' => "required|numeric",
    'ddate.*' => "required|date",
  ];

  public function loadPurchaseEntryAjax(Request $request, $id) {
    if ($id) {

      $headerObj = PurchaseHeader::find($id);
      $detailObjs = $headerObj->purchaseDetail;
      $fmtr = new \NumberFormatter( $headerObj->currency->getFormat()['regex'], \NumberFormatter::CURRENCY );
      $monetaryFormat = "%0." . $headerObj->currency->getFormat()['fdigit'] . "f";

      return response()->json([
        'success' => true,
        'data' => [
          'id' => $headerObj->id,
          'csrf' => csrf_token(),
          'type' => $headerObj->type,
          'status' => $headerObj->status,
          'increment' => $headerObj->title,
          'supplier' => $headerObj->entity_id,
          'inputdate' => DateHelper::dbToGuiDate($headerObj->order_date),
          'payment' => $headerObj->payment_term_id,
          'reference' => $headerObj->reference,
          'incoterm' => $headerObj->fob,
          'via' => $headerObj->via,
          'contact' => $headerObj->contact_id,
          'staff' => $headerObj->purchase_id,
          'currency' => $headerObj->currency_id,
          'billing_address' => $headerObj->billing_address_id,
          'shipping_address' => $headerObj->shipping_address_id,
          'currency_min' => $headerObj->currency->getFormat(true)['min'],
          'currency_regex' => $headerObj->currency->getFormat(true)['regex'],
          'currency_fdigit' => $headerObj->currency->getFormat(true)['fdigit'],
          'currency_symbol' => $headerObj->currency->symbol,
          'history' => array_map(function($elem) {
							$timeElem = explode(" ", $elem['updated_at']);
							$timeString = DateHelper::dbToGuiDate($timeElem[0]) . " " . date("g:iA", strtotime($elem['updated_at']));
							return sprintf(trans('messages.%1$s %2$s at %3$s'), $elem['staff']['name'], trans('action.'.$elem['process_status']), $timeString);
						}, $headerObj->history()->with('staff')->orderBy('created_at', 'desc')->get()->toArray()),
          'line' => $detailObjs->pluck('id'),
          'product' => $detailObjs->pluck('unique_tradable_id'),
          'display' => $detailObjs->pluck('manufacture_model'),
          'ivcost' => array_map(function($elem) use ($monetaryFormat) { return sprintf($monetaryFormat, $elem); }, $detailObjs->pluck('inventory_cost')->toArray()),
          'unitprice' => array_map(function($elem) use ($monetaryFormat) { return sprintf($monetaryFormat, $elem); }, $detailObjs->pluck('unit_price')->toArray()),
          'description' => $detailObjs->pluck('description'),
          'quantity' => array_map(function($elem) { return sprintf(env('APP_QUANTITY_FORMAT'), $elem['ordered_quantity'] - $elem['shipped_quantity']); }, $detailObjs->toArray()),
          'ddate' => array_map(function($elem) { return DateHelper::dbToGuiDate($elem); }, $detailObjs->pluck('delivery_date')->toArray()),
          'warehouse' => $detailObjs->pluck('receiving_location_id'),
          'taxable' => $detailObjs->pluck('taxable'),
          'subtotal' => array_map(function($elem) use ($monetaryFormat) { return sprintf($monetaryFormat, ($elem['ordered_quantity'] - $elem['shipped_quantity']) * $elem['unit_price']); }, $detailObjs->toArray()),
          'untaxed_subtotal' => $fmtr->format($detailObjs->sum(function($detail) { return $detail->taxable ? 0 : $detail->ordered_quantity * $detail->unit_price; })),
          'taxed_subtotal' => $fmtr->format($detailObjs->sum(function($detail) { return $detail->taxable ? $detail->ordered_quantity * $detail->unit_price : 0; })),
          'tax_amount' => $fmtr->format($detailObjs->sum(function($detail) use($headerObj) { return $detail->taxable ? $detail->ordered_quantity * $detail->unit_price * $headerObj->tax_rate / 100 : 0; })),
          'grand_total' => $fmtr->format($detailObjs->sum(function($detail) use($headerObj) { return $detail->taxable ? $detail->ordered_quantity * $detail->unit_price * (100 + $headerObj->tax_rate) / 100 : $detail->ordered_quantity * $detail->unit_price; }))
        ]
      ]);
    }

    return response()->json([
      'success' => true,
      'data' => [
        'id' => 0,
        'csrf' => csrf_token(),
        'type' => '',
        'status' => '',
        'increment' => '',
        'supplier' => 0,
        'inputdate' => '',
        'payment' => 0,
        'incoterm' => '',
        'via' => '',
        'contact' => '',
        'staff' => '',
        'currency' => '',
        'billing_address' => '',
        'shipping_address' => '',
        'currency_min' => 0.01,
        'currency_regex' => '',
        'currency_fdigit' => '',
        'currency_symbol' => '',
        'history' => [ ],
        'line' => [ ],
        'product' => [ ],
        'dipslay' => [ ],
        'ivcost' => [ ],
        'unitprice' => [ ],
        'description' => [ ],
        'quantity' => [ ],
        'ddate' => [ ],
        'warehouse' => [ ],
        'taxable' => [ ],
        'subtotal' => [ ],
        'untaxed_subtotal' => '',
        'taxed_subtotal' => '',
        'tax_amount' => '',
        'grand_total' => ''
      ]
    ]);
  }

  public function createPostAjax(Request $request) {
    // run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), self::VALIDATION_RULES);
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
      return response()->json([ 'success' => false, 'errors' => $validator->errors() ]);
		}
		$purchaseHeaderObj = null;

		try {
			DB::transaction(function() use ($request, &$purchaseHeaderObj) {
				$purchaseHeaderObj = PurchaseHeader::initialize($request->input('type'), $request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
      return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('vrm.Entry can not be created') ] ] ]);
		}

    return response()->json([ 'success' => true, 'data' => [ 'entry' => new PurchaseHeaderResource($purchaseHeaderObj) ] ]);
  }

  public function printPostAjax(Request $request, $id) {
    $purchaseHeaderObj = PurchaseHeader::find($id);
		$pdf = $purchaseHeaderObj->generatePdf();
		$pdf->Output("Purchase ".ucfirst($purchaseHeaderObj->type)." #".$purchaseHeaderObj->title.".pdf", "D");
  }

  public function updatePostAjax(Request $request, $id) {
    // run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), self::VALIDATION_RULES);
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
      return response()->json([ 'success' => false, 'errors' => $validator->errors() ]);
		}

		$purchaseHeaderObj = PurchaseHeader::find($id);
		if ($purchaseHeaderObj->isNotOpen()) {
      return response()->json([ 'success' => false, 'errors' => [ 'general' => [ str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Entry ### can not be updated')) ] ] ]);
		}

		try {
			// update database
			DB::transaction(function() use ($request, $purchaseHeaderObj) {
				$purchaseHeaderObj->synchronize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
      return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

    return response()->json([ 'success' => true, 'data' => [ 'entry' => new PurchaseHeaderResource($purchaseHeaderObj) ] ]);
  }

  // not used for the moment
  public function voidPostAjax(Request $request, $id) {
    // check if entry voidable
    $purchaseHeaderObj = PurchaseHeader::find($id);
    if ($purchaseHeaderObj->status != 'open') {
      return response()->json([ 'success' => false, 'errors' => [ 'general' => [ str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Entry ### can not be voided')) ] ] ]);
    }

    try {
      DB::transaction(function() use ($request, $purchaseHeaderObj) {
        // void the transaction
        $purchaseHeaderObj->void($request);
      });
    } catch (\Exception $e) {
      $registration = recordAndReportProblem($e);
      return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
    }

    return response()->json([ 'success' => true, 'data' => [ 'entry' => new PurchaseHeaderResource($purchaseHeaderObj)]]);
  }

  public function approvePostAjax(Request $request, $id) {
    // check if order is approvable
    $purchaseHeaderObj = PurchaseHeader::find($id);
		if ($purchaseHeaderObj->isNotOpen()) {
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Entry ### can not be approved')) ] ]]);
		}

		try {
			DB::transaction(function() use ($request, $purchaseHeaderObj) {
				switch ($request->input('decision')) {
				case 'approve':
					$purchaseHeaderObj->approve(auth()->user()->id, $request->ip());
					break;
				case 'disapprove':
					$purchaseHeaderObj->disapprove(auth()->user()->id, $request->ip());
					break;
				default:
					break;
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

    return response()->json([ 'success' => true, 'data' => [ 'entry' => new PurchaseHeaderResource($purchaseHeaderObj)]]);
  }

  public function processPostAjax(Request $request, $id) {
    $purchaseHeaderObj = PurchaseHeader::find($id);
		if ($purchaseHeaderObj->isNotOpen()) {
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ str_replace("###", "#".$purchaseHeaderObj->title, trans('vrm.Order ### can not be processed')) ] ]]);
		}
    $allZero = array_reduce(array_keys($request->input('line')), function($carry, $item) use ($request) { return ($carry &= ($request->input('processing.'.$item) == 0)); }, true);
		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), [
				'expiration' => 'required|date',
				'processing.*' => [
					"required",
					"numeric",
					"min:" . ($allZero ? "1" : "0"),
					new PurchaseDetailQuantityRestriction($request->input('line'),
																								DateHelper::guiToDbDate($request->input('expiration')),
																								TaxableEntity::theCompany()->id),
				],
			]);
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return redirect(HistoryHelper::goBackPages(1))
					->with('alert-warning', trans('messages.Please correct all errors'))
					->withErrors($validator) // send back all errors
					->withInput($request->all()); // send back the input so that we can repopulate the form
		}


    $transactableHeaderObj = null;

		try {
			DB::transaction(function() use ($request, $purchaseHeaderObj, &$transactableHeaderObj) {
        // create the transaction
				$transactableHeaderObj = $purchaseHeaderObj->createPayable($request);

				// if amount is 0, close it.
				if ($transactableHeaderObj->balance == 0) {
					$transactableHeaderObj->close($request);
					if (($transactableHeaderObj->src_table == 'expense_headers') &&
						($transactableHeaderObj->src_id > 0)) {
						$expenseHdr = ExpenseHeader::find($transactableHeaderObj->src_id);
						$expenseHdr->update([ 'status' => 'paid' ]);
					}
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

    return response()->json([ 'success' => true, 'data' => [ 'entry' => new PurchaseHeaderResource($purchaseHeaderObj), 'transactable' => new TransactableHeaderResource($transactableHeaderObj) ]]);
  }

  public function releaseAjax(Request $request)
  {
    return response()->json([
      'success' => true,
      'data' => [
        'csrf' => csrf_token()
      ]
    ]);
  }
}
