<?php

namespace App;

use App\Helpers\SerialRangeHelper;
use DB;
use Illuminate\Database\Eloquent\Model;

class WarehouseDetail extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'header_id', 'src_table', 'src_id', 'unique_tradable_id', 'expected_quantity', 'processed_quantity', 'description', 'status', 'notes',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

	public function uniqueTradable()
	{
		return $this->belongsTo('\App\UniqueTradable', 'unique_tradable_id');
	}

	public function binTransactions()
	{
		return $this->hasMany('\App\WarehouseBinTransaction', 'warehouse_detail_id');
	}

	public function getWarehouseHeader()
	{
		return $this->belongsTo('\App\WarehouseHeader', 'header_id')->withoutGlobalScope('currentFiscal');
	}

	public function getSerialNumbers($mode = 'html')
	{
		$result = SerialRangeHelper::formatRange(Serial::where([['src_table', '=', 'warehouse_details'], ['src_id', '=', $this->id]])->get()->pluck('serial'), 'text');

		switch ($mode) {
			case 'text':
				return implode("\n", $result);
				break;
			case 'array':
				return $result;
				break;
			case 'html':
			default:
				return implode("<br>", $result);
				break;
		}
		return '';
	}

	public function getBinLocations($mode = 'text')
	{
		$warehouseHeaderObj = $this->getWarehouseHeader;

		if (in_array($warehouseHeaderObj->type, ['receive'])) {
			return ($mode == 'text' ? "" : []);
		}

		$locationId = $warehouseHeaderObj->shipping_location_id;
		$uniqueTradableId = $this->unique_tradable_id;

		$result = array_map(
				function ($element) { return $element->name; },
				DB::select("SELECT DISTINCT warehouse_bins.name FROM warehouse_bins INNER JOIN bin_serial ON warehouse_bins.id = bin_serial.bin_id INNER JOIN tradables ON tradables.id = bin_serial.tradable_id WHERE tradables.unique_tradable_id = " . $uniqueTradableId . " AND bin_serial.occupied_since IS NOT NULL AND bin_serial.occupied_until IS NULL AND warehouse_bins.valid = 1 AND warehouse_bins.location_id = " . $locationId)
			);

		return count($result) ?
				($mode == 'text' ? implode(", ", $result) : $result) :
				($mode == 'text' ? "" : []);
	}

	public function recordBinTransaction($bin_id, $balance)
	{
		switch ($this->getWarehouseHeader->src) {
			case 'purchase_headers':
				WarehouseBinTransaction::create([
					'warehouse_detail_id' => $this->id,
					'bin_id' => $bin_id,
					'tradable_id' => $this->uniqueTradable->tradableByEntity($this->getWarehouseHeader->external_entity_id)->id,
					'quantity' => (($this->getWarehouseHeader->type == 'receive') ? 1 : -1) * $balance,
					'valid' => 1,
				]);
				break;
			default:
				if ($this->getWarehouseHeader->type == 'receive') {
					WarehouseBinTransaction::create([
						'warehouse_detail_id' => $this->id,
						'bin_id' => $bin_id,
						'tradable_id' => $this->uniqueTradable->tradables->last()->id,
						'quantity' => $balance,
						'valid' => 1,
					]);
				} else {
					foreach (WarehouseBin::find($bin_id)->getTradableBatches($this->uniqueTradable, $balance) as $batch) {
						WarehouseBinTransaction::create([
							'warehouse_detail_id' => $this->id,
							'bin_id' => $bin_id,
							'tradable_id' => $batch['tradable_id'],
							'quantity' => - $batch['quantity'],
							'valid' => 1,
						]);
					}
				}
				break;
		}
	}

}
