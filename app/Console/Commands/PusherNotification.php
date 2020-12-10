<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Pusher\Pusher;

class PusherNotification extends Command
{
  /**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'pusher:notify ' .
                        '{channel : channel to send notification}' .
                        '{event : type of notification}' .
                        '{notification : content of notification}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Pusher notification tool';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */

	public function handle()
	{
    $pusher = new Pusher(
      env('PUSHER_APP_KEY'),
      env('PUSHER_APP_SECRET'),
      env('PUSHER_APP_ID'),
      [
        'cluster' => env('PUSHER_APP_CLUSTER'),
        'useTLS' => true
      ]
    );

    $pusher->trigger($this->argument('channel'), $this->argument('event'), $this->argument('notification'));

  }
}
