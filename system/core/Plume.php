<?php if (!defined('PATH')) exit ('不允許直接呼叫檔案！');

  // set_error_handler ('_exception_handler');

  if ((version_compare (PHP_VERSION, '5.5') < 0))
    exit ('PHP 版本錯誤！');

  // 設定反應時間長度
  if ((function_exists ('set_time_limit') == true) && (@ini_get ('safe_mode') == 0))
    @set_time_limit (300);

  require_once F_SYS . 'core' . DIRECTORY_SEPARATOR . 'Config.php';
  require_once F_SYS . 'core' . DIRECTORY_SEPARATOR . 'Utf8.php';
  require_once F_SYS . 'core' . DIRECTORY_SEPARATOR . 'Uri.php';
  require_once F_SYS . 'core' . DIRECTORY_SEPARATOR . 'Router.php';
  
  $utf8 = new FireUtf8 ();
  require_once F_CFG . 'router.php';
  FireRouter::fetch ();


function convert () { $size = memory_get_usage(); $unit = array('b','kb','mb','gb','tb','pb'); return @round ($size / pow (1024, ($i = floor (log ($size, 1024)))), 2) . ' ' . $unit[$i]; } echo convert ();
exit ();

  require_once BASEPATH.'helpers/file_helper.php';



  $OUT =& load_class('Output', 'core');

/*
 * ------------------------------------------------------
 *  Is there a valid cache file?  If so, we're done...
 * ------------------------------------------------------
 */
  if ($EXT->_call_hook('cache_override') === FALSE)
  {
    if ($OUT->_display_cache($CFG, $URI) == TRUE)
    {
      exit;
    }
  }

/*
 * -----------------------------------------------------
 * Load the security class for xss and csrf support
 * -----------------------------------------------------
 */
  $SEC =& load_class('Security', 'core');

/*
 * ------------------------------------------------------
 *  Load the Input class and sanitize globals
 * ------------------------------------------------------
 */
  $IN =& load_class('Input', 'core');

/*
 * ------------------------------------------------------
 *  Load the Language class
 * ------------------------------------------------------
 */
  $LANG =& load_class('Lang', 'core');

/*
 * ------------------------------------------------------
 *  Load the app controller and local controller
 * ------------------------------------------------------
 *
 */
  // Load the base controller class
  require BASEPATH.'core/Controller.php';

  function &get_instance()
  {
    return CI_Controller::get_instance();
  }

  if (($controllers = get_filenames (APPPATH.'core/controllers/')) && sort ($controllers)) {
    foreach ($controllers as $controller) {
      if ((('.' . pathinfo ($controller, PATHINFO_EXTENSION)) == EXT) && file_exists (APPPATH . 'core/controllers/' . $controller)) {
        require APPPATH . 'core/controllers/' . $controller;
      }
    }
  } else {
    if (file_exists(APPPATH.'core/'.$CFG->config['subclass_prefix'].'Controller.php')) {
      require APPPATH.'core/'.$CFG->config['subclass_prefix'].'Controller.php';
    }
  }

  // Load the local application controller
  // Note: The Router class automatically validates the controller path using the router->_validate_request().
  // If this include fails it means that the default controller in the Routes.php file is not resolving to something valid.
  if ( ! file_exists(APPPATH.'controllers/'.$RTR->fetch_directory().$RTR->fetch_class().'.php'))
  {
    show_error('Unable to load your default controller. Please make sure the controller specified in your Routes.php file is valid.');
  }

  include_once (APPPATH.'controllers/'.$RTR->fetch_directory().$RTR->fetch_class().'.php');

  // Set a mark point for benchmarking
  $BM->mark('loading_time:_base_classes_end');

/*
 * ------------------------------------------------------
 *  Security check
 * ------------------------------------------------------
 *
 *  None of the functions in the app controller or the
 *  loader class can be called via the URI, nor can
 *  controller functions that begin with an underscore
 */
  $class  = $RTR->fetch_class();
  $method = $RTR->fetch_method();

  if (!class_exists ($class) || (strncmp ($method, '_', 1) == 0) || in_array (strtolower ($method), array_map ('strtolower', get_class_methods ('CI_Controller'))))
    show_404 ();

/*
 * ------------------------------------------------------
 *  Is there a "pre_controller" hook?
 * ------------------------------------------------------
 */
  $EXT->_call_hook('pre_controller');

/*
 * ------------------------------------------------------
 *  Instantiate the requested controller
 * ------------------------------------------------------
 */
  // Mark a start point so we can benchmark the controller
  $BM->mark('controller_execution_time_( '.$class.' / '.$method.' )_start');

  $CI = new $class();

/*
 * ------------------------------------------------------
 *  Is there a "post_controller_constructor" hook?
 * ------------------------------------------------------
 */
  $EXT->_call_hook('post_controller_constructor');

/*
 * ------------------------------------------------------
 *  Call the requested method
 * ------------------------------------------------------
 */
  // Is there a "remap" function? If so, we call it instead
  if (method_exists ($CI, '_remap'))
    $CI->_remap ($method, array_slice ($URI->rsegments, 2));
  else {
    if (!in_array (strtolower ($method), array_map ('strtolower', get_class_methods ($CI))))
      show_404();

    call_user_func_array(array(&$CI, $method), array_slice($URI->rsegments, 2));
  }


  // Mark a benchmark end point
  $BM->mark('controller_execution_time_( '.$class.' / '.$method.' )_end');

/*
 * ------------------------------------------------------
 *  Is there a "post_controller" hook?
 * ------------------------------------------------------
 */
  $EXT->_call_hook('post_controller');

/*
 * ------------------------------------------------------
 *  Send the final rendered output to the browser
 * ------------------------------------------------------
 */
  if ($EXT->_call_hook('display_override') === FALSE)
  {
    $OUT->_display();
  }

/*
 * ------------------------------------------------------
 *  Is there a "post_system" hook?
 * ------------------------------------------------------
 */
  $EXT->_call_hook('post_system');

/*
 * ------------------------------------------------------
 *  Close the DB connection if one exists
 * ------------------------------------------------------
 */
  if (class_exists('CI_DB') AND isset($CI->db))
  {
    $CI->db->close();
  }


/* End of file CodeIgniter.php */
/* Location: ./system/core/CodeIgniter.php */