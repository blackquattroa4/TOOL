<?php

namespace App\Http\Controllers;

use App\Helpers\DateHelper;
use App\SalesHeader;
use App\Http\Resources\SalesHeader as SalesHeaderResource;
use App\TransactableHeader;
use App\Http\Resources\TransactableHeader as TransactableHeaderResource;
use App\Rules\SalesDetailQuantityRestriction;
use App\TaxableEntity;
use DB;
use Illuminate\Http\Request;
use Validator;

class SalesEntryController extends Controller
{
  // validation rules for 'create', 'update'
  CONST VALIDATION_RULES = [
    'reference' => 'required',
		'incoterm' => 'required',
		'inputdate' => 'required',
		'expiration' => 'required',
		'unitprice.*' => "required|numeric|min:0",
		'quantity.*' => "required|numeric|min:0",  // should we restrict maximum when return? (total quantity purchased)
  ];

  public function loadSalesEntryAjax(Request $request, $id) {
    if ($id) {

      $headerObj = SalesHeader::find($id);
      $detailObjs = $headerObj->salesDetail;
      $fmtr = new \NumberFormatter( $headerObj->currency->getFormat()['regex'], \NumberFormatter::CURRENCY );
      $monetaryFormat = "%0." . $headerObj->currency->getFormat()['fdigit'] . "f";

      return response()->json([
        'success' => true,
        'data' => [
          'id' => $headerObj->id,
          'csrf' => csrf_token(),
          'increment' => $headerObj->title,
          'type' => $headerObj->type,
          'customer' => $headerObj->entity_id,
          'reserved_receivable_title' => $headerObj->reserved_receivable_title,
          'inputdate' => DateHelper::dbToGuiDate($headerObj->order_date),
          'payment' => $headerObj->payment_term_id,
          'expiration' => DateHelper::dbToGuiDate($headerObj->order_date),
          'incoterm' => $headerObj->fob,
          'status' => $headerObj->status,
          'contact' => $headerObj->contact_id,
          'reference' => $headerObj->reference,
          'currency' => $headerObj->currency_id,
          'staff' => $headerObj->sales_id,
          'tax_rate' => $headerObj->tax_rate,
          'via' => $headerObj->via,
          'show_bank_account' => $headerObj->show_bank_account,
          'show_discount' => $headerObj->show_discount,
          'email_when_invoiced' => $headerObj->email_when_invoiced,
          'palletized' => $headerObj->palletized,
          'warehouse' => $headerObj->shipping_location_id,
          'billing_address' => $headerObj->billing_address_id,
          'shipping_address' => $headerObj->shipping_address_id,
          'currency_min' => $headerObj->currency->getFormat(true)['min'],
          'currency_regex' => $headerObj->currency->getFormat(true)['regex'],
          'currency_fdigit' => $headerObj->currency->getFormat(true)['fdigit'],
          'currency_symbol' => $headerObj->currency->symbol,
          'currency_icon' => $fmtr->getSymbol(\NumberFormatter::CURRENCY_SYMBOL),
          'history' => array_map(function($elem) {
							$timeElem = explode(" ", $elem['updated_at']);
							$timeString = DateHelper::dbToGuiDate($timeElem[0]) . " " . date("g:iA", strtotime($elem['updated_at']));
							return sprintf(trans('messages.%1$s %2$s at %3$s'), $elem['staff']['name'], trans('action.'.$elem['process_status']), $timeString);
						}, $headerObj->history()->with('staff')->orderBy('created_at', 'desc')->get()->toArray()),
          'shipment_info' => $headerObj->getShipmentInformation(),
          'notes' => $headerObj->notes,
          'line' => $detailObjs->pluck('id'),
          'product' => $detailObjs->pluck('unique_tradable_id'),
          'display' =>  $detailObjs->pluck('display_as'),
          'unitprice' => array_map(function($elem) use ($monetaryFormat) { return sprintf($monetaryFormat, $elem); }, $detailObjs->pluck('unit_price')->toArray()),
          'description' => $detailObjs->pluck('description'),
          'quantity' => array_map(function($elem) { return sprintf(env('APP_QUANTITY_FORMAT'), $elem['ordered_quantity'] - $elem['shipped_quantity']); }, $detailObjs->toArray()),
          'disctype' => $detailObjs->map(function ($detail) { return $detail->getDiscountTypeSymbol(); }),
          'discount' => $detailObjs->pluck('discount'),
          'taxable' => $detailObjs->pluck('taxable'),
          'subtotal' => array_map(function($elem) use ($monetaryFormat) { return sprintf($monetaryFormat, ($elem['ordered_quantity'] - $elem['shipped_quantity']) * (($elem['discount_type'] == 'amount') ? ($elem['unit_price'] - $elem['discount']) : ($elem['unit_price'] * (100 - $elem['discount']) / 100))); }, $detailObjs->toArray()),
          'untaxed_subtotal' => $fmtr->format($detailObjs->sum(function($detail) { return $detail->taxable ? 0 : ($detail->ordered_quantity - $detail->shipped_quantity) * (($detail->discount_type == 'amount') ? ($detail->unit_price - $detail->discount) : ($detail->unit_price * (100 - $detail->discount) / 100)); })),
          'taxed_subtotal' => $fmtr->format($detailObjs->sum(function($detail) { return $detail->taxable ? ($detail->ordered_quantity - $detail->shipped_quantity) * (($detail->discount_type == 'amount') ? ($detail->unit_price - $detail->discount) : ($detail->unit_price * (100 - $detail->discount) / 100)) : 0; })),
          'tax_amount' => $fmtr->format($detailObjs->sum(function($detail) use($headerObj) { return $detail->taxable ? ($detail->ordered_quantity - $detail->shipped_quantity) * (($detail->discount_type == 'amount') ? ($detail->unit_price - $detail->discount) : ($detail->unit_price * (100 - $detail->discount) / 100)) * $headerObj->tax_rate / 100 : 0; })),
          'grand_total' => $fmtr->format($detailObjs->sum(function($detail) use($headerObj) { return $detail->taxable ?
            ($detail->ordered_quantity - $detail->shipped_quantity) * (($detail->discount_type == 'amount') ? ($detail->unit_price - $detail->discount) : ($detail->unit_price * (100 - $detail->discount) / 100)) * (100 + $headerObj->tax_rate) / 100 :
            ($detail->ordered_quantity - $detail->shipped_quantity) * (($detail->discount_type == 'amount') ? ($detail->unit_price - $detail->discount) : ($detail->unit_price * (100 - $detail->discount) / 100)); })),
        ]
      ]);
    }

    return response()->json([
      'success' => true,
      'data' => [
        'id' => 0,
        'csrf' => csrf_token(),
        'increment' => '',
        'type' => '',
        'customer' => 0,
        'reserved_receivable_title' => '',
        'inputdate' => '',
        'payment' => 0,
        'expiration' => '',
        'incoterm' => '',
        'status' => '',
        'contact' => 0,
        'reference' => '',
        'currency' => 0,
        'staff' => 0,
        'tax_rate' => 0,
        'via' => '',
        'show_bank_account' => 0,
        'show_discount' => 0,
        'email_when_invoiced' => 0,
        'palletized' => 0,
        'warehouse' => 0,
        'billing_address' => 0,
        'shipping_address' => 0,
        'currency_min' => 0.01,
        'currency_regex' => '',
        'currency_fdigit' => '',
        'currency_symbol' => '',
        'currency_icon' => '',
        'history' => [ ],
        'shipment_info' => [ ],
        'notes' => '',
        'line' => [ ],
        'product' => [ ],
        'display' => [ ],
        'unitprice' => [ ],
        'description' => [ ],
        'quantity' => [ ],
        'disctype' => [ ],
        'discount' => [ ],
        'taxable' => [ ],
        'subtotal' => [ ],
        'untaxed_subtotal' => '',
        'taxed_subtotal' => '',
        'tax_amount' => '',
        'grand_total' => '',
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
		$salesHeaderObj = null;

		try {
			DB::transaction(function() use ($request, &$salesHeaderObj) {
				$salesHeaderObj = SalesHeader::initialize($request->input('type'), $request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('crm.Entry can not be created') ] ] ]);
		}

		return response()->json([ 'success' => true, 'data' => [ 'entry' => new SalesHeaderResource($salesHeaderObj) ] ]);
  }

  public function printPostAjax(Request $request, $id) {
    $salesHeaderObj = SalesHeader::find($id);
    $pdf = $salesHeaderObj->generatePdf();
    $pdf->Output("Sales ".ucfirst($salesHeaderObj->type)." #".$salesHeaderObj->title.".pdf", "D");
  }

  public function updatePostAjax(Request $request, $id) {
    // run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), self::VALIDATION_RULES);
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return response()->json([ 'success' => false, 'errors' => $validator->errors() ]);
		}

		$salesHeaderObj = SalesHeader::find($id);
		if ($salesHeaderObj->isNotOpen()) {
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ str_replace("###", "#".$salesHeaderObj->title, trans('crm.Entry ### can not be updated')) ] ] ]);
		}

		try {
			DB::transaction(function() use ($request, $salesHeaderObj) {
				$salesHeaderObj->synchronize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => [ 'entry' => new SalesHeaderResource($salesHeaderObj) ] ]);
  }

  // not used for the moment
  public function voidPostAjax(Request $request, $id) {
    // check if entry voidable
    $salesHeaderObj = SalesHeader::find($id);
    if ($salesHeaderObj->status != 'open') {
      return response()->json([ 'success' => false, 'errors' => [ 'general' => [ str_replace("###", "#".$salesHeaderObj->title, trans('crm.Entry ### can not be voided')) ] ] ]);
    }

    try {
      DB::transaction(function() use ($request, $salesHeaderObj) {
        // void the transaction
        $purchaseHeaderObj->void($request);
      });
    } catch (\Exception $e) {
      $registration = recordAndReportProblem($e);
      return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
    }

    return response()->json([ 'success' => true, 'data' => [ 'entry' => new SalesHeaderResource($salesHeaderObj)]]);
  }

  public function approvePostAjax(Request $request, $id) {
    $salesHeaderObj = SalesHeader::find($id);
		if ($salesHeaderObj->isNotOpen()) {
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ str_replace("###", "#".$salesHeaderObj->title, trans('crm.Entry ### can not be approved')) ] ]]);
		}

		try {
			DB::transaction(function() use ($request, $salesHeaderObj) {
				switch ($request->input('decision')) {
				case 'approve':
					$salesHeaderObj->approve(auth()->user()->id, $request->ip());
					break;
				case 'disapprove':
					$salesHeaderObj->disapprove(auth()->user()->id, $request->ip());
					break;
				default:
					break;
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

    return response()->json([ 'success' => true, 'data' => [ 'entry' => new SalesHeaderResource($salesHeaderObj)]]);
  }

  public function processPostAjax(Request $request, $id) {
    $salesHeaderObj = SalesHeader::find($id);
		if ($salesHeaderObj->isNotOpen()) {
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ str_replace("###", "#".$salesHeaderObj->title, trans('crm.Entry ### can not be processed')) ] ]]);
		}

    // run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), [
			'expiration' => 'required|date',
			'processing.*' => [
					"required",
					"numeric",
					"min:" . (array_reduce(array_keys($request->input('line')), function($carry, $item) use ($request) { return ($carry &= ($request->input('processing.'.$item) == 0)); }, true) ? "1" : "0"),
					new SalesDetailQuantityRestriction($request->input('line'),
																							DateHelper::guiToDbDate($request->input('expiration')),
																							($salesHeaderObj->isOrder() && env('ACCOUNT_CONSIGNMENT_INVENTORY')) ? -1 : TaxableEntity::theCompany()->id),
				],
		]);
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return response()->json([ 'success' => false, 'errors' => $validator->errors() ]);
		}

    $transactableHeaderObj = null;

		try {
			DB::transaction(function() use ($request, $salesHeaderObj, &$transactableHeaderObj) {
        // create the transaction
				$transactableHeaderObj = $salesHeaderObj->createReceivable($request);

				// if $amount is $0, close it.
				if ($transactableHeaderObj->balance == 0) {
					$transactableHeaderObj->close($request);
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => [ 'entry' => new SalesHeaderResource($salesHeaderObj), 'transactable' => new TransactableHeaderResource($transactableHeaderObj) ]]);
  }

  public function reserveAjax(Request $request)
  {
    return response()->json([
      'success' => true,
      'data' => [
        'csrf' => csrf_token()
      ]
    ]);
  }
}
