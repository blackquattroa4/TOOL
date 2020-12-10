<?php

namespace App\Jobs;

use App\SalesHeader;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class EcommerceOrderSync implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  /**
   *  input request passed into constructor
   **/
  protected $request;

  public function __construct(Request $request)
  {
      $this->request = $request;
  }

  /**
   * Handle order import
   *
   * @param  mixed  $order
   * @return void
   */
  public function handle()
  {
    $salesHeaderObj = SalesHeader::where('reference', $this->request->input('reference'))->first();

    if (!$salesHeaderObj) {
      $salesHeaderObj = SalesHeader::initialize('order', $this->request);
      $salesHeaderObj->update([
          'external_source' => $this->request->input('external_source'),
    			'notes' => $this->request->input('notes'),
        ]);
    }

    // if a receivable should be created, then create invoice, receive payment, and apply payment to invoice
    if ($this->request->input('create_invoice')) {
      $transactableHeaderObj = TransactableHeader::where('reference', $this->request->input('reference'))->first();

      if (!$transactableHeaderObj) {
        $request = new \Illuminate\Http\Request();
        $request->server->set('REMOTE_ADDR', '127.0.0.1');
        $request->merge([
          'expiration' => date('m/d/Y'),
          'line' => $salesHeaderObj->salesDetail->pluck('id')->toArray(),
          'processing' => $salesHeaderObj->salesDetail->pluck('ordered_quantity')->toArray(),
        ]);
        $transactableHeaderObj = $salesHeaderObj->createReceivable($request);
        // :TODO: receive/apply payment
      }

      // order is fulfilled, mark corresponding warehouse order complete
      if ($this->request->input('order_fulfilled')) {
        $warehouseOrder = WarehouseHeader::where('src', $src_table)->where('src_id', $order['id'])->first();
        if ($warehouseOrder->status == 'open') {
          // :TODO: process warehouseOrder and close
        }
      }

    }
    // :TODO: if anything failed, re-shove to queue for re-try.  Make notes & number of attempts(?) on order history
  }

}
