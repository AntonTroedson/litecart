<?php

  document::$snippets['title'][] = language::translate('index:head_title', 'One fancy web shop');
  document::$snippets['description'] = language::translate('index:meta_description', '');
  
  document::$snippets['head_tags']['canonical'] = '<link rel="canonical" href="'. document::href_ilink('') .'" />';
  
  document::$snippets['head_tags']['opengraph'] = '<meta property="og:url" content="'. document::href_ilink('') .'" />' . PHP_EOL
                                                //. '<meta property="og:title" content="'. htmlspecialchars(language::translate('index.php:head_title')) .'" />' . PHP_EOL
                                                //. '<meta property="og:description" content="'. htmlspecialchars(language::translate('index.php:meta_description')) .'" />' . PHP_EOL
                                                . '<meta property="og:type" content="website" />' . PHP_EOL
                                                . '<meta property="og:image" content="'. document::href_link(WS_DIR_IMAGES . 'logotype.png') .'" />';
  
  $_page = new view();
  
  ob_start();
  include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_slider.inc.php');
  $_page->snippets['box_slider'] = ob_get_clean();
  
  ob_start();
  include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_manufacturer_logotypes.inc.php');
  $_page->snippets['box_manufacturer_logotypes'] = ob_get_clean();
  
  ob_start();
  include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_campaign_products.inc.php');
  $_page->snippets['box_campaign_products'] = ob_get_clean();
  
  ob_start();
  include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_most_popular_products.inc.php');
  $_page->snippets['box_most_popular_products'] = ob_get_clean();
  
  ob_start();
  include vmod::check(FS_DIR_HTTP_ROOT . WS_DIR_BOXES . 'box_latest_products.inc.php');
  $_page->snippets['box_latest_products'] = ob_get_clean();
  
  echo $_page->stitch('pages/index');
?>
