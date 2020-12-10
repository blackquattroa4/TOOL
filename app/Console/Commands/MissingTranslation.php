<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MissingTranslation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'detect:missing-translation'
                        ;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'detect missing translation';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // do a grep on source directory
        $problem = 0;
        $result = [];

        exec("grep -hirE \"trans\\([\\'\\\"].+[.].+[\\'\\\"]\\)\" --exclude-dir=storage " . base_path(), $result);

        // go through each line
        foreach ($result as $line) {
            // $this->line("I -> " . $line);
            $data = preg_replace("/^(.*)(trans)((\(\")(((?!\"\)).)*)(\"\))|(\(\')(((?!\'\)).)*)(\'\)))(.*)$/i", "$5{}$9", $line);
            $data = explode("{}", $data);
            $data = empty($data[0]) ? $data[1] : $data[0];
            $data = explode(".", $data, 2);

            $file = $data[0];
            $phrase = $data[1];

            //$this->line($file . " -> " . $phrase);

            if (!empty($file)) {
              if (!empty($phrase) && !in_array($phrase[0], ['\'', '"'])) {
                foreach (['en', 'es', 'zh', 'zht'] as $localization) {
                  $fileName = resource_path('lang') . DIRECTORY_SEPARATOR . $localization . DIRECTORY_SEPARATOR . $file . ".php";
                  if (file_exists($fileName)) {
                    // eval file and look for translation.
                    $phrases = eval(str_replace(["<?php", "?>"], ["", ""], file_get_contents($fileName)));
                    if (!array_key_exists($phrase, $phrases)) {
                        $this->line(trim($line));
                        $this->error("'" . $phrase . "' is not translated in " . $fileName);
                        $problem++;
                    }
                  } else {
                    $this->line(trim($line));
                    $this->error("file '" . $fileName . "' does not exist!");
                    $problem++;
                  }
                }
              } else {
                $this->line(trim($line));
                $this->info("phrase is not string-literal; skipped");
              }
            } else {
              $this->line(trim($line));
              $this->error("file is not specified; skipped");
              $problem++;
            }
        }

        if ($problem) {
            $this->error($problem . " problem detected");
        } else {
            $this->info("all phrases are translated");
        }
    }
}
