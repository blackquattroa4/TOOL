<?php

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use Auth;
use Illuminate\Http\Resources\Json\Resource;

class PurchaseHeader extends Resource
{
  const TYPE_TO_INITIAL = [
    'quote' => 'pq',
    'order' => 'po',
    'return' => 'pr',
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
    $details = $this->purchaseDetail;
    $fmtr = new \NumberFormatter($this->currency->regex, \NumberFormatter::CURRENCY);

    return [
      'id' => $this->id,
      'type' => $this->type,
      'title' => $this->title,
      'is_valid' => date("Y-m-d") <= $details->min('delivery_date'),
      'is_approved' => $this->approved,
      'is_released' => $this->released,
      'is_prepared' => false,
      'is_shipped' => false,
      'is_closed' => $this->status == 'closed',
      'is_void' => $this->status == 'void',
      'supplier' => $this->entity->code,
      'contact' => $this->contact->name,
      'delivery_date' => $details->min('delivery_date'),
      'delivery_date_display' => DateHelper::dbToGuiDate($details->min('delivery_date')),
      'items' => $details->count(),
      'staff' => $this->staff->name,
      'balance' => $fmtr->format($details->sum(function ($item) { return ($item->ordered_quantity * $item->unit_price) - $item->shipped_amount; })),
      'percent' => sprintf("%0.1f", ($details->sum('ordered_quantity') ? $details->sum('shipped_quantity') * 100 / $details->sum('ordered_quantity') : 0)) . '%',
      'search-key' => implode(" ", $this->generateSearchAttribute()),
      'can_view' => $user->can(self::TYPE_TO_INITIAL[$this->type].'-view'),
      'can_edit' => ($this->status == 'open') && $user->can(self::TYPE_TO_INITIAL[$this->type].'-edit'),
      'need_approve' => $this->requireApproval($user->id),
      'can_release' => !$this->requireApproval() && in_array($this->status, ['open']) && $user->can(self::TYPE_TO_INITIAL[$this->type].'-process'),
    ];
  }
}
