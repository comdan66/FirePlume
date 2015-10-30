<?php if (!defined ('PATH')) exit ('不允許直接呼叫檔案！');

class Security {
  private static $xssHash      = null;

  private static $neverAllowedStrings = array (
      'document.cookie' => '[removed]',
      'document.write'  => '[removed]',
      '.parentNode'     => '[removed]',
      '.innerHTML'      => '[removed]',
      'window.location' => '[removed]',
      '-moz-binding'    => '[removed]',
      '<!--'            => '&lt;!--',
      '-->'             => '--&gt;',
      '<![CDATA['       => '&lt;![CDATA[',
      '<comment>'       => '&lt;comment&gt;'
    );

  private static $neverAllowedRegex = array (
      'javascript\s*:',
      'expression\s*(\(|&\#40;)', // CSS and IE
      'vbscript\s*:', // IE, surprise!
      'Redirect\s+302',
      "([\"'])?data\s*:[^\\1]*?base64[^\\1]*?,[^\\1]*?\\1?"
    );


  // private $tokenName   = 'csrfTokenName';
  // private $cookieName  = 'csrfCookieName';
  // private $expire      = 7200;
  // private $csrfHash     = '';

  // public function __construct () {
  //   if (Config::get ('security', 'isEnable') === TRUE) {
  //     foreach (array ('tokenName', 'cookieName', 'expire') as $key)
  //       if (($val = Config::get ('security', $key)) != null)
  //         $this->$key = $val;

  //     if ($cookiePrefix = Config::get ('cookie', 'prefix'))
  //       $this->cookieName .= $cookiePrefix;

  //     $this->setCsrfHash();
  //   }
  // }

  // private function setCsrfHash () {
  //   if (!$this->csrfHash) {
  //     if (isset ($_COOKIE[$this->cookieName]) && (preg_match ('#^[0-9a-f]{32}$#iS', $_COOKIE[$this->cookieName]) === 1))
  //       return $this->csrfHash = $_COOKIE[$this->cookieName];
  //     return $this->csrfHash = md5 (uniqid (rand (), true));
  //   }

  //   return $this->csrfHash;
  // }
  // // --------------------------------------------------------------------

  // public function csrfVerify() {
  //   if (strtoupper ($_SERVER['REQUEST_METHOD']) !== 'POST')
  //     return $this->setCsrfCookie ();

  //   if (!isset ($_POST[$this->tokenName], $_COOKIE[$this->cookieName]))
  //     exit ('你剛才的請求是不允許的！');

  //   if ($_POST[$this->tokenName] != $_COOKIE[$this->cookieName])
  //     exit ('你剛才的請求是不允許的！');

  //   unset ($_POST[$this->tokenName]);

  //   unset ($_COOKIE[$this->cookieName]);
  //   $this->setCsrfHash();
  //   $this->setCsrfCookie();
  //   return $this;
  // }
  // public function setCsrfCookie () {
  //   $expire = time () + $this->expire;

  //   if (($secure = Config::get ('cookie', 'secure') === true ? 1 : 0) && (empty ($_SERVER['HTTPS']) || (strtolower ($_SERVER['HTTPS']) === 'off')))
  //     return false;

  //   setcookie ($this->cookieName, $this->csrfHash, $expire, Config::get ('cookie', 'path'), Config::get ('cookie', 'domain'), $secure);

  //   return $this;
  // }










  private static function xssHash () {
    if (self::$xssHash !== null)
      return self::$xssHash;

    mt_srand();
    return self::$xssHash = md5(time() + mt_rand(0, 1999999999));
  }
  private static function validateEntities ($str) {
    $str = preg_replace ('|\&([a-z\_0-9\-]+)\=([a-z\_0-9\-]+)|i', self::xssHash () . "\\1=\\2", $str);
    $str = preg_replace ('#(&\#?[0-9a-z]{2,})([\x00-\x20])*;?#i', "\\1;\\2", $str);
    $str = preg_replace ('#(&\#x?)([0-9A-F]+);?#i',"\\1\\2;",$str);
    $str = str_replace (self::xssHash (), '&', $str);

    return $str;
  }

  private static function convertAttribute ($match) {
    return str_replace (array ('>', '<', '\\'), array ('&gt;', '&lt;', '\\\\'), $match[0]);
  }
  private static function decodeEntity ($match) {
    return self::entityDecode ($match[0]);
  }
  private static function entityDecode ($str, $charset = 'UTF-8') {
    if (stristr ($str, '&') === false)
      return $str;

    $str = html_entity_decode ($str, ENT_COMPAT, $charset);
    $str = preg_replace ('~&#x(0*[0-9a-f]{2,5})~ei', 'chr(hexdec("\\1"))', $str);
    return preg_replace ('~&#([0-9]{2,4})~e', 'chr(\\1)', $str);
  }
  private static function doNeverAllowed ($str) {
    $str = str_replace (array_keys (self::$neverAllowedStrings), self::$neverAllowedStrings, $str);

    foreach (self::$neverAllowedRegex as $regex)
      $str = preg_replace ('#' . $regex . '#is', '[removed]', $str);

    return $str;
  }
  private static function compactExplodedWords ($matches) {
    return preg_replace ('/\s+/s', '', $matches[1]).$matches[2];
  }

  private static function filterAttributes ($str) {
    $out = '';

    if (preg_match_all ('#\s*[a-z\-]+\s*=\s*(\042|\047)([^\\1]*?)\\1#is', $str, $matches))
      foreach ($matches[0] as $match)
        $out .= preg_replace ("#/\*.*?\*/#s", '', $match);

    return $out;
  }
  private static function jsLinkRemoval ($match) {
    return str_replace ($match[1], preg_replace ('#href=.*?(alert\(|alert&\#40;|javascript\:|livescript\:|mocha\:|charset\=|window\.|document\.|\.cookie|<script|<xss|data\s*:)#si', '', self::filterAttributes (str_replace (array ('<', '>'), '', $match[1]))), $match[0]);
  }
  private static function jsImgRemoval ($match) {
    return str_replace ($match[1], preg_replace ('#src=.*?(alert\(|alert&\#40;|javascript\:|livescript\:|mocha\:|charset\=|window\.|document\.|\.cookie|<script|<xss|base64\s*,)#si', '', self::filterAttributes (str_replace (array ('<', '>'), '', $match[1]))), $match[0]);
  }

  private static function removeEvilAttributes ($str, $isImage) {
    $evilAttributes = array ('on\w*', 'style', 'xmlns', 'formaction');

    if ($isImage === true)
      unset($evilAttributes[array_search ('xmlns', $evilAttributes)]);

    do {
      $count = 0;
      $attribs = array ();

      preg_match_all ('/(' . implode ('|', $evilAttributes) . ')\s*=\s*([^\s>]*)/is', $str, $matches, PREG_SET_ORDER);

      foreach ($matches as $attr)
        array_push ($attribs, preg_quote ($attr[0], '/'));

      preg_match_all ("/(" . implode ('|', $evilAttributes) . ")\s*=\s*(\042|\047)([^\\2]*?)(\\2)/is",  $str, $matches, PREG_SET_ORDER);

      foreach ($matches as $attr)
        array_push ($attribs, preg_quote ($attr[0], '/'));

      if ($attribs)
        $str = preg_replace ("/<(\/?[^><]+?)([^A-Za-z<>\-])(.*?)(" . implode ('|', $attribs) . ")(.*?)([\s><])([><]*)/i", '<$1 $3$5$6$7', $str, -1, $count);
    } while ($count);

    return $str;
  }
  private static function sanitizeNaughtyHtml ($matches) {
    $str = '&lt;' . $matches[1] . $matches[2] . $matches[3];
    $str .= str_replace (array ('>', '<'), array ('&gt;', '&lt;'), $matches[4]);
    return $str;
  }
  public static function cleanXss ($str, $isImage = false) {
    if (is_array ($str)) {
      while (list ($key) = each ($str))
        $str[$key] = self::cleanXss ($str[$key]);
      return $str;
    }

    $str = removeInvisibleCharacters ($str);
    $str = self::validateEntities ($str);
    $str = rawurldecode ($str);
    $str = preg_replace_callback ("/[a-z]+=([\'\"]).*?\\1/si", array ('Security', 'convertAttribute'), $str);
    $str = preg_replace_callback ("/<\w+.*?(?=>|<|$)/si", array ('Security', 'decodeEntity'), $str);
    $str = removeInvisibleCharacters ($str);


    if (strpos ($str, "\t") !== false)
      $str = str_replace ("\t", ' ', $str);

    $convertedString = $str;

    $str = self::doNeverAllowed ($str);

    if ($isImage === true)
      $str = preg_replace ('/<\?(php)/i', "&lt;?\\1", $str);
    else
      $str = str_replace (array ('<?', '?'.'>'),  array('&lt;?', '?&gt;'), $str);

    $words = array (
      'javascript', 'expression', 'vbscript', 'script', 'base64',
      'applet', 'alert', 'document', 'write', 'cookie', 'window'
    );

    foreach ($words as $word) {
      $temp = '';
      for ($i = 0, $wordlen = strlen($word); $i < $wordlen; $i++)
        $temp .= substr ($word, $i, 1) . "\s*";
      $str = preg_replace_callback ('#(' . substr ($temp, 0, -3) . ')(\W)#is', array ('Security', 'compactExplodedWords'), $str);
    }

    do {
      $original = $str;

      if (preg_match ("/<a/i", $str))
        $str = preg_replace_callback ("#<a\s+([^>]*?)(>|$)#si", array ('Security', 'jsLinkRemoval'), $str);

      if (preg_match ("/<img/i", $str))
        $str = preg_replace_callback ("#<img\s+([^>]*?)(\s?/?>|$)#si", array ('Security', 'jsImgRemoval'), $str);

      if (preg_match ("/script/i", $str) || preg_match ("/xss/i", $str))
        $str = preg_replace ("#<(/*)(script|xss)(.*?)\>#si", '[removed]', $str);
    } while($original != $str);

    unset ($original);

    $str = self::removeEvilAttributes ($str, $isImage);

    $naughty = 'alert|applet|audio|basefont|base|behavior|bgsound|blink|body|embed|expression|form|frameset|frame|head|html|ilayer|iframe|input|isindex|layer|link|meta|object|plaintext|style|script|textarea|title|video|xml|xss';
    $str = preg_replace_callback ('#<(/*\s*)(' . $naughty . ')([^><]*)([><]*)#is', array ('Security', 'sanitizeNaughtyHtml'), $str);
    $str = preg_replace ('#(alert|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si', "\\1\\2&#40;\\3&#41;", $str);
    $str = self::doNeverAllowed ($str);

    if ($isImage === true)
      return ($str == $convertedString) ? true : false;

    return $str;
  }














  public function get_csrf_hash()
  {
    return $this->_csrf_hash;
  }

  public function get_csrf_token_name()
  {
    return $this->_csrf_token_name;
  }

  public function sanitize_filename($str, $relative_path = FALSE)
  {
    $bad = array(
      "../",
      "<!--",
      "-->",
      "<",
      ">",
      "'",
      '"',
      '&',
      '$',
      '#',
      '{',
      '}',
      '[',
      ']',
      '=',
      ';',
      '?',
      "%20",
      "%22",
      "%3c",    // <
      "%253c",  // <
      "%3e",    // >
      "%0e",    // >
      "%28",    // (
      "%29",    // )
      "%2528",  // (
      "%26",    // &
      "%24",    // $
      "%3f",    // ?
      "%3b",    // ;
      "%3d"   // =
    );

    if ( ! $relative_path)
    {
      $bad[] = './';
      $bad[] = '/';
    }

    $str = remove_invisible_characters($str, FALSE);
    return stripslashes(str_replace($bad, '', $str));
  }

}
