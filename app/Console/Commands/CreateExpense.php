<?php

namespace App\Console\Commands;

use App\ExpenseHeader;
use App\ExpenseHistory;
use App\TaxableEntity;
use DB;
use Illuminate\Console\Command;

//***** sample input JSON text *****
// {
// 	"entity" : 2,
// 	"reference" : "ABCDEF",
// 	"currency" : 1,
// 	"notes" : "SOME TEXT GOES HERE",
// 	"product" : [
// 		3
// 	],
// 	"file" : [
// 		"/home/someone/test doc.pdf"
// 	],
// 	"unitprice" : [
// 		18.25
// 	],
// 	"quantity" : [
// 		1
// 	],
// 	"description" : [
// 		"USB 3.0 card reader"
// 	]
// }
//***** end of text *****

// create one instance of an expense
class CreateExpense extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'create:expense' .
                      '{file : file to be processed}'
                      ;

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'create expense';

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
    // check for valid file input
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
        $data['incurdate'] = array_fill(0, count($data['product']), date("m/d/Y"));

        // detach files from data and prepare to re-attach to request object
        $files = $data['file'];
        unset($data['file']);
        $files = array_map(function($filePath) {
          $position = strrpos($filePath, PATH_SEPARATOR);
          return new \Illuminate\Http\UploadedFile($filePath, ($position === false) ? $filePath : substr($filePath, $position+1));
        }, $files);

      } else {
        $this->error('input file does not exist');
        return;
      }
    }

    try {
      $expenseHeaderObj = null;
      $payableHeaderObj = null;
      DB::transaction(function() use (&$payableHeaderObj, &$expenseHeaderObj, $data, $files) {
        $systemUser = TaxableEntity::theCompany()->contact[0];

        // spoof system as authenticated user
        auth()->login($systemUser);

        // convert $data into proper HTTP request object
        $request = new \Illuminate\Http\Request();
        // spoof IP address
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        $request->merge($data);
        $request->files->set('upload-selector', $files);
        $expenseHeaderObj = ExpenseHeader::initialize($request);

        $expenseHeaderObj->submit($request);

				// email/contact all approvers for approval
				if ($expenseHeaderObj->requireApproval()) {
					$expenseHeaderObj->sendEmailRequestApproval();
				} else {
					$expenseHeaderObj->autoApprove($request);
				}

        auth()->logout();
      });
      if ($payableHeaderObj) {
        $this->info('Payable #' . $payableHeaderObj->title . ' created');
      } else if ($expenseHeaderObj) {
        $this->info('Expense #' . $expenseHeaderObj->title . ' created');
      }
    } catch (\Exception $e) {
      $this->error($e->getMessage());
      $this->info("=== stack ===\n".$e->getTraceAsString());
      auth()->logout();
    }

    return;
  }
}
