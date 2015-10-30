<?php if (!defined ('PATH')) exit ('不允許直接呼叫檔案！');

  // 載入核心資源
  require_once F_SYS . 'Function.php';
  require_once F_SYS . 'Config.php';
  require_once F_SYS . 'Cache.php';
  require_once F_SYS . 'Uri.php';
  require_once F_SYS . 'Router.php';
  require_once F_SYS . 'Output.php';

  // 檢查快取
  View::displayCache () && exit;

  // 載入進階核心資源
  require_once F_SYS . 'Security.php';
  require_once F_SYS . 'Input.php';
  require_once F_SYS . 'Controller.php';

  // 整理 uri 參數資訊
  FireRouter::fetch ();

  // 取 controller 檔案
  if (is_readable ($path = F_APP . implode (DIRECTORY_SEPARATOR, array_merge (array ('controller'),  FireRouter::getDirectory (), array (($className = FireRouter::getClassName ()) . EXT)))))
    include_once $path;
  else exit ('找不到 Controller！');
  unset ($path);

  if (!(class_exists ($className) && !in_array ($methodName = FireRouter::getMethodName (), get_class_methods ('Controller'))))
    exit ('找不到 Controller！');

  $controller = new $className ();
  unset ($className);

  if (!in_array ($methodName, get_class_methods ($controller)))
    exit ('找不到 Controller！');

  $controller = call_user_func_array (array ($controller, $methodName), FireRouter::getParameters ());
  unset ($methodName);

  if ($controller instanceof Output)
    echo $controller->getView ();
