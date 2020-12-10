<?php

namespace App\Console\Commands;

use App\Facades\OcrService as Ocr;
use Illuminate\Console\Command;

class OpticalCharacterRecognition extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recognize:image ' .
                          '{--tokenize : tokenize to keyword}' .
                          '{file : file to be processed}'
                        ;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'perform OCR on PDF/image file';

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
      $filePath = $this->argument('file');
      $this->info(" result :\n" . json_encode($this->option('tokenize') ? Ocr::tokenizedResult($filePath) : Ocr::rawResult($filePath)));
    }
}
