<?php

namespace App;

use ACH;
use App\Helpers\ParameterHelper;
use App\Contracts\AchServiceContract as AchServiceContract;
use DB;
use DOMPDF;
use GuzzleHttp\Client as Client;
use Illuminate\Http\Request;
use Mail;
use Storage;

class Stripe implements AchServiceContract
{
  public static function getDepositBankAccount($data)
  {
    $depositAccountId = ParameterHelper::getValue('stripe_deposit_account_id');
    if (is_null($depositAccountId)) {
      $cashTAccountIds = ParameterHelper::getValue('bank_cash_t_account_ids');
      if (count($cashTAccountIds) == 1) {
        $depositAccountId = $cashTAccountIds[0];
      } else {
        // don't really know which account Stripe deposit into
        throw new \Exception("Can not determine deposit account for Stripe");
      }
    }
    return ChartAccount::find($depositAccountId);
  }

  // Stripe collaborate with Plaid to do bank-authentication.
  // For simplicity, just call Plaid API from here.
  public static function performAuthorization($data)
  {
    // exchange for access token
    $headers[] = 'Content-Type: application/json';
    $params = [
       'client_id' => env('PLAID_CLIENT_ID'),
       'secret' => env('PLAID_SECRET'),
       'public_token' => $data->get('public_token'),
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://" . env('PLAID_ENVIRONMENT') . ".plaid.com/item/public_token/exchange");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 80);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    if(!$result = curl_exec($ch)) {
       throw new \Exception(curl_error($ch));
    }
    curl_close($ch);

    $jsonParsed = json_decode($result);

    // use access token to get bank account token
    $btok_params = [
       'client_id' => env('PLAID_CLIENT_ID'),
       'secret' => env('PLAID_SECRET'),
       'access_token' => $jsonParsed->access_token,
       'account_id' => $data->get('account_id'),
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://" . env('PLAID_ENVIRONMENT') . ".plaid.com/processor/stripe/bank_account_token/create");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($btok_params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 80);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    if(!$result = curl_exec($ch)) {
       throw new \Exception(curl_error($ch));
    }
    curl_close($ch);

    $btok_parsed = json_decode($result);
    $trxId = $btok_parsed->request_id;
    $stripeAccountToken = $btok_parsed->stripe_bank_account_token;

    return $stripeAccountToken;
  }

  public static function transferFund($data)
  {
    \Stripe\Stripe::setApiKey(env('STRIPE_COMPANY_SECRET_KEY'));

    $amount = explode(".", $data->get('amount'));
    $charge = \Stripe\Charge::create([
        'amount' => $amount[0] . str_pad(substr($amount[1], 0, 2), 2, "0"),
        'currency' => 'usd',
        'source' => $data->get('bank_token'),
      ]);

    return $charge;
  }

  /*
   * this is a queued job
   *
   *  'data' => [
   *    'charge_id' => '...',
   *    'transactable' => {obj},
   *    'agreement' => {obj},
   *    'permission' => [ ... ],
   *  ];
   */
  public static function fundTransferCallback($data)
  {
    \Stripe\Stripe::setApiKey(env('STRIPE_COMPANY_SECRET_KEY'));

    $charge = \Stripe\Charge::retrieve($data['charge_id'], [ ]);

    switch ($charge->status) {
    case 'pending':
      // cause job to requeue itself
      throw new \Exception("charge " . $charge->id . " is still pending");
      break;
    case 'failed':
      recordAndReportProblem(new \Exception("charge " . $charge->id . " failed"));
      break;
    case 'succeeded':
      // only when status => 'succeeded', 'receipt_url' will not be null.
      $receiptPdf = DOMPDF::loadHTML(
        file_get_contents(
          $charge->receipt_url,
          false,
          stream_context_create([
            "http" => [
              "header" => "User-Agent:Application"
            ]
          ])));

      $hashValue = md5(date('YmdHis') . $data['transactable']->title . " receipt");

      file_put_contents(Storage::getDriver()->getAdapter()->getPathPrefix() . $hashValue . ".pdf", $receiptPdf->output());

      $subRequest = new \Illuminate\Http\Request();
			$subRequest->server->set('REMOTE_ADDR', '127.0.0.1');
      $additionalData = [
        'title' => date("Y M") . " ACH receipt",
        'description' => date("Y M") . " ACH receipt",
        'permission' => $data['permission']
      ];
      $subRequest->merge($additionalData);
      $receiptDocument = Document::initialize($subRequest, Storage::getDriver()->getAdapter()->getPathPrefix() . $hashValue . ".pdf");

      $transactableHeaderObj = $data['transactable'];
      $customer = $data['transactable']->contact->first();
      $amount = strval($charge->amount);
      $amount = substr($amount, 0, -2) . "." . substr($amount, -2);
      $agreementDocument = $data['agreement'];

      // record into database
      DB::transaction(function() use ($subRequest, $customer, $transactableHeaderObj, $amount, $agreementDocument) {
        // process payment received.
        if ($amount > 0) {
          TaccountTransaction::create([
            'debit_t_account_id' => ACH::getDepositBankAccount(null)->id,
            'credit_t_account_id' => $customer->entity->transaction_t_account_id,
            'amount' => $amount,
            'currency_id' => $transactableHeaderObj->currency_id,
            'book_date' => date("Y-m-d"),
            'src' => 'cash_receipt',
            'src_id' => 0,
            'valid' => 1,
            'reconciled' => 0,
            'notes' => 'Payment to invoice #' . $transactableHeaderObj->title,
          ]);
          // apply amount to the invoice
          $transactableHeaderObj->balance -= $amount;
          $transactableHeaderObj->save();
          TransactableHistory::create([
            'src' => 'transactable_headers',
            'src_id' => $transactableHeaderObj->id,
            'amount' => $amount,
            'staff_id' => $customer->id,
            'machine' => $subRequest->ip(),
            'process_status' =>  'credited',
            'notes' => 'Payment to invoice #' . $transactableHeaderObj->title,
          ]);
          if ($transactableHeaderObj->balance == 0) {
            $transactableHeaderObj->close($subRequest);
          }
          event(new \App\Events\TransactableUpsertEvent($transactableHeaderObj));
        }
      });

        // grab receipt URL & S3 path, include both in email and send to client
      try {
        Mail::send('tenant.email_templates.ach_confirmation',
              [
                'customer' => $customer,
                'agreement_url' => '/document/view/' . $agreementDocument->file_path,
                'receipt_url' => '/document/view/' . $receiptDocument->file_path,
              ],
              function ($m) use($customer) {
                $m->subject('ACH confirmation');
                $m->from(config("mail.from.address"), config("mail.from.name"));
                $m->to($customer->email, $customer->name);
              });
      } catch (\Exception $e) {
        recordAndReportProblem($e);
      }

      break;
    }

  }

}
