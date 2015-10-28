<?php if (!defined('PATH')) exit ('不允許直接呼叫檔案！');

class Config {
    private static $files = array ();

    public static function get () {
        if (!($args = array_filter (func_get_args ())))
            return null;

        $file = array_shift ($args);

        if (isset (self::$files[$file]))
            return self::pick (self::$files[$file], $args);

        $path = F_CFG . $file . EXT;
        if (is_readable ($path))
            $data = include_once $path;
        else
            $data = null;

        self::$files[$file] = $data;

        return self::pick (self::$files[$file], $args);
    }
    private static function pick ($data, $keys) {
        if (($key = array_shift ($keys)) === null)
            return $data;

        if (!isset ($data[$key]))
            return null;

        if (!count($keys))
            return $data[$key];

        if (count($keys))
            return self::pick ($data[$key], $keys);
    }
}