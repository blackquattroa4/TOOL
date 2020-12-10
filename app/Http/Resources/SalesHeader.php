<?php

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use Auth;
use Illuminate\Http\Resources\Json\Resource;

class SalesHeader extends Resource
{
  const TYPE_TO_INITIAL = [
    'quote' => 'sq',
    'order' => 'so',
    'return' => 'sr',
  ];

  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request)
  {
    $user = Auth::user();
    $details = $this->salesDetail;
    $fmtr = new \NumberFormatter($this->currency->regex, \NumberFormatter::CURRENCY);
    $deliveryDate = $details->max('delivery');
    $balance = $details->sum(function ($item) { return ($item->ordered_quantity - $item->shipped_quantity) * (($item->discount_type == 'amount') ? ($item->unit_price - $item->discount) : ($item->unit_price * (100 - $item->discount) / 100)) * ($item->taxable ? ((100 + $this->tax_rate) / 100) : 1); });
    $total = $details->sum(function ($item) { return $item->ordered_quantity * (($item->discount_type == 'amount') ? ($item->unit_price - $item->discount) : ($item->unit_price * (100 - $item->discount) / 100)) * ($item->taxable ? ((100 + $this->tax_rate) / 100) : 1); });

    return [
      'id' => $this->id,
      'increment' => $this->title,
      'reserved_receivable_title' => $this->reserved_receivable_title,
      'type' => $this->type,
      'is_valid' => $details->min('delivery_date') <= date("Y-m-d"),
      'is_approved' => $this->approved,
      'is_prepared' => (optional(optional($this->warehouseOrder)->detail)->sum('processed_quantity') > 0) ?: 0,
      'is_shipped' => (optional($this->warehouseOrder)->status == 'closed') ?: 0,
      'is_closed' => $this->status == 'closed',
      'customer' => $this->entity->code,
      'input_date' => $this->order_date,
      'input_date_display' => DateHelper::dbToGuiDate($this->order_date),
      'delivery_date' => $deliveryDate,
      'delivery_date_display' => DateHelper::dbToGuiDate($deliveryDate),
      'contact' => $this->contact->name,
      'staff' => $this->sales->name,
      'items' => $details->count(),
      'balance' => $fmtr->format($balance),
      'percent' => sprintf("%3.0f", ($total > 0) ? ($balance * 100 / $total) : 0) . '%',
      'total' => $fmtr->format($total),
      'search_key' => implode(" ", $this->generateSearchAttribute()),
      'can_view' => $user->can(self::TYPE_TO_INITIAL[$this->type].'-view'),
      'can_edit' => ($this->status == 'open') && $user->can(self::TYPE_TO_INITIAL[$this->type].'-edit'),
      'can_reserve' => empty($this->reserved_receivable_title) && ($this->status == 'open') && $user->can(self::TYPE_TO_INITIAL[$this->type].'-process'),
      'need_approve' => $this->requireApproval($user->id),
    ];
  }
}
