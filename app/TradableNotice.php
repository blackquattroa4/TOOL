<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Storage;

class TradableNotice extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'summary', 'document_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function product()
    {
      return $this->belongsToMany('App\Tradable', 'tradable_notice_tradable', 'notice_id', 'tradable_id');
    }

    public function relatedProducts($products = null) {
      if ($products) {
        $this->product()->sync($products);
      }
      return $this->product;
    }

    public function document()
    {
      return $this->hasOne('\App\Document', 'id', 'document_id');
    }

    public static function initialize(Request $request)
    {
      $products = $request->input('product');
      $summary = $request->input('summary');

      $permission = [ 'roles' => [], 'users' => [ auth()->user()->id => [ 'read' => true, 'update' => true]]];
      foreach (array_column(DB::select("select id from roles order by display_name"), "id") as $role) {
        $permission['roles'][$role] = [ 'read' => true ];
      }
      $additionalData = [
        'title' => $summary . " (Product Update Notice)",
        'description' => "Product Update Notice\n".$summary,
        'permission' => $permission,
      ];
      $request->merge($additionalData);
      $document = Document::initialize($request, 'thefile');

      $notice = self::create([
          'summary' => $summary,
          'document_id' => $document->id,
        ]);

      $notice->relatedProducts(Tradable::findMany($products));

      return $notice;
    }

    public function synchronize(Request $request)
    {
      $products = $request->input('product');
      $summary = $request->input('summary');

      $permission = [ 'roles' => [], 'users' => [ auth()->user()->id => [ 'read' => true, 'update' => true]]];
      foreach (array_column(DB::select("select id from roles order by display_name"), "id") as $role) {
        $permission['roles'][$role] = [ 'read' => true ];
      }
      $additionalData = [
        'title' => $summary . " (Product Update Notice)",
        'description' => "Product Update Notice\n".$summary,
        'permission' => $permission,
      ];
      $request->merge($additionalData);
      $document = $this->document->synchronize($request, 'thefile');

      $this->update([
          'summary' => $summary,
          'document_id' => $document->id,
        ]);

      $this->relatedProducts(Tradable::findMany($products));

      return $this;
    }
}
