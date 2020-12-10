<?php

namespace App;

use App\Helpers\DateHelper;
use App\Helpers\ParameterHelper;
use Auth;
use DB;
use Mail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;


class SalesHeader extends Model
{
	private static $convenientAttribute = [
			'quote' => [
					'init' => 'Q',
				],
			'order' => [
					'init' => '',
				],
			'return' => [
					'init' => 'R',
				],
		];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'title', 'printed', 'email_when_invoiced', 'show_bank_account', 'show_discount', 'palletized', 'approved', 'reference', 'entity_id', 'contact_id', 'sales_id', 'type', 'status', 'billing_address_id', 'shipping_address_id', 'payment_term_id', 'fob', 'via', 'order_date', 'tax_rate', 'currency_id', 'shipping_location_id', 'reserved_receivable_title', 'notes',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

	CONST NOT_CLOSED_STATUS = [ 'open' ];

	protected static function boot()
  {
    parent::boot();

		// scope objects are in designated-period or objects are not-closed
		if ($timeWindow = env('TIME_WINDOW')) {
	    static::addGlobalScope('currentFiscal', function (Builder $builder) use ($timeWindow) {
	      $builder->where(function ($query) use ($timeWindow) {
					if ($timeWindow == 'current') {
						$query->orWhere('sales_headers.created_at', '>=', date("Y-01-01"))
									->orWhereIn('sales_headers.status', static::NOT_CLOSED_STATUS);
					} else if (strtotime(date("Y-m-01") . " " . env('TIME_WINDOW'))) {
						$query->orWhere('sales_headers.created_at', '>=', date("Y-m-d", strtotime(date("Y-m-01") . " " . $timeWindow)))
									->orWhereIn('sales_headers.status', static::NOT_CLOSED_STATUS);
					}
				});
	    });
		}
  }

	public function entity()
	{
		return $this->belongsTo('\App\TaxableEntity', 'entity_id');
	}

	public function contact()
	{
		return $this->belongsTo('\App\User', 'contact_id');
	}

	public function sales()
	{
		return $this->belongsTo('\App\User', 'sales_id');
	}

	public function billingAddress()
	{
		return $this->belongsTo('\App\Address', 'billing_address_id');
	}

	public function shippingAddress()
	{
		return $this->belongsTo('\App\Address', 'shipping_address_id');
	}

	public function paymentTerm()
	{
		return $this->belongsTo('\App\PaymentTerm', 'payment_term_id');
	}

	public function shippingLocation()
	{
		return $this->belongsTo('\App\Location', 'shipping_location_id');
	}

	public function getEarliestDeliveryDate()
	{
		return $this->purchaseDetail()->min('delivery');
	}

	public function currency()
	{
		return $this->belongsTo('\App\Currency', 'currency_id');
	}

	public function warehouseOrder()
	{
		return $this->hasOne('\App\WarehouseHeader', 'src_id')->withoutGlobalScope('currentFiscal')->where('src', 'sales_headers');
	}

	public function getDiscountTypeText($val)
	{
		if ($val == $this->currency->getSymbol()) {
			return 'amount';
		} else if ($val == "%") {
			return 'percent';
		}
		return "percent";
	}

	/*
	 * flash(pre-fill) old input into option array.  This is for update/view/approve/process function.
	 */
	public function generateArrayForOldInput($textFormat = false)
	{
		$currencyFormat = $this->currency->getFormat();
		$fmtr = new \NumberFormatter( $currencyFormat['regex'], \NumberFormatter::CURRENCY );

		$expiration = DateHelper::dbToGuiDate($this->order_date);
		$line = array();
		$item = array();
		$display = array();
		$unitprice = array();
		$discount = array();
		$discfontawesome = array();
		$discountType = array();
		$taxable = array();
		$description = array();
		$quantity = array();
		$shippedQuantity = array();
		$subtotal = array();
		$untaxed_subtotal = 0;
		$taxed_subtotal = 0;
		$tax_amount = 0;
		$grand_total = 0;
		foreach ($this->salesDetail as $oneDetail) {
			$line[] = $oneDetail->id;
			$item[] = $textFormat ? UniqueTradable::find($oneDetail->unique_tradable_id)->sku : $oneDetail->unique_tradable_id;
			$display[] = $oneDetail->display_as;
			$unitprice[] = $textFormat ?
					($this->approved ? $fmtr->format($oneDetail->unit_price) : "---") :
					sprintf("%.".$currencyFormat['fdigit']."f", $oneDetail->unit_price);
			$discount[] = ($this->isQuote() || $textFormat) ? "" : $oneDetail->discount;
			$discfontawesome[] = $oneDetail->getFontawesomeClass();
			$discountType[] = $oneDetail->getDiscountTypeSymbol();
			$taxable[] = $oneDetail->taxable;
			$description[] = $oneDetail->description;
			$quantity[] = ($textFormat && !$this->approved) ? "---" : sprintf(env('APP_QUANTITY_FORMAT'), $oneDetail->ordered_quantity - $oneDetail->shipped_quantity);
			$shippedQuantity[] = $oneDetail->shipped_quantity;
			$expiration = DateHelper::dbToGuiDate($oneDetail->delivery);
			$temp = $oneDetail->getSubtotal();
			$subtotal[] = $textFormat ?
					($this->approved ? $fmtr->format($temp) : "---") :
					sprintf("%.".$currencyFormat['fdigit']."f", $temp);
			$grand_total += $temp;
			if ($oneDetail->taxable) {
				$taxed_subtotal += $temp;
				$tax_amount += $oneDetail->getTaxPerUnit() * ($oneDetail->ordered_quantity - $oneDetail->shipped_quantity);
			} else {
				$untaxed_subtotal += $temp;
			}
		}
		$grand_total += $tax_amount;

		$billingAddressText = "";
		$shippingAddressText = "";
		if ($textFormat) {
			$billingAddress = $this->billingAddress;
			$billingAddressText = $billingAddress->name."\n".$billingAddress->street." ".$billingAddress->unit."\n".$billingAddress->city." ".$billingAddress->district."\n".$billingAddress->state.", ".$billingAddress->country." ".$billingAddress->zipcode;
			$shippingAddress = $this->shippingAddress;
			$shippingAddressText = $shippingAddress->name."\n".$shippingAddress->street." ".$shippingAddress->unit."\n".$shippingAddress->city." ".$shippingAddress->district."\n".$shippingAddress->state.", ".$shippingAddress->country." ".$shippingAddress->zipcode;
		}

		$currency = $this->currency;

		// load sales order detail
		return [
			'type' => $this->type,
			'increment' => $this->title,
			'customer' => $textFormat ? $this->entity->code : $this->entity_id,
			'inputdate' => DateHelper::dbToGuiDate($this->order_date),
			'payment' => $textFormat ? $this->paymentTerm->symbol : $this->payment_term_id,
			'expiration' => $expiration,
			'incoterm' => $this->fob,
			'via' => $this->via,
			'tax_rate' => $this->tax_rate . ($textFormat ? "%" : ""),
			'delivery_date' => DateHelper::dbToGuiDate($this->delivery),
			'palletized' => $textFormat ? ($this->palletized ? "Yes" : "No") : ($this->palletized ? true : false),
			'transportation' => "---",
			"container_load" => "---",
			"bl_release" => "---",
			'show_bank_account' => $textFormat ? ($this->show_bank_account ? "Yes" : "No") : ($this->show_bank_account ? true : false),
			'show_discount' => $textFormat ? ($this->show_discount ? "Yes" : "No") : ($this->show_discount ? true : false),
			'email_when_invoiced' => $textFormat ? ($this->email_when_invoiced ? "Yes" : "No") : ($this->email_when_invoiced ? true : false),
			'contact' => $textFormat ? $this->contact->name : $this->contact_id,
			'reference' => $this->reference,
			'staff' => $textFormat ? $this->sales->name : $this->sales_id,
			'currency' => $textFormat ? $currency->symbol : $this->currency_id,
			'currencyFormat' => [
					'regex' => $currency->regex,
					'symbol' => str_replace('_', '-', $currency->symbol),
				],
			'billing' => $textFormat ? $billingAddressText : $this->billing_address_id,
			'shipping' => $textFormat ? $shippingAddressText : $this->shipping_address_id,
			'notes' => $this->notes,
			'untaxed_subtotal' => $textFormat ?
						($this->approved ? $fmtr->format($untaxed_subtotal) : "---") :
						$fmtr->format($untaxed_subtotal),
						// sprintf("%.".$currencyFormat['fdigit']."f", $untaxed_subtotal),
			'taxed_subtotal' => $textFormat ?
						($this->approved ? $fmtr->format($taxed_subtotal) : "---") :
						$fmtr->format($taxed_subtotal),
						// sprintf("%.".$currencyFormat['fdigit']."f", $taxed_subtotal),
			'tax_amount' => $textFormat ?
						($this->approved ? $fmtr->format($tax_amount) : "---") :
						$fmtr->format($tax_amount),
						// sprintf("%.".$currencyFormat['fdigit']."f", $tax_amount),
			'grand_total' => $textFormat ?
						($this->approved ? $fmtr->format($grand_total) : "---") :
						$fmtr->format($grand_total),
						// sprintf("%.".$currencyFormat['fdigit']."f", $grand_total),
			'line' => $line,
			'product' => $item,
			'display' => $display,
			'unitprice' => $unitprice,
			'discount' => $discount,
			'discfontawesome' => $discfontawesome,
			'disctype' => $discountType,
			'taxable' => $taxable,
			'description' => $description,
			'quantity' => $quantity,
			'shippedQuantity' => $shippedQuantity,
			'subtotal' => $subtotal,
		];
	}

	public function salesDetail()
	{
		return $this->hasMany('\App\SalesDetail', 'header_id', 'id');
	}

	public function detail()
	{
		return $this->salesDetail();
	}

	public function isQuote()
	{
		return ($this->type == 'quote');
	}

	public function isNotQuote()
	{
		return ($this->type != 'quote');
	}

	public function isOrder()
	{
		return ($this->type == 'order');
	}

	public function isNotOrder()
	{
		return ($this->type != 'order');
	}

	public function isReturn()
	{
		return ($this->type == 'return');
	}

	public function isNotReturn()
	{
		return ($this->type != 'return');
	}

	public function isOpen()
	{
		return ($this->status == 'open');
	}

	public function isNotOpen()
	{
		return in_array($this->status, ['closed', 'void']);
	}

	public static function initialize($type, Request $request)
	{
		$title = ParameterHelper::getNextSequence('sales_order_number');

		// if type is quote, just stuff following with default parameter
		if ($type == 'quote') {
			self::populateMissingColumn($request);
		}

		// update database
		$header = SalesHeader::create([
			'title' => $title,
			'printed' => 0,
			'email_when_invoiced' => $request->input('email_when_invoiced') !== null,
			'show_bank_account' => $request->input('show_bank_account') !== null,
			'show_discount' => $request->input('show_discount') !== null,
			'palletized' => $request->input('palletized') !== null,
			'approved' => 0,
			'reference' => $request->input('reference'),
			'entity_id' => $request->input('customer'),
			'contact_id' => $request->input('contact'),
			'sales_id' => $request->input('staff'),
			'type' => $type,
			'status' => 'open',
			'billing_address_id' => $request->input('billing'),
			'shipping_address_id' => $request->input('shipping'),
			'payment_term_id' => $request->input('payment'),
			'fob' => $request->input('incoterm'),
			'via' => $request->input('via'),
			'order_date' => DateHelper::guiToDbDate($request->input('inputdate')),
			'tax_rate' => $request->input('tax_rate'),
			'currency_id' => $request->input('currency'),
			'shipping_location_id' => in_array($type, ['order', 'return']) ? $request->input('warehouse') : Location::where('type', 'warehouse')->first()->id,
			'reserved_receivable_title' => '',
			'notes' => in_array($type, ['order', 'return']) ? $request->input('notes') : '',
		]);

		SalesHistory::create([
			'src' => 'sales_headers',
			'src_id' => $header->id,
			'staff_id' => auth()->user()['id'],
			'machine' => $request->ip(),
			'process_status' => 'created',
			'notes' => '',
		]);

		foreach ($request->input('product') as $idx => $product_id) {
			$detail = SalesDetail::create([
				'header_id' => $header['id'],
				'unique_tradable_id' => $product_id,
				'display_as' => $request->input('display')[$idx],
				'description' => $request->input('description')[$idx],
				'unit_price' => $request->input('unitprice')[$idx],
				'ordered_quantity' => $request->input('quantity')[$idx],
				'allocated_quantity' => 0,
				'shipped_quantity' => 0,
				'shipped_amount' => 0,
				'discount' => ($type == 'quote') ? 0 : $request->input('discount')[$idx],
				'discount_type' => ($type == 'quote') ? 'amount' : $header->getDiscountTypeText($request->input('disctype')[$idx]),
				'taxable' => in_array($request->input('taxable')[$idx], [1, "1", true, "true"], true),
				'status' => 'open',
				'delivery' => DateHelper::guiToDbDate($request->input('expiration')),
				'notes' => '',
			]);

			SalesHistory::create([
				'src' => 'sales_details',
				'src_id' => $detail['id'],
				'staff_id' => auth()->user()['id'],
				'machine' => $request->ip(),
				'process_status' => 'created',
				'notes' => '',
			]);
		}

		if ($header->requireApproval()) {
			$header->sendPendingApproverReminder();
		} else {
			$header->update([ 'approved' => 1 ]);
			if ($header->isNotQuote()) {
				WarehouseHeader::synchronize($header, $request->ip());
			}
		}

		event(new \App\Events\SalesUpsertEvent($header));

		return $header;
	}

	public function synchronize(Request $request)
	{
		if ($this['type'] == 'quote') {
			self::populateMissingColumn($request);
		}

		// update database
		$this->update([
				'printed' => 0,
				'show_bank_account' => $request->input('show_bank_account') !== null,
				'show_discount' => $request->input('show_discount') !== null,
				'email_when_invoiced' => $request->input('email_when_invoiced') !== null,
				'palletized' => $request->input('palletized') !== null,
				'approved' => 0,
				'reference' => $request->input('reference'),
				'contact_id' => $request->input('contact'),
				'sales_id' => $request->input('staff'),
				'payment_term_id' => $request->input('payment'),
				'fob' => $request->input('incoterm'),
				'via' => $request->input('via'),
				'currency_id' => $request->input('currency'),
				'billing_address_id' => $request->input('billing'),
				'shipping_address_id' => $request->input('shipping'),
				'order_date' => DateHelper::guiToDbDate($request->input('inputdate')),
				'tax_rate' => $request->input('tax_rate'),
			]);

		if (in_array($this['type'], ['order', 'return'])) {
			$this->update([
					'shipping_location_id' => $request->input('warehouse'),
					'notes' => $request->input('notes'),
				]);
		}

		SalesHistory::create([
			'src' => 'sales_headers',
			'src_id' => $this['id'],
			'staff_id' => auth()->user()['id'],
			'machine' => $request->ip(),
			'process_status' => 'updated',
			'notes' => '',
		]);

		$newTotalQuantity = 0;
		foreach ($request->input('product') as $idx => $product_id) {
			if ($request->input('line')[$idx] > 0) {
				$detail = SalesDetail::find($request->input('line')[$idx]);
				$detail->update([
						'unique_tradable_id' => $product_id,
						'display_as' => $request->input('display')[$idx],
						'ordered_quantity' => $request->input('quantity')[$idx] + $detail['shipped_quantity'],
						'description' => $request->input('description')[$idx],
						'unit_price' => $request->input('unitprice')[$idx],
						'discount' => ($this['type'] == 'quote') ? 0 : $request->input('discount')[$idx],
						'discount_type' => $this->getDiscountTypeText($request->input('disctype')[$idx]),
						'taxable' => in_array($request->input('taxable')[$idx], [1, "1", true, "true"], true),
						'status' => $request->input('quantity')[$idx] ? 'open' : 'closed',
						'delivery' => DateHelper::guiToDbDate($request->input('expiration')),
					]);

				SalesHistory::create([
					'src' => 'sales_details',
					'src_id' => $detail['id'],
					'staff_id' => auth()->user()['id'],
					'machine' => $request->ip(),
					'process_status' => 'updated',
					'notes' => '',
				]);
			} else {
				$detail = SalesDetail::create([
					'header_id' => $this['id'],
					'unique_tradable_id' => $product_id,
					'display_as' => $request->input('display')[$idx],
					'description' => $request->input('description')[$idx],
					'unit_price' => $request->input('unitprice')[$idx],
					'ordered_quantity' => $request->input('quantity')[$idx],
					'allocated_quantity' => 0,
					'shipped_quantity' => 0,
					'shipped_amount' => 0,
					'discount' => ($this['type'] == 'quote') ? 0 : $request->input('discount')[$idx],
					'discount_type' => ($this['type'] == 'quote') ? 'amount' : $this->getDiscountTypeText($request->input('disctype')[$idx]),
					'taxable' => in_array($request->input('taxable')[$idx], [1, "1", true, "true"], true),
					'status' => $request->input('quantity')[$idx] ? 'open' : 'closed',
					'delivery' => DateHelper::guiToDbDate($request->input('expiration')),
					'notes' => '',
				]);

				SalesHistory::create([
					'src' => 'sales_details',
					'src_id' => $detail['id'],
					'staff_id' => auth()->user()['id'],
					'machine' => $request->ip(),
					'process_status' => 'created',
					'notes' => '',
				]);
			}
			$newTotalQuantity += $request->input('quantity')[$idx];
		}

		// all approval should be voided and re-approve
		SalesHistory::where([
					[ 'src', '=', 'sales_headers' ],
					[ 'src_id', '=', $this['id'] ],
					['process_status', '=', 'approved' ]
				])->update(['process_status' => 'approval expired']);
		SalesHistory::where([
					[ 'src', '=', 'sales_headers' ],
					[ 'src_id', '=', $this['id'] ],
					[ 'process_status', '=', 'rejected' ]
				])->update(['process_status' => 'disapproval expired']);

		if ($newTotalQuantity) {
			if ($this->requireApproval()) {
				$this->sendPendingApproverReminder();
			} else {
				$this->update([ 'approved' => 1 ]);
				if ($this->isNotQuote()) {
					WarehouseHeader::synchronize($this, $request->ip());
				}
			}
		} else {
			$this->update([ 'status' => 'closed' ]);

			SalesHistory::create([
				'src' => 'sales_headers',
				'src_id' => $this['id'],
				'staff_id' => auth()->user()['id'],
				'machine' => $request->ip(),
				'process_status' => 'closed',
				'notes' => '',
			]);
		}

		event(new \App\Events\SalesUpsertEvent($this));

		return $this;
	}

	/*
	 * if $userId = null, return if there are pending approvers;
	 * if $userId <> null, return yes/no of corresponding to requirement of approver $userId
	 */
	public function requireApproval($userId = null)
	{
		// if $userId <> null, return yes/no of corresponding to requirement of approver $userId
		if ($userId) {
			$allApprovers = array_column(DB::select("select approver_id from sales_approval_rules where valid and (src_entity_id = " . $this->entity_id . " or src_entity_id = 0) and (case '" . $this->type . "' when 'quote' then 1 else ((select sum(if(taxable, (ordered_quantity * unit_price) * (100 + tax_rate) / 100, ordered_quantity*unit_price)) from sales_details, sales_headers where sales_details.header_id = " . $this->id . " and sales_headers.id = " . $this->id .") > threshold) end) and (case '" . $this->type . "' when 'quote' then applied_to_quote when 'order' then applied_to_order when 'return' then applied_to_return end)"), "approver_id");
			$doneApprovers = array_column(DB::select("select staff_id from sales_histories where src='sales_headers' and src_id=" . $this->id . " and process_status='approved'"), "staff_id");
			return in_array($userId, $allApprovers) && !(in_array($userId, $doneApprovers));
		}

		// if $userId = null, return if there are pending approvers;
		$allApprovers = array_column(DB::select("select approver_id from sales_approval_rules where valid and (src_entity_id = " . $this->entity_id . " or src_entity_id = 0) and (case '" . $this->type . "' when 'quote' then 1 else ((select sum((if(taxable, (ordered_quantity * unit_price) * (100 + tax_rate) / 100, ordered_quantity*unit_price))) from sales_details, sales_headers where sales_details.header_id = " . $this->id . " and sales_headers.id = " . $this->id . ") > threshold) end) and (case '" . $this->type . "' when 'quote' then applied_to_quote when 'order' then applied_to_order when 'return' then applied_to_return end)"), "approver_id");
		$doneApprovers = array_column(DB::select("select staff_id from sales_histories where src='sales_headers' and src_id=" . $this->id . " and process_status='approved'"), "staff_id");
		return count(array_diff($allApprovers, $doneApprovers));
	}

	public function sendPendingApproverReminder()
	{
		// send pending approver reminder to approve this quote/order/return
		$allApprovers = array_column(DB::select("select approver_id from sales_approval_rules where valid and (src_entity_id = " . $this->entity_id . " or src_entity_id = 0) and (case '" . $this->type . "' when 'quote' then 1 else ((select sum((if(taxable, (ordered_quantity * unit_price) * (100 + tax_rate) / 100, ordered_quantity*unit_price))) from sales_details, sales_headers where sales_details.header_id = " . $this->id . " and sales_headers.id = " . $this->id . ") > threshold) end) and (case '" . $this->type . "' when 'quote' then applied_to_quote when 'order' then applied_to_order when 'return' then applied_to_return end)"), "approver_id");
		$doneApprovers = array_column(DB::select("select staff_id from sales_histories where src='sales_headers' and src_id=" . $this->id . " and process_status='approved'"), "staff_id");
		$approvers = User::findMany(array_diff($allApprovers, $doneApprovers));

		try {
			Mail::send('email_templates.sales_approval', ['order' => $this], function ($m) use ($approvers) {
					$m->subject('Sales Approval Request');
	        $m->from(config("mail.from.address"), config("mail.from.name"));
					foreach ($approvers as $approver) {
	        	$m->to($approver->email, $approver->name);
					}
				});
		} catch (\Exception $e) {
			// what to do in case of email error!?
		}
	}

	public function approve($approverId, $ipAddress='127.0.0.1', $notes='')
	{
		SalesHistory::create([
			'src' => 'sales_headers',
			'src_id' => $this->id,
			'staff_id' => $approverId,
			'machine' => $ipAddress,
			'process_status' => 'approved',
			'notes' => $notes,
		]);

		if (!$this->requireApproval()) {
			$this->update([ 'approved' => 1 ]);
			event(new \App\Events\SalesUpsertEvent($this));
			if ($this->isNotQuote()) {
				WarehouseHeader::synchronize($this, $ipAddress);
			}
		}
	}

	public function disapprove($approverId, $ipAddress='127.0.0.1', $notes='')
	{
		SalesHistory::create([
			'src' => 'sales_headers',
			'src_id' => $this->id,
			'staff_id' => $approverId,
			'machine' => $ipAddress,
			'process_status' => 'rejected',
			'notes' => $notes,
		]);
		event(new \App\Events\SalesUpsertEvent($this));
	}

	public function generatePdf()
	{
		$companyAddress = Address::where('entity_id', TaxableEntity::where('type', 'self')->first()->id)->where('purpose', 'billing')->where('is_default', 1)->first();

		$data = $this->generateArrayForOldInput(true);
		$data['company_address'] = $companyAddress->street.(empty($companyAddress->unit) ? "" : (" ".$companyAddress->unit))." ".$companyAddress->city.(empty($companyAddress->district) ? "" : (" ".$companyAddress->district))." ".$companyAddress->state." ".$companyAddress->country." ".$companyAddress->zipcode;
		$data['status'] = $this['status'];
		$pdf = new SalesFormPdf($data);
		$this->printed = 1;
		$this->save();
		event(new \App\Events\SalesUpsertEvent($this));
		return $pdf;
	}

	public function determineConsignmentRequirement($incurDate, $processing)
	{
		$consignments = [];

		foreach ($this->salesDetail as $oneLine) {
			if ($oneLine->status == 'open') {
				//$requestQty = $oneLine->ordered_quantity - $oneLine->shipped_quantity;
				$requestQty = $processing[$oneLine->id];
				$inventoryQty = $oneLine->uniqueTradable->getInventory($incurDate, $this->shipping_location_id, TaxableEntity::theCompany()->id);
				if ($oneLine->uniqueTradable->stockable && ($inventoryQty < $requestQty)) {
					$additionalQty = $requestQty - $inventoryQty;
					foreach ($oneLine->uniqueTradable->getConsignedBatch($additionalQty, $this->shipping_location_id, $incurDate) as $batch) {
						if (!array_key_exists($batch['owner_entity_id'], $consignments)) {
							$consignments[$batch['owner_entity_id']] = [];
						}
						if (!array_key_exists($batch['unique_tradable_id'], $consignments[$batch['owner_entity_id']])) {
							$consignments[$batch['owner_entity_id']][$batch['unique_tradable_id']] = [];
						}
						$consignments[$batch['owner_entity_id']][$batch['unique_tradable_id']][] = [
							"quantity" => $batch['quantity'],
							"unit_cost" => $batch['unit_cost'],
						];
					}
				}
			}
		}

		return $consignments;
	}

	public function createReceivable($request)
	{
		$totalAmount = 0;
		$totalTax = 0;
		$allClosed = true;
		$consignmentMeta = [];

		$title = $this->reserved_receivable_title;
		if (empty($title)) {
			$title = ParameterHelper::getNextSequence('transaction_number');
			$title = (($this->type == 'return') ? "C" : "") . $title;
		}

		$incurDate = DateHelper::guiToDbDate($request->input('expiration'));
		$dueDate = date('Y-m-d', strtotime($incurDate . ' + ' . $this->paymentTerm->grace_days . ' days'));
		$currencyFormat = $this->currency->getFormat();

		if (env('ACCOUNT_CONSIGNMENT_INVENTORY') && $this->isOrder()) {
			// determine if re-stock from consignment is necessary
			$consignmentItems = $this->determineConsignmentRequirement($incurDate, array_combine($request->input('line'), $request->input('processing')));
			if (count($consignmentItems)) {
				// issue out inventory (tradable_transaction)
				foreach ($consignmentItems as $supplierId => $content1) {
					foreach ($content1 as $uniqueTradableId => $content2) {
						foreach ($content2 as $content3) {
							$txObj = TradableTransaction::create([
									'unique_tradable_id' => $uniqueTradableId,
									'location_id' => $this->shipping_location_id,
									'owner_entity_id' => $supplierId,
									'quantity' => -$content3['quantity'],  // issue-out inventory
									'unit_cost' => $content3['unit_cost'],
									'src_table' => '',
									'src_id' => 0,
									'valid' => 1,
									'notes' => '',
									'created_at' => $incurDate . ' 00:00:00',
								]);
							event(new \App\Events\InventoryUpdateEvent($txObj));
							if (!array_key_exists('tradable_transactions', $consignmentMeta)) {
								$consignmentMeta['tradable_transactions'] = [];
							}
							$consignmentMeta['tradable_transactions'][] = $txObj->id;
						}
					}
				}
				// initialize POs
				foreach ($consignmentItems as $supplierId => $content1) {
					$rqx = clone $request;
					$supplier = TaxableEntity::find($supplierId);
					$line = [];
					$product = [];
					$display = [];
					$ivcost = [];
					$unitprice = [];
					$description = [];
					$quantity = [];
					$ddate = [];
					$warehouse = [];
					$taxable = [];
					foreach ($content1 as $uniqueTradableId => $content2) {
						foreach ($content2 as $content3) {
							$attr = UniqueTradable::find($uniqueTradableId)->getLastEntry($supplierId, 'purchase_details', 'order');
							$line[] = 0;
							$product[] = $uniqueTradableId;
							$display[] = $attr->display;
							$ivcost[] = $content3['unit_cost'];
							$unitprice[] = $content3['unit_cost'];
							$description[] = $attr->description;
							$quantity[] = $content3['quantity'];
							$ddate[] = DateHelper::dbToGuiDate($incurDate);
							$warehouse[] = $this->shipping_location_id;
							$taxable[] = false;
						}
					}
					// fulfill $rqx fields
					$rqx->merge([
							'supplier' => $supplierId,
							'inputdate' => DateHelper::dbToGuiDate($incurDate),
							'payment' => $supplier->payment_term_id,
							'incoterm' => 'N/A',
							'via' => 'Consignment',
							'contact' => $supplier->contact()->orderBy('id', 'desc')->first()->id,
							'reference' => 'Consignment for ' . $this->type . ' #' . $this->title,
							'staff' => auth()->user()->id,
							'currency' => $supplier->currency_id,
							'billing' => $supplier->defaultBillingAddress[0]->id,
							'shipping' => $supplier->defaultShippingAddress[0]->id,
							'line' => $line,
							'product' => $product,
							'display' => $display,
							'ivcost' => $ivcost,
							'unitprice' => $unitprice,
							'description' => $description,
							'quantity' => $quantity,
							'ddate' => $ddate,
							'warehouse' => $warehouse,
							'taxable' => $taxable,
						]);
					$purchaseHeaderObj = PurchaseHeader::initialize('order', $rqx);
					if (!array_key_exists('purchase_headers', $consignmentMeta)) {
						$consignmentMeta['purchase_headers'] = [];
					}
					$consignmentMeta['purchase_headers'][] = $purchaseHeaderObj->id;
				}
				// createPayables from POs
				foreach ($consignmentMeta['purchase_headers'] as $poId) {
					$rqx = clone $request;
					$purchaseHeaderObj = PurchaseHeader::find($poId);
					$line = [];
					$processing = [];
					foreach ($purchaseHeaderObj->purchaseDetail as $detail) {
						if ($detail->status == 'open') {
							$line[] = $detail->id;
							$processing[] = $detail->ordered_quantity - $detail->shipped_quantity;
						}
					}
					// fulfill $rqx fields
					$rqx->merge([
							'expiration' => DateHelper::dbToGuiDate($incurDate),
							'line' => $line,
							'processing' => $processing,
						]);
					$payableObj = $purchaseHeaderObj->createPayable($rqx);
					if (!array_key_exists('transactable_headers', $consignmentMeta)) {
						$consignmentMeta['transactable_headers'] = [];
					}
					$consignmentMeta['transactable_headers'][] = $payableObj->id;
				}
			}
		}

		$flags = [];
		if ($this->email_when_invoiced) {
			$flags[] = 'email_when_created';
		}
		if ($this->show_bank_account) {
			$flags[] = 'show_bank_account';
		}
		if ($this->show_discount) {
			$flags[] = 'show_discount';
		}

		// create header
		$transactableHeaderObj = TransactableHeader::create([
			'title' => $title,
			'src_table' => 'sales_headers',
			'src_id' => $this->id,
			'flags' => implode(',', $flags),
			'reference' => $this->reference,
			'entity_id' => $this->entity_id,
			'contact_id' => $this->contact_id,
			'staff_id' => $this->sales_id,
			'status' => 'open',
			'balance' => 0, // update after summing up all sales_detail
			'billing_address_id' => $this->billing_address_id,
			'shipping_address_id' => $this->shipping_address_id,
			'payment_term_id' => $this->payment_term_id,
			'incur_date' => $incurDate,
			'approx_due_date' => $dueDate,
			'tax_rate' => $this->tax_rate,
			'currency_id' => $this->currency_id,
			'notes' => $this->notes,
			'internal_notes' => '',
		]);

		// create history
		$transactionHistoryObj = TransactableHistory::create([
			'src' => 'transactable_headers',
			'src_id' => $transactableHeaderObj->id,
			'amount' => 0, // update after summing up all sales detail
			'staff_id' => Auth::user()->id,
			'machine' => $request->ip(),
			'process_status' => 'created',
			'notes' => '',
		]);

		// always set reserved_receivable_title to Empty
		$this->update([ 'reserved_receivable_title' => '' ]);

		foreach ($request->input('line') as $lineIdx => $DetailIdx) {
			$qty = $request->input('processing')[$lineIdx];
			if ($qty > 0) {
				$salesDetailObj = SalesDetail::find($DetailIdx);
				// create transactable detail
				$transactableDetailObj = TransactableDetail::create([
					'transactable_header_id' => $transactableHeaderObj->id,
					'src_table' => 'sales_details',
					'src_id' => $DetailIdx,
					'unique_tradable_id' => $salesDetailObj->unique_tradable_id,
					'display_as' => $salesDetailObj->display_as,
					'description' => $salesDetailObj->description,
					'unit_price' => $salesDetailObj->unit_price,
					'discount' => $salesDetailObj->discount,
					'discount_type' => $salesDetailObj->discount_type,
					'transacted_quantity' => $request->input('processing')[$lineIdx],
					'transacted_amount' => $salesDetailObj->unit_price * $request->input('processing')[$lineIdx],
					// since discount_amount & tax_amount is a fraction of transacted_amount, it is possible the value go beyond allowed decimal
					'discount_amount' => sprintf("%0.".$currencyFormat['fdigit']."f", $salesDetailObj->getDiscountPerUnit() * $request->input('processing')[$lineIdx]),
					'tax_amount' => sprintf("%0.".$currencyFormat['fdigit']."f", $salesDetailObj->getTaxPerUnit() * $request->input('processing')[$lineIdx]),
					'status' => 'valid',
				]);
				$totalAmount += $transactableDetailObj->transacted_amount - $transactableDetailObj->discount_amount;
				$totalTax += $transactableDetailObj->tax_amount;
				// record history
				TransactableHistory::create([
					'src' => 'transactable_details',
					'src_id' => $transactableDetailObj->id,
					'amount' => $transactableDetailObj->transacted_amount - $transactableDetailObj->discount_amount + $transactableDetailObj->tax_amount,
					'staff_id' => Auth::user()->id,
					'machine' => $request->ip(),
					'process_status' => 'created',
					'notes' => '',
				]);
				// calculate cost-of-good-sold
				if ($salesDetailObj->uniqueTradable->stockable) {
					$costOfGoodSold = $this->isOrder() ? $salesDetailObj->uniqueTradable->getUnitCost($request->input('processing')[$lineIdx], $this->shipping_location_id, TaxableEntity::theCompany()->id) : $salesDetailObj->uniqueTradable->getUnitReturnCost($request->input('processing')[$lineIdx], $this->shipping_location_id, $this->entity_id);
					// adjust inventory
					$transaction = TradableTransaction::create([
						'unique_tradable_id' => $transactableDetailObj->unique_tradable_id,
						'location_id' => $this->shipping_location_id,
						'owner_entity_id' => TaxableEntity::theCompany()->id,
						'quantity' => ($this->type == 'return') ? $request->input('processing')[$lineIdx] : 0 - $request->input('processing')[$lineIdx],
						'unit_cost' => $costOfGoodSold,
						'src_table' => 'transactable_details',
						'src_id' => $transactableDetailObj->id,
						'valid' => 1,
						'notes' => '',
						'created_at' => $incurDate . ' 23:59:59',
					]);
					event(new \App\Events\InventoryUpdateEvent($transaction));
					// record cost of good sold
					TaccountTransaction::create([
						'debit_t_account_id' => ($this->type == 'return') ?
														$this->shippingLocation->inventory_t_account_id :
														$salesDetailObj->uniqueTradable->cogs_t_account_id,
						'credit_t_account_id' => ($this->type == 'return') ?
														$salesDetailObj->uniqueTradable->cogs_t_account_id :
														$this->shippingLocation->inventory_t_account_id,
						'amount' =>  $costOfGoodSold * $transactableDetailObj->transacted_quantity,
						'currency_id' => $this->currency_id,
						'book_date' => $incurDate,
						'src' => 'transactable_details',
						'src_id' => $transactableDetailObj->id,
						'valid' => 1,
						'reconciled' => 0,
						'notes' => '',
					]);
				}
				// adjust sales order/return
				$salesDetailObj->shipped_quantity += $request->input('processing')[$lineIdx];
				$salesDetailObj->shipped_amount += $request->input('processing')[$lineIdx] * $salesDetailObj->unit_price;
				if ($salesDetailObj->ordered_quantity == $salesDetailObj->shipped_quantity) {
					$salesDetailObj->status = 'closed';
				} else {
					$allClosed = false;
				}
				$salesDetailObj->save();
			}
		}

		if ($allClosed) {
			$this->status = 'closed';
			$this->save();
		}

		// now update total
		$transactableHeaderObj->balance = $totalAmount + $totalTax;
		$transactableHeaderObj->save();
		$transactionHistoryObj->amount = $totalAmount + $totalTax;
		$transactionHistoryObj->save();

		// create AR entry
		TaccountTransaction::create([
				'debit_t_account_id' => ($this->type == 'return') ?
												TaxableEntity::find($transactableHeaderObj->entity_id)->revenue_t_account_id :
												TaxableEntity::find($transactableHeaderObj->entity_id)->transaction_t_account_id,
				'credit_t_account_id' => ($this->type == 'return') ?
												TaxableEntity::find($transactableHeaderObj->entity_id)->transaction_t_account_id :
												TaxableEntity::find($transactableHeaderObj->entity_id)->revenue_t_account_id,
				'amount' =>  $totalAmount,
				'currency_id' => $this->currency_id,
				'book_date' => $incurDate,
				'src' => 'transactable_headers',
				'src_id' => $transactableHeaderObj->id,
				'valid' => 1,
				'reconciled' => 0,
				'notes' => '',
			]);
		// if there's tax, handle it.
		if ($totalTax) {
			$taxAccount = ChartAccount::find(unserialize(Parameter::where('key', 'sales_tax_account_id')->first()->value));
			TaccountTransaction::create([
					'debit_t_account_id' => ($this->type == 'return') ?
													$taxAccount->id :
													TaxableEntity::find($transactableHeaderObj->entity_id)->transaction_t_account_id,
					'credit_t_account_id' => ($this->type == 'return') ?
													TaxableEntity::find($transactableHeaderObj->entity_id)->transaction_t_account_id :
													$taxAccount->id,
					'amount' =>  $totalTax,
					'currency_id' => $this->currency_id,
					'book_date' => $incurDate,
					'src' => 'transactable_headers',
					'src_id' => $transactableHeaderObj->id,
					'valid' => 1,
					'reconciled' => 0,
					'notes' => '',
				]);
		}
		// if consignment meta information is not null, record the relation.
		if ($consignmentMeta) {
			$transactableHeaderObj->associateConsignment($consignmentMeta);
		}

		event(new \App\Events\SalesUpsertEvent($this));
		event(new \App\Events\TransactableUpsertEvent($transactableHeaderObj));

		return $transactableHeaderObj;
	}

	public function translateWarehouseOrderType()
	{
		switch ($this['type']) {
			case 'order':
				return 'deliver';
			case 'return':
				return 'receive';
		}
		return null;
	}

	public function history()
	{
		return $this->hasMany('\App\SalesHistory', 'src_id')->where('src', 'sales_headers');
	}

	// spoof data for sales-quote
	private static function populateMissingColumn(Request $request)
	{
		$request->request->add([
				'billing' => Address::where([
						[ 'entity_id', '=', $request->input('customer') ],
						[ 'purpose', '=', 'billing' ],
						[ 'is_default', '=', 1 ],
					])->first()['id'],
				'shipping' => Address::where([
						[ 'entity_id', '=', $request->input('customer') ],
						[ 'purpose', '=', 'shipping' ],
						[ 'is_default', '=', 1 ],
					])->first()['id'],
				'tax_rate' => '0.00',
				'via' => '',
				'shipping_location_id' => Location::where([
							[ 'type', '=', 'warehouse' ],
							[ 'active', '=', 1 ],
							[ 'owner_entity_id', '=', TaxableEntity::theCompany()['id']],
						])->first()['id'],
			]);
	}

	public function getUrl($mode = 'view')
	{
		switch ($mode) {
			case 'update':
				return url('crm/update' . $this->type . "/". $this->id);
			case 'view':
			default:
				return url('crm/view' . $this->type . "/" . $this->id);
		}
	}

	public function getShipmentInformation()
	{
			$volume = 0;
			$volumeInfo = [ ];
			$weight = 0;
			$weightInfo = [ ];

			foreach ($this->salesDetail as $detail) {
				$volume += $detail->getTotalVolume();
				$weight += $detail->getTotalWeight();
			}

			foreach (Measurement::all() as $measurement) {
				switch ($measurement->type) {
					case 'length':
						array_push($volumeInfo, sprintf("%.3f", $volume / $measurement->conversion_ratio / $measurement->conversion_ratio / $measurement->conversion_ratio) . " " . $measurement->symbol . '&#179');
						break;
					case 'weight':
						array_push($weightInfo, sprintf("%.3f", $weight / $measurement->conversion_ratio ) . " " . $measurement->symbol );
						break;
					default:
						break;
				}
			}

			return [ trans('warehouse.Volume') => $volumeInfo, trans('warehouse.Weight') => $weightInfo ];
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
				str_pad('quote', 15) . trans('tool.Search sales quote'),
				str_pad('order', 15) . trans('tool.Search sales order'),
				str_pad('return', 15) . trans('tool.Search sales return'),
				str_pad('open', 15) . trans('tool.Search open entry'),
				str_pad('void', 15) . trans('tool.Search void entry'),
				str_pad('closed', 15) . trans('tool.Search entry closed'),
				str_pad('thisyear', 15) . trans('tool.Search from this year'),
				str_pad('thismonth', 15) . trans('tool.Search from this month'),
				str_pad('lastmonth', 15) . trans('tool.Search from last month'),
			]);
	}
}
