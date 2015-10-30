<?php if (!defined ('PATH')) exit ('不允許直接呼叫檔案！');

class Main extends Controller {
  public function index ($a = '') {
    // $a = 'asd';
    // // File::write (PATH . 'temp/aaa.txt', $a);
    // echo '<meta http-equiv="Content-type" content="text/html; charset=utf-8" /><pre>';
    // var_dump (File::delete (PATH . 'temp/aaa'));
    // exit ();
    // function convert () { $size = memory_get_usage(); $unit = array('b','kb','mb','gb','tb','pb'); return @round ($size / pow (1024, ($i = floor (log ($size, 1024)))), 2) . ' ' . $unit[$i]; } echo convert ();
    $a = Cache::get ('temp/www/dd/d/f/', 100, function () {
      return '----w----';
    });

    echo '<meta http-equiv="Content-type" content="text/html; charset=utf-8" /><pre>';
    var_dump ($a);
    exit ();
    // $a = Output::create ('a')->addVar ('a', 5)->cache ('222', 10)->getView ();
    // return View::create ('b')->addVar ('b', '___')->cache (10);
  }
}