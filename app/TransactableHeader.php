<?php

namespace App;

use App\ExpenseDetail;
use App\Helpers\DateHelper;
use App\PurchaseHeader;
use App\SalesHeader;
use App\TaxableEntity;
use App\TradableTransaction;
use App\TransactableDetail;
use App\TransactableHistory;
use Auth;
use DB;
use Lang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TransactableHeader extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'title', 'src_table', 'src_id', 'flags', 'reference', 'entity_id', 'contact_id', 'staff_id', 'status', 'balance', 'billing_address_id', 'shipping_address_id', 'payment_term_id', 'incur_date', 'approx_due_date', 'tax_rate', 'currency_id', 'notes', 'internal_notes',
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
						$query->orWhere('transactable_headers.incur_date', '>=', date("Y-01-01"))
									->orWhereIn('transactable_headers.status', static::NOT_CLOSED_STATUS);
					} else if (strtotime(date("Y-m-01") . " " . env('TIME_WINDOW'))) {
						$query->orWhere('transactable_headers.incur_date', '>=', date("Y-m-d", strtotime(date("Y-m-01") . " " . $timeWindow)))
									->orWhereIn('transactable_headers.status', static::NOT_CLOSED_STATUS);
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

	public function staff()
	{
		return $this->belongsTo('\App\User', 'staff_id');
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

	public function currency()
	{
		return $this->belongsTo('\App\Currency', 'currency_id');
	}

	public function detail()
	{
		return $this->hasMany('\App\TransactableDetail', 'transactable_header_id', 'id');
	}

	public function source()
	{
		switch ($this->src_table) {
		case 'sales_headers':
			return $this->belongsTo('\App\SalesHeader', 'src_id')->withoutGlobalScope('currentFiscal');
		case 'purchase_headers':
			return $this->belongsTo('\App\PurchaseHeader', 'src_id')->withoutGlobalScope('currentFiscal');
		case 'expense_headers':
			return $this->belongsTo('\App\ExpenseHeader', 'src_id')->withoutGlobalScope('currentFiscal');
		case 'loan_headers':
			return $this->belongsTo('\App\LoanHeader', 'src_id')->withoutGlobalScope('currentFiscal');
		default:
			// weird that a null object is calling this function.  maybe one day I'll know why.
			return $this->belongsTo('\App\TransactableHeader', '')->withDefault();
		}
		// weird that a null object is calling this function.  maybe one day I'll know why.
		return $this->belongsTo('\App\TransactableHeader', '')->withDefault();
	}

	public function sourceText()
	{
		$text = "";
		switch ($this->src_table) {
		case 'sales_headers':
			if ($this->isReceivableInvoice()) {
				$text .= "Sales Order #" . $this->source->title;
			} else if ($this->isReceivableCredit()) {
				$text .= "Sales Return #" . $this->source->title;
			} else {
				$text .= "Unknown";
			}
			break;
		case 'purchase_headers':
			if ($this->isPayableCredit()) {
				$text .= "Purchase Order #" . $this->source->title;
			} else if ($this->isPayableInvoice()) {
				$text .= "Purchase Return #" . $this->source->title;
			} else {
				$text .= "Unknown";
			}
			break;
		case 'expense_headers':
			// A/P transfer can set src_id = 0
			$text .= ($this->src_id > 0) ? ("Expense #" . $this->source->title) : "Unknown";
			break;
		case 'loan_headers':
			$text .= ($this->src_id > 0) ? ("Loan " . $this->source->title) : "Unknown";
			break;
		default:
			$text .= "Unknown";
			break;
		}
		return $text;
	}

	/*
	 * flash(pre-fill) old input into option array.  This is for update/view/approve/process function.
	 */
	public function generateArrayForOldInput($textFormat = false)
	{
		$currencyFormat = $this->currency->getFormat();
		$fmtr = new \NumberFormatter( $currencyFormat['regex'], \NumberFormatter::CURRENCY );

		//$incur_date = DateHelper::dbToGuiDate($this->incur_date);
		//$approx_due_date = DateHelper::dbToGuiDate($this->approx_due_date);

		$line = array();
		$item = array();
		$display = array();
		$description = array();
		$unitprice = array();
		$discount = array();
		$discountType = array();
		$taxAmount = array();
		$quantity = array();
		$subtotal = array();
		$grand_subtotal = 0;
		$grand_tax = 0;
		$grand_total = 0;

		foreach ($this->detail()->where('status', 'valid')->get() as $oneDetail) {
			$line[] = $oneDetail->id;
			$item[] = $textFormat ? $oneDetail->uniqueTradable->sku : $oneDetail->unique_tradable_id;
			$display[] = $oneDetail->display_as;
			$unitprice[] = $textFormat ? $fmtr->format($oneDetail->unit_price) : sprintf("%.".$currencyFormat['fdigit']."f", $oneDetail->unit_price);
			$discount[] = $oneDetail->discount;
			$discountType[] = $oneDetail->getDiscountTypeSymbol();
			$taxAmount[] = $oneDetail->tax_amount;
			$description[] = $oneDetail->description;
			$quantity[] = sprintf(env('APP_QUANTITY_FORMAT'), $oneDetail->transacted_quantity);
			$temp = $oneDetail->getLineSubtotal();
			$subtotal[] = $textFormat ? $fmtr->format($temp) : sprintf("%.".$currencyFormat['fdigit']."f", $temp);
			$grand_subtotal += $temp;
			$grand_tax += $oneDetail->tax_amount;
		}
		$grand_total = $grand_subtotal + $grand_tax;

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
			'increment' => $this->title,
			'showdiscount' => in_array('show_discount', explode(',', $this->flags)),
			'entity' => $textFormat ? $this->entity->code : $this->entity_id,
			'incurdate' => DateHelper::dbToGuiDate($this->incur_date),
			'payment' => $textFormat ? $this->paymentTerm->symbol : $this->payment_term_id,
			'duedate' => DateHelper::dbToGuiDate($this->approx_due_date),
			'source' => $this->sourceText(),
			'reference' => $this->reference,
			'staff' => $textFormat ? $this->staff->name : $this->staff_id,
			'contact' => $textFormat ? $this->contact->name : $this->contact_id,
			'currency' => $textFormat ? $currency->symbol : $this->currency_id,
			'currencyFormat' => [
					'regex' => $currency->regex,
					'symbol' => str_replace('_', '-', $currency->symbol),
				],
			'billing' => $textFormat ? $billingAddressText : $this->billing_address_id,
			'shipping' => $textFormat ? $shippingAddressText : $this->shipping_address_id,
			'notes' => $this->notes,
			'grand_subtotal' => $textFormat ? $fmtr->format($grand_subtotal) : $fmtr->format($grand_subtotal),
																// sprintf("%.".$currencyFormat['fdigit']."f", $grand_subtotal),
			'tax_amount' => $textFormat ? $fmtr->format($grand_tax) : $fmtr->format($grand_tax),
																// sprintf("%.".$currencyFormat['fdigit']."f", $grand_tax),
			'grand_total' => $textFormat ? $fmtr->format($grand_total) : $fmtr->format($grand_total),
																// sprintf("%.".$currencyFormat['fdigit']."f", $grand_total),
			'balance' => $textFormat ? $fmtr->format($this->balance) : $fmtr->format($this->balance),
																// sprintf("%.".$currencyFormat['fdigit']."f", $this->balance),
			'line' => $line,
			'product' => $item,
			'display' => $display,
			'unitprice' => $unitprice,
			'discount' => $discount,
			'disctype' => $discountType,
			'description' => $description,
			'quantity' => $quantity,
			'linetax' => $taxAmount,
			'subtotal' => $subtotal,
		];
	}

	public function totalAmount()
	{
		$result = DB::select("select sum(transacted_amount - discount_amount + tax_amount) as total from transactable_details where transactable_header_id = " . $this->id /*. " and status = 'valid'"*/);
		return $result[0]->total;
	}

	public function isReceivable()
	{
		return ($this->src_table == 'sales_headers') ||
				(($this->src_table == 'loan_headers') && ($this->source->role == 'lender'));
	}

	public function isPayable()
	{
		return ($this->src_table == 'purchase_headers') || ($this->src_table == 'expense_headers') ||
				(($this->src_table == 'loan_headers') && ($this->source->role == 'borrower'));
	}

	public function void(Request $request)
	{
		$this->update([
			'status' => 'void',
		]);
		TransactableHistory::create([
				'src' => 'transactable_headers',
				'src_id' => $this->id,
				'amount' => $this->balance,
				'staff_id' => Auth::user()->id,
				'machine' => $request->ip(),
				'process_status' => 'voided',
				'notes' => '',
			]);
		foreach ($this->detail as $oneDetail) {
			// DO NOT void detail so it will still show up when view
			//$oneDetail->update([
			//	'status' => 'void',
			//]);
			TransactableHistory::create([
				'src' => 'transactable_details',
				'src_id' => $oneDetail->id,
				'amount' => $oneDetail->transacted_amount - $oneDetail->discount_amount + $oneDetail->tax_amount,
				'staff_id' => Auth::user()->id,
				'machine' => $request->ip(),
				'process_status' => 'voided',
				'notes' => '',
			]);
			$tradableTransaction = TradableTransaction::where('src_table', 'transactable_details')->where('src_id', $oneDetail->id)->first();
			if ($tradableTransaction) {
				$tradableTransaction->update([
					'valid' => 0,
				]);
				event(\App\Events\InventoryUpdateEvent($tradableTransaction));
			}
			foreach (TaccountTransaction::where('src', 'transactable_details')->where('src_id', $oneDetail->id)->get() as $oneTx) {
				$oneTx->update([
					'valid' => 0,
				]);
			}
		}
		// void taccount_transactions
		foreach (TaccountTransaction::where('src', 'transactable_headers')->where('src_id', $this->id)->get() as $oneTx) {
			$oneTx->update([
				'valid' => 0,
			]);
		}
		// if invoice is generated from consignment
		if ($this->isReceivableInvoice()) {
			$result = DB::select("select meta from consignment_receivable_relations where transactable_header_id = " . $this->id);
			if (count($result)) {
				foreach (unserialize($result[0]->meta) as $table => $ids) {
					switch ($table) {
						case 'tradable_transactions':
							TradableTransaction::whereIn('id', $ids)->update([ 'valid' => 0 ]);
							foreach ($ids as $id) {
								event(new \App\Events\InventoryUpdateEvent(TradableTransaction::find($id)));
							}
							break;
						case 'transactable_headers':
							foreach ($ids as $id) {
								TransactableHeader::find($id)->void($request);
							}
							break;
						default:
							break;
					}
				}
			}
		}

		event(new \App\Events\TransactableUpsertEvent($this));

		return $this;
	}

	public function close(Request $request)
	{
		if ($this->balance != 0) {
			throw new \Exception('TransactableHeader can not be closed if balance is not zero');
		}

		$this->update(['status' => 'closed']);
		TransactableHistory::create([
			'src' => 'transactable_headers',
			'src_id' => $this->id,
			'amount' => '0.00',
			'staff_id' => Auth::user()->id,
			'machine' => $request->ip(),
			'process_status' => 'closed',
			'notes' => '',
		]);

		event(new \App\Events\TransactableUpsertEvent($this));
	}

	public function isInvoice()
	{
		switch ($this->src_table) {
			case 'sales_headers':
				switch ($this->source->type) {
					case 'order':
						return true;
					case 'return':
						return false;
				}
				throw new \Exception("Can't determine debit/credit of transaction");
				break;
			case 'purchase_headers':
				switch ($this->source->type) {
					case 'order':
						return false;
					case 'return':
						return true;
				}
				throw new \Exception("Can't determine debit/credit of transaction");
				break;
			case 'expense_headers':
				// A/P transfer sets src_id = 0
				return ($this->src_id > 0) ? ($this->source->detail()->sum('subtotal') < 0) : false;
				break;
			case 'loan_headers':
				return $this->source->role == 'lender';
				break;
		}
		throw new \Exception("Can't determine debit/credit of transaction");
	}

	public function isCredit()
	{
		switch ($this->src_table) {
			case 'sales_headers':
				switch ($this->source->type) {
					case 'order':
						return false;
					case 'return':
						return true;
				}
				throw new \Exception("Can't determine debit/credit of transaction");
				break;
			case 'purchase_headers':
				switch ($this->source->type) {
					case 'order':
						return true;
					case 'return':
						return false;
				}
				throw new \Exception("Can't determine debit/credit of transaction");
				break;
			case 'expense_headers':
				// A/P transfer sets src_id = 0
				return ($this->src_id > 0) ? ($this->source->detail()->sum('subtotal') > 0) : true;
				break;
			case 'loan_headers':
				return $this->source->role == 'borrower';
				break;
		}
		throw new \Exception("Can't determine debit/credit of transaction");
	}

	public function isReceivableInvoice() {
		return $this->isReceivable() && $this->isInvoice();
	}

	public function isReceivableCredit() {
		// receivable credit can come from invoice or chargeback
		return ($this->isReceivable() && $this->isCredit()) || (($this->src_table == 'expense_headers') && $this->isCredit());
	}

	public function isPayableInvoice() {
		return $this->isPayable() && $this->isInvoice();
	}

	public function isPayableCredit() {
		return $this->isPayable() && $this->isCredit();
	}

	public function generatePdf()
	{
		$data = $this->generateArrayForOldInput(true);

		$transactableTypeText = '';
		// force phrases in English
		if ($this->isReceivable()) {
			$transactableTypeText = Lang::get('finance.Receivable', [], 'en') . ' ';
		} else if ($this->isPayable()) {
			$transactableTypeText = Lang::get('finance.Payable', [], 'en') . ' ';
		}
		$data['increment'] =  $transactableTypeText . $data['increment'];

		$companyAddress = Address::where('entity_id', TaxableEntity::theCompany()->id)->where('purpose', 'billing')->where('is_default', 1)->first();
		$data['company_address'] = $companyAddress->street.(empty($companyAddress->unit) ? "" : (" ".$companyAddress->unit))." ".$companyAddress->city.(empty($companyAddress->district) ? "" : (" ".$companyAddress->district))." ".$companyAddress->state." ".$companyAddress->country." ".$companyAddress->zipcode;
		$data['status'] = $this['status'];
		$pdf = new TransactableFormPdf($data);
		return $pdf;
	}

	public function history()
	{
		return $this->hasMany('\App\TransactableHistory', 'src_id', 'id')->where('src', 'transactable_headers');
	}

	public function associateConsignment($meta)
	{
		if (!$this->isReceivableInvoice()) {
			return;
		}

		DB::table('consignment_receivable_relations')->insert([
			'transactable_header_id' => $this->id,
			'meta' => serialize($meta),
		]);
	}

	CONST STATUS_SEARCH_KEY_MAPPING = [
		'open' => 'open',
		'void' => 'void',
		'closed' => 'paid',
	];

	// If new keyword is added, make sure generateSearchTips() is updated as well
	public function generateSearchAttribute()
	{
		$result = [];

		array_push($result, $this->isReceivable() ? 'receivable' : 'payable');
		array_push($result, $this->isInvoice() ? 'invoice' : 'credit');
		array_push($result, self::STATUS_SEARCH_KEY_MAPPING[$this->status]);
		if (substr($this->incur_date, 0, 4) == date("Y")) {
			array_push($result, 'thisyear');
		}
		if (substr($this->incur_date, 0, 7) == date("Y-m")) {
			array_push($result, 'thismonth');
		}
		if (substr($this->incur_date, 0, 7) == date("Y-m", strtotime("-1 month"))) {
			array_push($result, 'lastmonth');
		}

		return $result;
	}

	// If new keyword is added, make sure generateSearchAttribute() is updated as well
	public static function generateSearchTips($delimiter)
	{
		return implode($delimiter, [
				str_pad('receivable', 15) . trans('tool.Search receivable'),
				str_pad('payable', 15) . trans('tool.Search payable'),
				str_pad('invoice', 15) . trans('tool.Search invoice'),
				str_pad('credit', 15) . trans('tool.Search credit'),
				str_pad('thisyear', 15) . trans('tool.Search from this year'),
				str_pad('thismonth', 15) . trans('tool.Search from this month'),
				str_pad('lastmonth', 15) . trans('tool.Search from last month'),
			]);
	}
}
