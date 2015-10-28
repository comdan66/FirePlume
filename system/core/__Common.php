<?php if (!defined('PATH')) exit ('不允許直接呼叫檔案！');

class FireCommon {
  private static $classes = array ();

  public static function loadClass ($class, $directory) {
    $className = 'Fire' . $class;

    if (isset (self::$classes[$className]))
      return self::$classes[$className];

    $directory = rtrim ($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    require_once $directory . $class . EXT;

    return self::$classes[$className] = new $className ();
  }
}
// if (!function_exists ('loadClass')) {
//   function &loadClass ($class, $directory) {
//     $name = FALSE;

//     // Look for the class first in the local application/libraries folder
//     // then in the native system/libraries folder
//     foreach (array(APPPATH, BASEPATH) as $path)
//     {
//       if (file_exists($path.$directory.'/'.$class.'.php'))
//       {
//         $name = $prefix.$class;

//         if (class_exists($name) === FALSE)
//         {
//           require($path.$directory.'/'.$class.'.php');
//         }

//         break;
//       }
//     }

//     // Is the request a class extension?  If so we load it too
//     if (file_exists(APPPATH.$directory.'/'.config_item('subclass_prefix').$class.'.php'))
//     {
//       $name = config_item('subclass_prefix').$class;

//       if (class_exists($name) === FALSE)
//       {
//         require(APPPATH.$directory.'/'.config_item('subclass_prefix').$class.'.php');
//       }
//     }

//     // Did we find the class?
//     if ($name === FALSE)
//     {
//       // Note: We use exit() rather then show_error() in order to avoid a
//       // self-referencing loop with the Excptions class
//       exit('Unable to locate the specified class: '.$class.'.php');
//     }

//     // Keep track of what we just loaded
//     is_loaded($class);

//     $_classes[$class] = new $name();
//     return $_classes[$class];
//   }
// }