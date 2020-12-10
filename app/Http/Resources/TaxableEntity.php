<?php

namespace App\Http\Resources;

use Auth;
use Illuminate\Http\Resources\Json\Resource;

class TaxableEntity extends Resource
{
  const TYPE_TO_INITIAL = [
    'supplier' => 'supplier',
    'customer' => 'customer',
    'employee' => 'user',
  ];

  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request)
  {
    $canTransact = [
      'supplier' => Auth::user()->can('ap-process') || Auth::user()->can('rap-process'),
      'customer' => Auth::user()->can('ar-process') || Auth::user()->can('rar-process'),
      'employee' => Auth::user()->can('ap-process') || Auth::user()->can('rap-process'),
    ];

    return [
      'id' => $this->id,
      'type' => $this->type,
      'name' => $this->name,
      'is_active' => $this->active,
      'code' => $this->code,
      'contact' => $this->contact->last()->name,
      'region' => '---',
      'outstanding' => $this->outstandingOrder->count(),
      'outstanding_transactable' => $this->outStandingTransactable->count(),
      'search-key' => implode(" ", $this->generateSearchAttribute()),
      'can_view' => Auth::user()->can(self::TYPE_TO_INITIAL[$this->type] . '-edit'),
      'can_edit' => Auth::user()->can(self::TYPE_TO_INITIAL[$this->type] . '-edit'),
      'can_transact' => $canTransact[$this->type],
    ];
  }
}
