<?php

namespace App\Contracts;

interface AchServiceContract
{
  public static function getDepositBankAccount($data);

  // function that provides implementation-specific token.
  public static function performAuthorization($data);

  public static function transferFund($data);

  public static function fundTransferCallback($data);

}
