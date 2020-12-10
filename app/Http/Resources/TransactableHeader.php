<?php

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use Illuminate\Http\Resources\Json\Resource;

class TransactableHeader extends Resource
{
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request)
  {
    $fmtr = new \NumberFormatter($this->currency->regex, \NumberFormatter::CURRENCY);

    return [
      'id' => $this->id,
      'type' => $this->isReceivable() ? 'receivable' : 'payable',
      'title' => $this->title,
      'title_numeral' => preg_replace('/^c/i', '', $this->title),
      'is_closed' => $this->status == 'closed',
      'is_void' => $this->status == 'void',
      'is_credit' => $this->isCredit(),
      'entity' => $this->entity->code,
      'incur_date' => $this->incur_date,
      'incur_date_display' => DateHelper::dbToGuiDate($this->incur_date),
      'due_date' => $this->approx_due_date,
      'due_date_display' => DateHelper::dbToGuiDate($this->approx_due_date),
      'is_pastdue' => ($this->status == 'open') && ($this->approx_due_date < date("Y-m-d")),
      'total' =>  $fmtr->format($this->totalAmount()),
      'balance' =>  $fmtr->format($this->balance),
      'search-key' => $this->generateSearchAttribute(),
      'can_view' => $this->isReceivable()
              ? ($this->isReceivableInvoice() ? auth()->user()->can('ar-view') : auth()->user()->can('rar-view'))
              : ($this->isPayableInvoice() ? auth()->user()->can('ap-view') : auth()->user()->can('rap-view')),
      'can_void' => ($this->status == 'open') &&
              ($this->isReceivable()
                ? (in_array($this->src_table, ['sales_headers']) && ($this->isInvoice() ? auth()->user()->can('ar-process') : auth()->user()->can('rar-process')))
                : (in_array($this->src_table, ['purchase_headers', 'expense_headers']) && ($this->isInvoice() ? auth()->user()->can('ap-process') : auth()->user()->can('rap-process')))),
    ];
  }
}
