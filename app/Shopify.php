<?php

namespace App;

use App\Contracts\EcommerceServiceContract;
use App\Helpers\ParameterHelper;
use App\Jobs\EcommerceOrderSync;
use GuzzleHttp\Client as Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Shopify implements EcommerceServiceContract
{

  CONST DOMAIN = "myshopify.com";

  CONST DISCOUNT_TYPE_MAP = [
    "percentage" => "percent",
    "" => "amount",
  ];

  public static function orderSync()
  {
    $httpClient = new Client([
        'base_uri' => "https://" . env('SHOPIFY_API_KEY') . ":" . env('SHOPIFY_API_SECRET') . "@" . env('SHOPIFY_SUBDOMAIN') . "." . self::DOMAIN,
        'timeout' => 60,
    ]);

    $request = $httpClient->request(
        'GET',
        // :TODO: how to determine where was left-off since last time?
        //'admin/api/2019-10/orders.json?financial_status=paid'
        'admin/api/2019-10/orders.json?created_at_min=2019-12-26T10:20:00-08:00'
      );

    foreach (json_decode($request->getBody(), true)['orders'] as $order) {
      $request = new \Illuminate\Http\Request();
      // spoof IP address
      $request->server->set('REMOTE_ADDR', '127.0.0.1');
      // prepare temporary variables
      $order_date = date_parse($order['created_at']);
      $customer_entity = TaxableEntity::where([
          'code' => env('SHOPIFY_CUSTOMER_ENTITY'),
        ])->first();
      $staff = User::select('users.*')
        ->leftjoin('taxable_entities', 'users.entity_id', '=', 'taxable_entities.id')
        ->where([
          'users.name' => env('SHOPIFY_SALES_NAME'),
          'taxable_entities.type' => 'employee',
        ])->first();
      $warehouse = Location::where([
          'name' => env('SHOPIFY_WAREHOUSE'),
        ])->first();
      array_walk($order['tax_lines'], function(&$item, $key) {
          $item["display"] = $item["title"] . " (" . sprintf("%.2f", floatval($item["rate"])*100) . "%)";
        });
      $shipping_address = Address::firstOrCreate([
          'entity_id' => $customer_entity->id,
          'purpose' => 'shipping',
          'name' => $order["shipping_address"]["name"],
          'street' => $order["shipping_address"]["address1"],
          'city' => $order["shipping_address"]["city"],
          'state' => $order["shipping_address"]["province_code"],
          'country' => $order["shipping_address"]["country_code"],
          'zipcode' => $order["shipping_address"]["zip"],
        ], [
          'is_default' => 0,
          'unit' => '',
          'district' => ''
        ]);

      $productArray = [];
      $displayArray = [];
      $descriptionArray = [];
      $unitPriceArray = [];
      $quantityArray = [];
      $discountArray = [];
      $disctypeArray = [];
      $taxableArray = [];
      foreach ($order["line_items"] as $index => $detail) {
        $product = UniqueTradable::where('sku', $detail['sku'])->first();
        if ($product) {
          array_push($productArray, $product->id);
          array_push($displayArray, $detail["sku"]);
          array_push($descriptionArray, $detail["name"]);
          array_push($unitPriceArray, $detail["price"]);
          array_push($quantityArray, $detail["quantity"]);
          array_push($discountArray, isset($detail["discount_allocations"]) ? array_reduce($detail["discount_allocations"], function($carry, $item) {
              return $carry += floatval($item["amount"]);
            }, 0) : 0);
          array_push($disctypeArray, "amount");
          array_push($taxableArray, $detail["taxable"]);
        } else {
          // if no product match, proceed to next product
          $registration = recordAndReportProblem(new \Exception("No product match : " . $detail["sku"]));
          continue;
        }
      }
      // prepare order parameter
      $request->merge([
        'type' => 'order',
        'customer' => $customer_entity->id,
        'contact' => $customer_entity->contact->last()->id,
        'payment' => $customer_entity->payment_term_id,
        'reference' => 'Shopify ' . $order['name'],
        'staff' => $staff->id,
        'billing' => $customer_entity->defaultBillingAddress[0]->id,
        'shipping' => $shipping_address->id,
        'incoterm' => 'online',
        'via' => 'T.B.D.',
        'tax_rate' => sprintf("%.2f", array_reduce($order["tax_lines"], function($carry, $item) {
            return $carry += $item["rate"]*100;
          }, 0)),
        'currency' => $customer_entity->currency_id,
        'inputdate' => $order_date['year'].'-'.$order_date['month'].'-'.$order_date['day'],
        'location' => $warehouse->id,

        // followings are passed in for decision making
        'create_invoice' => $order['financial_status'] == 'paid',
        'order_fulfilled' => false,   // :TODO:
        'external_source' => 'shopify',
  			'notes' => 'order id: ' . $order['id'] . "\n" .
                  'taxes: ' . implode(", ", array_column($order["tax_lines"], "display")),

        // sales_details array
        'product' => $productArray,
        'display' => $displayArray,
        'description' => $descriptionArray,
        'unitprice' => $unitPriceArray,
        'quantity' => $quantityArray,
        'discount' => $discountArray,
        'disctype' => $disctypeArray,
        'taxable' => $taxableArray,
      ]);

      EcommerceOrderSync::dispatch($request);
    };
  }

}
