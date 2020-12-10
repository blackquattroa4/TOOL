<?php

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use Illuminate\Http\Resources\Json\Resource;

class WarehouseHeader extends Resource
{
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request)
  {
    $bins = $this->bins->pluck('name')->toArray();

    return [
      'id' => $this->id,
      'type' => $this->type,
      'title' => $this->title,
      'is_assigned' => ($this->type != 'receive'),  // :TODO: tempoarary; designed for external 3PL to indicate if booking is assigned.
      'is_released' => optional($this->source)->released, // if source does not have 'released' field, null will be returned
      'is_prepared' => ($this->balanceCount() == 0),
      'is_delivered' => in_array($this->type, ['receive', 'deliver']) ? ($this->status == 'closed') : false,
      'is_transferred' => in_array($this->type, ['relocate', 'transfer']) ? ($this->status == 'closed') : false,
      'is_open' => $this->status == 'open',
      'is_closed' => $this->status == 'closed',
      'is_void' => $this->status == 'void',
      'source' => optional($this->source)->title ?: '',
      'reference' => $this->reference,
      'entity' => $this->externalEntity->code,
      'operator' => $this->location->owner->code,
      'delivery_date' => $this->order_date,
      'delivery_date_display' => DateHelper::dbToGuiDate($this->order_date),
      'items' => $this->quantityCount(),
      'balance' => $this->balanceCount(),
      'bins' => $bins,
      'bins_string' => implode(", ", $bins),
      'search-key' => implode(" ", $this->generateSearchAttribute()),
      'can_view' => auth()->user()->can('wo-view'),
      'can_edit' => auth()->user()->can('wo-process') && $this->isOpen(),
      'can_void' => auth()->user()->can('wo-process') && ($this->status != 'void'),
    ];
  }
}
