<?php

namespace App;

use App\CommissionSource;
use App\Currency;
use App\Helpers\ArrayHelper;
use App\Helpers\DateHelper;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CommissionProfile extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'payable_entity_id', 'base', 'last_recorded_date',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function sources()
    {
      return $this->hasMany('App\CommissionSource', 'profile_id');
    }

    public function payableEntity()
    {
      return $this->hasOne('App\TaxableEntity', 'id', 'payable_entity_id');
    }

    public static function calculateCommission(Request $request)
    {
      $companyCurrency = TaxableEntity::theCompany()->currency->getFormat();
      $commissionFmtr = new \NumberFormatter( $companyCurrency['regex'], \NumberFormatter::CURRENCY );

  		$success = true;
  		$criteria = "";
  		$result = "";
  		$entities = $request->input('entity');
  		$products = $request->input('product');
  		$rates = $request->input('rate');
  		$methods = $request->input('method');

  		$transactionDetails = collect([]);

  		foreach ($entities as $idx => $entity_ids) {
        switch ($request->input('base')) {
          case 'purchase-basis':
            if ($methods[$idx] == 'percent') {
              $commission = sprintf("%1\$s%%", $rates[$idx]);
              $basis = trans('tool.purchase amount');
            } else {
              $commission = $commissionFmtr->format(floatval($rates[$idx]));
              $basis = trans('tool.each purchase quantity');
            }
            break;
          case 'sales-basis':
            if ($methods[$idx] == 'percent') {
              $commission = sprintf("%1\$s%%", $rates[$idx]);
              $basis = trans('tool.sales amount');
            } else {
              $commission = $commissionFmtr->format(floatval($rates[$idx]));
              $basis = trans('tool.each sales quantity');
            }
            break;
          case 'payment-basis':
            $commission = sprintf("%1\$s%%", $rates[$idx]);
            $basis = trans('tool.Payment amount');
            break;
            break;
          case 'gross-profit-basis':
            $commission = sprintf("%1\$s%%", $rates[$idx]);
            $basis = trans('tool.Gross-profit amount');
            break;
          default:
            throw new Exception(trans('tool.Unknown commission base'));
            break;
        }

  			$criteria .= "<p>" . sprintf(trans('tool.%1$s of %2$s, based on product(s) %3$s sold to %4$s between %5$s and %6$s'),
  					$commission,
  					$basis,
  					implode(", ", UniqueTradable::findMany($products[$idx])->pluck('sku')->toArray()),
  					implode(", ", TaxableEntity::findMany($entities[$idx])->pluck('code')->toArray()),
  					$request->input('date')[0],
  					$request->input('date')[1]) . "</p>";
  		}

  		switch ($request->input('base')) {
  			case 'purchase-basis':
  				$transactionDetails = TransactableDetail::select('transactable_details.*')
                ->join('transactable_headers', 'transactable_headers.id', '=', 'transactable_details.transactable_header_id')
  							->where([['transactable_details.status', 'valid'], ['transactable_details.src_table', 'purchase_details'], ['transactable_headers.status', '<>', 'void']])
  							->whereIn('transactable_details.unique_tradable_id', ArrayHelper::union($products))
  							->whereIn('transactable_headers.entity_id', ArrayHelper::union($entities))
  							->whereBetween('incur_date', [DateHelper::guiToDbDate($request->input('date')[0]), DateHelper::guiToDbDate($request->input('date')[1])]);
  				break;
  			case 'sales-basis':
  				$transactionDetails = TransactableDetail::select('transactable_details.*')
                ->join('transactable_headers', 'transactable_headers.id', '=', 'transactable_details.transactable_header_id')
  							->where([['transactable_details.status', 'valid'], ['transactable_details.src_table', 'sales_details'], ['transactable_headers.status', '<>', 'void']])
  							->whereIn('transactable_details.unique_tradable_id', ArrayHelper::union($products))
  							->whereIn('transactable_headers.entity_id', ArrayHelper::union($entities))
  							->whereBetween('incur_date', [DateHelper::guiToDbDate($request->input('date')[0]), DateHelper::guiToDbDate($request->input('date')[1])]);
  				break;
  			case 'payment-basis':
          $paymentDetails = DB::select("select book_date as incur_date, taxable_entities.id as entity_id, code, amount, taccount_transactions.currency_id from taccount_transactions left join taxable_entities on (taxable_entities.transaction_t_account_id = taccount_transactions.debit_t_account_id) or (taxable_entities.transaction_t_account_id = taccount_transactions.credit_t_account_id) where taxable_entities.id in (" . implode(",", ArrayHelper::union($entities)) . ") and taccount_transactions.src = 'cash_receipt' and book_date between '" . DateHelper::guiToDbDate($request->input('date')[0]) . "' and '" . DateHelper::guiToDbDate($request->input('date')[1]) . "'");
  				break;
  			case 'gross-profit-basis':
  			default:
          throw new Exception(trans('tool.Unknown commission base'));
  				break;
  		}

      $totalCommission = 0;
      switch ($request->input('base')) {
        case 'purchase-basis':
        case 'sales-basis':
    			$result .= "<table class=\"table table-striped col-md-12\"><tr><th>" . trans('forms.Document') .
    						"</th><th>" . trans('forms.Entity') .
    						"</th><th>" . trans('forms.Date') .
    						"</th><th>" . trans('forms.Product') .
                "</th><th>" . trans('forms.Quantity') .
    						"</th><th>" . trans('finance.Amount') .
    						"</th><th>" . trans('tool.Commission') . "</th></tr>";
    			foreach ($transactionDetails->get() as $detail) {
            $header = $detail->header;
    				$currency = $header->currency->getFormat();
    				$fmtr = new \NumberFormatter( $currency['regex'], \NumberFormatter::CURRENCY );
    				// identify rate/method of each line by findind applicable source-id
            $applicableTradablesSourceIds = array_keys(array_filter($products, function($val) use($detail) { return in_array($detail->unique_tradable_id, $val); }));
            $applicableEntitiesSourceIds = array_keys(array_filter($entities, function($val) use($detail) { return in_array($detail->entity_id, $val); }));
            $applicableSourceIds = array_intersect($applicableTradablesSourceIds, $applicableEntitiesSourceIds);
            // if purchase-return or sales-return, amount should be negative
            $multiplier = ($header->isPayableCredit() || $header->isReceivableInvoice()) ? 1 : -1;
            if ($methods[$applicableSourceIds[0]] == 'percent') {
              $commission = $multiplier * ($detail->transacted_amount - $detail->discounted_amount) * $detail->currency->getConversionRatio(TaxableEntity::theCompany()->currency) * $rates[$applicableSourceIds[0]] / 100;
            } else {
              $commission = $multiplier * $detail->transacted_quantity * $rates[$applicableSourceIds[0]];
            }
    				$result .= "<tr><td>" . $detail->title .
    						"</td><td>" . TaxableEntity::find($detail->entity_id)->code .
    						"</td><td>" . DateHelper::dbToGuiDate($detail->incur_date) .
                "</td><td>" . UniqueTradable::find($detail->unique_tradable_id)->sku .
    						"</td><td class=\"text-right\">" . sprintf(env('APP_QUANTITY_FORMAT'), $detail->transacted_quantity) .
    						"</td><td class=\"text-right\" title=\"" . Currency::find($detail->currency_id)->symbol . "\">" . $fmtr->format($multiplier * ($detail->transacted_amount - $detail->discounted_amount)) .
    						"</td><td class=\"text-right\" title=\"" . Currency::find(TaxableEntity::theCompany()->currency_id)->symbol . "\">" . $commissionFmtr->format($commission) . "</td></tr>";
    				$totalCommission += $commission;
    			}
    			$result .= "<tr><td colspan=6 class=\"text-right\"><strong>" . trans('finance.Total') . "</strong></td><td class=\"text-right\">" . $commissionFmtr->format($totalCommission) . "</td></tr></table>";
          break;
        case 'payment-basis':
          $result .= "<table class=\"table table-striped col-md-12\"><tr><th>" . trans('forms.Date') .
                "</th><th>" . trans('forms.Entity') .
                "</th><th>" . trans('finance.Amount') .
                "</th><th>" . trans('tool.Commission') . "</th></tr>";
          foreach ($paymentDetails as $detail) {
            $currency = Currency::find($detail->currency_id);
            $fmtr = new \NumberFormatter( $currency['regex'], \NumberFormatter::CURRENCY );
            // identify rate/method of each line by findind applicable source-id
            $applicableEntitiesSourceIds = array_keys(array_filter($entities, function($val) use($detail) { return in_array($detail->entity_id, $val); }));
            $commission = $detail->amount * Currency::find($detail->currency_id)->getConversionRatio(TaxableEntity::theCompany()->currency) * $rates[$applicableEntitiesSourceIds[0]] / 100;
            $result .= "<tr><td>" . DateHelper::dbToGuiDate($detail->incur_date) .
                "</td><td>" . TaxableEntity::find($detail->entity_id)->code .
                "</td><td class=\"text-right\" title=\"" . Currency::find($detail->currency_id)->symbol . "\">" . $fmtr->format($detail->amount) .
                "</td><td class=\"text-right\" title=\"" . Currency::find(TaxableEntity::theCompany()->currency_id)->symbol . "\">" . $commissionFmtr->format($commission) . "</td></tr>";
            $totalCommission += $commission;
          }
          $result .= "<tr><td colspan=3 class=\"text-right\"><strong>" . trans('finance.Total') . "</strong></td><td class=\"text-right\">" . $commissionFmtr->format($totalCommission) . "</td></tr></table>";
          break;
        case 'gross-profit-basis':
        default:
          throw new Exception(trans('tool.Unknown commission base'));
          break;
  		}

      return [
          'criteria' => $criteria,
          'result' => $result,
          'commission' => $totalCommission,
        ];
    }
}
