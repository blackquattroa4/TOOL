<?php

namespace App;

use App\UniqueTradable;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Tradable extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'unique_tradable_id', 'serial_pattern', 'supplier_entity_id', 'unit_weight', 'unit_length', 'unit_width', 'unit_height', 'unit_per_carton', 'carton_weight', 'carton_length', 'carton_width', 'carton_height', 'carton_per_pallet', 'lead_days', 'content', 'manufacture_origin', 'current',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

	public static function getCurrentProducts($order, $direction)
	{
		return Tradable::where('current', 1)->whereIn('unique_tradable_id', UniqueTradable::where('stockable', 1)->pluck('id')->toArray())->get();
	}

	public function uniqueTradable()
	{
		return $this->belongsTo('App\UniqueTradable', 'unique_tradable_id');
	}

	public function supplier()
	{
		return $this->belongsTo('App\TaxableEntity', 'supplier_entity_id');
	}

	public static function initialize(Request $request)
	{
		$uniqueTradable = UniqueTradable::where('sku', $request->input('model'))->first();

		if (!$uniqueTradable) {
			$cogsAccount = ChartAccount::create([	// cost of good sold
				'account' => '50000',
				'type' => 'cogs',
				'currency_id' => 1,
				'description' => 'cost-of-good-sold of ' . $request->input('model'),
				'active' => 1,
			]);
			$expenseAccountId = $request->input('account');
			if (!$expenseAccountId) {
				$expenseAccountId = ChartAccount::where('type', 'unknown')->first()->id;
			}
			$uniqueTradable = UniqueTradable::create([
				'sku' => $request->input('model'),
				'description' => $request->input('description'),
				'product_id' => $request->input('productid'),
				'current' => empty($request->input('active')) ? 0 : 1,
				'phasing_out' => empty($request->input('phaseout')) ? 0 : 1,
				'stockable' => ($request->input('itemtype')=="stockable") ? 1 : 0,
				'expendable' => ($request->input('itemtype')=="expendable") ? 1 : 0,
				'forecastable' => empty($request->input('forecast')) ? 0 : 1,
				'replacing_unique_tradable_id' => -1,
				'replaced_by_unique_tradable_id' => -1,
				'expense_t_account_id' => $expenseAccountId,
				'cogs_t_account_id' => $cogsAccount->id,
			]);
			$cogsAccount->update([
				'account' => '5' . sprintf('%04u', $uniqueTradable->id),
			]);
			event(new \App\Events\AccountUpsertEvent($cogsAccount));
			UniqueTradableRestriction::create([
				'unique_tradable_id' => $uniqueTradable->id,
				'action' => 'include',
				'associated_attribute' => 'entity',
				'associated_id' => 0,
				'enforce' => 1,
			]);
		} else {
			$uniqueTradable->update([
				'current' => $uniqueTradable->current || $request->has('active'),
			]);
		}

		$tradable = Tradable::create([
			'unique_tradable_id' => $uniqueTradable->id,
			'serial_pattern' => $request->input('serial_pattern'),
			'supplier_entity_id' => $request->input('supplier'),
			'unit_weight' => $request->input('unit_weight'),
			'unit_length' => $request->input('unit_length'),
			'unit_width' => $request->input('unit_width'),
			'unit_height' => $request->input('unit_height'),
			'unit_per_carton' => $request->input('per_carton'),
			'carton_weight' => $request->input('carton_weight'),
			'carton_length' => $request->input('carton_length'),
			'carton_width' => $request->input('carton_width'),
			'carton_height' => $request->input('carton_height'),
			'carton_per_pallet' => $request->input('per_pallet'),
			'lead_days' => $request->input('lead_day'),
			'content' => $request->input('content'),
			'manufacture_origin' => $request->input('country'),
			'current' => empty($request->input('active')) ? 0 : 1,
		]);

		event(new \App\Events\TradableUpsertEvent($tradable));
		return $tradable;
	}

	public function synchronize(Request $request)
	{
		// update database
		$this->update([
			'serial_pattern' => $request->input('serial_pattern'),
			'supplier_entity_id' => $request->input('supplier'),
			'unit_weight' => $request->input('unit_weight'),
			'unit_length' => $request->input('unit_length'),
			'unit_width' => $request->input('unit_width'),
			'unit_height' => $request->input('unit_height'),
			'unit_per_carton' => $request->input('per_carton'),
			'carton_weight' => $request->input('carton_weight'),
			'carton_length' => $request->input('carton_length'),
			'carton_width' => $request->input('carton_width'),
			'carton_height' => $request->input('carton_height'),
			'carton_per_pallet' => $request->input('per_pallet'),
			'lead_days' => $request->input('lead_day'),
			'content' => $request->input('content'),
			'manufacture_origin' => $request->input('country'),
			'current' => $request->has('active'),
		]);

		$this->uniqueTradable->update([
			'sku' => $request->input('model'),
			'description' => $request->input('description'),
			'product_id' => $request->input('productid'),
			'current' => DB::select("select bit_or(current) as current from tradables where unique_tradable_id=9")[0]->current,
			'phasing_out' => $request->has('phaseout'),
			'stockable' => ($request->input('itemtype')=="stockable"),
			'expendable' => ($request->input('itemtype')=="expendable"),
			'forecastable' => $request->has('forecast'),
			'replacing_unique_tradable_id' => -1,
			'replaced_by_unique_tradable_id' => -1,
			'expense_t_account_id' => $request->input('account') ? $request->input('account') : ChartAccount::where('type', 'unknown')->first()->id,
		]);

		event(new \App\Events\TradableUpsertEvent($this));
		return $this;
	}

	// If new keyword is added, make sure generateSearchTips() is updated as well
	public function generateSearchAttribute()
	{
		$result = [];
		$uniqueTradable = $this->uniqueTradable;

		array_push($result, $uniqueTradable->current ? 'active' : 'passive');
		if ($uniqueTradable->phasing_out) {
			array_push($result, 'phaseout');
		}
		if ($uniqueTradable->stockable) {
			array_push($result, 'stockable');
		}
		if ($uniqueTradable->expendable) {
			array_push($result, 'expendable');
		}
		if ($uniqueTradable->forecastable) {
			array_push($result, 'forecastable');
		}

		return $result;
	}

	// If new keyword is added, make sure generateSearchAttribute() is updated as well
	public static function generateSearchTips($delimiter)
	{
		return implode($delimiter, [
				str_pad('active', 15) . trans('tool.Search active product'),
				str_pad('passive', 15) . trans('tool.Search passive product'),
				str_pad('phaseout', 15) . trans('tool.Search phased-out product'),
				str_pad('stockable', 15) . trans('tool.Search stockable product'),
				str_pad('expendable', 15) . trans('tool.Search expendable product'),
				str_pad('forecastable', 15) . trans('tool.Search forecastable product'),
			]);
	}
}
