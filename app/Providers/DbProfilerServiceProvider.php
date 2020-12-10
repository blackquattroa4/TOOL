<?php

namespace App\Providers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class DbProfilerServiceProvider extends ServiceProvider
{
    private static $counter;

    private $logger = null;

    private static function tickCounter()
    {
        return self::$counter++;
    }

    public function boot()
    {
        self::$counter = 1;

        // formatter for the logger
        $formatter = new LineFormatter(
              // Format of message in log, default [%datetime%] %channel%.%level_name%: %message% %context% %extra%\n
              "[%datetime%] %level_name%: %message% %context% %extra%\n",
              "Y-m-d H:i:s", // Datetime format
              true, // allowInlineLineBreaks option, default false
              true  // ignoreEmptyContextAndExtra option, default false
          );
        // handler for the logger
        $handler = new StreamHandler(storage_path('logs' . DIRECTORY_SEPARATOR . 'db-profiler.log'), Logger::DEBUG);
        $handler->setFormatter($formatter);
        // instantiate new logger
        $this->logger = new Logger('db-profiler');
        $this->logger->pushHandler($handler);

        if ($this->isEnabled()) {
          DB::listen(function (QueryExecuted $query) {
                $i = self::tickCounter();
                if ($i == 1) {
                    $this->logger->debug("below queries are executed on " . $this->app->request->url());
                }
                $sql = preg_replace("/(\s)+/", " ", $this->applyBindings($query->sql, $query->bindings));
                $this->logger->debug("[$i]: {$sql}; ({$query->time} ms)");
          });
        }
    }

    private function isEnabled()
    {
        if ($this->app->runningInConsole()) {
            // return env('ENABLE_DB_PROFILER', false) && in_array('-vvv', $_SERVER['argv']);
            return env('ENABLE_DB_PROFILER', false);
        }

        // return env('ENABLE_DB_PROFILER', false) && $this->app->request->exists('vvv');
        return env('ENABLE_DB_PROFILER', false);
    }

    private function applyBindings($sql, array $bindings)
    {
        if (empty($bindings)) {
            return $sql;
        }

        $placeholder = preg_quote('?', '/');
        foreach ($bindings as $binding) {
            $binding = is_numeric($binding) ? $binding : "'{$binding}'";
            $sql = preg_replace('/' . $placeholder . '/', $binding, $sql, 1);
        }

        return $sql;
    }
}
