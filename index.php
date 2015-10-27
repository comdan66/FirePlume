<?php
  // 系統資料夾
  $sys_folder = 'system';

  // app 資料夾
  $app_folder = 'app';

  //設定時區
  date_default_timezone_set ('Asia/Taipei');

  // 環境變數
  define ('ENV', 'development');

  // 環境變數設定值
  switch (ENV) {
    case 'development':
      error_reporting (E_ALL);
      ini_set ('display_errors', 1);
    break;

    case 'production':
      error_reporting (0);
      ini_set ('display_errors', 0);
    break;

    default:
      exit ('環境變數錯誤！');
  }

  // 為 CLI 設定目前的資料夾位置
  if (defined ('STDIN'))
    chdir (dirname (__FILE__));

  // 定義 系統 路徑常數
  if (($sys_folder = realpath ($sys_folder)) !== FALSE)
    define ('F_SYS', DIRECTORY_SEPARATOR . trim ($sys_folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
  unset ($sys_folder);

  if (!is_dir (F_SYS))
    exit ("找不到系統資料夾！");

  // 定義 app 路徑常數
  if (($app_folder = realpath ($app_folder)) !== FALSE)
    define ('F_APP', DIRECTORY_SEPARATOR . trim ($app_folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
  unset ($app_folder);

  if (!is_dir (F_APP))
    exit ("找不到 app 資料夾！");

  // 定義後端檔案格式
  define ('EXT', '.php');

  // 定義專案目錄
  define ('PATH', str_replace (pathinfo (__FILE__, PATHINFO_BASENAME), '', __FILE__));
  
  // 載入主體
  require_once F_SYS . 'core/FirePlume.php';
