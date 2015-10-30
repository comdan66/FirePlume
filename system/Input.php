<?php if (!defined ('PATH')) exit ('不允許直接呼叫檔案！');

class Input {
  private static $ip = null;
  private static $userAgent = null;
  private static $headers = null;

  private static function fetchFromArray (&$method, $index = '', $isCleanXss = false) {
    if ($index === null)
      return $isCleanXss === true ? Security::cleanXss ($method) : $method;

    if (!isset ($method[$index]))
      return null;

    return $isCleanXss === true ? Security::cleanXss ($method[$index]) : $method[$index];
  }

  public static function get ($index = null, $isCleanXss = false) {
    if (($index === null) && !empty ($_GET)) {
      $get = array ();
      foreach (array_keys ($_GET) as $key)
        $get[$key] = self::fetchFromArray ($_GET, $key, $isCleanXss);
      return $get;
    }
    return self::fetchFromArray ($_GET, $index, $isCleanXss);
  }
  public static function post ($index = null, $isCleanXss = false) {
    if (($index === null) && !empty ($_POST)) {
      $post = array ();
      foreach (array_keys($_POST) as $key)
        $post[$key] = self::fetchFromArray ($_POST, $key, $isCleanXss);
      return $post;
    }
    return self::fetchFromArray ($_POST, $index, $isCleanXss);
  }
  public static function cookie ($index = null, $isCleanXss = false) {
    return self::fetchFromArray ($_COOKIE, $index, $isCleanXss);
  }

  public static function server ($index = null, $isCleanXss = false) {
    return self::fetchFromArray ($_SERVER, $index, $isCleanXss);
  }


  private static function validIpv4 ($ip) {
    $ipSegments = explode ('.', $ip);

    if (count ($ipSegments) !== 4)
      return false;
    
    if ($ipSegments[0][0] == '0')
      return false;

    foreach ($ipSegments as $ipSegment)
      if (($ipSegment == '') || preg_match ('/[^0-9]/', $ipSegment) || ($ipSegment > 255) || (strlen ($ipSegment) > 3))
        return false;

    return true;
  }

  private static function validIpv6 ($str) {
    $groups = 8;
    $collapsed = false;

    $chunks = array_filter (preg_split ('/(:{1,2})/', $str, NULL, PREG_SPLIT_DELIM_CAPTURE));

    if ((current ($chunks) == ':') || (end ($chunks) == ':'))
      return false;

    if (strpos (end ($chunks), '.') !== false) {
      $ipv4 = array_pop ($chunks);
      if (!self::validIpv4 ($ipv4))
        return false;
      $groups--;
    }

    while ($seg = array_pop ($chunks)) {
      if ($seg[0] == ':') {
        if (--$groups == 0) return false;
        if (strlen ($seg) > 2) return false;
        if ($seg == '::') {
          if ($collapsed) return false; 
          $collapsed = true;
        }
      } elseif (preg_match ('/[^0-9a-f]/i', $seg) || (strlen ($seg) > 4)) {
        return false;
      }
    }

    return $collapsed || ($groups == 1);
  }

  public static function validIp ($ip, $which = '') {
    $which = strtolower ($which);

    if (is_callable ('filter_var')) {
      switch ($which) {
        case 'Ipv4':
          $flag = FILTER_FLAG_IPV4;
          break;
        case 'Ipv6':
          $flag = FILTER_FLAG_IPV6;
          break;
        default:
          $flag = '';
          break;
      }

      return (bool)filter_var ($ip, FILTER_VALIDATE_IP, $flag);
    }

    if (($which !== 'Ipv6') && ($which !== 'Ipv4')) {
      if (strpos ($ip, ':') !== false)
        $which = 'Ipv6';
      elseif (strpos ($ip, '.') !== false)
        $which = 'Ipv4';
      else
        return false;
    }

    $func = 'valid' . $which;
    return self::$func ($ip);
  }
  public static function ip () {
    if (self::$ip !== null)
      return self::$ip;

    $proxyIps = ''; //e.g. '10.0.1.200,10.0.1.201'
    if ($proxyIps) {
      $proxyIps = explode (',', str_replace (' ', '', $proxyIps));

      foreach (array ('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_CLIENT_IP', 'HTTP_X_CLUSTER_CLIENT_IP') as $header)
        if (($spoof = self::server ($header)) !== false) {
          if (strpos ($spoof, ',') !== false) {
            $spoof = explode (',', $spoof, 2);
            $spoof = array_shift ($spoof);
          }

          if (!self::validIp ($spoof))
            $spoof = false;
          else
            break;
        }

      self::$ip = ($spoof !== false && in_array ($_SERVER['REMOTE_ADDR'], $proxyIps, true)) ? $spoof : $_SERVER['REMOTE_ADDR'];
    } else {
      self::$ip = $_SERVER['REMOTE_ADDR'];
    }

    if (!self::validIp (self::$ip))
      self::$ip = '0.0.0.0';

    return self::$ip;
  }

  public static function userAgent () {
    if (self::$userAgent !== null)
      return self::$userAgent;

    return self::$userAgent = !isset ($_SERVER['HTTP_USER_AGENT']) ? false : $_SERVER['HTTP_USER_AGENT'];
  }
  public static function isAjaxRequest () {
    return self::server ('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest';
  }
  public static function isCliRequest () {
    return (php_sapi_name () === 'cli') || defined ('STDIN');
  }

  public static function headers ($index = null, $isCleanXss = false) {
    if (self::$headers !== null) {
      self::$headers = $isCleanXss ? Security::cleanXss (self::$headers) : self::$headers;
      return $index ? isset (self::$headers[$index]) ? self::$headers[$index] : null : self::$headers;
    }

    if (function_exists ('apache_request_headers')) {
      $headers = apache_request_headers ();
    } else {
      $headers['Content-Type'] = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : @getenv ('CONTENT_TYPE');

      foreach ($_SERVER as $key => $val)
        if (strncmp ($key, 'HTTP_', 5) === 0)
          $headers[substr ($key, 5)] = self::fetchFromArray($_SERVER, $key, false);
    }

    self::$headers = array ();
    foreach ($headers as $key => $val)
      self::$headers[str_replace (' ', '-', ucwords (str_replace ('_', ' ', strtolower ($key))))] = $val;

    return self::headers ($index, $isCleanXss);
  }





  // var $ip_address       = false;
  // var $user_agent       = false;
  // var $_allow_get_array   = true;
  // var $_standardize_newlines  = true;
  // var $_enable_xss      = false;
  // var $_enable_csrf     = false;
  // protected $headers      = array();

  // public function __construct()
  // {
  //   log_message('debug', "Input Class Initialized");

  //   $this->_allow_get_array = (config_item('allow_get_array') === true);
  //   $this->_enable_xss    = (config_item('global_xss_filtering') === true);
  //   $this->_enable_csrf   = (config_item('csrf_protection') === true);

  //   global $SEC;
  //   $this->security =& $SEC;

  //   // Do we need the UTF-8 class?
  //   if (UTF8_ENABLED === true)
  //   {
  //     global $UNI;
  //     $this->uni =& $UNI;
  //   }

  //   // Sanitize global arrays
  //   $this->_sanitize_globals();
  // }







  // function set_cookie($name = '', $value = '', $expire = '', $domain = '', $path = '/', $prefix = '', $secure = false)
  // {
  //   if (is_array($name))
  //   {
  //     // always leave 'name' in last place, as the loop will break otherwise, due to $$item
  //     foreach (array('value', 'expire', 'domain', 'path', 'prefix', 'secure', 'name') as $item)
  //     {
  //       if (isset($name[$item]))
  //       {
  //         $$item = $name[$item];
  //       }
  //     }
  //   }

  //   if ($prefix == '' AND config_item('cookie_prefix') != '')
  //   {
  //     $prefix = config_item('cookie_prefix');
  //   }
  //   if ($domain == '' AND config_item('cookie_domain') != '')
  //   {
  //     $domain = config_item('cookie_domain');
  //   }
  //   if ($path == '/' AND config_item('cookie_path') != '/')
  //   {
  //     $path = config_item('cookie_path');
  //   }
  //   if ($secure == false AND config_item('cookie_secure') != false)
  //   {
  //     $secure = config_item('cookie_secure');
  //   }

  //   if ( ! is_numeric($expire))
  //   {
  //     $expire = time() - 86500;
  //   }
  //   else
  //   {
  //     $expire = ($expire > 0) ? time() + $expire : 0;
  //   }

  //   setcookie($prefix.$name, $value, $expire, $path, $domain, $secure);
  // }




  // function _sanitize_globals()
  // {
  //   // It would be "wrong" to unset any of these GLOBALS.
  //   $protected = array('_SERVER', '_GET', '_POST', '_FILES', '_REQUEST',
  //             '_SESSION', '_ENV', 'GLOBALS', 'HTTP_RAW_POST_DATA',
  //             'system_folder', 'application_folder', 'BM', 'EXT',
  //             'CFG', 'URI', 'RTR', 'OUT', 'IN');

  //   // Unset globals for securiy.
  //   // This is effectively the same as register_globals = off
  //   foreach (array($_GET, $_POST, $_COOKIE) as $global)
  //   {
  //     if ( ! is_array($global))
  //     {
  //       if ( ! in_array($global, $protected))
  //       {
  //         global $$global;
  //         $$global = NULL;
  //       }
  //     }
  //     else
  //     {
  //       foreach ($global as $key => $val)
  //       {
  //         if ( ! in_array($key, $protected))
  //         {
  //           global $$key;
  //           $$key = NULL;
  //         }
  //       }
  //     }
  //   }

  //   // Is $_GET data allowed? If not we'll set the $_GET to an empty array
  //   if ($this->_allow_get_array == false)
  //   {
  //     $_GET = array();
  //   }
  //   else
  //   {
  //     if (is_array($_GET) AND count($_GET) > 0)
  //     {
  //       foreach ($_GET as $key => $val)
  //       {
  //         $_GET[$this->_clean_input_keys($key)] = $this->_clean_input_data($val);
  //       }
  //     }
  //   }

  //   // Clean $_POST Data
  //   if (is_array($_POST) AND count($_POST) > 0)
  //   {
  //     foreach ($_POST as $key => $val)
  //     {
  //       // $_POST[$this->_clean_input_keys($key)] = $this->_clean_input_data($val);
  //     }
  //   }

  //   // Clean $_COOKIE Data
  //   if (is_array($_COOKIE) AND count($_COOKIE) > 0)
  //   {
  //     // Also get rid of specially treated cookies that might be set by a server
  //     // or silly application, that are of no use to a CI application anyway
  //     // but that when present will trip our 'Disallowed Key Characters' alarm
  //     // http://www.ietf.org/rfc/rfc2109.txt
  //     // note that the key names below are single quoted strings, and are not PHP variables
  //     unset($_COOKIE['$Version']);
  //     unset($_COOKIE['$Path']);
  //     unset($_COOKIE['$Domain']);

  //     foreach ($_COOKIE as $key => $val)
  //     {
  //       $_COOKIE[$this->_clean_input_keys($key)] = $this->_clean_input_data($val);
  //     }
  //   }

  //   // Sanitize PHP_SELF
  //   $_SERVER['PHP_SELF'] = strip_tags($_SERVER['PHP_SELF']);


  //   // CSRF Protection check on HTTP requests
  //   if ($this->_enable_csrf == true && ! $this->is_cli_request())
  //   {
  //     $this->security->csrf_verify();
  //   }

  //   log_message('debug', "Global POST and COOKIE data sanitized");
  // }


  // function _clean_input_data($str)
  // {
  //   if (is_array($str))
  //   {
  //     $new_array = array();
  //     foreach ($str as $key => $val)
  //     {
  //       $new_array[$this->_clean_input_keys($key)] = $this->_clean_input_data($val);
  //     }
  //     return $new_array;
  //   }

  //   /* We strip slashes if magic quotes is on to keep things consistent

  //      NOTE: In PHP 5.4 get_magic_quotes_gpc() will always return 0 and
  //      it will probably not exist in future versions at all.
  //   */
  //   if ( ! is_php('5.4') && get_magic_quotes_gpc())
  //   {
  //     $str = stripslashes($str);
  //   }

  //   // Clean UTF-8 if supported
  //   if (UTF8_ENABLED === true)
  //   {
  //     $str = $this->uni->clean_string($str);
  //   }

  //   // Remove control characters
  //   $str = remove_invisible_characters($str);

  //   // Should we filter the input data?
  //   if ($this->_enable_xss === true)
  //   {
  //     $str = $this->security->xss_clean($str);
  //   }

  //   // Standardize newlines if needed
  //   if ($this->_standardize_newlines == true)
  //   {
  //     if (strpos($str, "\r") !== false)
  //     {
  //       $str = str_replace(array("\r\n", "\r", "\r\n\n"), PHP_EOL, $str);
  //     }
  //   }

  //   return $str;
  // }

  // function _clean_input_keys($str)
  // {
  //   if ( ! preg_match("/^[a-z0-9:_\/-]+$/i", $str))
  //   {
  //     exit('Disallowed Key Characters.');
  //   }

  //   // Clean UTF-8 if supported
  //   if (UTF8_ENABLED === true)
  //   {
  //     $str = $this->uni->clean_string($str);
  //   }

  //   return $str;
  // }



}