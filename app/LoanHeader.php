<?php

namespace App;

use App\User;
use App\Helpers\DateHelper;
use App\Helpers\ParameterHelper;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class LoanHeader extends Model
{

	const LENDER_BAD_DEBT_NOTES = "bad-debt write-off";

	const BORROWER_BAD_DEBT_NOTES = "loan forgiven";

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'title', 'role', 'entity_id', 'principal', 'annual_percent_rate', 'currency_id', 'notes', 'staff_id'
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
				$builder->leftjoin(DB::raw("(select src_id as id, status from transactable_headers where src_table='loan_headers') t1"), "t1.id", "=", "loan_headers.id");
	      $builder->where(function ($query) use ($timeWindow) {
					if ($timeWindow == 'current') {
						$query->orWhere('loan_headers.created_at', '>=', date("Y-01-01"))
									->orWhereIn('t1.status', static::NOT_CLOSED_STATUS);
					} else if (strtotime(date("Y-m-01") . " " . env('TIME_WINDOW'))) {
						$query->orWhere('loan_headers.created_at', '>=', date("Y-m-d", strtotime(date("Y-m-01") . " " . $timeWindow)))
									->orWhereIn('t1.status', static::NOT_CLOSED_STATUS);
					}
				});
	    });
		}
  }

	public static function initialize(Request $request)
	{
		$company = TaxableEntity::theCompany();
		$staff = auth()->user() ?: User::getSystemUser();
		$entity = TaxableEntity::find($request->input('entity'));

		// create loan_header
		$header = LoanHeader::create([
			'title' => $request->input('title'),
			'role' => $request->input('role'),
			'entity_id' => $entity->id,
			'principal' => $request->input('principal'),
			'annual_percent_rate' => $request->input('apr'),
			'currency_id' => $company->currency_id,
			'notes' => $request->input('notes'),
			'staff_id' => $staff->id
		]);
		// and associated loan_history
		LoanHistory::create([
			'src' => 'loan_headers',
			'src_id' => $header->id,
			'staff_id' => $staff->id,
			'machine' => $request->ip(),
			'process_status' => 'created',
			'notes' => '',
		]);

		$dueDate = date('Y-m-d');

		$title = ParameterHelper::getNextSequence('transaction_number');
		$title = (($header->role == 'borrower') ? "C" : "") . $title;

		// create transactable_header
		$transactableHeaderObj = TransactableHeader::create([
			'title' => $title,
			'src_table' => 'loan_headers',
			'src_id' => $header->id,
			'flags' => '',
			'reference' => 'Loan ' . $request->input('title'),
			'entity_id' => $entity->id,
			'contact_id' => $entity->contact()->orderBy('id', 'desc')->first()->id,
			'staff_id' => $staff->id,
			'status' => 'open',
			'balance' => $request->input('principal'),
			'billing_address_id' => $entity->defaultBillingAddress[0]->id,
			'shipping_address_id' => $entity->defaultShippingAddress[0]->id,
			'payment_term_id' => $entity->payment_term_id,
			'incur_date' => date("Y-m-d"),
			'approx_due_date' => $dueDate,
			'tax_rate' => 0.0,
			'currency_id' => $company->currency_id,
			'notes' => '',
			'internal_notes' => $request->input('notes'),
		]);
		// and associated transactable_history
		TransactableHistory::create([
			'src' => 'transactable_headers',
			'src_id' => $transactableHeaderObj->id,
			'amount' => $request->input('principal'),
			'staff_id' => $staff->id,
			'machine' => $request->ip(),
			'process_status' => 'created',
			'notes' => '',
		]);

		// create transactable_detail
		$transactableDetailObj = TransactableDetail::create([
			'transactable_header_id' => $transactableHeaderObj->id,
			'src_table' => '',
			'src_id' => 0,
			'unique_tradable_id' => UniqueTradable::getApTransferItem()->id,
			'display_as' => 'Loan',
			'description' => 'Loan',
			'unit_price' => $request->input('principal'),
			'discount' => 0,
			'discount_type' => 'amount',
			'transacted_quantity' => 1,
			'transacted_amount' => $request->input('principal'),
			'discount_amount' => 0,
			'tax_amount' => 0,
			'status' => 'valid',
		]);
		// and associated transactable_history
		TransactableHistory::create([
			'src' => 'transactable_details',
			'src_id' => $transactableDetailObj->id,
			'amount' => $request->input('principal'),
			'staff_id' => $staff->id,
			'machine' => $request->ip(),
			'process_status' => 'created',
			'notes' => '',
		]);

		// create taccount_transaction
		TaccountTransaction::create([
			'debit_t_account_id' => ($header->role == 'lender') ?
											$entity->transaction_t_account_id :
											$request->input('cash_account'),
			'credit_t_account_id' => ($header->role == 'lender') ?
											$request->input('cash_account') :
											$entity->transaction_t_account_id,
			'amount' =>  $request->input('principal'),
			'currency_id' => $company->currency_id,
			'book_date' => date("Y-m-d"),
			'src' => 'transactable_headers',
			'src_id' => $transactableHeaderObj->id,
			'valid' => 1,
			'reconciled' => 0,
			'notes' => '',
		]);

		event(new \App\Events\LoanUpsertEvent($header));
		event(new \App\Events\TransactableUpsertEvent($transactableHeaderObj));

		return $header;
	}

	public function synchronize(Request $request)
	{
		$this->update([
			'title' => $request->input('title'),
			'annual_percent_rate' => $request->input('apr'),
			'notes' => $request->input('notes'),
		]);

		event(new \App\Events\LoanUpsertEvent($this));

		return $this;
	}

	public function history()
	{
		return $this->hasMany('App\LoanHistory', 'src_id')->where('src', 'loan_headers');
	}

	public function entity()
	{
		return $this->belongsTo('\App\TaxableEntity', 'entity_id');
	}

	public function currency()
	{
		return $this->belongsTo('\App\Currency', 'currency_id');
	}

	public function staff()
	{
		return $this->belongsTo('\App\User', 'staff_id');
	}

	public function getBalanceAsOf($date = null)
	{
		if (is_null($date)) {
			$date = date("Y-m-d");
		}

		$result = DB::select("select sum(case process_status when 'created' then amount when 'debited' then " . (($this->role == "borrower") ? "-" : "") . " amount when 'credited' then " . (($this->role == "lender") ? "-" : "") . " amount else 0 end) as balance from transactable_histories where src = 'transactable_headers' and src_id = (select id from transactable_headers where src_table = 'loan_headers' and src_id = " . $this->id .") and created_at <= '" . $date . " 23:59:59'");

		return $result[0]->balance;
	}

	public function getTransactionsAsOf($date = null)
	{
		if (is_null($date)) {
			$date = date("Y-m-d");
		}

		$currencyFormat = $this->currency->getFormat();
		$fmtr = new \NumberFormatter( $currencyFormat['regex'], \NumberFormatter::CURRENCY );

		$balance = 0;

		$result = DB::select("select date_format(created_at, \"%Y-%m-%d\") as date, notes as description, (case process_status when 'created' then amount when 'debited' then " . (($this->role == "borrower") ? "0" : "amount") . " when 'credited' then " . (($this->role == "borrower") ? "amount" : "0") . " else 0 end) as debit, (case process_status when 'created' then 0 when 'debited' then " . (($this->role == "lender") ? "0" : "amount") . " when 'credited' then " . (($this->role == "lender") ? "amount" : "0") . " else amount end) as credit, 0 as balance from transactable_histories where src = 'transactable_headers' and src_id = (select id from transactable_headers where src_table = 'loan_headers' and src_id = " . $this->id . ") and created_at <= '" . $date . " 23:59:59' order by created_at");

		foreach ($result as $idx => $transaction) {
			$balance += $transaction->debit - $transaction->credit;
			$result[$idx]->date = \App\Helpers\DateHelper::dbToGuiDate($transaction->date);
			$result[$idx]->debit = ($transaction->debit == "0") ? "" : $fmtr->format($transaction->debit);
			$result[$idx]->credit = ($transaction->credit == "0") ? "" : $fmtr->format($transaction->credit);
			$result[$idx]->balance = $fmtr->format($balance);
		}

		return array_reverse($result);
	}

	public function recordInterest($date, $amount, $category_id, $staffId, $requestIp)
	{
		// grab associated transaction header
		$transactableHeaderObj = TransactableHeader::where([['src_table', 'loan_headers'], ['src_id', $this->id]])->first();

		// add associated loan_history
		LoanHistory::create([
			'src' => 'loan_headers',
			'src_id' => $this->id,
			'staff_id' => $staffId,
			'machine' => $requestIp,
			'process_status' => ($this->role == "lender") ? "debited" : "credited",
			'notes' => 'interest accrued',
		]);

		// add associated transactable_history
		TransactableHistory::create([
			'src' => 'transactable_headers',
			'src_id' => $transactableHeaderObj->id,
			'amount' => $amount,
			'staff_id' => $staffId,
			'machine' => $requestIp,
			'process_status' => ($this->role == 'lender') ? "debited" : "credited",
			'notes' => 'interest accrued',
		]);

		// add one more line iteam into proper transactable_details
		$transactableDetailObj = TransactableDetail::create([
			'transactable_header_id' => $transactableHeaderObj->id,
			'src_table' => '',
			'src_id' => 0,
			'unique_tradable_id' => UniqueTradable::getApTransferItem()->id,
			'display_as' => 'Interest',
			'description' => 'Interest',
			'unit_price' => $amount,
			'discount' => 0,
			'discount_type' => 'amount',
			'transacted_quantity' => 1,
			'transacted_amount' => $amount,
			'discount_amount' => 0,
			'tax_amount' => 0,
			'status' => 'valid',
		]);

		// and associated transactable_history
		TransactableHistory::create([
			'src' => 'transactable_details',
			'src_id' => $transactableDetailObj->id,
			'amount' => $amount,
			'staff_id' => $staffId,
			'machine' => $requestIp,
			'process_status' => 'created',
			'notes' => '',
		]);

		// update header balance
		$transactableHeaderObj->balance += $amount;
		$transactableHeaderObj->save();
		event(new \App\Events\TransactableUpsertEvent($transactableHeaderObj));

		// update T-account accordingly.
		TaccountTransaction::create([
			'debit_t_account_id' => ($this->role == 'lender') ?
											$this->entity->transaction_t_account_id :
											$category_id,
			'credit_t_account_id' => ($this->role == 'lender') ?
											$category_id :
											$this->entity->transaction_t_account_id,
			'amount' =>  $amount,
			'currency_id' => $this->currency_id,
			'book_date' => $date,
			'src' => 'transactable_headers',
			'src_id' => $transactableHeaderObj->id,
			'valid' => 1,
			'reconciled' => 0,
			'notes' => '',
		]);

		event(new \App\Events\LoanUpsertEvent($this));

		return $this->getBalanceAsOf();
	}

	// If new keyword is added, make sure generateSearchTips() is updated as well
	public function generateSearchAttribute()
	{
		$result = [];

		array_push($result, $this->role);
		array_push($result, $this->entity->code);
		array_push($result, ($this->getBalanceAsOf() > 0) ? 'open' : 'closed');

		return $result;
	}

	// If new keyword is added, make sure generateSearchAttribute() is updated as well
	public static function generateSearchTips($delimiter)
	{
		return implode($delimiter, [
				str_pad('lender', 15) . trans('tool.Search loan lent'),
				str_pad('borrower', 15) . trans('tool.Search loan borrowed'),
				str_pad('open', 15) . trans('tool.Search open loan'),
				str_pad('closed', 15) . trans('tool.Search loan closed'),
			]);
	}

	public function becomeBadDebt($date, $category_id, $staffId, $requestIp)
	{
		$amount = $this->getBalanceAsOf();

		$transactableHeaderObj = TransactableHeader::where([['src_table', 'loan_headers'], ['src_id', $this->id]])->first();

		// add associated loan_history
		LoanHistory::create([
			'src' => 'loan_headers',
			'src_id' => $this->id,
			'staff_id' => $staffId,
			'machine' => $requestIp,
			'process_status' => ($this->role == "lender") ? "credited" : "debited",
			'notes' => ($this->role == "lender") ? self::LENDER_BAD_DEBT_NOTES : self::BORROWER_BAD_DEBT_NOTES,
		]);

		$transactableHeaderObj->update([
			'balance' => 0,
			'status' => 'closed',
			'internal_notes' => ($this->role == "lender") ? self::LENDER_BAD_DEBT_NOTES : self::BORROWER_BAD_DEBT_NOTES,
		]);

		// add associated transactable_history and close transactable_headers
		TransactableHistory::create([
				'src' => 'transactable_headers',
				'src_id' => $transactableHeaderObj->id,
				'amount' => $amount,
				'staff_id' => $staffId,
				'machine' => $requestIp,
				'process_status' => ($this->role == "lender") ? "credited" : "debited",
				'notes' => ($this->role == "lender") ? self::LENDER_BAD_DEBT_NOTES : self::BORROWER_BAD_DEBT_NOTES,
			]);

		TransactableHistory::create([
				'src' => 'transactable_headers',
				'src_id' => $transactableHeaderObj->id,
				'amount' => 0,
				'staff_id' => $staffId,
				'machine' => $requestIp,
				'process_status' => "closed",
				'notes' => ($this->role == "lender") ? self::LENDER_BAD_DEBT_NOTES : self::BORROWER_BAD_DEBT_NOTES,
			]);

		event(new \App\Events\TransactableUpsertEvent($transactableHeaderObj));

		// update T-account accordingly.
		TaccountTransaction::create([
			'debit_t_account_id' => ($this->role == 'lender') ?
											$category_id :
											$this->entity->transaction_t_account_id,
			'credit_t_account_id' => ($this->role == 'lender') ?
											$this->entity->transaction_t_account_id :
											$category_id,
			'amount' =>  $amount,
			'currency_id' => $this->currency_id,
			'book_date' => $date,
			'src' => 'transactable_headers',
			'src_id' => $transactableHeaderObj->id,
			'valid' => 1,
			'reconciled' => 0,
			'notes' => ($this->role == "lender") ? self::LENDER_BAD_DEBT_NOTES : self::BORROWER_BAD_DEBT_NOTES,
		]);

		event(new \App\Events\LoanUpsertEvent($this));

		return $amount;
	}

	public function isBadDebt()
	{
		return $this->history()
				->where(function($query) {
					$query->where('notes', self::LENDER_BAD_DEBT_NOTES)
						->orWhere('notes', self::BORROWER_BAD_DEBT_NOTES);
				})
				->count() > 0;
	}

	// if no previous payment, use loan initiation date
	public function getLastPaymentDate()
	{
		$transactableHeaderObj = TransactableHeader::where([['src_table', 'loan_headers'], ['src_id', $this->id]])->first();

		// when pay:
		// lender     cash_account | transaction_account
		// borrower   transaction_account | cash_account

		$trx = $transactableHeaderObj->history()->where('process_status', ($this->role == 'lender') ? 'credited' : 'debited')->orderBy('created_at', 'desc')->first();

		if ($trx) {
			return explode(" ", $trx->created_at)[0];
		}

		return explode(" ", $this->created_at)[0];
	}

}
