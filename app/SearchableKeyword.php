<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SearchableKeyword extends Model
{
	public $timestamps = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'word',
	];

	public function documents()
	{
		return $this->belongsToMany('App\Document', 'document_keyword', 'keyword_id', 'document_id' );
	}
}
