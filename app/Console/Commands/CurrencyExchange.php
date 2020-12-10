<?php

namespace App\Console\Commands;

use App\Facades\ForexService as Forex;
use Illuminate\Console\Command;

class CurrencyExchange extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exchange:currency ' .
                          '{--simplified : simplify response}' .
                          '{source : 3-digit-code(ISO-4217) of source currency}' .
                          '{target : 3-digit-code(ISO-4217) of target currency}'
                        ;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'query exchange rate from source currency to target currency';

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
      $this->info(" result :\n" . json_encode(Forex::getExchangeRate($this->argument('source'), $this->argument('target'), $this->option('simplified'))));
    }
}
