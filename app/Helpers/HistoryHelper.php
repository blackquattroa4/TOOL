<?php
namespace App\Helpers;

class HistoryHelper
{
	public static function goBackPages($n)
	{
		if ($n > 0) {
			if (auth()->user()) {
				$totalLinks = count(session('session_url_links'));
				if ($n == min($n, env('LAST_N_VISITED_PAGES'), $totalLinks)) {
					return session('session_url_links')[$n - 1];
				}
				$oldestLink = min(env('LAST_N_VISITED_PAGES'), $totalLinks);
				return ($oldestLink > 0) ? session('session_url_links')[$oldestLink - 1] : session('session_url_links')[0];
			}
		}
		return "/";
	}
}
?>
