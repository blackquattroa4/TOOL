<?php

namespace App;

use Illuminate\Support\Facades\Log;
use Illuminate\View\Compilers\BladeCompiler;

class MinifyBladeCompiler extends BladeCompiler
{

  // unfortunately there's buffer limitation (~5000) on both preg_replace() & preg_replace_callback(),
  // so I have to write my own comment removal function
  public function removeWithinContext($contents, $contextStart, $contextEnd, $delimiterStart, $delimiterEnd)
  {
    $result = "";
    $previousContextEndPos = 0;
    $contentLength = strlen($contents);

    do {
      $nextContextStartPos = empty($contextStart) ? $previousContextEndPos : stripos($contents, $contextStart, $previousContextEndPos);
      $nextContextEndPos = empty($contextEnd) ? $contentLength : stripos($contents, $contextEnd, $nextContextStartPos);
      if (($nextContextStartPos === false) || ($nextContextEndPos === false)) {
        $result .= substr($contents, $previousContextEndPos);
        break;
      } else {
// \Illuminate\Support\Facades\Log::info("\n" . $this->getPath() . "\n{" . $nextContextStartPos . "," . $nextContextEndPos . "}\n" . $contextStart . " " . $contextEnd . "\n" . $delimiterStart . " " . $delimiterEnd . "\n" . substr($contents, $nextContextStartPos, ($nextContextEndPos - $nextContextStartPos)));
        $result .= substr($contents, $previousContextEndPos, ($nextContextStartPos - $previousContextEndPos));
        $result .= preg_replace("/(" . $delimiterStart . ")((.|\s)*?)(" . $delimiterEnd . ")/im", "", substr($contents, $nextContextStartPos, ($nextContextEndPos - $nextContextStartPos)));
        $previousContextEndPos = $nextContextEndPos;
      }
    } while ($previousContextEndPos < $contentLength);

    return $result;
  }

  // override compile method
  public function compile($path = null)
  {

    if ($path) {
        $this->setPath($path);
    }

    if (! is_null($this->cachePath)) {
        $contents = $this->compileString($this->files->get($this->getPath()));

        if (env('MINIFY_HTTP_RESPONSE')) {
          // remove HTML comment
          $contents = $this->removeWithinContext($contents, null, null, "<!--", "-->");
          // remove CSS comment
          $contents = $this->removeWithinContext($contents, "<style", "</style>", "\\/\\*", "\\*\\/");
          // remove JS block comment
          $contents = $this->removeWithinContext($contents, "<script", "</script>", "\\/\\*", "\\*\\/");
          // remove JS line comment
          $contents = $this->removeWithinContext($contents, "<script", "</script>", "\\/\\/", "\n");
          // compress space between HTML tags
          $contents = preg_replace("/(>)(\s*)(<)/m", "$1$3", $contents);
          // compress space in CSS/JS
          $contents = preg_replace("/(\n\s*)/m", " ", $contents);
        }

        if (! empty($this->getPath())) {
            $contents = $this->appendFilePath($contents);
        }

        $this->files->put(
            $this->getCompiledPath($this->getPath()), $contents
        );
    }
  }

}
