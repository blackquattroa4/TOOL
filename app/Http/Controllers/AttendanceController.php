<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App;
use Auth;
use App\Http\Requests;
use Validator;

class AttendanceController extends Controller
{
	public function index()
	{
		return view()->first(generateTemplateCandidates('attendance.index'));
	}
}
