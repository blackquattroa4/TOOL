<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Tradable extends Resource
{
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request)
  {

    return [
      'id' => $this->id,
      'sku' => $this->uniqueTradable->sku,
      'is_current' => $this->current,
      'is_stockable' => $this->uniqueTradable->stockable,
      'is_expendable' => $this->uniqueTradable->expendable,
      'product_id' => $this->uniqueTradable->product_id,
      'description' => $this->uniqueTradable->description,
      'supplier' => $this->supplier->code,
      'search-key' => implode(" ", $this->generateSearchAttribute()),
      'can_view' => auth()->user()->can('pd-view'),
      'can_edit' => auth()->user()->can('pd-edit'),
    ];
  }
}
