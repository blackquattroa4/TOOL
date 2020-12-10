<?php

namespace App\Console\Commands;

use App\LoanHeader;
use App\User;
use Illuminate\Console\Command;

// calculate loan interest
class CalculateLoanInterest extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'calculate:loan-interest' .
                      ' {--id= : specify loan id}' .
                      ' {--record : record interest into system}' .
                      ' {account : expense/income T-account}'
                      ;

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'calculate loan interest';

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
    $loans = $this->option('id') ? collect([ LoanHeader::find($this->option('id')) ]) : LoanHeader::all();

    foreach ($loans as $loan) {
      if ($loan->getBalanceAsOf() > 0) {
        // findout day-ago/balance after last payment, then accrued interest
        $lastPaymentDate = $loan->getLastPaymentDate();
        $lastBalance = $loan->getBalanceAsOf($lastPaymentDate);
        $daysSinceLastPayment = (strtotime(date("Y-m-d")) - strtotime($lastPaymentDate)) / 86400;
        $interestAccrued = $lastBalance * $daysSinceLastPayment * $loan->annual_percent_rate / (36500);  // 365 days
        $message = 'interest of loan \'' . $loan->title . '\'(#' . $loan->id . ') since ' . $lastPaymentDate . ' is ' . $interestAccrued;
        if ($this->option('record')) {
          $message .= '  recorded.';
          $loan->recordInterest(date("Y-m-d"), $interestAccrued, $this->argument("account"), User::getSystemUser()->id, "127.0.0.1");
        }
        $this->info($message);
      } else {
        $this->info('loan \'' . $loan->title . '\'(#' . $loan->id . ') is closed');
      }
    }
  }
}
