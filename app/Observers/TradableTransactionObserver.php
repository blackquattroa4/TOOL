<?php

namespace App\Observers;

use App\Helpers\ParameterHelper;
use App\TaxableEntity;
use DB;
use Mail;

/**
 * Observes the Users model
 */
class TradableTransactionObserver
{
  /**
   * Function will be triggerd when a TradableTransaction is created
   *
   * @param TradableTransaction $model
   */
    public function created($model)
    {
      $this->checkInventoryLevel($model);
    }

  /**
   * Function will be triggerd when a TradableTransaction is updated
   *
   * @param TradableTransaction $model
   */
    public function updated($model)
    {
       $this->checkInventoryLevel($model);
    }

   /**
    * function that checks inventory level
    *
    * @param TradableTransaction $model
    *
    */
    protected function checkInventoryLevel($model)
    {
      $uniqueTradable = $model->uniqueTradable;
      $location = $model->location;
      if ($uniqueTradable->stockable) {
        $inventoryLevel = $uniqueTradable->getInventory(explode(" ", $model->created_at)[0], $location->id, TaxableEntity::theCompany()->id);
        // check against preset inventory-level warning.
        $result = DB::select("select * from inventory_alert_rules where (location_id = 0 or location_id = " . $location->id . ") and (unique_tradable_id = 0 or unique_tradable_id = " . $uniqueTradable->id . ") and (min >= " . $inventoryLevel . " or max <= " . $inventoryLevel . ")");
        if (count($result)) {
          $message = null;
          $locale = app()->getLocale();
          $recipients = ParameterHelper::getValue('inventory_alert_email');
          if (!$recipients) {
            // temporary change locale to English for loggin purpose.
            app()->setLocale('en');
          }
          if ($result[0]->min >= $inventoryLevel ) {
            $message = sprintf(trans("messages.Inventory level of %1\$s at %2\$s unit (%3\$s: %4\$s)"), $uniqueTradable->sku, sprintf(env('APP_QUANTITY_FORMAT'), $inventoryLevel), trans('forms.Lower limit'), sprintf(env('APP_QUANTITY_FORMAT'), $result[0]->min));
          } else if ($result[0]->max <= $inventoryLevel) {
            $message = sprintf(trans("messages.Inventory level of %1\$s at %2\$s unit (%3\$s: %4\$s)"), $uniqueTradable->sku, sprintf(env('APP_QUANTITY_FORMAT'), $inventoryLevel), trans('forms.Upper limit'), sprintf(env('APP_QUANTITY_FORMAT'), $result[0]->max));
          }
          if (!$recipients) {
            app()->setLocale($locale);
          }
          if ($recipients) {
            // parse recipient & filter out mal-formatted address.
            $recipients = array_filter(preg_split("/(,)?(\s)+/", $recipients), function($val) {
              return filter_var($val, FILTER_VALIDATE_EMAIL);
            });
            try {
              // send notification email
              Mail::send('email_templates.free_style',
                [
                  'body' => $message
                ], function ($m) use ($recipients) {
                  $m->subject('Inventory alert');
                  $m->from(config("mail.from.address"), config("mail.from.name"));
                  foreach ($recipients as $recipient) {
                    $m->to($recipient);
                  }
                });
              } catch (\Exception $e) {
                $registration = recordAndReportProblem($e);
              }
          } else {
            \Illuminate\Support\Facades\Log::info(" No inventory alert recipient; message is logged.\n" . $message);
          }
        }
      }
    }
}
