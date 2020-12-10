<?php

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use Illuminate\Http\Resources\Json\Resource;

class TradableNotice extends Resource
{
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request)
  {
    $date = date("Y-m-d", strtotime($this->created_at));
    // $skus = implode(" ", $this->relatedProducts()->map(function ($tradable) { return "<span class=\"label label-info\">" . $tradable->uniqueTradable->sku . "</span>"; })->toArray());
    $skus = implode(" ", $this->relatedProducts()->map(function ($tradable) { return $tradable->uniqueTradable->sku; })->toArray());

    return [
      'id' => $this->id,
      'summary' => $this->summary,
      'date' => $date,
      'date_display' => DateHelper::dbToGuiDate($date),
      'staff' => $this->document->creator->name,
      'document_id' => $this->document_id,
      'skus' => $skus,
      'search-key' => implode(" ", array_column($this->document->keywords->toArray(), "word")),
      'can_view' => $this->document->canView(auth()->user()),
      'can_edit' => $this->document->canUpdate(auth()->user()),
    ];
  }
}
