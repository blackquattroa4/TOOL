<?php

namespace App\Http\Controllers;

use App;
use App\ChartAccount;
use App\Document;
use App\Measurement;
use App\TradableFaq;
use App\TradableNotice;
use App\Tradable;
use App\UniqueTradable;
use App\UniqueTradableRestriction;
use App\Helpers\CountryHelper;
use App\Helpers\HistoryHelper;
use App\Http\Requests;
use App\Http\Resources\Tradable as TradableResource;
use App\Http\Resources\TradableNotice as TradableNoticeResource;
use App\Http\Resources\TradableFAQ as TradableFAQResource;
use App\TaxableEntity;
use App\User;
use Auth;
use DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Session;
use Storage;
use Validator;

class ProductController extends Controller
{
	public function index()
	{

		$switch = [
			'product-table' => auth()->user()->can('pd-list'),
			'product-modal' => auth()->user()->can(['pd-create', 'pd-edit', 'pd-view']),
			'product-analysis-modal' => auth()->user()->can('pd-view'),
			'create-product-button' => auth()->user()->can('pd-create'),
			'notice-table' => auth()->user()->can('pd-list'),
			'notice-modal' => auth()->user()->can(['pd-create', 'pd-edit', 'pd-view']),
			'create-notice-button' => auth()->user()->can('pd-create'),
			'faq-table' => auth()->user()->can('pd-list'),
			'faq-modal' => auth()->user()->can(['pd-create', 'pd-edit', 'pd-view']),
			'create-faq-button' => auth()->user()->can('pd-create'),
			'document-modal' => auth()->user()->can(['pd-create', 'pd-view', 'pd-edit']),
			'product-faq-modal' => auth()->user()->can(['pd-create', 'pd-view', 'pd-edit']),
			'product-notice-modal' => auth()->user()->can(['pd-create', 'pd-view', 'pd-edit']),
		];

		$level2Switch = [
			'product-window' => $switch['product-table'] || $switch['create-product-button'],
			'notice-window' => $switch['notice-table'] || $switch['create-notice-button'],
			'faq-window' => $switch['faq-table'] || $switch['create-faq-button'],

			'product-template' => $switch['product-table'] || $switch['product-modal'],
			'notice-template' => $switch['notice-table'] || $switch['notice-modal'],
			'faq-template' => $switch['faq-table'] || $switch['faq-modal'],
		];

		return view()->first(generateTemplateCandidates('product.list'), [
				'controlSwitch' => array_merge($switch, $level2Switch)
			]);
	}

	public function getDashboardTradableAjax(Request $request)
	{
			return response()->json([ 'success' => true, 'data' => TradableResource::collection(Tradable::select('tradables.*')->leftjoin('unique_tradables', 'tradables.unique_tradable_id', '=', 'unique_tradables.id')->where('stockable', '1')->get()) ]);
	}

	public function getDashboardTradableUpdateAjax(Request $request)
	{
			return response()->json([ 'success' => true, 'data' => TradableNoticeResource::collection(TradableNotice::all()) ]);
	}

	public function getDashboardTradableFAQAjax(Request $request)
	{
			return response()->json([ 'success' => true, 'data' => TradableFAQResource::collection(TradableFAQ::all()) ]);
	}

	public function createProduct(Request $request)
	{
		// flash old input data so it won't mistakenly appears on page.
		// but this cause old input to disappear when validation failed
		//$request->session()->ageFlashData();

		$supplier = array();
		$result = DB::select("select id, code, name from taxable_entities where `type`='supplier' order by code asc");
		foreach ($result as $oneResult) {
			$supplier[$oneResult->id] = $oneResult->code . "&emsp;" . $oneResult->name;
		}

		$account = array();
		$result = DB::select("select id, account, description from chart_accounts where `type`='expense' and active=1 order by account asc");
		foreach ($result as $oneResult) {
			$account[$oneResult->id] = $oneResult->account . "&emsp;" . $oneResult->description;
		}

		$measurement = array();
		foreach(Measurement::where('conversion_ratio', 1)->where('active', 1)->get() as $oneResult) {
			$measurement[$oneResult->type] = $oneResult->symbol;
		}

		return view()->first(generateTemplateCandidates('form.tradable'),
					array(
						'source' => array(
							'title' => trans('product.New product'),
							'post_url' => '/' . $request->path(),
							'action' => trans('forms.Create')
						),
						'misc' => array(
							'length' => $measurement['length'],
							'weight' => $measurement['weight'],
						),
						'supplier' => $supplier,
						'country' => CountryHelper::getAllCountryOptions(),
						'account' => $account,
						'read' => array(
							'model' => 0,
						),
					));
	}

	public function createProductPost(Request $request)
	{
		// validate first.
		// validate the info, create rules for the inputs
		$rules = array(
			'model' => 'required|max:50',
			'description' => 'required|max:150',
			'productid' => 'required_if:itemtype,stockable',
			'content' => 'required_if:itemtype,stockable',
		);
		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), $rules);

		// define a lambda function to reuse for validation.
		$lambda = function($input) { return $input->itemtype == 'stockable'; };
		// conditionally add rules based on itemtype
		$validator->sometimes('unit_length', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('unit_width', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('unit_height', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('unit_weight', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('per_carton', 'required|integer|min:1', $lambda);
		$validator->sometimes('carton_length', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('carton_width', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('carton_height', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('carton_weight', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('per_pallet', 'required|integer|min:1', $lambda);
		$validator->sometimes('lead_day', 'required|integer|min:1', $lambda);

		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return redirect('/' . $request->path())
					->with('alert-warning', trans('messages.Please correct all errors'))
					->withErrors($validator) // send back all errors to the login form
					->withInput($request->all()); // send back the input (not the password) so that we can repopulate the form
		}

		try {
			DB::transaction(function() use ($request) {
				Tradable::initialize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		// show meesage of success, and give option to go back to 'dashboard'
		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', trans('product.New product added.'));
	}

	public function createPostAjax(Request $request)
	{
		// validate first.
		// validate the info, create rules for the inputs
		$rules = array(
			'model' => 'required|max:50',
			'description' => 'required|max:150',
			'productid' => 'required_if:itemtype,stockable',
			'content' => 'required_if:itemtype,stockable',
		);
		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), $rules);

		// define a lambda function to reuse for validation.
		$lambda = function($input) { return $input->itemtype == 'stockable'; };
		// conditionally add rules based on itemtype
		$validator->sometimes('unit_length', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('unit_width', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('unit_height', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('unit_weight', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('per_carton', 'required|integer|min:1', $lambda);
		$validator->sometimes('carton_length', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('carton_width', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('carton_height', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('carton_weight', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('per_pallet', 'required|integer|min:1', $lambda);
		$validator->sometimes('lead_day', 'required|integer|min:1', $lambda);

		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return response()->json([ 'success' => false, 'errors' => $validator->errors() ]);
		}

		$tradable = null;
		try {
			DB::transaction(function() use ($request, &$tradable) {
				$tradable = Tradable::initialize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => new TradableResource($tradable) ]);
	}

	public function updateProduct($id, Request $request)
	{
		$tradable = Tradable::find($id);

		if (!$tradable) {
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', str_replace("###", "#$id", trans('product.Product ### can not be updated')));
		}

		$uniqueTradable = $tradable->uniqueTradable;

		if ($uniqueTradable->stockable && (!$uniqueTradable->expendable)) {
			$itemType = "stockable";
		} else if ((!$uniqueTradable->stockable) && $uniqueTradable->expendable) {
			$itemType = "expendable";
		} else {
			$itemType = "";
		}

		// flash following data into session so that it's available for old() function call in template
		$expense_t_account_id = $uniqueTradable->expense_t_account_id;
		if (ChartAccount::find($expense_t_account_id)->type == 'unknown') {
			$expense_t_account_id = 0;
		}

		// load product detail; errors imply redirect back, flashing input removes old value
		if (!Session::has('alert-danger') && !Session::has('alert-warning') && !Session::has('errors')) {
			$request->session()->flashInput([
				'model' => $uniqueTradable->sku,
				'description' => $uniqueTradable->description,
				'productid' => $uniqueTradable->product_id,
				'active' => $tradable->current,
				'phaseout' => $uniqueTradable->phasing_out,
				'itemtype' => $itemType,
				'forecast' => $uniqueTradable->forecastable,
				'account' => $expense_t_account_id,
				'serial_pattern' => $tradable->serial_pattern,
				'supplier' => $tradable->supplier_entity_id,
				'unit_weight' => $tradable->unit_weight,
				'unit_length' => $tradable->unit_length,
				'unit_width' => $tradable->unit_width,
				'unit_height' => $tradable->unit_height,
				'per_carton' => $tradable->unit_per_carton,
				'carton_weight' => $tradable->carton_weight,
				'carton_length' => $tradable->carton_length,
				'carton_width' => $tradable->carton_width,
				'carton_height' => $tradable->carton_height,
				'per_pallet' => $tradable->carton_per_pallet,
				'lead_day' => $tradable->lead_days,
				'content' => $tradable->content,
				'country' => $tradable->manufacture_origin,
			]);
		}

		$supplier = array();
		$result = DB::select("select id, code, name from taxable_entities where `type`='supplier' order by code asc");
		foreach ($result as $oneResult) {
			$supplier[$oneResult->id] = $oneResult->code . "&emsp;" . $oneResult->name;
		}

		$account = array();
		$result = DB::select("select id, account, description from chart_accounts where `type`='expense' and active=1 order by account asc");
		foreach ($result as $oneResult) {
			$account[$oneResult->id] = $oneResult->account . "&emsp;" . $oneResult->description;
		}

		$measurement = array();
		foreach(Measurement::where('conversion_ratio', 1)->where('active', 1)->get() as $oneResult) {
			$measurement[$oneResult->type] = $oneResult->symbol;
		}

		return view()->first(generateTemplateCandidates('form.tradable'),
					array(
						'source' => array(
							'title' => trans('product.Update product'),
							'post_url' => '/' . $request->path(),
							'action' => trans('forms.Update'),
						),
						'misc' => array(
							'length' => $measurement['length'],
							'weight' => $measurement['weight'],
						),
						'supplier' => $supplier,
						'country' => CountryHelper::getAllCountryOptions(),
						'account' => $account,
						'read' => array(
							'model' => 1,
						)
					));
	}

	public function updateProductPost($id, Request $request)
	{
		// validate first.
		// validate the info, create rules for the inputs
		$rules = array(
			'model' => 'required|max:50',
			'description' => 'required|max:150',
			'productid' => 'required_if:itemtype,stockable',
			'content' => 'required_if:itemtype,stockable',
		);
		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), $rules);

		// define a lambda function to reuse for validation.
		$lambda = function($input) { return $input->itemtype == 'stockable'; };
		// conditionally add rules based on itemtype
		$validator->sometimes('unit_length', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('unit_width', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('unit_height', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('unit_weight', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('per_carton', 'required|integer|min:1', $lambda);
		$validator->sometimes('carton_length', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('carton_width', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('carton_height', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('carton_weight', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('per_pallet', 'required|integer|min:1', $lambda);
		$validator->sometimes('lead_day', 'required|integer|min:1', $lambda);

		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return redirect('/' . $request->path())
					->with('alert-warning', trans('messages.Please correct all errors'))
					->withErrors($validator) // send back all errors to the login form
					->withInput($request->all()); // send back the input (not the password) so that we can repopulate the form
		}

		try {
			DB::transaction(function() use ($request, $id) {
				Tradable::find($id)->synchronize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		// show meesage of success, and give option to go back to 'dashboard' member function of this controller
		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', trans('product.Product updated.'));
	}

	public function updatePostAjax($id, Request $request)
	{
		// validate first.
		// validate the info, create rules for the inputs
		$rules = array(
			'model' => 'required|max:50',
			'description' => 'required|max:150',
			'productid' => 'required_if:itemtype,stockable',
			'content' => 'required_if:itemtype,stockable',
		);
		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), $rules);

		// define a lambda function to reuse for validation.
		$lambda = function($input) { return $input->itemtype == 'stockable'; };
		// conditionally add rules based on itemtype
		$validator->sometimes('unit_length', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('unit_width', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('unit_height', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('unit_weight', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('per_carton', 'required|integer|min:1', $lambda);
		$validator->sometimes('carton_length', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('carton_width', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('carton_height', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('carton_weight', 'required|numeric|min:0.01', $lambda);
		$validator->sometimes('per_pallet', 'required|integer|min:1', $lambda);
		$validator->sometimes('lead_day', 'required|integer|min:1', $lambda);

		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return response()->json([ 'success' => false, 'errors' => $validator->errors() ]);
		}

		$tradable = Tradable::find($id);
		try {
			DB::transaction(function() use ($request, $tradable) {
				$tradable->synchronize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => new TradableResource($tradable) ]);
	}

	public function viewproduct($id, Request $request)
	{
		$product = Tradable::find($id);

		$notice = array();
		$result = DB::select("select tradable_notices.id, tradable_notices.summary, tradable_notices.document_id, tradable_notices.created_at, users.name, (select group_concat('\"',tradable_id,'\":\"',unique_tradables.sku,'\"') from tradable_notice_tradable left join tradables on tradable_notice_tradable.tradable_id = tradables.id left join unique_tradables on unique_tradables.id = tradables.unique_tradable_id where notice_id = tradable_notices.id) as products from tradable_notices left join documents on tradable_notices.document_id = documents.id left join users on documents.creator_id = users.id left join tradable_notice_tradable on tradable_notice_tradable.notice_id = tradable_notices.id
		where tradable_notice_tradable.tradable_id = " . $id);
		foreach ($result as $oneEntry) {
			$notice[] = [
					'summary' => $oneEntry->summary,
					'date' => date("Y-m-d", strtotime($oneEntry->created_at)),
					'staff' => $oneEntry->name,
					'products' => json_decode("{" . $oneEntry->products . "}"),
					'document_id' => $oneEntry->document_id,
					'can_view' => Auth::user()->can('pd-view'),
				];
		}

		$faq = array();
		$result = DB::select("select tradable_faqs.id, tradable_faqs.question, tradable_faqs.answer, tradable_faqs.document_id, tradable_faqs.created_at, users.name, (select group_concat('\"',tradable_id,'\":\"',unique_tradables.sku,'\"') from tradable_faq_tradable left join tradables on tradable_faq_tradable.tradable_id = tradables.id left join unique_tradables on unique_tradables.id = tradables.unique_tradable_id where faq_id = tradable_faqs.id) as products from tradable_faqs left join documents on tradable_faqs.document_id = documents.id left join users on documents.creator_id = users.id left join tradable_faq_tradable on tradable_faq_tradable.faq_id = tradable_faqs.id
		where tradable_faq_tradable.tradable_id = " . $id);
		foreach ($result as $oneEntry) {
			$faq[] = [
					'question' => $oneEntry->question,
					'date' => date("Y-m-d", strtotime($oneEntry->created_at)),
					'staff' => $oneEntry->name,
					'products' => json_decode("{" . $oneEntry->products . "}"),
					'document_id' => $oneEntry->document_id,
					'can_view' => Auth::user()->can('pd-view'),
				];
		}

		return view()->first(generateTemplateCandidates('product.viewproduct'), ['product' => $product, 'misc' => [ 'length' => 'in', 'weight' => 'lb' ], 'notice' => $notice, 'faq' => $faq]);
	}

	public function loadProductAjax(Request $request, $id)
	{
		if ($id) {
			$tradable = Tradable::find($id);
			$uniqueTradable = $tradable->uniqueTradable;

			if ($uniqueTradable->stockable && (!$uniqueTradable->expendable)) {
				$itemType = "stockable";
			} else if ((!$uniqueTradable->stockable) && $uniqueTradable->expendable) {
				$itemType = "expendable";
			} else {
				$itemType = "";
			}

			// flash following data into session so that it's available for old() function call in template
			$expense_t_account_id = $uniqueTradable->expense_t_account_id;
			if (ChartAccount::find($expense_t_account_id)->type == 'unknown') {
				$expense_t_account_id = 0;
			}

			// load product detail; errors imply redirect back, flashing input removes old value
			return response()->json([
				'success' => true,
				'data' => [
					'csrf' => csrf_token(),
					'id' => $tradable->id,
					'sku' => $uniqueTradable->sku,
					'description' => $uniqueTradable->description,
					'product_id' => $uniqueTradable->product_id,
					'phasing_out' => $uniqueTradable->phasing_out,
					'item_type' => $itemType,
					'forecastable' => $uniqueTradable->forecastable,
					'account' => $expense_t_account_id,
					'current' => $tradable->current,
					'serial_pattern' => $tradable->serial_pattern,
					'supplier' => $tradable->supplier_entity_id,
					'unit_length' => $tradable->unit_length,
					'unit_width' => $tradable->unit_width,
					'unit_height' => $tradable->unit_height,
					'unit_weight' => $tradable->unit_weight,
					'unit_per_carton' => $tradable->unit_per_carton,
					'carton_length' => $tradable->carton_length,
					'carton_width' => $tradable->carton_width,
					'carton_height' => $tradable->carton_height,
					'carton_weight' => $tradable->carton_weight,
					'carton_per_pallet' => $tradable->carton_per_pallet,
					'lead_day' => $tradable->lead_days,
					'content' => $tradable->content,
					'country' => $tradable->manufacture_origin,
				]
			]);
		}

		return response()->json([
			'success' => true,
			'data' => [
				'csrf' => csrf_token(),
				'id' => 0,
				'sku' => '',
				'description' => '',
				'product_id' => '',
				'phasing_out' => false,
				'item_type' => '',
				'forecastable' => false,
				'account' => 0,
				'current' => true,
				'serial_pattern' => '',
				'supplier' => 0,
				'unit_length' => 0,
				'unit_width' => 0,
				'unit_height' => 0,
				'unit_weight' => 0,
				'unit_per_carton' => 0,
				'carton_length' => 0,
				'carton_width' => 0,
				'carton_height' => 0,
				'carton_weight' => 0,
				'carton_per_pallet' => 0,
				'lead_day' => 0,
				'content' => '',
				'country' => 0,
			]
		]);
	}

	public function createProductNotice(Request $request)
	{
		return view()->first(generateTemplateCandidates("form.tradable_notice"), [ 'readonly' => false, 'product' => Tradable::getCurrentProducts('sku', 'asc'), 'action' => trans('forms.Create') ]);
	}

	public function createProductNoticePost(Request $request)
	{
		$rules = [
					'product' => "required|min:1",
					'summary' => "required",
					'thefile' => "required|file",
				];

		$validator = Validator::make($request->all(), $rules);

		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return redirect('/' . $request->path())
					->with('alert-warning', trans('messages.Please correct all errors'))
					->withErrors($validator)
					->withInput($request->all());
		}

		try {
			DB::transaction(function() use ($request) {
				TradableNotice::initialize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(2))->with("alert-success", trans("product.Product update notice created"));
	}

	public function createNoticePostAjax(Request $request)
	{
		$rules = [
					'product' => "required|min:1",
					'summary' => "required",
					'thefile' => "required|file",
				];

		$validator = Validator::make($request->all(), $rules);

		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return response()->json([ 'success' => false, 'errors' => $validator->errors() ]);
		}

		$notice = null;
		try {
			DB::transaction(function() use ($request, &$notice) {
				$notice = TradableNotice::initialize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => new TradableNoticeResource($notice) ]);
	}

	public function updateProductNotice($id, Request $request)
	{
		// load notice detail; errors imply redirect back, flashing input removes old value
		if (!Session::has('alert-danger') && !Session::has('alert-warning') && !Session::has('errors')) {
			$request->session()->flashInput([
					'product' => array_column(DB::select("select tradable_id from tradable_notice_tradable where notice_id = " . $id), "tradable_id", "tradable_id"),
					'summary' => TradableNotice::find($id)->summary,
				]);
		}

		return view()->first(generateTemplateCandidates("form.tradable_notice"), [ 'readonly' => false, 'product' => Tradable::getCurrentProducts('sku', 'asc'), 'action' => trans('forms.Update') ]);
	}

	public function updateProductNoticePost($id, Request $request)
	{
		$rules = [
					'product' => "required|min:1",
					'summary' => "required",
				];

		$validator = Validator::make($request->all(), $rules);

		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return redirect('/' . $request->path())
					->with('alert-warning', trans('messages.Please correct all errors'))
					->withErrors($validator)
					->withInput($request->all());
		}

		try {
			DB::transaction(function() use ($id, $request) {
				TradableNotice::find($id)->synchronize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(1))->with("alert-success", trans("product.Product update notice updated"));
	}

	public function updateNoticePostAjax($id, Request $request)
	{
		$rules = [
					'product' => "required|min:1",
					'summary' => "required",
				];

		$validator = Validator::make($request->all(), $rules);

		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return response()->json([ 'success' => false, 'errors' => $validator->errors() ]);
		}

		$notice = TradableNotice::find($id);
		try {
			DB::transaction(function() use ($notice, $request) {
				$notice->synchronize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => new TradableNoticeResource($notice) ]);
	}

	public function loadProductNoticeAjax(Request $request, $id)
	{
		if ($id) {
			$notice = TradableNotice::find($id);

			return response()->json([
				'success' => true,
				'data' => [
					'id' => $notice->id,
					'csrf' => csrf_token(),
					'document_id' => $notice->document_id,
					'file_name' => $notice->document->file_name,
					'product' => $notice->product->pluck('id'),
					'summary' => $notice->summary,
				]
			]);
		}

		return response()->json([
			'success' => true,
			'data' => [
				'id' => 0,
				'csrf' => csrf_token(),
				'document_id' => 0,
				'file_name' => '',
				'product' => [ ],
				'summary' => '',
			]
		]);
	}

	public function loadProductFaqAjax(Request $request, $id)
	{
		if ($id) {
			$faq = TradableFAQ::find($id);

			return response()->json([
				'success' => true,
				'data' => [
					'id' => $faq->id,
					'csrf' => csrf_token(),
					'document_id' => $faq->document_id,
					'file_name' => $faq->document->file_name,
					'product' => $faq->product->pluck('id'),
					'question' => $faq->question,
					'answer' => $faq->answer,
				]
			]);
		}

		return response()->json([
			'success' => true,
			'data' => [
				'id' => 0,
				'csrf' => csrf_token(),
				'document_id' => 0,
				'file_name' => '',
				'product' => [ ],
				'question' => '',
				'answer' => '',
			]
		]);
	}

	public function createProductFaq(Request $request)
	{
		return view()->first(generateTemplateCandidates("form.tradable_faq"), [ 'readonly' => false, 'product' => Tradable::getCurrentProducts('sku', 'asc'), 'action' => trans('forms.Create') ]);
	}

	public function createProductFaqPost(Request $request)
	{
		$rules = [
					'product' => "required|min:1",
					'question' => "required",
					'answer' => "required",
					'thefile' => "required|file",
				];

		$validator = Validator::make($request->all(), $rules);

		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return redirect('/' . $request->path())
					->with('alert-warning', trans('messages.Please correct all errors'))
					->withErrors($validator)
					->withInput($request->all());
		}

		try {
			DB::transaction(function() use ($request) {
				TradableFaq::initialize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(2))->with("alert-success", trans("product.Product FAQ created"));
	}

	public function createFaqPostAjax(Request $request)
	{
		$rules = [
					'product' => "required|min:1",
					'question' => "required",
					'answer' => "required",
					'thefaqfile' => "required|file",
				];

		$validator = Validator::make($request->all(), $rules);

		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return response()->json([ 'success' => false, 'errors' => $validator->errors() ]);
		}

		$faq = null;
		try {
			DB::transaction(function() use ($request, &$faq) {
				$faq = TradableFAQ::initialize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => new TradableFAQResource($faq) ]);
	}

	public function updateProductFaq($id, Request $request)
	{
		// load FAQ detail; errors imply redirect back, flashing input removes old value
		if (!Session::has('alert-danger') && !Session::has('alert-warning') && !Session::has('errors')) {
			$request->session()->flashInput([
					'product' => array_column(DB::select("select tradable_id from tradable_faq_tradable where faq_id = " . $id), "tradable_id", "tradable_id"),
					'question' => TradableFaq::find($id)->question,
					'answer' => TradableFaq::find($id)->answer,
				]);
		}

		return view()->first(generateTemplateCandidates("form.tradable_faq"), [ 'readonly' => false, 'product' => Tradable::getCurrentProducts('sku', 'asc'), 'action' => trans('forms.Update') ]);
	}

	public function updateProductFaqPost($id, Request $request)
	{
		$rules = [
					'product' => "required|min:1",
					'question' => "required",
					'answer' => "required",
				];

		$validator = Validator::make($request->all(), $rules);

		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return redirect('/' . $request->path())
					->with('alert-warning', trans('messages.Please correct all errors'))
					->withErrors($validator)
					->withInput($request->all());
		}

		try {
			DB::transaction(function() use ($id, $request) {
				TradableFaq::find($id)->synchronize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(1))->with("alert-success", trans("product.Product FAQ updated"));
	}

	public function updateFaqPostAjax(Request $request, $id)
	{
		$rules = [
					'product' => "required|min:1",
					'question' => "required",
					'answer' => "required",
				];

		$validator = Validator::make($request->all(), $rules);

		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return response()->json([ 'success' => false, 'errors' => $validator->errors() ]);
		}

		$faq = TradableFaq::find($id);
		try {
			DB::transaction(function() use ($faq, $request) {
				$faq->synchronize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => new TradableFAQResource($faq) ]);
	}

}
