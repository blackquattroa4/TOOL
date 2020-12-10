<?php

namespace App\Http\Resources;

use App\Helpers\DateHelper;
use Illuminate\Http\Resources\Json\Resource;

class Email extends Resource
{
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request)
  {
    $fromEntity = [];
    preg_match("/^(([^\<\>]+)|([^\<]*)(<)([^\>]+)(>))$/i", $this->from, $fromEntity);

    return [
      'id' => $this->id,
      'date' => $this->sent_at,
      'date_display' => DateHelper::dbToGuiDate(date("Y-m-d", strtotime($this->sent_at))) . " " . date("g:iA", strtotime($this->sent_at)),
      'subject' => $this->subject,
      'is_deleted' => $this->deleted,
      'is_answered' => $this->answered,
      'is_flagged' => $this->flagged,
      'is_unseen' => !$this->seen,
      'is_recent' => $this->recent,
      'from_name' => (count($fromEntity) == 7) ? $fromEntity[3] : explode("@", $fromEntity[2])[0],
      'from_email' => (count($fromEntity) == 7) ? $fromEntity[5] : $fromEntity[2],
    ];
  }
}
