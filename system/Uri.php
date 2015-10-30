<?php if (!defined ('PATH')) exit ('不允許直接呼叫檔案！');

class FireUri {
  private static $uriString = '';
  private static $uris = array ();

  public static function fetchUri () {
    if ((php_sapi_name () == 'cli') || defined ('STDIN'))
      return self::setUriString (implode ('/', self::parseCliArgs ()));

    if ($uri = self::detectUri ())
      return self::setUriString ($uri);

    if ((trim ($path = isset ($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : @getenv ('PATH_INFO'), '/') != '') && ($path != '/index.php'))
      return self::setUriString ($path);
    
    if (trim ($path = (isset ($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : @getenv ('QUERY_STRING'), '/') != '')
      return self::setUriString ($path);

    if (is_array ($_GET) && (count ($_GET) == 1) && (trim (key ($_GET), '/') != ''))
      return self::setUriString ($_GET);

    return self::setUriString ('');
  }
  private static function setUriString ($str) {
    $str = removeInvisibleCharacters ($str, FALSE);
    self::$uriString = ($str == '/') ? '' : $str;
    
    self::$uris = array ();
    foreach (explode ('/', preg_replace('|/*(.+?)/*$|', '\\1', self::getUriString ())) as $val)
      if (($val = trim (self::filterUri ($val))) != '') array_push (self::$uris, urldecode ($val));

    return self::getUriString ();
  }
  private static function filterUri ($str) {
    if ($str != '')
      if (!preg_match ('|^[' . str_replace (array ('\\-', '\-'), '-', preg_quote ('a-z 0-9~%.:_\-', '-')) . ']+$|i', $str))
        exit ('網址中有不合法的字串！');

    $bad  = array ('$', '(', ')', '%28', '%29');
    $good = array ('&#36;', '&#40;', '&#41;', '&#40;', '&#41;');

    return str_replace ($bad, $good, $str);
  }
  public static function getUriString () {
    return self::$uriString;
  }
  public static function getUris () {
    return self::$uris;
  }
  public static function parseCliArgs () {
    return count ($args = array_slice ($_SERVER['argv'], 1)) < 2 ? array_merge ($args, array ('index')) : $args;
  }
  private static function detectUri () {
    if (!isset ($_SERVER['REQUEST_URI']) || !isset ($_SERVER['SCRIPT_NAME'])) return '';

    if (strpos ($uri = $_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']) === 0) $uri = substr ($uri, strlen ($_SERVER['SCRIPT_NAME']));
    elseif (strpos ($uri, dirname ($_SERVER['SCRIPT_NAME'])) === 0) $uri = substr ($uri, strlen (dirname ($_SERVER['SCRIPT_NAME'])));

    if (strncmp ($uri, '?/', 2) === 0) $uri = substr ($uri, 2);

    $parts = preg_split ('#\?#i', $uri, 2);
    $uri = $parts[0];

    if (isset ($parts[1])) { $_SERVER['QUERY_STRING'] = $parts[1]; parse_str ($_SERVER['QUERY_STRING'], $_GET); }
    else { $_SERVER['QUERY_STRING'] = ''; $_GET = array (); }

    if (($uri == '/') || empty ($uri)) return '/';

    $uri = parse_url ($uri, PHP_URL_PATH);
    return str_replace (array ('//', '../'), '/', trim ($uri, '/'));
  }





  // // --------------------------------------------------------------------

  // /**
  //  * Remove the suffix from the URL if needed
  //  *
  //  * @access  private
  //  * @return  void
  //  */
  // function _remove_url_suffix()
  // {
  //   if  ($this->config->item('url_suffix') != "")
  //   {
  //     $this->uri_string = preg_replace("|".preg_quote($this->config->item('url_suffix'))."$|", "", $this->uri_string);
  //   }
  // }

  // // --------------------------------------------------------------------

  // /**
  //  * Explode the URI Segments. The individual segments will
  //  * be stored in the $this->segments array.
  //  *
  //  * @access  private
  //  * @return  void
  //  */

  // // --------------------------------------------------------------------
  // /**
  //  * Re-index Segments
  //  *
  //  * This function re-indexes the $this->segment array so that it
  //  * starts at 1 rather than 0.  Doing so makes it simpler to
  //  * use functions like $this->uri->segment(n) since there is
  //  * a 1:1 relationship between the segment array and the actual segments.
  //  *
  //  * @access  private
  //  * @return  void
  //  */
  // function _reindex_segments()
  // {
  //   array_unshift($this->segments, NULL);
  //   array_unshift($this->rsegments, NULL);
  //   unset($this->segments[0]);
  //   unset($this->rsegments[0]);
  // }

  // // --------------------------------------------------------------------

  // /**
  //  * Fetch a URI Segment
  //  *
  //  * This function returns the URI segment based on the number provided.
  //  *
  //  * @access  public
  //  * @param integer
  //  * @param bool
  //  * @return  string
  //  */
  // function segment($n, $no_result = FALSE)
  // {
  //   return ( ! isset($this->segments[$n])) ? $no_result : $this->segments[$n];
  // }

  // // --------------------------------------------------------------------

  // /**
  //  * Fetch a URI "routed" Segment
  //  *
  //  * This function returns the re-routed URI segment (assuming routing rules are used)
  //  * based on the number provided.  If there is no routing this function returns the
  //  * same result as $this->segment()
  //  *
  //  * @access  public
  //  * @param integer
  //  * @param bool
  //  * @return  string
  //  */
  // function rsegment($n, $no_result = FALSE)
  // {
  //   return ( ! isset($this->rsegments[$n])) ? $no_result : $this->rsegments[$n];
  // }

  // // --------------------------------------------------------------------

  // /**
  //  * Generate a key value pair from the URI string
  //  *
  //  * This function generates and associative array of URI data starting
  //  * at the supplied segment. For example, if this is your URI:
  //  *
  //  *  example.com/user/search/name/joe/location/UK/gender/male
  //  *
  //  * You can use this function to generate an array with this prototype:
  //  *
  //  * array (
  //  *      name => joe
  //  *      location => UK
  //  *      gender => male
  //  *     )
  //  *
  //  * @access  public
  //  * @param integer the starting segment number
  //  * @param array an array of default values
  //  * @return  array
  //  */
  // function uri_to_assoc($n = 3, $default = array())
  // {
  //   return $this->_uri_to_assoc($n, $default, 'segment');
  // }
  // /**
  //  * Identical to above only it uses the re-routed segment array
  //  *
  //  * @access  public
  //  * @param   integer the starting segment number
  //  * @param   array an array of default values
  //  * @return  array
  //  *
  //  */
  // function ruri_to_assoc($n = 3, $default = array())
  // {
  //   return $this->_uri_to_assoc($n, $default, 'rsegment');
  // }

  // // --------------------------------------------------------------------

  // *
  //  * Generate a key value pair from the URI string or Re-routed URI string
  //  *
  //  * @access  private
  //  * @param integer the starting segment number
  //  * @param array an array of default values
  //  * @param string  which array we should use
  //  * @return  array
   
  // function _uri_to_assoc($n = 3, $default = array(), $which = 'segment')
  // {
  //   if ($which == 'segment')
  //   {
  //     $total_segments = 'total_segments';
  //     $segment_array = 'segment_array';
  //   }
  //   else
  //   {
  //     $total_segments = 'total_rsegments';
  //     $segment_array = 'rsegment_array';
  //   }

  //   if ( ! is_numeric($n))
  //   {
  //     return $default;
  //   }

  //   if (isset($this->keyval[$n]))
  //   {
  //     return $this->keyval[$n];
  //   }

  //   if ($this->$total_segments() < $n)
  //   {
  //     if (count($default) == 0)
  //     {
  //       return array();
  //     }

  //     $retval = array();
  //     foreach ($default as $val)
  //     {
  //       $retval[$val] = FALSE;
  //     }
  //     return $retval;
  //   }

  //   $segments = array_slice($this->$segment_array(), ($n - 1));

  //   $i = 0;
  //   $lastval = '';
  //   $retval  = array();
  //   foreach ($segments as $seg)
  //   {
  //     if ($i % 2)
  //     {
  //       $retval[$lastval] = $seg;
  //     }
  //     else
  //     {
  //       $retval[$seg] = FALSE;
  //       $lastval = $seg;
  //     }

  //     $i++;
  //   }

  //   if (count($default) > 0)
  //   {
  //     foreach ($default as $val)
  //     {
  //       if ( ! array_key_exists($val, $retval))
  //       {
  //         $retval[$val] = FALSE;
  //       }
  //     }
  //   }

  //   // Cache the array for reuse
  //   $this->keyval[$n] = $retval;
  //   return $retval;
  // }

  // // --------------------------------------------------------------------

  // /**
  //  * Generate a URI string from an associative array
  //  *
  //  *
  //  * @access  public
  //  * @param array an associative array of key/values
  //  * @return  array
  //  */
  // function assoc_to_uri($array)
  // {
  //   $temp = array();
  //   foreach ((array)$array as $key => $val)
  //   {
  //     $temp[] = $key;
  //     $temp[] = $val;
  //   }

  //   return implode('/', $temp);
  // }

  // // --------------------------------------------------------------------

  // /**
  //  * Fetch a URI Segment and add a trailing slash
  //  *
  //  * @access  public
  //  * @param integer
  //  * @param string
  //  * @return  string
  //  */
  // function slash_segment($n, $where = 'trailing')
  // {
  //   return $this->_slash_segment($n, $where, 'segment');
  // }

  // // --------------------------------------------------------------------

  // /**
  //  * Fetch a URI Segment and add a trailing slash
  //  *
  //  * @access  public
  //  * @param integer
  //  * @param string
  //  * @return  string
  //  */
  // function slash_rsegment($n, $where = 'trailing')
  // {
  //   return $this->_slash_segment($n, $where, 'rsegment');
  // }

  // // --------------------------------------------------------------------

  // /**
  //  * Fetch a URI Segment and add a trailing slash - helper function
  //  *
  //  * @access  private
  //  * @param integer
  //  * @param string
  //  * @param string
  //  * @return  string
  //  */
  // function _slash_segment($n, $where = 'trailing', $which = 'segment')
  // {
  //   $leading  = '/';
  //   $trailing = '/';

  //   if ($where == 'trailing')
  //   {
  //     $leading  = '';
  //   }
  //   elseif ($where == 'leading')
  //   {
  //     $trailing = '';
  //   }

  //   return $leading.$this->$which($n).$trailing;
  // }

  // // --------------------------------------------------------------------

  // /**
  //  * Segment Array
  //  *
  //  * @access  public
  //  * @return  array
  //  */
  // function segment_array()
  // {
  //   return $this->segments;
  // }

  // // --------------------------------------------------------------------

  // /**
  //  * Routed Segment Array
  //  *
  //  * @access  public
  //  * @return  array
  //  */
  // function rsegment_array()
  // {
  //   return $this->rsegments;
  // }

  // // --------------------------------------------------------------------

  // /**
  //  * Total number of segments
  //  *
  //  * @access  public
  //  * @return  integer
  //  */
  // function total_segments()
  // {
  //   return count($this->segments);
  // }

  // // --------------------------------------------------------------------

  // /**
  //  * Total number of routed segments
  //  *
  //  * @access  public
  //  * @return  integer
  //  */
  // function total_rsegments()
  // {
  //   return count($this->rsegments);
  // }

  // // --------------------------------------------------------------------

  // /**
  //  * Fetch the entire URI string
  //  *
  //  * @access  public
  //  * @return  string
  //  */
  // function uri_string()
  // {
  //   return $this->uri_string;
  // }


  // // --------------------------------------------------------------------

  // /**
  //  * Fetch the entire Re-routed URI string
  //  *
  //  * @access  public
  //  * @return  string
  //  */
  // function ruri_string()
  // {
  //   return '/'.implode('/', $this->rsegment_array());
  // }

}