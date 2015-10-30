<?php if (!defined ('PATH')) exit ('不允許直接呼叫檔案！');

class File {
  public static function read ($file) {
    if (!file_exists ($file)) return false;
    if (function_exists ('file_get_contents')) return file_get_contents ($file);
    if (!($fp = @fopen($file, FOPEN_READ))) return false;

    flock ($fp, LOCK_SH);

    $data = ''; if (filesize ($file) > 0) $data =& fread ($fp, filesize ($file));

    flock ($fp, LOCK_UN);
    fclose ($fp);

    return $data;
  }
  public static function write ($file, $data, $mode = 'wb') {
    if (!($fp = @fopen ($file, $mode))) return false;

    flock ($fp, LOCK_EX);
    fwrite ($fp, $data);
    flock ($fp, LOCK_UN);
    fclose ($fp);

    return true;
  }
  public static function delete ($file) {
    if (!file_exists ($file))
      return true;

    if (is_dir ($file))
      return false;

    return unlink ($file);
  }
}
