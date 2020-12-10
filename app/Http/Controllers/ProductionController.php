<?php

namespace App\Http\Controllers;

use App;
use Auth;
use App\User;
use App\Http\Requests;
use Illuminate\Http\Request;

class ProductionController extends Controller
{
	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		return redirect(generateTemplateCandidates('home')[0]);
	}
}
