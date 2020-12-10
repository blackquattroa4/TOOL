<?php

namespace App\Http\Controllers;

use App\CommissionProfile;
use App\CommissionSource;
use App\ExpenseHeader;
use App\ExpenseHistory;
use App\TaxableEntity;
use App\TaccountTransaction;
use App\TransactableDetail;
use App\UniqueTradable;
use App\User;
use App\Helpers\DateHelper;
use App\Http\Requests;
use Auth;
use DB;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Storage;
use Validator;

class CommissionController extends Controller
{
  public function commissionWizard(Request $request)
	{
		return view()->first(generateTemplateCandidates('tool.commission_calculator'), [
				'currencySymbol' => TaxableEntity::theCompany()->currency->getSymbol(),
				'profiles' => CommissionProfile::all(),
				'products' => UniqueTradable::getProducts('sku', 'asc'),
				'payees' => TaxableEntity::getActiveEntities('code', 'asc'),
				'entities' => TaxableEntity::getSupplyChainEntities('code', 'asc'),
			]);
	}

	public function loadCommissionProfile(Request $request, $id)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		// return info from a commission profile
		$profile = CommissionProfile::find($id);
		$source = [
				'base' => $profile->base,
				'payable_entity' => $profile->payable_entity_id,
				'last_unrecorded_date' => DateHelper::dbToGuiDate(empty($profile->last_recorded_date) ? TaccountTransaction::min('book_date') : date("Y-m-d", strtotime($profile->last_recorded_date . "+1 days"))),
				'entity' => [],
				'product' => [],
				'rate' => [],
				'method' => [],
			];

		foreach ($profile->sources as $commissionSource) {
			$source['entity'][] = explode(",", $commissionSource->source_entity_id);
			$source['product'][] = explode(",", $commissionSource->source_product_id);
			$source['rate'][] = $commissionSource->per_piece_rate;
			$source['method'][] = $commissionSource->per_piece_method;
		}

		return response([
				'success' => true,
				'message' => null,
				'source' => $source,
			], 200);
	}

	public function saveCommissionProfile(Request $request, $id)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		try {
			DB::transaction(function() use ($request, $id) {
				if ($id == 0) {
					$profile = CommissionProfile::create([
						'name' => $request->input('source')['name'],
						'payable_entity_id' => $request->input('source')['payable_entity'],
						'base' => $request->input('source')['base'],
					]);
				} else {
					$profile = CommissionProfile::find($id);
					$profile->update([
							'name' => $request->input('source')['name'],
							'payable_entity_id' => $request->input('source')['payable_entity'],
							'base' => $request->input('source')['base'],
						]);
					CommissionSource::where('profile_id', $id)->delete();
				}
				foreach ($request->input('source')['entity'] as $idx => $entityIds) {
					CommissionSource::create([
							'profile_id' => $profile->id,
							'source_product_id' => implode(",", $request->input('source')['product'][$idx]),
							'source_entity_id' => implode(",", $request->input('source')['entity'][$idx]),
							'per_piece_rate' => $request->input('source')['rate'][$idx],
							'per_piece_method' => $request->input('source')['method'][$idx],
						]);
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response([
					'success' => false,
					'message' => trans('messages.System failure') . ' #' . $registration,
				], 200);
		}

		return response([
				'success' => true,
				'message' => trans('tool.Commission profile saved successfully'),
			], 200);
	}

	public function calculateCommission(Request $request)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

    try {
      $result = CommissionProfile::calculateCommission($request);
    } catch (Exception $e) {
      return response([
  				'success' => false,
  				'message' => $e->getMessage(),
  			], 200);
    }

		return response([
				'success' => true,
				'message' => '',
				'criteria' => $result['criteria'],
				'result' => $result['result'],
			], 200);
	}

	public function recordCommission(Request $request)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

    $expenseTitle = null;

		try {
			DB::transaction(function() use ($request, &$expenseTitle) {
        // temporary change locale to English for PDF report purpose.
        $locale = app()->getLocale();
        app()->setLocale('en');
        $result = CommissionProfile::calculateCommission($request);
        app()->setLocale($locale);

        // convert $data into proper HTTP request object
        $newRequest = new \Illuminate\Http\Request();
        // spoof IP address
        $newRequest->server->set('REMOTE_ADDR', '127.0.0.1');

        // create attachment
        // create directory if not exist. since dompdf does not create all-parent-directory automatically.
        $directoryPath = Storage::getDriver()->getAdapter()->getPathPrefix() . 'tmp' . DIRECTORY_SEPARATOR . session()->getId();
        if (!file_exists($directoryPath)) {
          mkdir($directoryPath);
        }
        $pdfPath = $directoryPath . DIRECTORY_SEPARATOR . str_random(16) . ".pdf";
        $pdf = new Dompdf();
        // Add some remedial CSS for minimal styling.
        $pdf->loadHtml("<style type=\"text/css\" media=\"all\">.text-right { text-align:right; }</style>" . $result['criteria'] . "<br><br>" . $result['result']);
        $pdf->render();
        file_put_contents($pdfPath, $pdf->output());

        $files = [
          new \Illuminate\Http\UploadedFile($pdfPath, "commission.pdf"),
        ];

        $newRequest->merge([
          'entity' => $request->input('payee'),  // payee
          'reference' => 'N/A',
          'currency' => TaxableEntity::theCompany()->currency_id,
          'notes' => "commission to " . TaxableEntity::find($request->input('payee'))->name . " from " . $request->input('date.0') . " to " . $request->input('date.1'),
          'product' => [ UniqueTradable::where('expense_t_account_id', $request->input('account'))->first()->id ],
          'incurdate' => [ $request->input('date.1') ],
          'unitprice' => [ $result['commission'] ],
          'quantity' => [ 1 ],
          'description' => [ "Commission" ],
        ]);

        $newRequest->files->set('upload-selector', $files);
        // create expense
        $expenseHeaderObj = ExpenseHeader::initialize($newRequest);
        // submit-expense with re-cycled $request object.
        $expenseHeaderObj->submit($request);

				// email/contact all approvers for approval
				if ($expenseHeaderObj->requireApproval()) {
					$expenseHeaderObj->sendEmailRequestApproval();
				} else {
					$expenseHeaderObj->autoApprove($newRequest);
				}

        unlink($pdfPath);

        $expenseTitle = $expenseHeaderObj->title;

        // save last-record-date into profile
        if ($request->input('profile')) {
          CommissionProfile::find($request->input('profile'))->update([
            'last_recorded_date' => DateHelper::guiToDbDate($request->input('date.1')),
          ]);
        }
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response([
					'success' => false,
					'message' => trans('messages.System failure') . ' #' . $registration,
				], 200);
		}

		return response([
				'success' => true,
				'message' => str_replace("###", "#".$expenseTitle, trans('tool.Commission entered as expense ###')),
			], 200);
	}
}

?>
