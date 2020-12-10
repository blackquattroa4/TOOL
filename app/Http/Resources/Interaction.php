<?php

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use Illuminate\Http\Resources\Json\Resource;

class Interaction extends Resource
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
      'type' => $this->type,
      'description' => $this->description,
      'status' => $this->status,
      'create_date' => explode(" ", $this->created_at)[0],
      'create_date_display' => DateHelper::dbToGuiDate(explode(" ", $this->created_at)[0]),
      'update_date' => explode(" ", $this->updated_at)[0],
      'update_date_display' => DateHelper::dbToGuiDate(explode(" ", $this->updated_at)[0]),
      'search-key' => implode(" ", $this->generateSearchAttribute()),
      'can_view' => in_array(auth()->user()->id, $this->users->pluck('id')->toArray()),  // viewable to relevant user
      'can_update' => in_array(auth()->user()->entity->type, [ 'self', 'employee' ]),  // updatable to staff
    ];
  }
}
