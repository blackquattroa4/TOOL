<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class NoSqlLogEvent extends Eloquent
{
    public $timestamps = false;

    protected $connection = 'mongologdb';

    protected $collection = 'logs';

    protected $fillable = [
      'client_ip', 'client_agent', 'request_url', 'server_ip', 'time', 'severity', 'summary', 'stack'
    ];
}

?>
