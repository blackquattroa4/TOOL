<?php

namespace App\Http\Resources;

use App\ExpenseDetail;
use App\Helpers\DateHelper;
use Illuminate\Http\Resources\Json\Resource;

class RecurringExpense extends Resource
{
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request)
  {
    $lastExpense = ExpenseDetail::select('expense_details.*')
              ->join('expense_headers', 'expense_details.expense_header_id', '=', 'expense_headers.id')
              ->join('taxable_entities', 'taxable_entities.id', '=', 'expense_headers.entity_id')
              ->where('expense_headers.entity_id', $this->entity_id)
              ->where('expense_details.unique_tradable_id', $this->unique_tradable_id)
              ->orderBy('expense_details.incur_date', 'desc')
              ->first();
    $currencyFormat = $this->entity->currency->getFormat();
    $fmtr = new \NumberFormatter( $currencyFormat['regex'], \NumberFormatter::CURRENCY);
    $upcoming = $lastExpense ? date("Y-m-d", strtotime($lastExpense->incur_date . " +" . $this->frequency_numeral . " " . $this->frequency_unit)) : "---";

    return [
      'id' => $this->id,
      'entity' => $this->entity->code,
      'notes' => $this->notes,
      'frequency_numeral' => $this->frequency_numeral,
      'frequency_unit' => $this->frequency_unit,
      'frequency_display' => sprintf(trans('messages.Every %1$d %2$s'), $this->frequency_numeral, trans('finance.'.$this->frequency_unit)),
      'valid' => $this->valid,
      'last_entered_on' => $lastExpense ? $lastExpense->incur_date : "---",
      'last_entered_on_display' => $lastExpense ? DateHelper::dbToGuiDate($lastExpense->incur_date) : "---",
      'last_entered_amount' => $lastExpense ? $fmtr->format($lastExpense->subtotal) : "---",
      'pastdue' => $upcoming < date("Y-m-d"),
      'upcoming' => $upcoming,
      'upcoming_display' => $lastExpense ? DateHelper::dbToGuiDate(date("Y-m-d", strtotime($lastExpense->incur_date . " +" . $this->frequency_numeral . " " . $this->frequency_unit))) : "---",
      'can_create' => auth()->user()->can('ex-create'),
    ];
  }

}
