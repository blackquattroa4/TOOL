<?php

namespace App;

use App\Helpers\DateHelper;
use App\Helpers\ParameterHelper;
use DB;
use Mail;
use Storage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PurchaseHeader extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'title', 'printed', 'approved', 'released', 'reference', 'entity_id', 'contact_id', 'purchase_id', 'type', 'status', 'billing_address_id', 'shipping_address_id', 'payment_term_id', 'fob', 'via', 'tax_rate', 'currency_id', 'order_date', 'notes', 'internal_notes',
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
						$query->orWhere('purchase_headers.created_at', '>=', date("Y-01-01"))
									->orWhereIn('purchase_headers.status', static::NOT_CLOSED_STATUS);
					} else if (strtotime(date("Y-m-01") . " " . env('TIME_WINDOW'))) {
						$query->orWhere('purchase_headers.created_at', '>=', date("Y-m-d", strtotime(date("Y-m-01") . " " . $timeWindow)))
									->orWhereIn('purchase_headers.status', static::NOT_CLOSED_STATUS);
					}
				});
	    });
		}
  }

	public function getEarliestDeliveryDate()
	{
		return $this->purchaseDetail()->min('delivery_date');
	}

	/*
	 * flash(pre-fill) old input into option array.  This is for update/view/approve/process function.
	 */
	public function generateArrayForOldInput($textFormat = false)
	{
		$currencyFormat = $this->currency->getFormat();
		$fmtr = new \NumberFormatter( $currencyFormat['regex'], \NumberFormatter::CURRENCY );

		$line = array();
		$item = array();
		$display = array();
		$ivcost = array();
		$unitprice = array();
		$taxable = array();
		$description = array();
		$quantity = array();
		$shippedQuantity = array();
		$deliveryDate = array();
		$recvLocation = array();
		$subtotal = array();
		$untaxed_subtotal = 0;
		$taxed_subtotal = 0;
		$tax_amount = 0;
		$grand_total = 0;
		foreach ($this->purchaseDetail as $oneDetail) {
			$line[] = $oneDetail->id;
			$item[] = $textFormat ? UniqueTradable::find($oneDetail->unique_tradable_id)->sku : $oneDetail->unique_tradable_id;
			$display[] = $oneDetail->manufacture_model;
			$unitprice[] = $textFormat ? $fmtr->format($oneDetail->unit_price) : sprintf("%.".$currencyFormat['fdigit']."f", $oneDetail->unit_price);
			$taxable[] = $oneDetail->taxable;
			$description[] = $oneDetail->description;
			$quantity[] = sprintf(env('APP_QUANTITY_FORMAT'), $oneDetail->ordered_quantity - $oneDetail->shipped_quantity);
			$shippedQuantity[] = $oneDetail->shipped_quantity;
			$ivcost[] = $textFormat ? $fmtr->format($oneDetail->inventory_cost) : sprintf("%.".$currencyFormat['fdigit']."f", $oneDetail->inventory_cost);
			$deliveryDate[] = DateHelper::dbToGuiDate($oneDetail->delivery_date);
			$recvLocation[] = $textFormat ? Location::find($oneDetail->receiving_location_id)->name : $oneDetail->receiving_location_id;
			$temp = $oneDetail->unit_price * ($oneDetail->ordered_quantity - $oneDetail->shipped_quantity);
			$subtotal[] = $textFormat ? $fmtr->format($temp) : sprintf("%.".$currencyFormat['fdigit']."f", $temp);
			$grand_total += $temp;
			if ($oneDetail->taxable) {
				$taxed_subtotal += $temp;
				$tax_amount += $oneDetail->getTaxPerUnit() * ($oneDetail->ordered_quantity - $oneDetail->shipped_quantity);
			} else {
				$untaxed_subtotal += $temp;
			}
		}

		$billingAddressText = "";
		$shippingAddressText = "";
		if ($textFormat) {
			$billingAddress = $this->billingAddress;
			$billingAddressText = $billingAddress->name."\n".$billingAddress->street." ".$billingAddress->unit."\n".$billingAddress->city." ".$billingAddress->district."\n".$billingAddress->state.", ".$billingAddress->country." ".$billingAddress->zipcode;
			$shippingAddress = $this->shippingAddress;
			$shippingAddressText = $shippingAddress->name."\n".$shippingAddress->street." ".$shippingAddress->unit."\n".$shippingAddress->city." ".$shippingAddress->district."\n".$shippingAddress->state.", ".$shippingAddress->country." ".$shippingAddress->zipcode;
		}

		$currency = $this->currency;

		// load purchase order detail
		return [
			'type' => $this->type,
			'increment' => $this->title,
			'supplier' => $textFormat ? $this->entity->code : $this->entity_id,
			'inputdate' => DateHelper::dbToGuiDate($this->order_date),
			'payment' => $textFormat ? $this->paymentTerm->symbol : $this->payment_term_id,
			'incoterm' => $this->fob,
			'via' => $this->via,
			'shipping_date' => DateHelper::dbToGuiDate($this->getEarliestDeliveryDate()),   // just for PDF
			'tax_rate' => $this->tax_rate,
			'contact' => $textFormat ? $this->contact->name : $this->contact_id,
			'reference' => $this->reference,
			'staff' => $textFormat ? $this->staff->name : $this->purchase_id,
			'currency' => $textFormat ? $currency->symbol : $this->currency_id,
			'billing' => $textFormat ? $billingAddressText : $this->billing_address_id,
			'shipping' => $textFormat ? $shippingAddressText : $this->shipping_address_id,
			'notes' => $this->notes,
			'untaxed_subtotal' => $textFormat ? $fmtr->format($untaxed_subtotal) : $fmtr->format($untaxed_subtotal),
																	// sprintf("%.".$currencyFormat['fdigit']."f", $untaxed_subtotal),
			'taxed_subtotal' => $textFormat ? $fmtr->format($taxed_subtotal) : $fmtr->format($taxed_subtotal),
																	// sprintf("%.".$currencyFormat['fdigit']."f", $taxed_subtotal),
			'tax_amount' => $textFormat ? $fmtr->format($tax_amount) : $fmtr->format($tax_amount),
																	// sprintf("%.".$currencyFormat['fdigit']."f", $tax_amount),
			'grand_total' => $textFormat ? $fmtr->format($grand_total) : $fmtr->format($grand_total),
																	// sprintf("%.".$currencyFormat['fdigit']."f", $grand_total),
			'line' => $line,
			'product' => $item,
			'display' => $display,
			'unitprice' => $unitprice,
			'taxable' => $taxable,
			'description' => $description,
			'quantity' => $quantity,
			'shippedQuantity' => $shippedQuantity,
			'ivcost' => $ivcost,
			'ddate' => $deliveryDate,
			'warehouse' => $recvLocation,
			'subtotal' => $subtotal,
		];
	}

	public function currency()
	{
		return $this->belongsTo('\App\Currency', 'currency_id');
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

	public function purchaseDetail()
	{
		return $this->hasMany('\App\PurchaseDetail', 'header_id');
	}

	public function detail()
	{
		return $this->purchaseDetail();
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

	public function isApproved()
	{
		return $this->approved;
	}

	public function isNotApproved()
	{
		return !$this->approved;
	}

	public function entity()
	{
		return $this->belongsTo('App\TaxableEntity', 'entity_id');
	}

	public function contact()
	{
		return $this->belongsTo('App\User', 'contact_id');
	}

	public function staff()
	{
		return $this->belongsTo('App\User', 'purchase_id');
	}

	public static function initialize($type, Request $request)
	{
		$title = ParameterHelper::getNextSequence('purchase_order_number');

		// if type is quote, just stuff following with default parameter
		if ($type == 'quote') {
			self::populateMissingColumn($request);
		}

		// update database
		$header = PurchaseHeader::create([
			'title' => $title,
			'printed' => 0,
			'approved' => 0,
			'released' => 0,
			'reference' => $request->input('reference'),
			'entity_id' => $request->input('supplier'),
			'contact_id' => $request->input('contact'),
			'purchase_id' => $request->input('staff'),
			'type' => $type,
			'status' => 'open',
			'billing_address_id' => $request->input('billing'),
			'shipping_address_id' => $request->input('shipping'),
			'payment_term_id' => $request->input('payment'),
			'fob' => $request->input('incoterm'),
			'via' => $request->input('via'),
			'tax_rate' => 0,
			'currency_id' => $request->input('currency'),
			'order_date' => DateHelper::guiToDbDate($request->input('inputdate')),
			'notes' => '',
			'internal_notes' => '',
		]);
		PurchaseHistory::create([
			'src' => 'purchase_headers',
			'src_id' => $header->id,
			'staff_id' => auth()->user()->id,
			'machine' => $request->ip(),
			'process_status' => 'created',
			'notes' => '',
		]);

		foreach ($request->input('product') as $idx => $product_id) {
			$detail = PurchaseDetail::create([
				'header_id' => $header->id,
				'unique_tradable_id' => $product_id,
				'manufacture_model' => $request->input('display')[$idx],
				'manufacture_reference' => '',
				'ordered_quantity' => $request->input('quantity')[$idx],
				'shipped_quantity' => 0,
				'shipped_amount' => 0,
				'description' => $request->input('description')[$idx],
				'unit_price' => $request->input('unitprice')[$idx],
				'inventory_cost' => ($type == 'quote') ? 0 : $request->input('ivcost')[$idx],
				'taxable' => in_array($request->input('taxable')[$idx], [1, "1", true, "true"], true),
				'status' => 'open',
				'delivery_date' => ($type == 'quote') ? DateHelper::guiToDbDate($request->input('expiration')) : DateHelper::guiToDbDate($request->input('ddate')[$idx]),
				'receiving_location_id' => ($type == 'quote') ? $request->input('receiving_location_id') : $request->input('warehouse')[$idx],
				'notes' => '',
			]);

			PurchaseHistory::create([
				'src' => 'purchase_details',
				'src_id' => $detail->id,
				'staff_id' => auth()->user()->id,
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

		event(new \App\Events\PurchaseUpsertEvent($header));

		return $header;
	}

	public function synchronize(Request $request)
	{
		if ($this['type'] == 'quote') {
			self::populateMissingColumn($request);
		}

		$this->update([
				'approved' => 0,
				'reference' => $request->input('reference'),
				'entity_id' => $request->input('supplier'),
				'contact_id' => $request->input('contact'),
				'purchase_id' => $request->input('staff'),
				'billing_address_id' => $request->input('billing'),
				'shipping_address_id' => $request->input('shipping'),
				'payment_term_id' => $request->input('payment'),
				'fob' => $request->input('incoterm'),
				'via' => $request->input('via'),
				'tax_rate' => 0,
				'currency_id' => $request->input('currency'),
				'order_date' => DateHelper::guiToDbDate($request->input('inputdate')),
			]);

		PurchaseHistory::create([
			'src' => 'purchase_headers',
			'src_id' => $this['id'],
			'staff_id' => auth()->user()['id'],
			'machine' => $request->ip(),
			'process_status' => 'updated',
			'notes' => '',
		]);

		$newTotalQuantity = 0;
		foreach ($request->input('product') as $idx => $product_id) {
			if ($request->input('line')[$idx] > 0) {
				$detail = PurchaseDetail::find($request->input('line')[$idx]);
				$detail->update([
						'unique_tradable_id' => $product_id,
						'manufacture_model' => $request->input('display')[$idx],
						'ordered_quantity' => $request->input('quantity')[$idx] + $detail['shipped_quantity'],
						'description' => $request->input('description')[$idx],
						'unit_price' => $request->input('unitprice')[$idx],
						'inventory_cost' => ($this['type'] == 'quote') ? 0 : $request->input('ivcost')[$idx],
						'taxable' => in_array($request->input('taxable')[$idx], [1, "1", true, "true"], true),
						'status' => $request->input('quantity')[$idx] ? 'open' : 'closed',
						'delivery_date' => ($this['type'] == 'quote') ? DateHelper::guiToDbDate($request->input('expiration')) : DateHelper::guiToDbDate($request->input('ddate')[$idx]),
						'receiving_location_id' => ($this['type'] == 'quote') ? $request->input('receiving_location_id') : $request->input('warehouse')[$idx],
					]);

				PurchaseHistory::create([
					'src' => 'purchase_details',
					'src_id' => $detail->id,
					'staff_id' => auth()->user()['id'],
					'machine' => $request->ip(),
					'process_status' => 'updated',
					'notes' => '',
				]);
			} else {
				$detail = PurchaseDetail::create([
					'header_id' => $this['id'],
					'unique_tradable_id' => $product_id,
					'manufacture_model' => $request->input('display')[$idx],
					'manufacture_reference' => '',
					'ordered_quantity' => $request->input('quantity')[$idx],
					'shipped_quantity' => 0,
					'shipped_amount' => 0,
					'description' => $request->input('description')[$idx],
					'unit_price' => $request->input('unitprice')[$idx],
					'inventory_cost' => ($this['type'] == 'quote') ? 0 : $request->input('ivcost')[$idx],
					'taxable' => in_array($request->input('taxable')[$idx], [1, "1", true, "true"], true),
					'status' => $request->input('quantity')[$idx] ? 'open' : 'closed',
					'delivery_date' => ($this['type'] == 'quote') ? DateHelper::guiToDbDate($request->input('expiration')) : DateHelper::guiToDbDate($request->input('ddate')[$idx]),
					'receiving_location_id' => ($this['type'] == 'quote') ? $request->input('receiving_location_id') : $request->input('warehouse')[$idx],
					'notes' => '',
				]);

				PurchaseHistory::create([
					'src' => 'purchase_details',
					'src_id' => $detail['id'],
					'staff_id' => auth()->user()['id'],
					'machine' => $request->ip(),
					'process_status' => 'created',
					'notes' => '',
				]);
			}
			$newTotalQuantity += $request->input('quantity')[$idx];
		}

		// all approval should be expired and re-approve
		PurchaseHistory::where([
					[ 'src', '=', 'purchase_headers' ],
					[ 'src_id', '=', $this['id'] ],
					[ 'process_status', '=', 'approved' ]
				])->update(['process_status' => 'approval expired']);
		PurchaseHistory::where([
					[ 'src', '=', 'purchase_headers' ],
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

			PurchaseHistory::create([
				'src' => 'purchase_headers',
				'src_id' => $this['id'],
				'staff_id' => auth()->user()['id'],
				'machine' => $request->ip(),
				'process_status' => 'closed',
				'notes' => '',
			]);
		}

		event(new \App\Events\PurchaseUpsertEvent($this));

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
			$allApprovers = array_column(DB::select("select approver_id from purchase_approval_rules where valid and (src_entity_id = " . $this->entity_id . " or src_entity_id = 0) and (case '" . $this->type . "' when 'quote' then 1 else ((select sum(if(taxable, (ordered_quantity * unit_price) * (100 + tax_rate) / 100, ordered_quantity*unit_price)) from purchase_details, purchase_headers where purchase_details.header_id = " . $this->id . " and purchase_headers.id = " . $this->id .") > threshold) end) and (case '" . $this->type . "' when 'quote' then applied_to_quote when 'order' then applied_to_order when 'return' then applied_to_return end)"), "approver_id");
			$doneApprovers = array_column(DB::select("select staff_id from purchase_histories where src='purchase_headers' and src_id=" . $this->id . " and process_status='approved'"), "staff_id");
			return in_array($userId, $allApprovers) && !(in_array($userId, $doneApprovers));
		}

		// if $userId = null, return if there are pending approvers;
		$allApprovers = array_column(DB::select("select approver_id from purchase_approval_rules where valid and (src_entity_id = " . $this->entity_id . " or src_entity_id = 0) and (case '" . $this->type . "' when 'quote' then 1 else ((select sum((if(taxable, (ordered_quantity * unit_price) * (100 + tax_rate) / 100, ordered_quantity*unit_price))) from purchase_details, purchase_headers where purchase_details.header_id = " . $this->id . " and purchase_headers.id = " . $this->id . ") > threshold) end) and (case '" . $this->type . "' when 'quote' then applied_to_quote when 'order' then applied_to_order when 'return' then applied_to_return end)"), "approver_id");
		$doneApprovers = array_column(DB::select("select staff_id from purchase_histories where src='purchase_headers' and src_id=" . $this->id . " and process_status='approved'"), "staff_id");
		return count(array_diff($allApprovers, $doneApprovers));
	}

	public function sendPendingApproverReminder()
	{
		// send pending approver reminder to approve this quote/order/return
		$allApprovers = array_column(DB::select("select approver_id from purchase_approval_rules where valid and (src_entity_id = " . $this->entity_id . " or src_entity_id = 0) and (case '" . $this->type . "' when 'quote' then 1 else ((select sum((if(taxable, (ordered_quantity * unit_price) * (100 + tax_rate) / 100, ordered_quantity*unit_price))) from purchase_details, purchase_headers where purchase_details.header_id = " . $this->id . " and purchase_headers.id = " . $this->id . ") > threshold) end) and (case '" . $this->type . "' when 'quote' then applied_to_quote when 'order' then applied_to_order when 'return' then applied_to_return end)"), "approver_id");
		$doneApprovers = array_column(DB::select("select staff_id from purchase_histories where src='purchase_headers' and src_id=" . $this->id . " and process_status='approved'"), "staff_id");
		$approvers = User::findMany(array_diff($allApprovers, $doneApprovers));

		try {
			Mail::send('email_templates.purchase_approval', ['order' => $this], function ($m) use ($approvers) {
					$m->subject('Purchase Approval Request');
					$m->from(config("mail.from.address"), config("mail.from.name"));
					foreach ($approvers as $approver) {
					$m->to($approver->email, $approver->name);
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
		}
	}

	public function approve($approverId, $ipAddress='127.0.0.1', $notes='')
	{
		PurchaseHistory::create([
			'src' => 'purchase_headers',
			'src_id' => $this->id,
			'staff_id' => $approverId,
			'machine' => $ipAddress,
			'process_status' => 'approved',
			'notes' => $notes,
		]);

		if (!$this->requireApproval()) {
			$this->update([ 'approved' => 1 ]);
			event (new \App\Events\PurchaseUpsertEvent($this));
			if ($this->isNotQuote()) {
				WarehouseHeader::synchronize($this, $ipAddress);
			}
			if (($this->isOrder() && env('AUTO_PO_RELEASE')) ||
				($this->isReturn() && env('AUTO_PR_RELEASE'))) {
				$this->release(auth()->user()->id, $ipAddress, $notes);
			}
		}
	}

	public function disapprove($approverId, $ipAddress='127.0.0.1', $notes='')
	{
		PurchaseHistory::create([
			'src' => 'purchase_headers',
			'src_id' => $this->id,
			'staff_id' => $approverId,
			'machine' => $ipAddress,
			'process_status' => 'rejected',
			'notes' => $notes,
		]);
		$this->update([ 'status' => 'closed' ]);
		event (new \App\Events\PurchaseUpsertEvent($this));
	}

	public function release($releaserId, $ipAddress='127.0.0.1', $notes='')
	{
		if ($this->approved) {
			PurchaseHistory::create([
				'src' => 'purchase_headers',
				'src_id' => $this->id,
				'staff_id' => $releaserId,
				'machine' => $ipAddress,
				'process_status' => 'released',
				'notes' => $notes,
			]);

			// send out email, with PO attached to vendor
			try {
				if (!Storage::exists('tmp' . DIRECTORY_SEPARATOR . session()->getId())) {
					Storage::makeDirectory('tmp' . DIRECTORY_SEPARATOR . session()->getId());
				}
				$tempFile = Storage::getDriver()->getAdapter()->getPathPrefix() . 'tmp' . DIRECTORY_SEPARATOR . session()->getId() . DIRECTORY_SEPARATOR . "Purchase " . ucfirst($this->type) . " #" . $this->title . ".pdf";
				Mail::send('email_templates.purchase_release', ['order' => $this], function ($m) use ($tempFile) {
						$m->subject('Purchase Order #' . $this->title);
						$m->from(config("mail.from.address"), config("mail.from.name"));
						$contact = $this->contact;
						$m->to($contact->email, $contact->name);
						$staff = $this->staff;
						$m->cc($staff->email, $staff->name);
						$pdf = $this->generatePDF();
						$pdf->Output($tempFile, "F");
						$m->attach($tempFile);
					});
				Storage::delete($tempFile);
			} catch (\Exception $e) {
				$registration = recordAndReportProblem($e);
			}

			$this->update([ 'released' => 1 ]);
			event (new \App\Events\PurchaseUpsertEvent($this));
		}
	}

	public function generatePdf()
	{
		$companyAddress = Address::where('entity_id', TaxableEntity::where('type', 'self')->first()->id)->where('purpose', 'billing')->where('is_default', 1)->first();

		$data = $this->generateArrayForOldInput(true);
		$data['release_approval'] = $this->approved;
		$data['company_address'] = $companyAddress->street.(empty($companyAddress->unit) ? "" : (" ".$companyAddress->unit))." ".$companyAddress->city.(empty($companyAddress->district) ? "" : (" ".$companyAddress->district))." ".$companyAddress->state." ".$companyAddress->country." ".$companyAddress->zipcode;
		$data['status'] = $this['status'];
		$pdf = new PurchaseFormPdf($data);
		if ($this->approved) {
			$this->printed = 1;
			$this->save();
			event(new \App\Events\PurchaseUpsertEvent($this));
		}
		return $pdf;
	}

	public function createPayable($request)
	{
		$totalAmount = 0;
		$totalTax = 0;
		$allClosed = true;

		$title = ParameterHelper::getNextSequence('transaction_number');
		$title = (($this->type == 'return') ? "C" : "") . $title;

		$incurDate = DateHelper::guiToDbDate($request->input('expiration'));
		$dueDate = date('Y-m-d', strtotime($incurDate . ' + ' . $this->paymentTerm->grace_days . ' days'));

		// create header
		$transactableHeaderObj = TransactableHeader::create([
			'title' => $title,
			'src_table' => 'purchase_headers',
			'src_id' => $this->id,
			'flags' => '',
			'reference' => $this->reference,
			'entity_id' => $this->entity_id,
			'contact_id' => $this->contact_id,
			'staff_id' => $this->purchase_id,
			'status' => 'open',
			'balance' => 0, // update after summing up all purchase_detail
			'billing_address_id' => $this->billing_address_id,
			'shipping_address_id' => $this->shipping_address_id,
			'payment_term_id' => $this->payment_term_id,
			'incur_date' => $incurDate,
			'approx_due_date' => $dueDate,
			'tax_rate' => $this->tax_rate,
			'currency_id' => $this->currency_id,
			'notes' => $this->notes,
			'internal_notes' => $this->internal_notes,
		]);

		// create history
		$transactionHistoryObj = TransactableHistory::create([
			'src' => 'transactable_headers',
			'src_id' => $transactableHeaderObj->id,
			'amount' => 0, // update after summing up all purchase detail
			'staff_id' => auth()->user()->id,
			'machine' => $request->ip(),
			'process_status' => 'created',
			'notes' => '',
		]);

		foreach ($request->input('line') as $lineIdx => $DetailIdx) {
			$qty = $request->input('processing')[$lineIdx];
			if ($qty > 0) {
				$purchaseDetailObj = PurchaseDetail::find($DetailIdx);
				// create transactable detail
				$transactableDetailObj = TransactableDetail::create([
					'transactable_header_id' => $transactableHeaderObj->id,
					'src_table' => 'purchase_details',
					'src_id' => $DetailIdx,
					'unique_tradable_id' => $purchaseDetailObj->unique_tradable_id,
					'display_as' => $purchaseDetailObj->manufacture_model,
					'description' => $purchaseDetailObj->description,
					'unit_price' => $purchaseDetailObj->unit_price,
					'discount' => 0,
					'discount_type' => 'amount',
					'transacted_quantity' => $request->input('processing')[$lineIdx],
					'transacted_amount' => $purchaseDetailObj->unit_price * $request->input('processing')[$lineIdx],
					'discount_amount' => 0,
					'tax_amount' => $purchaseDetailObj->getTaxPerUnit() * $request->input('processing')[$lineIdx],
					'status' => 'valid',
				]);
				$totalAmount += $transactableDetailObj->transacted_amount - $transactableDetailObj->discount_amount;
				$totalTax += $transactableDetailObj->tax_amount;
				// record history
				TransactableHistory::create([
					'src' => 'transactable_details',
					'src_id' => $transactableDetailObj->id,
					'amount' => $transactableDetailObj->transacted_amount - $transactableDetailObj->discount_amount + $transactableDetailObj->tax_amount,
					'staff_id' => auth()->user()->id,
					'machine' => $request->ip(),
					'process_status' => 'created',
					'notes' => '',
				]);
				if ($purchaseDetailObj->uniqueTradable->stockable) {
					// adjust inventory
					$transaction = TradableTransaction::create([
						'unique_tradable_id' => $transactableDetailObj->unique_tradable_id,
						'location_id' => $purchaseDetailObj->receiving_location_id,
						'owner_entity_id' => TaxableEntity::theCompany()->id,
						'quantity' => ($this->type == 'return') ? 0 - $request->input('processing')[$lineIdx] : $request->input('processing')[$lineIdx],
						'unit_cost' => $purchaseDetailObj->inventory_cost,
						'src_table' => 'transactable_details',
						'src_id' => $transactableDetailObj->id,
						'valid' => 1,
						'notes' => '',
						'created_at' => $incurDate . ' 00:00:01',
					]);
					event(new \App\Events\InventoryUpdateEvent($transaction));
					TaccountTransaction::create([
						'debit_t_account_id' => ($this->type == 'return') ?
														TaxableEntity::find($transactableHeaderObj->entity_id)->transaction_t_account_id :
														Location::find($purchaseDetailObj->receiving_location_id)->inventory_t_account_id,
						'credit_t_account_id' => ($this->type == 'return') ?
														Location::find($purchaseDetailObj->receiving_location_id)->inventory_t_account_id :
														TaxableEntity::find($transactableHeaderObj->entity_id)->transaction_t_account_id,
						'amount' =>  $transactableDetailObj->unit_price * $transactableDetailObj->transacted_quantity,
						'currency_id' => $this->currency_id,
						'book_date' => $incurDate,
						'src' => 'transactable_details',
						'src_id' => $transactableDetailObj->id,
						'valid' => 1,
						'reconciled' => 0,
						'notes' => '',
					]);
				} else {
					TaccountTransaction::create([
						'debit_t_account_id' => ($this->type == 'return') ?
														TaxableEntity::find($transactableHeaderObj->entity_id)->transaction_t_account_id :
														$purchaseDetailObj->uniqueTradable->expense_t_account_id,
						'credit_t_account_id' => ($this->type == 'return') ?
														$purchaseDetailObj->uniqueTradable->expense_t_account_id :
														TaxableEntity::find($transactableHeaderObj->entity_id)->transaction_t_account_id,
						'amount' =>  $transactableDetailObj->unit_price * $transactableDetailObj->transacted_quantity,
						'currency_id' => $this->currency_id,
						'book_date' => $incurDate,
						'src' => 'transactable_details',
						'src_id' => $transactableDetailObj->id,
						'valid' => 1,
						'reconciled' => 0,
						'notes' => '',
					]);
				}
				// adjust purchase order/return
				$purchaseDetailObj->shipped_quantity += $request->input('processing')[$lineIdx];
				$purchaseDetailObj->shipped_amount += $request->input('processing')[$lineIdx] * $purchaseDetailObj->unit_price;
				if ($purchaseDetailObj->ordered_quantity == $purchaseDetailObj->shipped_quantity) {
					$purchaseDetailObj->status = 'closed';
				} else {
					$allClosed = false;
				}
				$purchaseDetailObj->save();
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

		// if there's tax, handle it.
		if ($totalTax) {
			$taxAccount = ChartAccount::find(unserialize(Parameter::where('key', 'sales_tax_account_id')->first()->value));
			TaccountTransaction::create([
					'debit_t_account_id' => ($this->type == 'return') ?
													TaxableEntity::find($transactableHeaderObj->entity_id)->transaction_t_account_id :
													$taxAccount->id,
					'credit_t_account_id' => ($this->type == 'return') ?
													$taxAccount->id :
													TaxableEntity::find($transactableHeaderObj->entity_id)->transaction_t_account_id,
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

		event(new \App\Events\PurchaseUpsertEvent($this));
		event(new \App\Events\TransactableUpsertEvent($transactableHeaderObj));

		return $transactableHeaderObj;
	}

	public function translateWarehouseOrderType()
	{
		switch ($this['type']) {
			case 'order':
				return 'receive';
			case 'return':
				return 'deliver';
		}
		return null;
	}

	public function history()
	{
		return $this->hasMany('\App\PurchaseHistory', 'src_id', 'id')->where('src', 'purchase_headers');
	}

	private static function populateMissingColumn(Request $request)
	{
		$request->request->add([
				'billing' => Address::where([
						[ 'entity_id', '=', $request->input('supplier') ],
						[ 'purpose', '=', 'billing' ],
						[ 'is_default', '=', 1 ],
					])->first()['id'],
				'shipping' => Address::where([
						[ 'entity_id', '=', $request->input('supplier') ],
						[ 'purpose', '=', 'shipping' ],
						[ 'is_default', '=', 1 ],
					])->first()['id'],
				'via' => '',
				'receiving_location_id' => Location::where([
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
				return url('vrm/update' . $this->type . "/". $this->id);
			case 'view':
			default:
				return url('vrm/view' . $this->type . "/" . $this->id);
		}
	}

	// If new keyword is added, make sure generateSearchTips is updated as well
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
				str_pad('quote', 15) . trans('tool.Search purchase quote'),
				str_pad('order', 15) . trans('tool.Search purchase order'),
				str_pad('return', 15) . trans('tool.Search purchase return'),
				str_pad('open', 15) . trans('tool.Search open entry'),
				str_pad('void', 15) . trans('tool.Search void entry'),
				str_pad('closed', 15) . trans('tool.Search entry closed'),
				str_pad('thisyear', 15) . trans('tool.Search from this year'),
				str_pad('thismonth', 15) . trans('tool.Search from this month'),
				str_pad('lastmonth', 15) . trans('tool.Search from last month'),
			]);
	}
}
