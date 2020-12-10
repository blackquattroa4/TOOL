<?php

namespace App\Jobs;

use ACH;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

class AchReceiptRetrieval implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  /**
   *  input request passed into constructor
   **/
  protected $charge_id;

  protected $transactable_id;

  protected $agreement_document_id;

  protected $document_permission;

  public function __construct($charge_id, $transactable_id, $agreement_document_id, $document_permission)
  {
      $this->charge_id = $charge_id;
      $this->transactable_id = $transactable_id;
      $this->agreement_document_id = $agreement_document_id;
      $this->document_permission = $document_permission;
  }

  /**
   * Handle post-ACH-transaction
   *
   * @param  mixed  $order
   * @return void
   */
  public function handle()
  {
    ACH::fundTransferCallback([
      'charge_id' => $this->charge_id,
      'transactable' => \App\TransactableHeader::find($this->transactable_id),
      'agreement' => \App\Document::find($this->agreement_document_id),
      'permission' => $this->document_permission
    ]);
  }

}
