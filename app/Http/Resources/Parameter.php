<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Parameter extends Resource
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
      'key' => $this->key,
      'value' => is_array(unserialize($this->value)) ? "array..." : unserialize($this->value),
      'can_edit' =>  auth()->user()->can('sy-edit'),
    ];
  }
}
