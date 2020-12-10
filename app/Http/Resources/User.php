<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class User extends Resource
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
      'email' => $this->email,
      'phone' => $this->phone,
      'code' => $this->entity->code,
      'search-key' => implode(" ", $this->entity->generateSearchAttribute()),
      'can_view' =>  auth()->user()->can('hr-view'),
      'can_edit' =>  auth()->user()->can('hr-edit'),
    ];
  }
}
