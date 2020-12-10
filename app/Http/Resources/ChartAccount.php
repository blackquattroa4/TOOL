<?php

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use DB;
use Illuminate\Http\Resources\Json\Resource;

class ChartAccount extends Resource
{
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request)
  {
    $user = auth()->user();
    $fmtr = new \NumberFormatter($this->currency->regex, \NumberFormatter::CURRENCY);

    $result = DB::select("
        SELECT
          (SELECT
              SUM(IF(debit_t_account_id = " . $this->id .", amount, - amount))
            FROM
              taccount_transactions
            WHERE
              valid = 1 AND reconciled = 1
              AND (debit_t_account_id = " . $this->id . "
                OR credit_t_account_id = " . $this->id . ")) AS balance,
          (SELECT
              SUM(amount)
            FROM
              taccount_transactions
            WHERE
              valid = 1 AND reconciled = 0
              AND debit_t_account_id = " . $this->id . ") AS debit,
          (SELECT
              SUM(amount)
            FROM
              taccount_transactions
            WHERE
              valid = 1 AND reconciled = 0
              AND credit_t_account_id = " . $this->id . ") AS credit")[0];

    return [
      'id' => $this->id,
      'account' => $this->account,
      'type' => $this->type,
      'currency' => $this->currency->symbol,
      'description' => $this->description,
      'balance' => $fmtr->format($result->balance),
      'debit' => $fmtr->format($result->debit),
      'credit' => $fmtr->format($result->credit),
      'active' => $this->active,
      'can_view' => $user->can('ar-list') && $user->can('rar-list') && $user->can('ap-list') && $user->can('rap-list'),
      'can_edit' => auth()->user()->can('acct-edit'),
      'can_reconcile' => $user->can('ar-process') && $user->can('rar-process') && $user->can('ap-process') && $user->can('rap-process') && ($result->debit || $result->credit),
      'can_ar_process' => $user->can('ar-process'),
    ];
  }
}
