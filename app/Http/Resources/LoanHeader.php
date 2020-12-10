<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class LoanHeader extends Resource
{
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request)
  {
    $currencyFormat = $this->currency->getFormat();
    $fmtr = new \NumberFormatter( $currencyFormat['regex'], \NumberFormatter::CURRENCY);
    $balance = $this->getBalanceAsOf();
    $can_process = (($this->role == 'lender') && ($balance > 0) && (auth()->user()->can('ar-process') || auth()->user()->can('rap-process'))) || (($this->role == 'borrower') && ($balance > 0) && (auth()->user()->can('ap-process') || auth()->user()->can('rar-process')));

    return [
      'id' => $this->id,
      'title' => $this->title,
      'role' => $this->role,
      'bad_debt' => $this->isBadDebt(),
      'status' => ($balance > 0) ? 'open' : 'closed',
      'entity' => $this->entity->name,
      'principal' => $fmtr->format($this->principal),
      'apr' => $this->annual_percent_rate . ' %',
      'balance' => $fmtr->format($balance),
      'search-key' => implode(" ", $this->generateSearchAttribute()),
      'can_view' => (($this->role == 'lender') && auth()->user()->can('ar-view')) || (($this->role == 'borrower') && auth()->user()->can('ap-view')),
      'can_edit' => (($this->role == 'lender') && ($balance > 0) && (auth()->user()->can('ar-edit') || auth()->user()->can('rap-edit'))) ||
        (($this->role == 'borrower') && ($balance > 0) && (auth()->user()->can('ap-edit') || auth()->user()->can('rar-edit'))),
      'can_process' => $can_process,
      'can_be_bad_debt' => ($balance > 0) && $can_process,
    ];
  }
}
