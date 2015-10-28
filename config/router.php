<?php if (!defined('PATH')) exit ('不允許直接呼叫檔案！');

Router::root ('main');
Router::get ('D/(:id)/5', 'main@a($1)');

Router::group ('admin', function () {
  Router::get ('/', 'main');

  Router::resourcePagination (array ('dintao_tags'), 'dintao_tags');
  Router::resourcePagination (array ('picture_tags'), 'picture_tags');
  Router::resourcePagination (array ('youtube_tags'), 'youtube_tags');
  Router::resourcePagination (array ('dintaos'), 'dintaos');
  Router::resourcePagination (array ('pictures'), 'pictures');
  Router::resourcePagination (array ('youtubes'), 'youtubes');
  Router::resourcePagination (array ('dintao_tags', 'dintaos'), 'dintao_tag_dintaos');
  Router::resourcePagination (array ('picture_tags', 'pictures'), 'picture_tag_pictures');
  Router::resourcePagination (array ('youtube_tags', 'youtubes'), 'youtube_tag_youtubes');
});