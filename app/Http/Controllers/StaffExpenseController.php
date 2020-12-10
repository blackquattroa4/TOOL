<?php

namespace App\Http\Controllers;

use App;
use App\Currency;
use App\ExpenseHeader;
use App\Http\Resources\ExpenseHeader as ExpenseHeaderResource;
use App\User;
use App\Http\Requests;
use Auth;
use DB;
use Illuminate\Http\Request;
use NumberFormatter;
use Validator;

class StaffExpenseController extends Controller
{
	public function index()
	{
		return view()->first(generateTemplateCandidates('staff.expense_list'));
	}

	public function inProgressExpenseAjax()
	{
		$expenseHeaders = ExpenseHeader::where('entity_id', auth()->user()->entity_id)->get();

		return response()->json([ 'success' => true, 'data' => ExpenseHeaderResource::collection($expenseHeaders) ]);
	}

	public function needApprovalExpenseAjax()
	{
		$userId = auth()->user()->id;

		$expenseHeaders = ExpenseHeader::whereRaw("((? not in (select distinct staff_id from expense_histories where (process_status='approved' or process_status='rejected') and src = 'expense_headers' and src_id = expense_headers.id)) and (select count(id) > 0 from expense_approval_rules where (expense_headers.entity_id = expense_approval_rules.src_entity_id or expense_approval_rules.src_entity_id = 0) and (expense_approval_rules.approver_id = ?) and (expense_approval_rules.valid = 1))) and expense_headers.status = 'under review'", [ $userId, $userId ])->get();

		return response()->json([ 'success' => true, 'data' => ExpenseHeaderResource::collection($expenseHeaders) ]);
	}
}
