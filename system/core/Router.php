<?php if (!defined('PATH')) exit ('不允許直接呼叫檔案！');

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2015 OA Wu Design
 */

class Router {
  static $routers = array ();
  static $methods = array ('get', 'post', 'put', 'delete');

  public static function root ($controller) {
    $controller = array_filter (explode ('@', $controller), function ($t) { return $t || $t === '0'; });
    self::get('/', $controller[0] . (isset ($controller[1]) ? '@' . $controller[1] : ''));
  }

  public static function __callStatic ($name, $arguments) {
    if (in_array (strtolower ($name), self::$methods) && (count ($arguments) == 2)) {
      if (($group = array_filter (array_map (function ($trace) {
                    return isset ($trace['class']) && ($trace['class'] == 'Router') && isset ($trace['function']) && ($trace['function'] == 'group') && isset ($trace['type']) && ($trace['type'] == '::') && isset ($trace['args'][0]) ? $trace['args'][0] : null;
                  }, debug_backtrace (DEBUG_BACKTRACE_PROVIDE_OBJECT)))) && ($group = array_shift ($group)))
        $group = trim ($group, '/') . '/';
      else
        $group = '';

      $path = array_filter (explode ('/', $arguments[0]));
      $controller = array_filter (preg_split ('/[@,\(\)\s]+/', $arguments[1]), function ($t) { return $t || $t === '0'; });

      if (count ($controller) < 2)
        array_push ($controller, 'index');

      self::$routers[$name . ':' . trim ($group . implode ('/', $path), '/') . '/'] = $group . implode ('/', $controller);
    } else {
      show_error ("Route 使用方法錯誤!<br/>尚未定義: Route::" . $name . " 的方法!");
    }
  }

  public static function resource ($uris, $controller, $prefix = '') {
    $uris = is_string ($uris) ? array ($uris) : $uris;
    $c = count ($uris);
    $prefix = trim ($prefix, '/') . '/';

    self::get ($prefix . implode ('/(:id)/', $uris) . '/', $prefix . $controller . '@index');
    self::get ($prefix . implode ('/(:id)/', $uris) . '/(:id)', $prefix . $controller . '@show($1' . ($c > 1 ? ', ' . implode (', ', array_map (function ($a) { return '$' . $a; }, range (2, $c))) : '') . ')');
    self::get ($prefix . implode ('/(:id)/', $uris) . '/add', $prefix . $controller . '@add(' . ($c > 1 ? ', ' . implode (', ', array_map (function ($a) { return '$' . $a; }, range (1, $c - 1))) : '') . ')');
    self::post ($prefix . implode ('/(:id)/', $uris) . '/', $prefix . $controller . '@create(' . ($c > 1 ? ', ' . implode (', ', array_map (function ($a) { return '$' . $a; }, range (1, $c - 1))) : '') . ')');
    self::get ($prefix . implode ('/(:id)/', $uris) . '/(:id)' .  '/edit', $prefix . $controller . '@edit($1' . ($c > 1 ? ', ' . implode (', ', array_map (function ($a) { return '$' . $a; }, range (2, $c))) : '') . ')');
    self::put ($prefix . implode ('/(:id)/', $uris) . '/(:id)', $prefix . $controller . '@update($1' . ($c > 1 ? ', ' . implode (', ', array_map (function ($a) { return '$' . $a; }, range (2, $c))) : '') . ')');
    self::delete ($prefix . implode ('/(:id)/', $uris) . '/(:id)', $prefix . $controller . '@destroy($1' . ($c > 1 ? ', ' . implode (', ', array_map (function ($a) { return '$' . $a; }, range (2, $c))) : '') . ')');
  }

  public static function resourcePagination ($uris, $controller, $prefix = '') {
    if (($group = array_filter (array_map (function ($trace) {
                  return isset ($trace['class']) && ($trace['class'] == 'Router') && isset ($trace['function']) && ($trace['function'] == 'group') && isset ($trace['type']) && ($trace['type'] == '::') && isset ($trace['args'][0]) ? $trace['args'][0] : null;
                }, debug_backtrace (DEBUG_BACKTRACE_PROVIDE_OBJECT)))) && ($group = array_shift ($group)))
      $group = trim ($group, '/') . '/';
    else
      $group = '';

    $uris = is_string ($uris) ? array ($uris) : $uris;
    $c = count ($uris);
    $prefix = trim ($group . trim ($prefix, '/'), '/') . '/';

    self::get ($prefix . implode ('/(:id)/', $uris) . '/', $prefix . $controller . '@index(' . ($c > 1 ? implode (', ', array_map (function ($a) { return '$' . $a; }, range (1, $c - 1))) . ', ' : '') . '0)');
    self::get ($prefix . implode ('/(:id)/', $uris) . '/(:num)', $prefix . $controller . '@index($1' . ($c > 1 ? ', ' . implode (', ', array_map (function ($a) { return '$' . $a; }, range (2, $c))) : '') . ')');
    // self::get ($prefix . implode ('/(:id)/', $uris) . '/(:id)', $prefix . $controller . '@show($1' . ($c > 1 ? ', ' . implode (', ', array_map (function ($a) { return '$' . $a; }, range (2, $c))) : '') . ')');
    self::get ($prefix . implode ('/(:id)/', $uris) . '/add', $prefix . $controller . '@add(' . ($c > 1 ? ', ' . implode (', ', array_map (function ($a) { return '$' . $a; }, range (1, $c - 1))) : '') . ')');
    self::post ($prefix . implode ('/(:id)/', $uris) . '/', $prefix . $controller . '@create(' . ($c > 1 ? ', ' . implode (', ', array_map (function ($a) { return '$' . $a; }, range (1, $c - 1))) : '') . ')');
    self::get ($prefix . implode ('/(:id)/', $uris) . '/(:id)' .  '/edit', $prefix . $controller . '@edit($1' . ($c > 1 ? ', ' . implode (', ', array_map (function ($a) { return '$' . $a; }, range (2, $c))) : '') . ')');
    self::put ($prefix . implode ('/(:id)/', $uris) . '/(:id)', $prefix . $controller . '@update($1' . ($c > 1 ? ', ' . implode (', ', array_map (function ($a) { return '$' . $a; }, range (2, $c))) : '') . ')');
    self::delete ($prefix . implode ('/(:id)/', $uris) . '/(:id)', $prefix . $controller . '@destroy($1' . ($c > 1 ? ', ' . implode (', ', array_map (function ($a) { return '$' . $a; }, range (2, $c))) : '') . ')');
    self::post ($prefix . implode ('/(:id)/', $uris) . '/(:id)' .  '/sort', $prefix . $controller . '@sort($1' . ($c > 1 ? ', ' . implode (', ', array_map (function ($a) { return '$' . $a; }, range (2, $c))) : '') . ')');
  }
  public static function group ($prefix, $callback) {
    $callback ();
  }
  public static function getRouters () {
    return self::$routers;
  }

}



class FireRouter {

  private static $defaultUriString = null;
  private static $directory = array ();
  private static $uris = array ();
  private static $className = null;
  private static $methodName = null;

  public static function fetch () {
    self::$defaultUriString = isset (Router::getRouters ()['get:/']) ? Router::getRouters ()['get:/'] : null;

    if (!($uriString = FireUri::fetchUri ()))
      return self::gotoDefaultUriString ();

    self::parseRouter ();
  }

  private static function gotoDefaultUriString () {
    if (self::$defaultUriString === null)
      exit ('找不到預設的頁面，請確認 config/router.php 是否有設置預設頁面。');

    return self::setUris (explode ('/', self::$defaultUriString));
  }

  private static function setUris ($uris) {
    $uris = self::verifyUris ($uris);

    self::setClassName ($uris[0]);

    if (isset ($uris[1]))
      self::setMethodName ($uris[1]);
    else
      self::setMethodName ($uris[1] = 'index');

    return self::$uris = $uris;
  }

  private static function verifyUris ($uris) {

    for ($i = 0, $c = count ($uris) - 1; $i < $c; $i++) {
      if (file_exists (F_APP . implode (DIRECTORY_SEPARATOR, array_merge (array ('controller'), $i > 0 ? array_slice ($uris, 0, $i + 1) : array ($uris[$i]))) . EXT))
        return $uris;

      if (is_dir (F_APP . implode (DIRECTORY_SEPARATOR, array_merge (array ('controller'), array_slice ($uris, 0, $i + 1)))) && isset ($uris[$i + 1]) && file_exists (F_APP . implode (DIRECTORY_SEPARATOR, array_merge (array ('controller'), array_slice ($uris, 0, $i + 1), array ($uris[$i + 1] . EXT)))) && self::setDirectory (array_slice ($uris, 0, $i + 1)))
        return array_slice ($uris, $i + 1);
    }

    exit ('找不到對應的 controller！');
  }

  private static function setClassName ($className) {
    return self::$className = str_replace (array (DIRECTORY_SEPARATOR, '.'), '', $className);
  }
  private static function setMethodName ($methodName) {
    return self::$methodName = $methodName;
  }
  private static function setDirectory ($dir) {
    return self::$directory = str_replace (array (DIRECTORY_SEPARATOR, '.'), '', $dir);
  }

  private static function parseRouter () {
    if (isset ($_REQUEST['_method']) && in_array (strtolower ($_REQUEST['_method']), Route::$methods))
      $_SERVER['REQUEST_METHOD'] = $_REQUEST['_method'];

    $requestMethod = isset ($_SERVER['REQUEST_METHOD']) ? strtolower ($_SERVER['REQUEST_METHOD']) : 'get';
    $uri = implode ('/', FireUri::getUris ()) . '/';

    if (isset (Router::getRouters ()[$requestMethod . ':' . $uri]) && is_string (Router::getRouters ()[$requestMethod . ':' . $uri]))
      return self::setUris (explode ('/', Router::getRouters ()[$requestMethod . ':' . $uri]));

    foreach (Router::getRouters () as $key => $val) {
      $key = str_replace (':any', '.+', str_replace (':num', '[0-9]+', str_replace (':id', '[0-9]+', $key)));

      if (preg_match ('#^' . $key . '$#', $requestMethod . ':' . $uri)) {
        if ((strpos ($val, '$') !== false) && (strpos ($key, '(') !== false))
          $val = preg_replace ('#^'.$key.'$#', $val, $requestMethod . ':' . $uri);

        return self::setUris (explode ('/', $val));
      }
    }

    return self::setUris (FireUri::getUris ());
  }
}