<?php if (!defined ('PATH')) exit ('不允許直接呼叫檔案！');

require_once F_SYS . 'File' . EXT;

class FileCache {
  public static function read ($path) {
    $path = PATH . $path;
    return File::read ($path);
  }
  public static function write ($path, $data) {
    $path = PATH . $path;
    if (!is_dir (pathinfo ($path, PATHINFO_DIRNAME))) {
      $oldmask = umask (0);
      @mkdir (dirname ($path), 0777, true);
      umask ($oldmask);
    }

    return File::write ($path, $data);
  }
}