<?php
  
  if (!empty($_POST['export_categories'])) {
    
    if (empty($_POST['language_code'])) notices::add('errors', language::translate('error_must_select_a_language', 'You must select a language'));
    
    if (empty(notices::$data['errors'])) {
      
      ob_clean();
      
      $csv = array();
      
      $categories_query = database::query("select id from ". DB_TABLE_CATEGORIES ." order by parent_id;");
      while ($category = database::fetch($categories_query)) {
        $category = new ref_category($category['id']);
        
        $csv[] = array(
          'id' => $category->id,
          'parent_id' => $category->parent_id,
          'code' => $category->code,
          'name' => $category->name[$_POST['language_code']],
          'short_description' => $category->short_description[$_POST['language_code']],
          'description' => $category->description[$_POST['language_code']],
          'keywords' => $category->keywords,
          'image' => $category->image,
          'status' => $category->status,
          'priority' => $category->priority,
          'language_code' => $_POST['language_code'],
        );
      }
      
      if ($_POST['output'] == 'screen') {
        header('Content-type: text/plain; charset='. $_POST['charset']);
      } else {
        header('Content-type: application/csv; charset='. $_POST['charset']);
        header('Content-Disposition: attachment; filename=categories-'. $_POST['language_code'] .'.csv');
      }
      
      switch($_POST['eol']) {
        case 'Linux':
          echo functions::csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'], "\r");
          break;
        case 'Max':
          echo functions::csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'], "\n");
          break;
        case 'Win':
        default:
          echo functions::csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'], "\r\n");
          break;
      }
      
      exit;
    }
  }
  
  if (!empty($_POST['import_categories'])) {
    
    if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
      
      ob_clean();
      
      header('Content-type: text/plain; charset='. language::$selected['charset']);
      
      echo "CSV Import\r\n"
         . "----------\r\n";
      
      $csv = file_get_contents($_FILES['file']['tmp_name']);
      
      if (empty($_POST['delimiter'])) {
        preg_match('/^([^(\r|\n)]+)/', $csv, $matches);
        if (strpos($matches[1], ',') !== false) {
          $_POST['delimiter'] = ',';
        } elseif (strpos($matches[1], ';') !== false) {
          $_POST['delimiter'] = ';';
        } elseif (strpos($matches[1], "\t") !== false) {
          $_POST['delimiter'] = "\t";
        } elseif (strpos($matches[1], '|') !== false) {
          $_POST['delimiter'] = '|';
        } else {
          trigger_error('Unable to determine CSV delimiter', E_USER_ERROR);
        }
      }
      
      $csv = functions::csv_decode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset']);
      
      $line = 0;
      foreach ($csv as $row) {
        $line++;
        
      // Find category
        if (!empty($row['id'])) {
          $category_query = database::query(
            "select id from ". DB_TABLE_CATEGORIES ."
            where id = '". (int)$row['id'] ."'
            limit 1;"
          );
        } elseif (!empty($row['code'])) {
          $category_query = database::query(
            "select id from ". DB_TABLE_CATEGORIES ."
            where code = '". database::input($row['code']) ."'
            limit 1;"
          );
        } else {
          echo "[Skipped] Could not identify category on line $line. Missing code.\r\n";
          continue;
        }
        
        $category = database::fetch($category_query);
        
      // No category, let's create it
        if (empty($category)) {
          if (empty($_POST['insert_categories'])) {
            echo "[Skipped] New category on line $line was not inserted to database.\r\n";
            continue;
          }
          $category = new ctrl_category();
          echo "Inserting new category '{$row['name']}'\r\n";
          
      // Get category
        } else {
          $category = new ctrl_category($category['id']);
          echo "Updating existing category '{$row['name']}'\r\n";
        }
        
      // Set new category data
        foreach (array('parent_id', 'status', 'code', 'keywords', 'image') as $field) {
          if (isset($row[$field])) $category->data[$field] = $row[$field];
        }
        
      // Set category info data
        foreach (array('name', 'short_description', 'description') as $field) {
          if (isset($row[$field])) $category->data[$field][$row['language_code']] = $row[$field];
        }
        
        $category->save();
      }
      
      exit;
    }
  }
  
  if (!empty($_POST['export_products'])) {
    
    if (empty($_POST['language_code'])) notices::add('errors', language::translate('error_must_select_a_language', 'You must select a language'));
    
    if (empty(notices::$data['errors'])) {
    
      $csv = array();
      
      ob_clean();
      
      $products_query = database::query(
        "select p.id from ". DB_TABLE_PRODUCTS ." p
        left join ". DB_TABLE_PRODUCTS_INFO ." pi on (pi.product_id = p.id and pi.language_code = '". database::input($_POST['language_code']) ."')
        order by pi.name;"
      );
      while ($product = database::fetch($products_query)) {
        $product = ref_product($product['id']);
        
        $csv[] = array(
          'id' => $product->id,
          'categories' => implode(',', array_keys($product->categories)),
          'manufacturer_id' => $product->manufacturer['id'],
          'status' => $product->status,
          'code' => $product->code,
          'sku' => $product->sku,
          'gtin' => $product->gtin,
          'taric' => $product->taric,
          'name' => $product->name[$_POST['language_code']],
          'short_description' => $product->short_description[$_POST['language_code']],
          'description' => $product->description[$_POST['language_code']],
          'keywords' => $product->keywords,
          'attributes' => $product->attributes[$_POST['language_code']],
          'head_title' => $product->head_title[$_POST['language_code']],
          'meta_description' => $product->meta_description[$_POST['language_code']],
          'images' => implode(';', $product->images),
          'purchase_price' => $product->purchase_price,
          'price' => $product->price,
          'tax_class_id' => $product->tax_class_id,
          'quantity' => $product->quantity,
          'weight' => $product->weight,
          'weight_class' => $product->weight_class,
          'delivery_status_id' => $product->delivery_status_id,
          'sold_out_status_id' => $product->sold_out_status_id,
          'language_code' => $_POST['language_code'],
          'currency_code' => $_POST['currency_code'],
          'date_valid_from' => $product->date_valid_from,
          'date_valid_to' => $product->date_valid_to,
        );
      }

      if ($_POST['output'] == 'screen') {
        header('Content-type: text/plain; charset='. $_POST['charset']);
      } else {
        header('Content-type: application/csv; charset='. $_POST['charset']);
        header('Content-Disposition: attachment; filename=products-'. $_POST['language_code'] .'.csv');
      }
      
      switch($_POST['eol']) {
        case 'Linux':
          echo functions::csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'], "\r");
          break;
        case 'Max':
          echo functions::csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'], "\n");
          break;
        case 'Win':
        default:
          echo functions::csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'], "\r\n");
          break;
      }
      
      exit;
    }
  }

  if (!empty($_POST['import_products'])) {
    
    if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
      
      ob_clean();
      
      header('Content-type: text/plain; charset='. language::$selected['charset']);
      
      echo "CSV Import\r\n"
         . "----------\r\n";
      
      $csv = file_get_contents($_FILES['file']['tmp_name']);
      
      if (empty($_POST['delimiter'])) {
        preg_match('/^([^(\r|\n)]+)/', $csv, $matches);
        if (strpos($matches[1], ',') !== false) {
          $_POST['delimiter'] = ',';
        } elseif (strpos($matches[1], ';') !== false) {
          $_POST['delimiter'] = ';';
        } elseif (strpos($matches[1], "\t") !== false) {
          $_POST['delimiter'] = "\t";
        } elseif (strpos($matches[1], '|') !== false) {
          $_POST['delimiter'] = '|';
        } else {
          trigger_error('Unable to determine CSV delimiter', E_USER_ERROR);
        }
      }
      
      $csv = functions::csv_decode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset']);
      
      $line = 0;
      foreach ($csv as $row) {
        $line++;
        
      // Find product
        if (!empty($row['id'])) {
          $product_query = database::query(
            "select id from ". DB_TABLE_PRODUCTS ."
            where id = '". database::input($row['id']) ."'
            limit 1;"
          );
        } elseif (!empty($row['code'])) {
          $product_query = database::query(
            "select id from ". DB_TABLE_PRODUCTS ."
            where code = '". database::input($row['code']) ."'
            limit 1;"
          );
        } elseif (!empty($row['sku'])) {
          $product_query = database::query(
            "select id from ". DB_TABLE_PRODUCTS ."
            where sku = '". database::input($row['sku']) ."'
            limit 1;"
          );
        } elseif (!empty($row['gtin'])) {
          $product_query = database::query(
            "select id from ". DB_TABLE_PRODUCTS ."
            where gtin = '". database::input($row['gtin']) ."'
            limit 1;"
          );
        } elseif (!empty($row['name']) && !empty($row['language_code'])) {
          $product_query = database::query(
            "select product_id as id from ". DB_TABLE_PRODUCTS_INFO ."
            where name = '". database::input($row['name']) ."'
            and language_code = '". $row['language_code'] ."'
            limit 1;"
          );
        } else {
          echo "[Skipped] Could not identify product on line $line.\r\n";
          continue;
        }
        $product = database::fetch($product_query);
        
      // No product, let's create it
        if (empty($product)) {
          if (empty($_POST['insert_products'])) {
            echo "[Skipped] New product on line $line was not inserted to database.\r\n";
            continue;
          }
          $product = new ctrl_product();
          echo "Inserting new product on line $line\r\n";
          
      // Get product
        } else {
          $product = new ctrl_product($product['id']);
          echo "Updating existing product ". (!empty($row['name']) ? $row['name'] : "on line $line") ."\r\n";
        }
        
      // Set new product data
        foreach (array('categories', 'manufacturer_id', 'status', 'code', 'sku', 'gtin', 'taric', 'tax_class_id', 'keywords', 'quantity', 'weight', 'weight_class', 'purchase_price', 'delivery_status_id', 'sold_out_status_id', 'date_valid_from', 'date_valid_to') as $field) {
          if (isset($row[$field])) $product->data[$field] = $row[$field];
          if (in_array($field, array('categories'))) $product->data[$field] = explode(',', str_replace(' ', '', $product->data[$field]));
        }
        
      // Set price
        if (!empty($row['currency_code'])) {
          if (isset($row['price'])) $product->data['prices'][$row['currency_code']] = $row['price'];
        }
        
      // Set product info data
        if (!empty($row['language_code'])) {
          foreach (array('name', 'short_description', 'description', 'attributes', 'head_title', 'meta_description') as $field) {
            if (isset($row[$field])) {
              $product->data[$field][$row['language_code']] = $row[$field];
            }
          }
        }
        
        if (isset($row['images'])) {
          $row['images'] = explode(';', $row['images']);
          
          $product_images = array();
          $current_images = array();
          foreach ($product->data['images'] as $key => $image) {
            if (in_array($image['filename'], $row['images'])) {
              $product_images[$key] = $image;
              $current_images[] = $image['filename'];
            }
          }
          
          $i=0;
          foreach($row['images'] as $image) {
            if (!in_array($image, $current_images)) {
              $product_images['new'.++$i] = array('filename' => $image);
            }
          }
          
          $product->data['images'] = $product_images;
        }
        
        $product->save();
      }
      
      exit;
    }
  }
  
?>
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo language::translate('title_csv_import_export', 'CSV Import/Export'); ?></h1>

<div class="col-md-6">
  
  <h2><?php echo language::translate('title_categories', 'Categories'); ?></h2>
  
  <div class="row">
    <div class="col-md-6">
      <fieldset class="well">
        <?php echo functions::form_draw_form_begin('import_categories_form', 'post', '', true); ?>
        <legend><?php echo language::translate('title_import_from_csv', 'Import From CSV'); ?></legend>
        
          <div class="form-group">
            <label><?php echo language::translate('title_csv_file', 'CSV File'); ?></label>
            <?php echo functions::form_draw_file_field('file'); ?>
          </div>
          
          <div class="form-group">
            <label><?php echo language::translate('title_delimiter', 'Delimiter'); ?></label>
            <?php echo functions::form_draw_select_field('delimiter', array(array(language::translate('title_auto', 'Auto') .' ('. language::translate('text_default', 'default') .')', ''), array(','),  array(';'), array('TAB', "\t"), array('|')), true, false, 'data-size="auto"'); ?>
          </div>
          
          <div class="form-group">
            <label><?php echo language::translate('title_enclosure', 'Enclosure'); ?></label>
            <?php echo functions::form_draw_select_field('enclosure', array(array('" ('. language::translate('text_default', 'default') .')', '"')), true, false, 'data-size="auto"'); ?>
          </div>
          
          <div class="form-group">
            <label><?php echo language::translate('title_escape_character', 'Escape Character'); ?></label>
            <?php echo functions::form_draw_select_field('escapechar', array(array('" ('. language::translate('text_default', 'default') .')', '"'), array('\\', '\\')), true, false, 'data-size="auto"'); ?>
          </div>
          
          <div class="form-group">
            <label><?php echo language::translate('title_charset', 'Charset'); ?></label>
            <?php echo functions::form_draw_select_field('charset', array(array('UTF-8'), array('ISO-8859-1')), true, false, 'data-size="auto"'); ?>
          </div>
          
          <div class="form-group">
            <label><?php echo functions::form_draw_checkbox('insert_categories', 'true', true); ?> <?php echo language::translate('text_insert_new_categories', 'Insert new categories'); ?></label>
          </div>
          
          <?php echo functions::form_draw_button('import_categories', language::translate('title_import', 'Import'), 'submit'); ?>
        
        <?php echo functions::form_draw_form_end(); ?>
      </fieldset>
    </div>

    <div class="col-md-6">
      <fieldset class="well">
        <?php echo functions::form_draw_form_begin('export_categories_form', 'post'); ?>
        <legend><?php echo language::translate('title_export_to_csv', 'Export To CSV'); ?></legend>

          <div class="form-group">
            <label><?php echo language::translate('title_language', 'Language'); ?></label>
            <?php echo functions::form_draw_languages_list('language_code', true, false, 'data-size="auto"').' '; ?>
          </div>
          
          <div class="form-group">
            <label><?php echo language::translate('title_delimiter', 'Delimiter'); ?></label>
            <?php echo functions::form_draw_select_field('delimiter', array(array(', ('. language::translate('text_default', 'default') .')', ','), array(';'), array('TAB', "\t"), array('|')), true, false, 'data-size="auto"'); ?>
          </div>
        
          <div class="form-group">
            <label><?php echo language::translate('title_enclosure', 'Enclosure'); ?></label>
            <?php echo functions::form_draw_select_field('enclosure', array(array('" ('. language::translate('text_default', 'default') .')', '"')), true, false, 'data-size="auto"'); ?>
          </div>
          
          <div class="form-group">
            <label><?php echo language::translate('title_escape_character', 'Escape Character'); ?></label>
            <?php echo functions::form_draw_select_field('escapechar', array(array('" ('. language::translate('text_default', 'default') .')', '"'), array('\\', '\\')), true, false, 'data-size="auto"'); ?>
          </div>
            
          <div class="form-group">
            <label><?php echo language::translate('title_charset', 'Charset'); ?></label>
            <?php echo functions::form_draw_select_field('charset', array(array('UTF-8'), array('ISO-8859-1')), true, false, 'data-size="auto"'); ?>
          </div>
          
          <div class="form-group">
            <label><?php echo language::translate('title_output', 'Output'); ?></label>
            <?php echo functions::form_draw_select_field('output', array(array(language::translate('title_file', 'File'), 'file'), array(language::translate('title_screen', 'Screen'), 'screen')), true, false, 'data-size="auto"'); ?>
          </div>
          
          <div class="form-group">
            <label><?php echo language::translate('title_line_ending', 'Line Ending'); ?></label>
            <?php echo functions::form_draw_select_field('eol', array(array('Win'), array('Mac'), array('Linux')), true, false, 'data-size="auto"'); ?>
          </div>
            
          <?php echo functions::form_draw_button('export_categories', language::translate('title_export', 'Export'), 'submit'); ?>
          
        <?php echo functions::form_draw_form_end(); ?>
      </fieldset>
    </div>
  </div>
</div>

<div class="col-md-6">

  <h2><?php echo language::translate('title_products', 'Products'); ?></h2>
  
  <div class="row">
    <div class="col-md-6">
      <fieldset class="well">
        <?php echo functions::form_draw_form_begin('import_products_form', 'post', '', true); ?>
          <legend><?php echo language::translate('title_import_from_csv', 'Import From CSV'); ?></legend>
          
            <div class="form-group">
              <label><?php echo language::translate('title_csv_file', 'CSV File'); ?></label>
                <?php echo functions::form_draw_file_field('file'); ?>
            </div>
            
            <div class="form-group">
              <label><?php echo language::translate('title_delimiter', 'Delimiter'); ?></label>
                <?php echo functions::form_draw_select_field('delimiter', array(array(language::translate('title_auto', 'Auto') .' ('. language::translate('text_default', 'default') .')', ''), array(','),  array(';'), array('TAB', "\t"), array('|')), true, false, 'data-size="auto"'); ?>
            </div>
            
            <div class="form-group">
              <label><?php echo language::translate('title_enclosure', 'Enclosure'); ?></label>
              <?php echo functions::form_draw_select_field('enclosure', array(array('" ('. language::translate('text_default', 'default') .')', '"')), true, false, 'data-size="auto"'); ?>
            </div>
            
            <div class="form-group">
              <label><?php echo language::translate('title_escape_character', 'Escape Character'); ?></label>
              <?php echo functions::form_draw_select_field('escapechar', array(array('" ('. language::translate('text_default', 'default') .')', '"'), array('\\', '\\')), true, false, 'data-size="auto"'); ?>
            </div>
            
            <div class="form-group">
              <label><?php echo language::translate('title_charset', 'Charset'); ?></label>
                <?php echo functions::form_draw_select_field('charset', array(array('UTF-8'), array('ISO-8859-1')), true, false, 'data-size="auto"'); ?>
            </div>
            
            <div class="form-group">
              <label><?php echo functions::form_draw_checkbox('insert_products', 'true', true); ?> <?php echo language::translate('text_insert_new_products', 'Insert new products'); ?></label>
            </div>
            
            <?php echo functions::form_draw_button('import_products', language::translate('title_import', 'Import'), 'submit'); ?>
          
        <?php echo functions::form_draw_form_end(); ?>
      </fieldset>
    </div>

    <div class="col-md-6">
      <fieldset class="well">
        <?php echo functions::form_draw_form_begin('export_products_form', 'post'); ?>
          <legend><?php echo language::translate('title_export_to_csv', 'Export To CSV'); ?></legend>
          
            <div class="form-group">
              <label><?php echo language::translate('title_language', 'Language'); ?></label>
                <?php echo functions::form_draw_languages_list('language_code', true, false, 'data-size="auto"'); ?>
            </div>
            
            <div class="form-group">
              <label><?php echo language::translate('title_currency', 'Currency'); ?></label>
                <?php echo functions::form_draw_currencies_list('currency_code', true, false, 'data-size="auto"'); ?>
            </div>
            
            <div class="form-group">
              <label><?php echo language::translate('title_delimiter', 'Delimiter'); ?></label>
                <?php echo functions::form_draw_select_field('delimiter', array(array(', ('. language::translate('text_default', 'default') .')', ','), array(';'), array('TAB', "\t"), array('|')), true, false, 'data-size="auto"'); ?>
            </div>
            
            <div class="form-group">
              <label><?php echo language::translate('title_enclosure', 'Enclosure'); ?></label>
                <?php echo functions::form_draw_select_field('enclosure', array(array('" ('. language::translate('text_default', 'default') .')', '"')), true, false, 'data-size="auto"'); ?>
            </div>
            
            <div class="form-group">
              <label><?php echo language::translate('title_escape_character', 'Escape Character'); ?></label>
                <?php echo functions::form_draw_select_field('escapechar', array(array('" ('. language::translate('text_default', 'default') .')', '"'), array('\\', '\\')), true, false, 'data-size="auto"'); ?>
            </div>
            
            <div class="form-group">
              <label><?php echo language::translate('title_charset', 'Charset'); ?></label>
                <?php echo functions::form_draw_select_field('charset', array(array('UTF-8'), array('ISO-8859-1')), true, false, 'data-size="auto"'); ?>
            </div>
            
            <div class="form-group">
              <label><?php echo language::translate('title_output', 'Output'); ?></label>
                <?php echo functions::form_draw_select_field('output', array(array(language::translate('title_file', 'File'), 'file'), array(language::translate('title_screen', 'Screen'), 'screen')), true, false, 'data-size="auto"'); ?>
            </div>
            
            <div class="form-group">
              <label><?php echo language::translate('title_line_ending', 'Line Ending'); ?></label>
                <?php echo functions::form_draw_select_field('eol', array(array('Win'), array('Mac'), array('Linux')), true, false, 'data-size="auto"'); ?>
            </div>
            
            <?php echo functions::form_draw_button('export_products', language::translate('title_export', 'Export'), 'submit'); ?>
          
        <?php echo functions::form_draw_form_end(); ?>
      </fieldset>
    </div>
  </div>
</div>