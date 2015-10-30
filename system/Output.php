<?php if (!defined ('PATH')) exit ('不允許直接呼叫檔案！');

class Output {
  private static $viewPath = null;
  
  private $path = null;
  private $data = array ();

  private $output = '';
  private $cacheKey = null;
  private $expire = 0;

  public function setPath ($path) {
    $this->path = $path;
    return $this;
  }
  public function addVar ($key, $val) {
    $this->data[$key] = $val;
    return $this;
  }

  public function getView () {
    // $output = Cache::save ('sad', 20, function () {

    // });

    return $this->render ()->getOutput ();
  }


  private function render () {
    extract ($this->data);

    @ob_end_clean ();
    ob_start ();
    include $this->path;
    $buffer = ob_get_contents ();
    @ob_end_clean ();
    
    return $this->setOutput ($buffer);
  }
  public function setOutput ($output) {
    $this->output = $output;
    return $this;
  }
  public function getOutput () {
    return $this->output;
  }

  public function cache ($expire, $cacheKey) { /* s */
    $this->cacheKey = $cacheKey;
    $this->expire = $expire;
    return $this;
  }







  
  public static function setViewPath ($viewPath) {
    return self::$viewPath = $viewPath;
  }
  public static function getViewPath () {
    return self::$viewPath !== null ? self::$viewPath : F_APP . 'view' . DIRECTORY_SEPARATOR;
  }
  
  public static function create ($path, $data = array ()) {
    if (!is_readable ($path = self::getViewPath () . ltrim ($path, DIRECTORY_SEPARATOR) . EXT))
      exit ('找不到 view！');
    
    $output = new self ();
    $output->setPath ($path);
    
    if ($data)
      foreach ($data as $key => $val)
        $output->addVar ($key, $val);

    return $output;
  }
}
class View extends Output {
  private static $instance = null;

  public static function instance () {
    if (self::$instance !== null) return self::$instance;
    return self::$instance = new self ();
  }
  public static function create ($path, $data = array ()) {
    if (!is_readable ($path = self::getViewPath () . ltrim ($path, DIRECTORY_SEPARATOR) . EXT))
      exit ('找不到 view！');
    
    self::instance ()->setPath ($path);
    
    if ($data)
      foreach ($data as $key => $val)
        self::instance ()->addVar ($key, $val);

    return self::instance ();
  }
  public function cache ($expire, $a = '') { /* s */
    return parent::cache ($expire, '___');
  }
  public static function displayCache () {

  }
}