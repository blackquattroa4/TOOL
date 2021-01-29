<?php

namespace App\Http\Controllers;

use App\Helpers\DataSetHelper;
use App\Helpers\DateHelper;
use App\TransactableHeader;
use App\Http\Resources\TransactableHeader as TransactableHeaderResource;
use DB;
use Illuminate\Http\Request;

class TransactableController extends Controller
{
	CONST TRANSACTABLE_CONDITION = [
		"receivable" => "entity_id IN (SELECT id FROM taxable_entities WHERE type in ('customer'))",
		"payable" => "entity_id IN (SELECT id FROM taxable_entities WHERE type in ('supplier','employee'))",
	];

  public function loadTransactableAjax(Request $request, $id) {
    if ($id) {

      $headerObj = Transactableheader::find($id);
      $detailObjs = $headerObj->detail;
      $fmtr = new \NumberFormatter( $headerObj->currency->getFormat()['regex'], \NumberFormatter::CURRENCY );
      $monetaryFormat = "%0." . $headerObj->currency->getFormat()['fdigit'] . "f";

      return response()->json([
        'success' => true,
        'data' => [
          'id' => $headerObj->id,
          'history' => array_map(function($elem) {
							$timeElem = explode(" ", $elem['updated_at']);
							$timeString = DateHelper::dbToGuiDate($timeElem[0]) . " " . date("g:iA", strtotime($elem['updated_at']));
							return sprintf(trans('messages.%1$s %2$s at %3$s'), $elem['staff']['name'], trans('action.'.$elem['process_status']), $timeString);
						}, $headerObj->history()->with('staff')->orderBy('created_at', 'desc')->get()->toArray()),
          'csrf' => csrf_token(),
          'type' => $headerObj->isReceivable() ? 'receivable' : 'payable',
          'status' => $headerObj->status,
          'increment' => $headerObj->title,
    			'show_discount' => in_array('show_discount', explode(',', $headerObj->flags)),
    			'entity' => $headerObj->entity_id,
    			'incur_date' => DateHelper::dbToGuiDate($headerObj->incur_date),
    			'payment' => $headerObj->payment_term_id,
    			'due_date' => DateHelper::dbToGuiDate($headerObj->approx_due_date),
    			'source' => $headerObj->sourceText(),
    			'reference' => $headerObj->reference,
    			'staff' => $headerObj->staff_id,
    			'contact' => $headerObj->contact_id,
    			'currency' => $headerObj->currency_id,
    			'currency_format' => [
              'min' => $headerObj->currency->getFormat()['min'],
    					'regex' => $headerObj->currency->regex,
    					'symbol' => $headerObj->currency->symbol,
    				],
    			'billing' => $headerObj->billing_address_id,
    			'shipping' => $headerObj->shipping_address_id,
    			'notes' => $headerObj->notes,
    			'grand_subtotal' => $fmtr->format($detailObjs->sum(function ($item) { return $item->transacted_amount - $item->discount_amount; })),
    			'tax_amount' => $fmtr->format($detailObjs->sum('tax_amount')),
    			'grand_total' => $fmtr->format($detailObjs->sum(function ($item) { return $item->transacted_amount - $item->discount_amount + $item->tax_amount; })),
    			'balance' => $fmtr->format($headerObj->balance),
    			'line' => $detailObjs->pluck('id'),
    			'product' => $detailObjs->pluck('unique_tradable_id'),
    			'display' => $detailObjs->pluck('display_as'),
    			'unitprice' => array_map(function($elem) use ($monetaryFormat) { return sprintf($monetaryFormat, $elem); }, $detailObjs->pluck('unit_price')->toArray()),
    			'discount' => $detailObjs->pluck('discount'),
    			'disctype' => $detailObjs->map(function($item, $key) { return $item->getDiscountTypeSymbol(); }),
    			'description' => $detailObjs->pluck('description'),
    			'quantity' => array_map(function($elem) { return sprintf(env('APP_QUANTITY_FORMAT'), $elem); }, $detailObjs->pluck('transacted_quantity')->toArray()),
    			'linetax' => $detailObjs->pluck('tax_amount'),
    			'subtotal' => array_map(function($elem) use ($monetaryFormat) { return sprintf($monetaryFormat, $elem["transacted_amount"] - $elem["discount_amount"]); }, $detailObjs->toArray())
        ]
      ]);
    }

    return response()->json([
      'success' => true,
      'data' => [
        'id' => 0,
        'history' => [ ],
        'csrf' => csrf_token(),
        'type' => '',
        'status' => '',
        'increment' => '',
        'show_discount' => false,
        'entity' => 0,
        'incur_date' => '',
        'payment' => 0,
        'due_date' => '',
        'source' => '',
        'reference' => '',
        'staff' => 0,
        'contact' => 0,
        'currency' => 0,
        'currency_format' => [
            'min' => 1,
            'regex' => '',
            'symbol' => '',
          ],
        'billing' => 0,
        'shipping' => 0,
        'notes' => '',
        'grand_subtotal' => '',
        'tax_amount' => '',
        'grand_total' => '',
        'balance' => '',
        'line' => [ ],
        'product' => [ ],
        'display' => [ ],
        'unitprice' => [ ],
        'discount' => [ ],
        'disctype' => [ ],
        'description' => [ ],
        'quantity' => [ ],
        'linetax' => [ ],
        'subtotal' => [ ]
      ]
    ]);
  }

  public function voidPostAjax(Request $request, $id) {

    $transactableHeaderObj = TransactableHeader::find($id);
    if ($transactableHeaderObj->status != 'open') {
      return response()->json([ 'success' => false, 'errors' => [ 'general' => [ str_replace("###", "#".$transactableHeaderObj->title, trans('finance.Receivable ### can not be voided')) ]]]);
    }

    try {
      DB::transaction(function() use ($request, $transactableHeaderObj) {
        // void the transaction
        $transactableHeaderObj->void($request);
      });
    } catch (\Exception $e) {
      $registration = recordAndReportProblem($e);
      return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
    }

    return response()->json([ 'success' => true, 'data' => [ 'transactable' => new TransactableHeaderResource($transactableHeaderObj)]]);
  }

  public function printPostAjax(Request $request, $id)
	{
		$transactableHeaderObj = TransactableHeader::find($id);
		$pdf = $transactableHeaderObj->generatePdf();
		DataSetHelper::addDataSetValue($transactableHeaderObj, 'flags', 'printed');
		$pdf->Output(($transactableHeaderObj->isReceivable() ? "Receivable" : "Payable") . " #".$transactableHeaderObj->title.".pdf", "D");
	}

  public function getDashboardTransactableAjax($type)
	{
		$transactables = TransactableHeader::whereRaw(self::TRANSACTABLE_CONDITION[$type], [])->get();

		return response()->json([ 'success' => true, 'data' => TransactableHeaderResource::collection($transactables) ]);
	}

  public function getDashboardProcessableOrderAjax($type)
	{
		$className = '\\App\\' . ucfirst($type) . 'Header';
		$classResourceName = '\\App\\Http\\Resources\\' . ucfirst($type) . 'Header';
		$cond = [];
		if (auth()->user()->can('ar-create')) { $cond[] = "order"; }
		if (auth()->user()->can('rar-create')) { $cond[] = "return"; }
		return response()->json([ 'success' => true, 'data' => $classResourceName::collection($className::where([['status', 'open'], ['approved', 1 ]])->whereIn('type', $cond)->get()) ]);
	}

}
