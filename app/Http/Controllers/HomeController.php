<?php

namespace App\Http\Controllers;

use App;
use Auth;
use App\User;
use App\SalesHeader;
use App\TransactableHeader;
use App\Helpers\DateHelper;
use App\Http\Requests;
use DB;
use Illuminate\Http\Request;

class HomeController extends Controller
{
	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		if (!auth()->user()) {
			return redirect('login');
		}

		$groups = [];

		// Sales
		if (auth()->user()->can('sq-list') || auth()->user()->can('so-list') || auth()->user()->can('sr-list')) {
			$groups[] = [
					'slug' => 'sales',
					'title' => trans('forms.Sales'),
			];
		}

		// Purchase
		if (auth()->user()->can('pq-list') || auth()->user()->can('po-list') || auth()->user()->can('pr-list')) {
			$groups[] = [
					'slug' => 'purchase',
					'title' => trans('forms.Purchase'),
			];
		}

		// Expense
		{
			$groups[] = [
					'slug' => 'expense',
					'title' => trans('forms.Expense'),
			];
		}

		// receivable
		if (auth()->user()->can('ar-list') || auth()->user()->can('rar-list')) {
			$groups[] = [
					'slug' => 'receivable',
					'title' => trans('forms.Receivable'),
			];
		}

		// payable
		if (auth()->user()->can('ap-list') || auth()->user()->can('rap-list')) {
			$groups[] = [
					'slug' => 'payable',
					'title' => trans('forms.Payable'),
			];
		}

		// product
		if (auth()->user()->can('pd-list')) {
			$groups[] = [
					'slug' => 'product',
					'title' => trans('product.Product'),
			];
		}

		return view()->first(generateTemplateCandidates('home'), ['groups' => $groups]);
	}

	public function getHomepageUpdateAjax(Request $request)
	{

		$groups = [];

		// Sales
		if (auth()->user()->can('sq-list') || auth()->user()->can('so-list') || auth()->user()->can('sr-list')) {
			$group = [
					'slug' => 'sales',
					'items' => [],
			];

			$viewPermission  = [
					'quote' => 'sq-view',
					'order' => 'so-view',
					'return' => 'sr-view',
				];

			$salesOrders = DB::select("select s1.id, s1.src_id, sales_headers.id as oid, sales_headers.type, sales_headers.title, s1.process_status, s1.created_at from sales_histories s1 join sales_headers on sales_headers.id = s1.src_id where (s1.src_id, s1.id) in (select src_id, max(id) as id from sales_histories where src = 'sales_headers' and created_at > '" . auth()->user()->last_login . "' group by src_id) and s1.src = 'sales_headers';");
			foreach ($salesOrders as $idx => $order) {
				$timeTick = strtotime($order->created_at);
				$timeDisplay = DateHelper::dbToGuiDate(date("Y-m-d", $timeTick)) . ' ' . date("h:iA", $timeTick);
				$group['items'][] = [
						'html' => sprintf(trans('messages.%1$s %2$s at %3$s'),
												trans('crm.'.ucfirst($order->type)) .
												"#" . $order->title,
												trans('action.'.$order->process_status),
												$timeDisplay),
					];
			}

			$groups[] = $group;
		}

		// Purchase
		if (auth()->user()->can('pq-list') || auth()->user()->can('po-list') || auth()->user()->can('pr-list')) {
			$group = [
					'slug' => 'purchase',
					'items' => [],
			];

			$viewPermission  = [
					'quote' => 'pq-view',
					'order' => 'po-view',
					'return' => 'pr-view',
				];

			$purchaseOrders = DB::select("select s1.id, s1.src_id, purchase_headers.id as oid, purchase_headers.type, purchase_headers.title, s1.process_status, s1.created_at from purchase_histories s1 join purchase_headers on purchase_headers.id = s1.src_id where (s1.src_id, s1.id) in (select src_id, max(id) as id from purchase_histories where src = 'purchase_headers' and created_at > '" . auth()->user()->last_login . "' group by src_id) and s1.src = 'purchase_headers';");
			foreach ($purchaseOrders as $idx => $order) {
				$timeTick = strtotime($order->created_at);
				$timeDisplay = DateHelper::dbToGuiDate(date("Y-m-d", $timeTick)) . ' ' . date("h:iA", $timeTick);
				$group['items'][] = [
						'html' => sprintf(trans('messages.%1$s %2$s at %3$s'),
												trans('vrm.'.ucfirst($order->type)) .
												'#' . $order->title,
												trans('action.' . $order->process_status),
												$timeDisplay),
					];
			}

			$groups[] = $group;
		}

		// Expense
		{
			$group = [
					'slug' => 'expense',
					'items' => [],
			];

			$expenses = DB::select("select s1.id, s1.src_id, expense_headers.id as eid, expense_headers.title, s1.process_status, s1.created_at from expense_histories s1 join expense_headers on expense_headers.id = s1.src_id where (s1.src_id, s1.id) in (select src_id, max(id) as id from expense_histories where src = 'expense_headers' and created_at > '" . auth()->user()->last_login . "' and staff_id = '" . auth()->user()->id . "' group by src_id) and s1.src = 'expense_headers';");
			foreach ($expenses as $idx => $expense) {
				$timeTick = strtotime($expense->created_at);
				$timeDisplay = DateHelper::dbToGuiDate(date("Y-m-d", $timeTick)) . ' ' . date("h:iA", $timeTick);
				$group['items'][] = [
						'html' => sprintf(trans('messages.%1$s %2$s at %3$s'),
												trans('forms.Expense') . ' #' . $expense->title ,
												trans('action.' . $expense->process_status),
												$timeDisplay),
					];
			}

			$groups[] = $group;
		}

		// receivable
		if (auth()->user()->can('ar-list') || auth()->user()->can('rar-list')) {
			$group = [
					'slug' => 'receivable',
					'items' => [],
			];

			$transactables = DB::select("select s1.id, s1.src_id, transactable_headers.id as tid, transactable_headers.title, s1.process_status, s1.created_at from transactable_histories s1 join transactable_headers on transactable_headers.id = s1.src_id where (s1.src_id, s1.id) in (select src_id, max(id) as id from transactable_histories where src = 'transactable_headers' and created_at > '" . auth()->user()->last_login . "' and transactable_headers.src_table in ('sales_headers') group by src_id) and s1.src = 'transactable_headers';");
			foreach ($transactables as $idx => $transactable) {
				// $hdr = TransactableHeader::find($transactable->tid);
				$timeTick = strtotime($transactable->created_at);
				$timeDisplay = DateHelper::dbToGuiDate(date("Y-m-d", $timeTick)) . ' ' . date("h:iA", $timeTick);
				$group['items'][] = [
						'html' => sprintf(trans('messages.%1$s %2$s at %3$s'),
												(trans('forms.Receivable') . ' #' . $transactable->title),
												trans('action.' . $transactable->process_status),
												$timeDisplay),
					];
			}

			$groups[] = $group;
		}

		// payable
		if (auth()->user()->can('ap-list') || auth()->user()->can('rap-list')) {
			$group = [
					'slug' => 'payable',
					'items' => [],
			];

			$transactables = DB::select("select s1.id, s1.src_id, transactable_headers.id as tid, transactable_headers.title, s1.process_status, s1.created_at from transactable_histories s1 join transactable_headers on transactable_headers.id = s1.src_id where (s1.src_id, s1.id) in (select src_id, max(id) as id from transactable_histories where src = 'transactable_headers' and created_at > '" . auth()->user()->last_login . "' and transactable_headers.src_table in ('purchase_headers','expense_headers') group by src_id) and s1.src = 'transactable_headers';");
			foreach ($transactables as $idx => $transactable) {
				// $hdr = TransactableHeader::find($transactable->tid);
				$timeTick = strtotime($transactable->created_at);
				$timeDisplay = DateHelper::dbToGuiDate(date("Y-m-d", $timeTick)) . ' ' . date("h:iA", $timeTick);
				$group['items'][] = [
						'html' => sprintf(trans('messages.%1$s %2$s at %3$s'),
												(trans('forms.Payable') . ' #' . $transactable->title ),
												trans('action.' . $transactable->process_status),
												$timeDisplay),
					];
			}

			$groups[] = $group;
		}

		// RMA
		if (auth()->user()->can('rma-list')) {
			$group = [
					'slug' => 'rma',
					'items' => [],
			];

			$rmas = DB::select("select * from rma_headers where updated_at > '" . auth()->user()->last_login . "'");

			foreach ($rmas as $rma) {
				$timeTick = strtotime($rma->updated_at);
				$timeDisplay = DateHelper::dbToGuiDate(date("Y-m-d", $timeTick)) . ' ' . date("h:iA", $timeTick);
				$group['items'][] = [
						'html' => sprintf(trans('messages.%1$s %2$s at %3$s'),
												trans('crm.RMA') .
												' #' . $rma->title,
												trans('action.updated'),
												$timeDisplay),
					];
			}

			$groups[] = $group;
		}

		// product
		if (auth()->user()->can('pd-list')) {
			$group = [
					'slug' => 'product',
					'items' => [],
			];

			$products = DB::select("(select distinct tradable_id, sku, 'Product FAQ' as parts, tradables.updated_at from tradable_faq_tradable join tradables on tradable_faq_tradable.tradable_id = tradables.id join unique_tradables on unique_tradables.id = tradables.unique_tradable_id where faq_id in (select id from tradable_faqs where updated_at > '" . auth()->user()->last_login . "')) union (select distinct tradable_id, sku, 'Product update notice' as parts, tradables.updated_at from tradable_notice_tradable join tradables on tradable_notice_tradable.tradable_id = tradables.id join unique_tradables on unique_tradables.id = tradables.unique_tradable_id where notice_id in (select id from tradable_notices where updated_at > '" . auth()->user()->last_login . "')) union (select tradables.id, unique_tradables.sku, 'Product' as parts, tradables.updated_at from tradables join unique_tradables on unique_tradables.id = tradables.unique_tradable_id where tradables.updated_at > '" . auth()->user()->last_login . "')");

			foreach ($products as $product) {
				$timeTick = strtotime($product->updated_at);
				$timeDisplay = DateHelper::dbToGuiDate(date("Y-m-d", $timeTick)) . ' ' . date("h:iA", $timeTick);
				$group['items'][] = [
						'html' => sprintf(trans('messages.%1$s %2$s at %3$s'),
												trans('product.'.$product->parts) .
												' ' . $product->sku,
												trans('action.updated'),
												$timeDisplay),
					];
			}

			$groups[] = $group;
		}

		return response()->json(['success' => true, 'data' => $groups ]);
	}

}
