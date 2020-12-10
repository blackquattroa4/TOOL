<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Storage;

class TradableFaq extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'question', 'answer', 'document_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function product()
    {
      return $this->belongsToMany('App\Tradable', 'tradable_faq_tradable', 'faq_id', 'tradable_id');
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
      $question = $request->input('question');
      $answer = $request->input('answer');

      // ready the permission
      $permission = [ 'roles' => [], 'users' => [ auth()->user()->id => [ 'read' => true, 'update' => true]]];
      foreach (array_column(DB::select("select id from roles order by display_name"), "id") as $role) {
        $permission['roles'][$role] = [ 'read' => true ];
      }
      $additionalData = [
        'title' => $question . " (Product FAQ)",
        'description' => "Product FAQ\n".$question,
        'permission' => $permission,
      ];
      $request->merge($additionalData);
      $document = Document::initialize($request, 'thefaqfile');

      $faq = self::create([
          'question' => $question,
          'answer' => $answer,
          'document_id' => $document->id,
        ]);

      $faq->relatedProducts(Tradable::findMany($products));

      return $faq;
    }

    public function synchronize(Request $request)
    {
      $products = $request->input('product');
      $question = $request->input('question');
      $answer = $request->input('answer');

      // ready the permission
      $permission = [ 'roles' => [], 'users' => [ auth()->user()->id => [ 'read' => true, 'update' => true]]];
      foreach (array_column(DB::select("select id from roles order by display_name"), "id") as $role) {
        $permission['roles'][$role] = [ 'read' => true ];
      }
      $additionalData = [
        'title' => $question . " (Product FAQ)",
        'description' => "Product FAQ\n".$question,
        'permission' => $permission,
      ];
      $request->merge($additionalData);
      $latestDocument = $this->document->synchronize($request, 'thefaqfile');

      $this->update([
          'question' => $question,
          'answer' => $answer,
          'document_id' => $latestDocument->id,
        ]);

      $this->relatedProducts(Tradable::findMany($products));

      return $this;
    }
}
