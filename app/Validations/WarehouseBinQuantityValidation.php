<?php

namespace App\Validations;

use App\Tradable;
use App\WarehouseBin;
use DB;

class WarehouseBinQuantityValidation
{
    // with bin_id, product_id, determine if quantity is enough
    public function validate($attribute, $value, $parameters, $validator)
    {

      // grab corresponding 'bin', 'product'' parameter.
      // for example, if we are validating 'quantity.5',
      // then we try to grab bin & product from 'bin.5' & 'location.5'
      $sourceIndices = explode('.', $attribute);
      $targetProductIndices = explode('.', $parameters[0]);
      $targetBinIndices = explode('.', $parameters[1]);

      if ((count($sourceIndices) != count($targetProductIndices)) ||
          (count($sourceIndices) != count($targetBinIndices))) {
        return false;
      }

      // grab product value
      $resultProductIndices = array_map(function($source, $target) {
        return ($target == '*') ? $source : $target;
      }, $sourceIndices, $targetProductIndices);

      $targetProductValue = $validator->getData();
      foreach ($resultProductIndices as $index) {
        $targetProductValue = $targetProductValue[$index];
      }

      // grab bin value
      $resultBinIndices = array_map(function($source, $target) {
        return ($target == '*') ? $source : $target;
      }, $sourceIndices, $targetBinIndices);

      // it is possible that bin[index] does not exist,
      // in such case, just fail validation
      try {
        $targetBinValue = $validator->getData();
        foreach ($resultBinIndices as $index) {
          $targetBinValue = $targetBinValue[$index];
        }
      } catch (\Exception $e) {
        return false;
      }

      $theBin = WarehouseBin::find($targetBinValue);
      //$theTradable = Tradable::find($targetProductValue);

      // if (is_null($theBin) || is_null($theTradable)) {
      if (is_null($theBin)) {
        return false;
      }

     // return $value <= $theBin->hasHowManyUniqueTradable(Tradable::find($targetProductValue)->uniqueTradable->id);
      return $value <= $theBin->hasHowManyUniqueTradable($targetProductValue);
    }
}
