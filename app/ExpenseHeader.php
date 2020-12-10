<?php

namespace App;

use App\Address;
use App\Downloadable;
use App\ExpenseDetail;
use App\ExpenseHistory;
use App\Location;
use App\TaxableEntity;
use App\TaccountTransaction;
use App\TradableTransaction;
use App\TransactableDetail;
use App\TransactableHeader;
use App\TransactableHistory;
use App\Helpers\DateHelper;
use App\Helpers\ParameterHelper;
use DB;
use Mail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Storage;

class ExpenseHeader extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'title', 'staff_id', 'entity_id', 'booking_date', 'reference', 'status', 'currency_id', 'notes',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

	CONST NOT_CLOSED_STATUS = [ 'un-submitted', 'under review', 'approved' ];

	protected static function boot()
  {
    parent::boot();

		// scope objects are in designated-period or objects are not-closed
		if ($timeWindow = env('TIME_WINDOW')) {
	    static::addGlobalScope('currentFiscal', function (Builder $builder) use ($timeWindow) {
	      $builder->where(function ($query) use ($timeWindow) {
					if ($timeWindow == 'current') {
						$query->orWhere('expense_headers.booking_date', '>=', date("Y-01-01"))
									->orWhereIn('expense_headers.status', static::NOT_CLOSED_STATUS);
					} else if (strtotime(date("Y-m-01") . " " . env('TIME_WINDOW'))) {
						$query->orWhere('expense_headers.booking_date', '>=', date("Y-m-d", strtotime(date("Y-m-01") . " " . $timeWindow)))
									->orWhereIn('expense_headers.status', static::NOT_CLOSED_STATUS);
					}
				});
	    });
		}
  }

	public function isOpen()
	{
		return in_array($this->status, ['un-submitted', 'under review', 'approved']);
	}

	public function isNotOpen()
	{
		return in_array($this->status, ['cancelled', 'rejected', 'void', 'paid']);
	}

	public function entity()
	{
		return $this->belongsTo('App\TaxableEntity', 'entity_id');
	}

	public function detail()
	{
		return $this->hasMany('App\ExpenseDetail', 'expense_header_id', 'id');
	}

	/*
	 * flash(pre-fill) old input into option array.  This is for update/view/approve/process function.
	 */
	public function generateArrayForOldInput()
	{
		$currencyFormat = $this->currency->getFormat(false);

		$lines = array();
		$products = array();
		$incurdates = array();
		$unitprices = array();
		$quantities = array();
		$details = array();
		$subtotals = array();
		$filename = array();
		$fileurl = array();
		foreach ($this->detail as $oneDetail) {
			$lines[] = $oneDetail->id;
			$products[] = $oneDetail->unique_tradable_id;
			$incurdates[] = DateHelper::dbToGuiDate($oneDetail->incur_date);
			$unitprices[] = sprintf("%0.".$currencyFormat['fdigit']."f", $oneDetail->unit_price);
			$quantities[] = sprintf(env('APP_QUANTITY_FORMAT'), $oneDetail->quantity);
			$details[] = $oneDetail->notes;
			$subtotals[] = sprintf("%0.".$currencyFormat['fdigit']."f", $oneDetail->unit_price*$oneDetail->quantity);
			$downloadable = Downloadable::find($oneDetail->attachment_id);
			$filename[] = $downloadable->original_name;
			$fileurl[] = '/file/download/' . $downloadable->hash;
		}

		return [
			'id' => $this->id,
			'increment' => $this->title,
			'entity' => $this->entity_id,
			'reference' => $this->reference,
			'staff' => $this->staff_id,
			'currency' => $this->currency_id,
			'notes' => $this->notes,
			'line' => $lines,
			'product' => $products,
			'incurdate' => $incurdates,
			'unitprice' => $unitprices,
			'quantity' => $quantities,
			'description' => $details,
			'subtotal' => $subtotals,
			'filename' => $filename,
			'fileurl' => $fileurl,
		];
	}

	// expense can only be modified if it pertains to user or from an external entity.
	public function canBeAccessedBy($userId)
	{
		$user = User::find($userId);
		$userEntity = $user->entity_id;
		$entities = array();
		foreach (TaxableEntity::whereIn('type', ['supplier', 'customer'])->where('active', 1)->get() as $oneEntity) {
			$entities[] = $oneEntity->id;
		}
		$entities[] = $userEntity;
		if ($user->can('ap-view')) {
			$entities[] = TaxableEntity::theCompany()->id;
		}
		return in_array($this->entity_id, $entities);
	}

	public static function initialize(Request $request)
	{
		$title = ParameterHelper::getNextSequence('expense_number');

		// update database
		$header = ExpenseHeader::create([
			'title' => $title,
			'staff_id' => auth()->user()->id,
			'entity_id' => $request->input('entity'),
			'booking_date' => DateHelper::maxGuiDate($request->input('incurdate'), true),
			'reference' => $request->input('reference'),
			'status' => 'un-submitted',
			'currency_id' => $request->input('currency'),
			'notes' => $request->input('notes'),
		]);

		ExpenseHistory::create([
			'src' => 'expense_headers',
			'src_id' => $header->id,
			'staff_id' => auth()->user()->id,
			'machine' => $request->ip(),
			'process_status' => 'created',
			'notes' => '',
		]);

		foreach ($request->input('product') as $idx => $product_id) {
			// prepare attachment file.
			$theFile = $request->file('upload-selector.'.$idx);
			$originalName = $theFile->getClientOriginalName();
			$fileSize = $theFile->getSize();
			$fileMime = $theFile->getMimeType();
			$hashValue = md5(date('YmdHis').$originalName);

			// copy to storage location
			//$theFile->move(Storage::getDriver()->getAdapter()->getPathPrefix(), $hashValue);
			Storage::disk('s3')->put($hashValue, file_get_contents($theFile->getRealPath()), 'public');

			$downloadable = Downloadable::create([
				'uploader_id' => auth()->user()->id,
				'title' => '',
				'description' => 'expense attachment uploaded by ' . auth()->user()->name . ' at ' . date('Y-m-d H:i:s'),
				'original_name' => $originalName,
				'file_size' => $fileSize,
				'mime_type' => $fileMime,
				'hash' => $hashValue,
				'valid' => 1,
			]);

			$detail = ExpenseDetail::create([
				'expense_header_id' => $header->id,
				'unique_tradable_id' => $product_id,
				'unit_price' => $request->input('unitprice')[$idx],
				'quantity' => $request->input('quantity')[$idx],
				'subtotal' => $request->input('unitprice')[$idx]*$request->input('quantity')[$idx],
				'incur_date' => DateHelper::guiToDbDate($request->input('incurdate')[$idx]),
				'notes' => $request->input('description')[$idx],
				'attachment_id' => $downloadable->id,
			]);

			ExpenseHistory::create([
				'src' => 'expense_details',
				'src_id' => $detail->id,
				'staff_id' => auth()->user()->id,
				'machine' => $request->ip(),
				'process_status' => 'created',
				'notes' => '',
			]);
		}

		event(new \App\Events\ExpenseUpsertEvent($header));

		return $header;
	}

	public function synchronize($request)
	{
		//// since only unsubmitted expense can be updated,
		//// an expense, by default, is update-able if it is 'un-submitted' state
		$this->update([
			'booking_date' => DateHelper::maxGuiDate($request->input('incurdate'), true),
			'reference' => $request->input('reference'),
			'currency_id' => $request->input('currency'),
			'notes' => $request->input('notes'),
		]);

		ExpenseHistory::create([
			'src' => 'expense_headers',
			'src_id' => $this->id,
			'staff_id' => auth()->user()->id,
			'machine' => $request->ip(),
			'process_status' => 'updated',
			'notes' => '',
		]);

		foreach ($request->input('product') as $idx => $product_id) {
			if ($request->input('line')[$idx] == "0") {
				// prepare attachment file.
				$theFile = $request->file('upload-selector.'.$idx);
				$originalName = $theFile->getClientOriginalName();
				$fileSize = $theFile->getSize();
				$fileMime = $theFile->getMimeType();
				$hashValue = md5(date('YmdHis').$originalName);

				// copy to storage location
				//$theFile->move(Storage::getDriver()->getAdapter()->getPathPrefix(), $hashValue);
				Storage::disk('s3')->put($hashValue, file_get_contents($theFile->getRealPath()), 'public');

				$downloadable = Downloadable::create([
					'uploader_id' => auth()->user()->id,
					'title' => '',
					'description' => 'expense attachment uploaded by ' . auth()->user()->name . ' at ' . date('Y-m-d H:i:s'),
					'original_name' => $originalName,
					'file_size' => $fileSize,
					'mime_type' => $fileMime,
					'hash' => $hashValue,
					'valid' => 1,
				]);

				$detail = ExpenseDetail::create([
					'expense_header_id' => $this->id,
					'unique_tradable_id' => $product_id,
					'unit_price' => $request->input('unitprice')[$idx],
					'quantity' => $request->input('quantity')[$idx],
					'subtotal' => $request->input('unitprice')[$idx]*$request->input('quantity')[$idx],
					'incur_date' => DateHelper::guiToDbDate($request->input('incurdate')[$idx]),
					'notes' => $request->input('description')[$idx],
					'attachment_id' => $downloadable->id,
				]);

				ExpenseHistory::create([
					'src' => 'expense_details',
					'src_id' => $detail->id,
					'staff_id' => auth()->user()->id,
					'machine' => $request->ip(),
					'process_status' => 'created',
					'notes' => '',
				]);
			} else {
				// prepare attachment file.
				$theFile = $request->file('upload-selector.'.$idx);
				$downloadable = 0;
				if ($theFile) {
					$originalName = $theFile->getClientOriginalName();
					$fileSize = $theFile->getSize();
					$fileMime = $theFile->getMimeType();
					$hashValue = md5(date('YmdHis').$originalName);

					// copy to storage location
					//$theFile->move(Storage::getDriver()->getAdapter()->getPathPrefix(), $hashValue);
					Storage::disk('s3')->put($hashValue, file_get_contents($theFile->getRealPath()), 'public');

					$downloadable = Downloadable::create([
						'uploader_id' => auth()->user()->id,
						'title' => '',
						'description' => 'expense attachment uploaded by ' . auth()->user()->name . ' at ' . date('Y-m-d H:i:s'),
						'original_name' => $originalName,
						'file_size' => $fileSize,
						'mime_type' => $fileMime,
						'hash' => $hashValue,
						'valid' => 1,
					]);
				}

				$detail = ExpenseDetail::find($request->input('line')[$idx]);
				$detailInputArray = [
					'expense_header_id' => $this->id,
					'unique_tradable_id' => $product_id,
					'unit_price' => $request->input('unitprice')[$idx],
					'quantity' => $request->input('quantity')[$idx],
					'subtotal' => $request->input('unitprice')[$idx]*$request->input('quantity')[$idx],
					'incur_date' => DateHelper::guiToDbDate($request->input('incurdate')[$idx]),
					'notes' => $request->input('description')[$idx],
				];
				if ($downloadable) {
					$detailInputArray['attachment_id'] = $downloadable->id;
				}
				$detail->update($detailInputArray);

				ExpenseHistory::create([
					'src' => 'expense_details',
					'src_id' => $detail->id,
					'staff_id' => auth()->user()->id,
					'machine' => $request->ip(),
					'process_status' => 'updated',
					'notes' => '',
				]);
			}
		}

		event(new \App\Events\ExpenseUpsertEvent($this));

		return $this;
	}

	public function retract($request)
	{
		$this->update([
			'status' => 'cancelled',
		]);

		ExpenseHistory::create([
			'src' => 'expense_headers',
			'src_id' => $this->id,
			'staff_id' => auth()->user()->id,
			'machine' => $request->ip(),
			'process_status' => 'cancelled',
			'notes' => '',
		]);

		event(new \App\Events\ExpenseUpsertEvent($this));

		return $this;
	}

	public function submit($request)
	{
		$this->update([
			'status' => 'under review',
		]);

		ExpenseHistory::create([
			'src' => 'expense_headers',
			'src_id' => $this->id,
			'staff_id' => auth()->user()->id,
			'machine' => $request->ip(),
			'process_status' => 'updated',
			'notes' => 'submitted for approval',
		]);

		event(new \App\Events\ExpenseUpsertEvent($this));

		return $this;
	}

	public function requireApproval($userId = null)
	{
		/*
		$approvable = DB::select("select expense_headers.id, (select sum(quantity*unit_price) from expense_details where expense_details.expense_header_id = expense_headers.id) > expense_approval_rules.threshold as overage from expense_headers, taxable_entities, expense_approval_rules where expense_headers.status='under review' and expense_headers.entity_id=taxable_entities.id and approver_id not in (select distinct staff_id from expense_histories where src='expense_headers' and src_id = expense_headers.id and (find_in_set('approved', process_status) or find_in_set('rejected', process_status))) and (expense_headers.entity_id = expense_approval_rules.src_entity_id or expense_approval_rules.src_entity_id = 0) and expense_approval_rules.approver_id=" . $userId . " and expense_approval_rules.valid and expense_headers.id=" . $this->id . " having overage > 0");
		*/
		if ($userId) {
			$result = DB::select("select " . $userId . " in (select approver_id from expense_approval_rules where valid and (src_entity_id = " . $this->entity_id . " or src_entity_id = 0) and ((select sum(subtotal) from expense_details, expense_headers where expense_details.expense_header_id = expense_headers.id and expense_headers.id = " . $this->id .") > threshold)) as required");
			$required = $result[0]->required;
			$result = DB::select("select " . $userId . " not in (select staff_id from expense_histories where src='expense_headers' and src_id=" . $this->id . " and process_status='approved') as absent");
			$absent = $result[0]->absent;
			return $required && $absent;
		}
		// if approver is not specified. Check all
		$allApprovers = array_map(function ($value) { return $value->approver_id; }, DB::select("select distinct approver_id from expense_approval_rules where valid and (src_entity_id = " . $this->entity_id . " or src_entity_id = 0) and ((select sum(subtotal) from expense_details, expense_headers where expense_details.expense_header_id = expense_headers.id and expense_headers.id = " . $this->id . ") > threshold)"));
		$doneApprovers = array_map(function ($value) { return $value->staff_id; }, DB::select("select distinct staff_id from expense_histories where src='expense_headers' and src_id=" . $this->id . " and process_status in ('approved', 'rejected')"));
		return count(array_diff($allApprovers, $doneApprovers));
	}

	public function sendEmailRequestApproval()
	{
		// send email request to all approver.
		$allApprovers = array_map(function ($value) { return $value->approver_id; }, DB::select("select distinct approver_id from expense_approval_rules where valid and (src_entity_id = " . $this->entity_id . " or src_entity_id = 0) and ((select sum(subtotal) from expense_details, expense_headers where expense_details.expense_header_id = expense_headers.id and expense_headers.id = " . $this->id . ") > threshold)"));
		$doneApprovers = array_map(function ($value) { return $value->staff_id; }, DB::select("select distinct staff_id from expense_histories where src='expense_headers' and src_id=" . $this->id . " and process_status in ('approved', 'rejected')"));
		$approvers = User::findMany(array_diff($allApprovers, $doneApprovers));

		try {
			Mail::send('email_templates.expense_approval', ['expense' => $this], function ($m) use ($approvers) {
					$m->subject('Expense Approval Request');
					$m->from(config("mail.from.address"), config("mail.from.name"));
					foreach ($approvers as $approver) {
						$m->to($approver->email, $approver->name);
					}
				});
		} catch (\Exception $e) {
			// what to do in case of email error!?
		}
	}

	public function isUnderReview()
	{
		return in_array($this->status, ['under review']);
	}

	public function isSubmitted()
	{
		return in_array($this->status, ['under review', 'approved', 'rejected', 'void', 'paid']);
	}

	public function isApproved()
	{
		return in_array($this->status, ['approved', 'paid']);
	}

	public function approve($approverId, $ipAddress='127.0.0.1', $notes='')
	{
		ExpenseHistory::create([
			'src' => 'expense_headers',
			'src_id' => $this->id,
			'staff_id' => $approverId,
			'machine' => $ipAddress,
			'process_status' => 'approved',
			'notes' => $notes,
		]);

		if ((!$this->requireApproval()) && ($this->status == 'under review')) {
			$this->status = 'approved';
			$this->save();
		}

		event(new \App\Events\ExpenseUpsertEvent($this));
	}

	public function autoApprove(Request $request)
	{
		$this->update([
			'status' => 'approved',
		]);
		ExpenseHistory::create([
			'src' => 'expense_headers',
			'src_id' => $this->id,
			'staff_id' => TaxableEntity::theCompany()->contact[0]->id,
			'machine' => $request->ip(),
			'process_status' => 'approved',
			'notes' => 'auto-approved',
		]);
		event(new \App\Events\ExpenseUpsertEvent($this));

		$this->convertToPayable($request->ip());
	}

	public function disapprove($approverId, $ipAddress='127.0.0.1', $notes='')
	{
		ExpenseHistory::create([
			'src' => 'expense_headers',
			'src_id' => $this->id,
			'staff_id' => $approverId,
			'machine' => $ipAddress,
			'process_status' => 'rejected',
			'notes' => $notes,
		]);

		if ((!$this->requireApproval()) && ($this->status == 'under review')) {
			$this->status = 'rejected';
			$this->save();
		}

		event(new \App\Events\ExpenseUpsertEvent($this));
	}

	public function currency()
	{
		return $this->belongsTo('\App\Currency', 'currency_id');
	}

	public function convertToPayable($ipAddress)
	{
		$title = ParameterHelper::getNextSequence('transaction_number');

		// expense from customer, use C as prefix
		if ($this->entity->type == 'customer') {
			$title = 'C' . $title;
		}

		$entity = $this->entity;
		$result = DB::select("select adddate('" . $this->booking_date . "', interval (select grace_days from payment_terms where id = " . $entity->payment_term_id . ") day) as 'dueDate'");
		$dueDate = $result[0]->dueDate;
		$billingAddress = 0;
		$shippingAddress = 0;
		foreach ($this->entity->address()->where('is_default', 1)->get() as $oneAddress) {
			switch ($oneAddress->purpose) {
			case 'billing':
				$billingAddress = $oneAddress->id;
				break;
			case 'shipping':
				$shippingAddress = $oneAddress->id;
				break;
			default:
				break;
			}
		}
		$result = DB::select("select sum(subtotal) as total from expense_details where expense_header_id = " . $this->id);
		$amount = $result[0]->total;
		$contact_id = $this->entity->contact->max('id');
		$header = TransactableHeader::create([
						'title' => $title,
						'src_table' => 'expense_headers',
						'src_id' => $this->id,
						'flags' => '',
						'reference' => $this->reference,
						'entity_id' => $this->entity_id,
						'contact_id' => $contact_id,
						'staff_id' => $this->staff_id,
						'status' => 'open',
						'balance' => $amount,
						'billing_address_id' => $billingAddress,
						'shipping_address_id' => $shippingAddress,
						'payment_term_id' => $entity->payment_term_id,
						'incur_date' => $this->booking_date,
						'approx_due_date' => $dueDate,
						'tax_rate' => 0,
						'currency_id' => $this->currency_id,
						'notes' => $this->notes,
						'internal_notes' => '',
					]);

		TransactableHistory::create([
						'src' => 'transactable_headers',
						'src_id' => $header->id,
						'amount' => $amount,
						'staff_id' => auth()->user()->id,
						'machine' => $ipAddress,
						'process_status' => 'created',
						'notes' => '',
					]);

		// convert to transactable detail
		foreach ($this->detail as $expenseDetailObj) {
			$detail = TransactableDetail::create([
								'transactable_header_id' => $header->id,
								'src_table' => 'expense_details',
								'src_id' => $expenseDetailObj->id,
								'unique_tradable_id' => $expenseDetailObj->unique_tradable_id,
								'display_as' => $expenseDetailObj->uniqueTradable->sku,
								'description' => $expenseDetailObj->notes,
								'unit_price' => $expenseDetailObj->unit_price,
								'discount' => 0,
								'discount_type' => 'amount',
								'transacted_quantity' => $expenseDetailObj->quantity,
								'transacted_amount' => $expenseDetailObj->subtotal,
								'discount_amount' => 0,
								'tax_amount' => 0,
								'status' => 'valid',
							]);
			TransactableHistory::create([
								'src' => 'transactable_details',
								'src_id' => $detail->id,
								'amount' => $expenseDetailObj->subtotal,
								'staff_id' => auth()->user()->id,
								'machine' => $ipAddress,
								'process_status' => 'created',
								'notes' => '',
							]);

			if ($expenseDetailObj->uniqueTradable->is_stockable) {
				$costOfGoodSold = $expenseDetailObj->uniqueTradable->getUnitCost($expenseDetailObj->quantity, Location::where('entity_id', TaxableEntity::theCompany()->id)->first()->id, TaxableEntity::theCompany()->id);
				// adjust inventory
				$transaction = TradableTransaction::create([
					'unique_tradable_id' => $detail->unique_tradable_id,
					'location_id' => Location::where('entity_id', TaxableEntity::theCompany()->id)->first()->id,
					'owner_entity_id' => TaxableEntity::theCompany()->id,
					'quantity' => - $expenseDetailObj->quantity,
					'unit_cost' => $costOfGoodSold,
					'src_table' => 'transactable_details',
					'src_id' => $detail->id,
					'valid' => 1,
					'notes' => '',
					'created_at' => $expenseDetailObj->incur_date . ' 00:00:00',
				]);
				event(new \App\Events\InventoryUpdateEvent($transaction));
				// record cost of good sold
				TaccountTransaction::create([
					'debit_t_account_id' => Location::where('entity_id', TaxableEntity::theCompany()->id)->first()->inventory_t_account_id,
					'credit_t_account_id' => $expenseDetailObj->uniqueTradable->cogs_t_account_id,
					'amount' =>  $costOfGoodSold * $detail->transacted_quantity,
					'currency_id' => $expenseHeaderObj->currency_id,
					'book_date' => $expenseDetailObj->incur_date,
					'src' => 'transactable_details',
					'src_id' => $detail->id,
					'valid' => 1,
					'reconciled' => 0,
					'notes' => '',
				]);
			}

			// create AP entry
			TaccountTransaction::create([
				'debit_t_account_id' => $expenseDetailObj->uniqueTradable->expense_t_account_id,
				'credit_t_account_id' => TaxableEntity::find($header->entity_id)->transaction_t_account_id,
				'amount' =>  $detail->transacted_amount,
				'currency_id' => $this->currency_id,
				'book_date' => $expenseDetailObj->incur_date,
				'src' => 'transactable_details',
				'src_id' => $detail->id,
				'valid' => 1,
				'reconciled' => 0,
				'notes' => '',
			]);
		}

		event(new \App\Events\TransactableUpsertEvent($header));

		return $header;
	}

	public function history()
	{
		return $this->hasMany('App\ExpenseHistory', 'src_id' )->where('src', 'expense_headers');
	}

	// If new keyword is added, make sure generateSearchTips() is updated as well
	public function generateSearchAttribute()
	{
		$result = [];

		array_push($result, ($this->status == 'paid') ? 'paid' : 'open');
		if (substr($this->booking_date, 0, 4) == date("Y")) {
			array_push($result, 'thisyear');
		}
		if (substr($this->booking_date, 0, 7) == date("Y-m")) {
			array_push($result, 'thismonth');
		}
		if (substr($this->booking_date, 0, 7) == date("Y-m", strtotime("-1 month"))) {
			array_push($result, 'lastmonth');
		}

		return $result;
	}

	// If new keyword is added, make sure generateSearchAttribute() is updated as well
	public static function generateSearchTips($delimiter)
	{
		return implode($delimiter, [
				str_pad('open', 15) . trans('tool.Search open expense'),
				str_pad('paid', 15) . trans('tool.Search expense paid'),
				str_pad('thisyear', 15) . trans('tool.Search from this year'),
				str_pad('thismonth', 15) . trans('tool.Search from this month'),
				str_pad('lastmonth', 15) . trans('tool.Search from last month'),
			]);
	}
}
