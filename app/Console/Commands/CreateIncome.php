<?php

namespace App\Console\Commands;

use App\Helpers\DataSetHelper;
use App\SalesHeader;
use App\SalesHistory;
use App\TaxableEntity;
use App\WarehouseHeader;
use DB;
use Illuminate\Console\Command;

//***** sample input JSON text *****
// {
// 	"type" : "order",
// 	"customer" : 7,
// 	"contact" : 7,
// 	"payment" : 2,
// 	"reference" : "N/A",
// 	"staff" : 2,
// 	"billing" : 13,
// 	"shipping" : 14,
// 	"incoterm" : "N/A",
// 	"via" : "N/A",
// 	"tax_rate" : 0,
// 	"currency" : 1,
// 	"product" : [
// 		6
// 	],
// 	"display" : [
// 		"ENUUH-354:US"
// 	],
// 	"description" : [
// 		"USB 3.0 4-port hub"
// 	],
// 	"unitprice" : [
// 		11.50
// 	],
// 	"quantity" : [
// 		2
// 	],
// 	"discount" : [
// 		0
// 	],
// 	"disctype" : [
// 		"amount"
// 	],
// 	"taxable" : [
// 		0
// 	]
// }
//***** end of text *****

// create one instance of an income
class CreateIncome extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'create:sales' .
                      '{--order : create sales order}' .
                      '{--return : create sales order}' .
                      '{--invoice=output.pdf : download generated invoice and save as output.pdf}' .
                      '{file : file to be processed}'
                      ;

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'create sales order/return';

  public function __construct()
  {
      parent::__construct();
  }

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    // check exactly one option is sepcified.
    if (empty($this->option('order')) && empty($this->option('return'))) {
      $this->error('must specify either order or return');
      return;
    }

    if ($this->option('order') && $this->option('return')) {
      $this->error('must specify only order or return; not both');
      return;
    }

    // check for valid file input.
    if (!$this->argument('file')) {
      $this->error('must specify input JSON file');
      return;
    } else {
      if (file_exists($this->argument('file'))) {
        $data = json_decode(file_get_contents($this->argument('file')), true);
        if (empty($data)) {
          $this->error('input file does not contain proper JSON');
          return;
        }
        $data['inputdate'] = date("m/d/Y");
        $data['expiration'] = date("m/d/Y");
      } else {
        $this->error('input file does not exist');
        return;
      }
    }

    try {
      $salesHeaderObj = null;
      $receivableHeaderObj = null;
      DB::transaction(function() use (&$receivableHeaderObj, &$salesHeaderObj, $data) {
        $systemUser = TaxableEntity::theCompany()->contact[0];

        // spoof system as authenticated user
        auth()->login($systemUser);

        // convert $data into proper HTTP request object
        $request = new \Illuminate\Http\Request();
        // spoof IP address
        $request->server->set('REMOTE_ADDR', '127.0.0.1');
    		$request->merge($data);

        if ($this->option('order')) {
          //$this->info('create sales order from ' . $this->argument('file'));
          $salesHeaderObj = SalesHeader::initialize('order', $request);
        } else if ($this->option('return')) {
          //$this->info('create sales return from ' . $this->argument('file'));
          $salesHeaderObj = SalesHeader::initialize('return', $request);
        }

        // approval is triggered upon SalesHeader::initialize

        if (!$salesHeaderObj->requireApproval()) {
          // spoof a request object
          $request = new \Illuminate\Http\Request();
          // spoof IP address
          $request->server->set('REMOTE_ADDR', '127.0.0.1');

          $request->merge([
            'expiration' => date('m/d/Y'),
            'line' => $salesHeaderObj->salesDetail->pluck('id')->toArray(),
            'processing' => $salesHeaderObj->salesDetail->pluck('ordered_quantity')->toArray(),
          ]);
          $receivableHeaderObj = $salesHeaderObj->createReceivable($request);
        }

        auth()->logout();
      });
      if ($receivableHeaderObj) {
        $this->info('Receivable #' . $receivableHeaderObj->title . ' created');
        if ($this->option('invoice')) {
          $pdf = $receivableHeaderObj->generatePdf();
      		DataSetHelper::addDataSetValue($receivableHeaderObj, 'flags', 'printed');
      		$pdf->Output($this->option('invoice'), "F");
          $this->info('Receivable #' . $receivableHeaderObj->title . ' saved as \'' . $this->option('invoice') .'\'');
        }
      } else if ($salesHeaderObj) {
        $this->info('Sales ' . ($this->option('order') ? 'order' : 'return') . ' #' . $salesHeaderObj->title . ' created');
      }
    } catch (\Exception $e) {
      $this->error($e->getMessage());
      $this->info("=== stack ===\n".$e->getTraceAsString());
      auth()->logout();
    }

    return;
  }
}
