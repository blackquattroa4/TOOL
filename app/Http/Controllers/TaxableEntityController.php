<?php

namespace App\Http\Controllers;

use App\Helpers\DateHelper;
use App\TaxableEntity;
use App\Http\Resources\TaxableEntity as TaxableEntityResource;
use DB;
use Illuminate\Http\Request;
use Validator;

class TaxableEntityController extends Controller
{

  CONST VALIDATION_RULES = [
    'code' => 'required|max:10',
    'name' => 'required|max:100',
    'contact' => 'required|max:100',
    'email' => 'required|email|max:100',
    'phone' => 'required|max:20',
    'bstreet' => 'required|min:1',
    'bcity' => 'required|min:1',
    'bstate' => 'required|min:1',
    'bzipcode' => 'required|min:1',
    'sstreet' => 'required|min:1',
    'scity' => 'required|min:1',
    'sstate' => 'required|min:1',
    'szipcode' => 'required|min:1',
  ];

  public function loadTaxableEntityAjax(Request $request, $id) {
    if ($id) {

      $entityObj = TaxableEntity::find($id);
      $contact = $entityObj->contact->last();
      $billing = $entityObj->defaultBillingAddress->first();
      $shipping = $entityObj->defaultShippingAddress->first();

      return response()->json([
        'success' => true,
        'data' => [
          'id' => $entityObj->id,
          'csrf' => csrf_token(),
          'code' => $entityObj->code,
          'type' => $entityObj->type,
          'name' => $entityObj->name,
          'active' => $entityObj->active,
          'contact' => $contact->name,
          'email' => $contact->email,
          'phone' => $contact->phone,
          'bstreet' => $billing->street,
          'bunit' => $billing->unit,
          'bdistrict' => $billing->district,
          'bcity' => $billing->city,
          'bstate' => $billing->state,
          'bcountry' => $billing->country,
          'bzipcode' => $billing->zipcode,
          'sstreet' => $shipping->street,
          'sunit' => $shipping->unit,
          'sdistrict' => $shipping->district,
          'scity' => $shipping->city,
          'sstate' => $shipping->state,
          'scountry' => $shipping->country,
          'szipcode' => $shipping->zipcode,
          'payment' => $entityObj->payment_term_id,
          'currency' => $entityObj->currency_id,
        ]
      ]);
    }

    return response()->json([
      'success' => true,
      'data' => [
        'id' => 0,
        'csrf' => csrf_token(),
        'code' => '',
        'type' => '',
        'name' => '',
        'active' => 0,
        'contact' => '',
        'email' => '',
        'phone' => '',
        'bstreet' => '',
        'bunit' => '',
        'bdistrict' => '',
        'bcity' => '',
        'bstate' => '',
        'bcountry' => '',
        'bzipcode' => '',
        'sstreet' => '',
        'sunit' => '',
        'sdistrict' => '',
        'scity' => '',
        'sstate' => '',
        'scountry' => '',
        'szipcode' => '',
        'payment' => 0,
        'currency' => 0,
      ]
    ]);
  }

  public function createPostAjax(Request $request) {
    // run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), array_merge(self::VALIDATION_RULES, [ 'code' => 'required|unique:taxable_entities|max:10' ]));
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return response()->json(['success' => false, 'errors' => $validator->errors() ]);
		}

    $entityObj = null;

		try {
			DB::transaction(function() use ($request, &$entityObj) {
        $entityObj = TaxableEntity::initialize($request->input());
      });
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.Entity can not be created') ] ] ]);
		}

    return response()->json([ 'success' => true, 'data' => [ 'entity' => new TaxableEntityResource($entityObj) ] ]);
  }

  public function updatePostAjax(Request $request, $id) {
    // run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), self::VALIDATION_RULES);
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return response()->json(['success' => false, 'errors' => $validator->errors() ]);
		}

    $entityObj = TaxableEntity::find($id);

		try {
			DB::transaction(function() use ($request, $entityObj) {
        $entityObj->synchronize($request->input());
      });
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.Entity can not be updated') ] ] ]);
		}

    return response()->json([ 'success' => true, 'data' => [ 'entity' => new TaxableEntityResource($entityObj) ] ]);
  }

  public function getDashboardTaxableEntityAjax($type)
	{
		$types = explode("-", $type);

		return response()->json([ 'success' => true, 'data' => TaxableEntityResource::collection(TaxableEntity::whereIn('type', $types)->get()) ]);
	}

}
