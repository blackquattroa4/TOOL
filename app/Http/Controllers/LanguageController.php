<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\User;
use App\Helpers\HistoryHelper;
use App\Http\Requests;

class LanguageController extends Controller
{
	public function index($lang)
	{
		// all Ajax controller does not register with session-history
		$this->removeFromHistory();

		$user = auth()->user();
		$user->language = $lang;
		$user->save();
		session([ 'session_language' => $lang ]);
		return redirect(HistoryHelper::goBackPages(1));
	}
}
