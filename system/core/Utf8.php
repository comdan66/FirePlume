<?php if (!defined('PATH')) exit ('不允許直接呼叫檔案！');

class FireUtf8 {

  public function __construct () {
    if (defined ('UTF8_ENABLED'))
      return;
    else
      if ((preg_match ('/./u', 'é') === 1) && function_exists ('iconv') && (ini_get ('mbstring.func_overload') != 1) && define ('UTF8_ENABLED', true))
        if (extension_loaded ('mbstring') && define ('MB_ENABLED', true)) mb_internal_encoding ('UTF-8');
        else define ('MB_ENABLED', false);
      else
        define('UTF8_ENABLED', false);
  }
  public static function removeInvisibleCharacters ($str, $urlEncoded = TRUE) {
    $nonDisplayables = array ();

    if ($urlEncoded)
      array_push ($nonDisplayables, '/%0[0-8bcef]/', '/%1[0-9a-f]/');

    array_push ($nonDisplayables, '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S');

    do {
      $str = preg_replace ($nonDisplayables, '', $str, -1, $count);
    } while ($count);

    return $str;
  }

  // public function clean_string ($str) {
  //   if ($this->_is_ascii ($str) === false)
  //     $str = @iconv ('UTF-8', 'UTF-8//IGNORE', $str);

  //   return $str;
  // }

  // public function safe_ascii_for_xml ($str) {
  //   return FireUtf8::removeInvisibleCharacters ($str, false);
  // }


  // public function convert_to_utf8($str, $encoding) {
  //   if (function_exists ('iconv'))
  //     return @iconv ($encoding, 'UTF-8', $str);
  //   elseif (function_exists ('mb_convert_encoding'))
  //     return @mb_convert_encoding ($str, 'UTF-8', $encoding);
  //   else
  //     return false;
  // }

  // private function _is_ascii ($str) {
  //   return (preg_match ('/[^\x00-\x7F]/S', $str) == 0);
  // }
}
