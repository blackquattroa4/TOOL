<?php

namespace App\Http\Resources;

use Auth;
use Illuminate\Http\Resources\Json\Resource;

class Role extends Resource
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
      'name' => $this->name,
      'display' => $this->display_name,
      'description' => $this->description,
      'can_view' => Auth::user()->can('role-view'),
      'can_edit' => Auth::user()->can('role-edit'),
    ];
  }
}
