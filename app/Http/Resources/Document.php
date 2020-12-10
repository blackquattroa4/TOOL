<?php

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use Illuminate\Http\Resources\Json\Resource;

class Document extends Resource
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
      'title' => $this->title,
      'version' => $this->version,
      'description' => $this->notes,
      'create_date' => explode(" ", $this->created_at)[0],
      'create_date_display' => DateHelper::dbToGuiDate(explode(" ", $this->created_at)[0]),
      'search-key' => implode(" ", array_column($this->keywords->toArray(), "word")),
      'can_view' => $this->canView(auth()->user()),
      'can_update' => $this->canUpdate(auth()->user()),
      'can_delete' => $this->canDelete(auth()->user()),
    ];
  }
}
