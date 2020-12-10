<?php

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use Illuminate\Http\Resources\Json\Resource;

class ExpenseHeader extends Resource
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
      'title' => $this->title,
      'incur_date' => $this->booking_date,
      'incur_date_display' => DateHelper::dbToGuiDate($this->booking_date),
      'items' => sprintf(env('APP_QUANTITY_FORMAT'), $this->detail()->sum('quantity')),
      'total' => $fmtr->getTextAttribute(\NumberFormatter::CURRENCY_CODE) . " " . $fmtr->format($this->detail()->sum('subtotal')),
      'status' => $this->status,
      'is_approved' => in_array($this->status, ['approved','void','paid']),
      'is_valid' => !in_array($this->status, ['cancel']),
      'entity' => $this->entity->code,
      'can_view' => auth()->user()->can('ex-list'),
      'can_edit' => auth()->user()->can('ex-edit') && ($this->status == 'un-submitted'),
      'can_submit' => (auth()->user()->can('ex-create') || auth()->user()->can('ex-edit')) && ($this->status == 'un-submitted'),
      'can_retract' => (in_array($this->status, ['un-submitted','under review'])) && (auth()->user()->can('ex-create') || auth()->user()->can('ex-edit')),
      'can_approve' => ($this->status == 'under review') && $this->requireApproval(auth()->user()->id),
      'search-key' => implode(" ", $this->generateSearchAttribute())
    ];
  }
}
