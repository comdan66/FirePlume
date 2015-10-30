<?php if (!defined ('PATH')) exit ('不允許直接呼叫檔案！');

if (!function_exists ('removeInvisibleCharacters')) {
  function removeInvisibleCharacters ($str, $urlEncoded = true) {
    $nonDisplayables = array ();

    if ($urlEncoded)
      array_push ($nonDisplayables, '/%0[0-8bcef]/', '/%1[0-9a-f]/');

    array_push ($nonDisplayables, '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S');

    do {
      $str = preg_replace ($nonDisplayables, '', $str, -1, $count);
    } while ($count);

    return $str;
  }
}
if (!function_exists ('camelize')) {
  // @resource https://gist.github.com/troelskn/751517
  function camelize($scored) {
    return lcfirst (implode ('', array_map ('ucfirst', array_map ('strtolower', preg_split ('/[_-]/', $scored)))));
  }
}
if (!function_exists ('underscore')) {
  // @resource https://gist.github.com/troelskn/751517
  function underscore ($cameled, $key = '_') {
    return implode ($key, array_map ('strtolower', preg_split ('/([A-Z]{1}[^A-Z]*)/', $cameled, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY)));
  }
}
