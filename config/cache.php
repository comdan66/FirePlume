<?php if (!defined ('PATH')) exit ('不允許直接呼叫檔案！');

return array (
      'driver' => 'FileCache', // RedisCache

      // FileCache
      'FileCache' => array (
          'prefix' => array (
              'file' => 'f_',
              'forder' => 'd_'
            ),
          'paths' => array (
              'general' => array ('cache', 'general'),
              'output' => array ('cache', 'output')
            )
        ),

      // RedisCache
      'RedisCachePaths' => array (
          'general' => array ('cache', 'general'),
          'output' => array ('cache', 'output')
        )

  );