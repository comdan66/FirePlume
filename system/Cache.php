<?php if (!defined ('PATH')) exit ('不允許直接呼叫檔案！');

class Cache {
  private static $className;

  public static function className () {
    if (self::$className !== null) return self::$className;

    self::$className = Config::get ('cache', 'driver');

    require_once F_SYS . 'CacheDriver' . DIRECTORY_SEPARATOR . self::$className . EXT;

    return self::$className;
  }
  public static function get ($path, $expire, $callback, $key = 'general') {
    $className = self::className ();
    $path = implode (DIRECTORY_SEPARATOR, Config::get ('cache', $className, 'paths', $key)) . DIRECTORY_SEPARATOR . trim ($path, DIRECTORY_SEPARATOR);

    if (($data = unserialize ($className::read ($path))) && (($data['time'] + $data['expire']) > time ()))
      return $data['content'];
    
    $data = $callback ();

    $className::write ($path, serialize (array (
          'time'     => time (),
          'expire'   => $expire,
          'content'     => $data
        )));

    return $data;
  }
}