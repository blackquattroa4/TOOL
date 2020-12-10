<?php

namespace App\Validations;

use App\Tradable;
use App\WarehouseBin;
use DB;

class WarehouseBinAvailabilityValidation
{
    public function validate($attribute, $value, $parameters, $validator)
    {
      // grab corresponding 'product' parameter.
      // for example, if we are validating 'bin.5',
      // then we try to grab product from 'bin.5'
      $sourceIndices = explode('.', $attribute);
      $targetProductIndices = explode('.', $parameters[0]);

      if (count($sourceIndices) != count($targetProductIndices)) {
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

      $theBin = WarehouseBin::find($value);
      // $theTradable = Tradable::find($targetProductValue);

      // if (is_null($theBin) || is_null($theTradable)) {
      if (is_null($theBin)) {
        return false;
      }

      // return is_null($theBin) ? false : $theBin->hasHowManyUniqueTradable($theTradable->uniqueTradable->id);
      return is_null($theBin) ? false : $theBin->hasHowManyUniqueTradable($targetProductValue);
    }
}
