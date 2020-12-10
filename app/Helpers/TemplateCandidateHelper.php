<?php

// a global template view prioritization mechanism
function generateTemplateCandidates($name)
{
  $templateCandidates = array_wrap($name);

  if (env('COMPANY_MNEMONIC')) {
    $nameArray = explode(".", $name);
    array_splice($nameArray, -1, 0, [ env('COMPANY_MNEMONIC') ]);
    array_unshift($templateCandidates, implode(".", $nameArray));
  }

  return $templateCandidates;
}

// priority customized template, if any, over default one
function prioritizeCustomizedTemplateOverDefault($dotNotation) {
  $dotNotations = generateTemplateCandidates($dotNotation);
  if (view()->exists($dotNotations[0])) {
    return $dotNotations[0];
  }
  return $dotNotations[1];
}

?>
