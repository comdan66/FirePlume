<?php if (!defined ('PATH')) exit ('不允許直接呼叫檔案！');

Router::root ('main');
Router::get ('D/(:id)/5', 'main@a($1)');

Router::group ('admin', function () {
  Router::get ('/', 'main');

  Router::resource (array ('pics', 'comments'), 'PicComments');
});