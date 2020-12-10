<?php

namespace App\Http\Controllers;

use App\Helpers\HistoryHelper;
use App\Address;
use App\ChartAccount;
use App\Location;
use App\TaxableEntity;
use App\User;
use App\WarehouseBin;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Session;
use Validator;

class LocationController extends Controller
{
	public function update(Request $request)
	{

		// load location detail; errors imply redirect back, flashing input removes old value
		if (!Session::has('alert-danger') && !Session::has('alert-warning') && !Session::has('errors')) {
			$locations = Location::all()->toArray();
			$size = count($locations);
			$request->session()->flashInput([
				'id' => array_column($locations, "id"),
				'name' => array_column($locations, "name"),
				'type' => array_column($locations, "type"),
				'contact_id' => array_column($locations, "contact_id"),
				'address_id' => array_column($locations, "address_id"),
				'active' => array_column($locations, "active"),
				'street' => array_fill(0, $size, ""),
				'unit' => array_fill(0, $size, ""),
				'district' => array_fill(0, $size, ""),
				'city' => array_fill(0, $size, ""),
				'state' => array_fill(0, $size, ""),
				'country' => array_fill(0, $size, array_keys(\App\Helpers\CountryHelper::getAllCountryOptions())[0]),
				'zipcode' => array_fill(0, $size, ""),
			]);
		}

		return view()->first(generateTemplateCandidates('rule.location'),
						[
							'readonly' => false,
							'types' => [
									[
										'key' => 'unknown',
										'value' => trans('forms.Unknown'),
									],
									[
										'key' => 'factory',
										'value' => trans('manufacture.Factory'),
									],
									[
										'key' => 'warehouse',
										'value' => trans('warehouse.Warehouse'),
									],
									[
										'key' => 'rma',
										'value' => trans('rma.RMA'),
									],
								],
							'employees' => User::getAllStaff('name', 'asc'),
							'addresses' => TaxableEntity::theCompany()->shippingAddress,
						]);
	}

	public function updatePost(Request $request)
	{
		//  validation, redirect back if failed.
		$validator = Validator::make($request->all(), [
			"name.*" => 'required|string',
			"type.*" => "required|in:warehouse,rma,factory",
			"contact_id.*" => 'required|min:1',
			"address_id.*" => 'required|min:1',
		]);

		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return redirect('/' . $request->path())
					->with('alert-warning', trans('messages.Please correct all errors'))
					->withErrors($validator) // send back all errors to the form
					->withInput($request->all()); // send back the input so we can repopulate the form
		}

		try {
			DB::transaction(function() use ($request) {
				foreach ($request->input('id') as $index => $id) {
					// address
					if ($request->input('address_id')[$index]) {
						// update
						$address_id = $request->input('address_id')[$index];
					} else {
						// new address
						$address_id = Address::create([
							'entity_id' => TaxableEntity::theCompany()->id,
							'purpose' => 'shipping',
							'is_default' => 0,
							'name' => User::find($request->input('contact_id')[$index])->name,
							'unit' => $request->input('unit')[$index],
							'street' => $request->input('street')[$index],
							'district' => $request->input('district')[$index],
							'city' => $request->input('city')[$index],
							'state' => $request->input('state')[$index],
							'country' => $request->input('country')[$index],
							'zipcode' => $request->input('zipcode')[$index],
						])->id;
					}
					// location
					if ($id) {
						// update
						$location = Location::find($id);
						$location->name = $request->input('name')[$index];
						$location->type = $request->input('type')[$index];
						$location->contact_id = $request->input('contact_id')[$index];
						$location->address_id = $address_id;
						$location->active = in_array($request->input('active')[$index], ["1", "true"]);
						$location->save();
					} else {
						// create
						$account = ChartAccount::create([
							'account' => '10003-XXX',
							'type' => 'asset',
							'currency_id' => TaxableEntity::theCompany()->currency_id,
							'description' => 'inventory of ' . $request->input('name')[$index],
							'active' => 1,
						]);
						$account->update([ 'account' => '10003-' . $account->id ]);
						$location = Location::create([
							'name' => $request->input('name')[$index],
							'type' => $request->input('type')[$index],
							'owner_entity_id' => TaxableEntity::theCompany()->id,
							'address_id' => $address_id,
							'contact_id' => $request->input('contact_id')[$index],
							'inventory_t_account_id' => $account->id,
							'active' => in_array($request->input('active')[$index], ["1", "true"]),
							'notes' => '',
						]);
						WarehouseBin::create([
							'location_id' => $location->id,
							'name' => 'default bin of ' . $request->input('name')[$index],
							'valid' => 1,
						]);
					}
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(1))->with('alert-success', trans('messages.Location updated.'));
	}
}
