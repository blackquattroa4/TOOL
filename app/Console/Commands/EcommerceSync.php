<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Facades\EcommerceService as Ecommerce;

class EcommerceSync extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'ecommerce:sync';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'sync ecommerce order';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */

	public function handle()
	{
    Ecommerce::orderSync();
  }
}
