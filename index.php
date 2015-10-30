<?php
  /**
   * @package     FirePlume
   * @author      OA Wu <comdan66@gmail.com>
   * @copyright   Copyright (c) 2015 OA Wu
   * @license
   * @link
   * @since       Version 0.0.1
   * @filesource
   */

  // 系統資料夾
  $sys_folder = 'system';

  // app 資料夾
  $app_folder = 'app';

  // config 資料夾
  $cfg_folder = 'config';

  // 檢查 PHP 版本
  if ((version_compare (PHP_VERSION, '5.5') < 0)) exit ('PHP 版本錯誤！');

  // 設定 UTF8 編碼
  if ((preg_match ('/./u', 'é') === 1) && function_exists ('iconv') && (ini_get ('mbstring.func_overload') != 1) && define ('UTF8_ENABLED', true))
    if (extension_loaded ('mbstring') && define ('MB_ENABLED', true)) mb_internal_encoding ('UTF-8');
    else define ('MB_ENABLED', false);
  else
    define('UTF8_ENABLED', false);

  // 設定反應時間長度
  if ((function_exists ('set_time_limit') == true) && (@ini_get ('safe_mode') == 0)) @set_time_limit (300);

  //設定時區
  date_default_timezone_set ('Asia/Taipei');

  // 環境變數
  define ('ENV', 'development');

  // 定義後端檔案格式
  define ('EXT', '.php');

  // 定義專案目錄
  define ('PATH', str_replace (pathinfo (__FILE__, PATHINFO_BASENAME), '', __FILE__));
  
  // 定義版本
  define('VERSION', '0.0.1');

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
  if (defined ('STDIN')) chdir (dirname (__FILE__));

  // 定義 系統 路徑常數
  if (($sys_folder = realpath ($sys_folder)) !== FALSE) define ('F_SYS', DIRECTORY_SEPARATOR . trim ($sys_folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
  unset ($sys_folder);
  if (!is_dir (F_SYS)) exit ("找不到系統資料夾！");

  // 定義 app 路徑常數
  if (($app_folder = realpath ($app_folder)) !== FALSE) define ('F_APP', DIRECTORY_SEPARATOR . trim ($app_folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
  unset ($app_folder);
  if (!is_dir (F_APP)) exit ("找不到 app 資料夾！");

  // 定義 config 路徑常數
  if (($cfg_folder = realpath ($cfg_folder)) !== FALSE) define ('F_CFG', DIRECTORY_SEPARATOR . trim ($cfg_folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
  unset ($cfg_folder);
  if (!is_dir (F_APP)) exit ("找不到 config 資料夾！");

  // 載入核心
  require_once F_SYS . 'Plume.php';
