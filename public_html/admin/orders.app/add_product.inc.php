<?php
  document::$layout = 'ajax';

  if (empty($_GET['currency_code'])) $_GET['currency_code'] = settings::get('store_currency_code');
  if (empty($_GET['currency_value'])) $_GET['currency_value'] = currency::$currencies[$_GET['currency_code']]['value'];

  if (!empty($_GET['product_id'])) {
    $product = reference::product($_GET['product_id'], $_GET['currency_code']);
  }
?>
<div class="container-fluid">
  <?php echo functions::form_draw_form_begin('form_add_product', 'post', null, false, !empty($_GET['product_id']) ? 'style="width: 960px;"' : 'style="width: 320px;"'); ?>

    <div class="row">
      <div class="form-group <?php echo !empty($_GET['product_id']) ? 'col-md-4' : 'col-md-12'; ?>">
        <?php echo functions::form_draw_products_list('product_id', true, false); ?>

        <?php if (!empty($product)) { ?>
        <div class="thumbnail">
<?php
  list($width, $height) = functions::image_scale_by_width(320, settings::get('product_image_ratio'));
  echo '<img src="'. functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $product->image, $width, $height, settings::get('product_image_clipping')) .'" />';
?>
        </div>
      </div>

      <div class="col-md-8">
        <?php echo functions::form_draw_hidden_field('name', $product->name[$_GET['language_code']]); ?>

<?php
    if (count($product->options) > 0) {
      echo '<div id="options" class="row">' . PHP_EOL;

      foreach ($product->options as $group) {

        echo '  <div class="form-group col-md-6">'
           . '    <label>'. $group['name'][$_GET['language_code']] .'</label>';

        switch ($group['function']) {

          case 'checkbox':

            foreach ($group['values'] as $value) {

              $price_adjust_text = '';
              $price_adjust = currency::format_raw($value['price_adjust']);
              $tax_adjust = currency::format(tax::get_tax($value['price_adjust'], $product->tax_class_id));

              if ($value['price_adjust']) {
                $price_adjust_text = currency::format(tax::get_price($value['price_adjust'], $product->tax_class_id));
                if ($value['price_adjust'] > 0) $price_adjust_text = ' +' . $price_adjust_text;
              }

              echo '<div class="checkbox">' . PHP_EOL
                 . '  <label>' . functions::form_draw_checkbox('options['.$group['name'][$_GET['language_code']].'][]', $value['name'][language::$selected['code']], true, 'data-group="'. $group['name'][$_GET['language_code']] .'" data-price-adjust="'. (float)$price_adjust .'" data-tax-adjust="'. (float)$tax_adjust .'"' . (!empty($group['required']) ? 'required="required"' : '')) .' '. $value['name'][language::$selected['code']] . $price_adjust_text . '</label>' . PHP_EOL
                 . '</div>';
            }
            break;

          case 'input':

            $value_ids = array_keys($group['values']);
            $value_id = array_shift($value_ids);

            $price_adjust_text = '';
            $price_adjust = currency::format_raw($value['price_adjust']);
            $tax_adjust = currency::format(tax::get_tax($value['price_adjust'], $product->tax_class_id));

            if ($value['price_adjust']) {
              $price_adjust_text = currency::format(tax::get_price($value['price_adjust'], $product->tax_class_id));
              if ($value['price_adjust'] > 0) $price_adjust_text = ' +'.$price_adjust_text;
            }

            echo functions::form_draw_text_field('options['.$group['name'][$_GET['language_code']].']', isset($_POST['options'][$group['name'][$_GET['language_code']]]) ? true : $value['value'], 'data-group="'. $group['name'][$_GET['language_code']] .'" data-price-adjust="'. (float)$price_adjust .'" data-tax-adjust="'. (float)$tax_adjust .'"' . (!empty($group['required']) ? 'required="required"' : '')) . $price_adjust_text . PHP_EOL;
            break;

          case 'radio':

            foreach ($group['values'] as $value) {

              $price_adjust_text = '';
              $price_adjust = currency::format_raw($value['price_adjust']);
              $tax_adjust = currency::format(tax::get_tax($value['price_adjust'], $product->tax_class_id));

              if ($value['price_adjust']) {
                $price_adjust_text = currency::format(tax::get_price($value['price_adjust'], $product->tax_class_id));
                if ($value['price_adjust'] > 0) $price_adjust_text = ' +'.$price_adjust_text;
              }

              echo '<div class="radio">' . PHP_EOL
                 . '  <label>'. functions::form_draw_radio_button('options['.$group['name'][$_GET['language_code']].']', $value['name'][language::$selected['code']], true, 'data-group="'. $group['name'][$_GET['language_code']] .'" data-price-adjust="'. (float)$price_adjust .'" data-tax-adjust="'. (float)$tax_adjust .'"' . (!empty($group['required']) ? 'required="required"' : '')) .' '. $value['name'][language::$selected['code']] . $price_adjust_text . '</label>' . PHP_EOL
                 . '</div>';
            }
            break;

          case 'select':

            $options = array(array('-- '. language::translate('title_select', 'Select') .' --', ''));
            foreach ($group['values'] as $value) {

              $price_adjust_text = '';
              $price_adjust = currency::format_raw($value['price_adjust']);
              $tax_adjust = currency::format(tax::get_tax($value['price_adjust'], $product->tax_class_id));

              if ($value['price_adjust']) {
                $price_adjust_text = currency::format(tax::get_price($value['price_adjust'], $product->tax_class_id));
                if ($value['price_adjust'] > 0) $price_adjust_text = ' +'.$price_adjust_text;
              }

              $options[] = array($value['name'][language::$selected['code']] . $price_adjust_text, $value['name'][language::$selected['code']], 'data-price-adjust="'. (float)$price_adjust .'" data-tax-adjust="'. (float)$tax_adjust .'"');
            }

            echo functions::form_draw_select_field('options['.$group['name'][$_GET['language_code']].']', $options, true, false, 'data-group="'. $group['name'][$_GET['language_code']] .'"' . (!empty($group['required']) ? ' required="required"' : ''));
            break;

          case 'textarea':

            $value_ids = array_keys($group['values']);
            $value_id = array_shift($value_ids);

            $price_adjust_text = '';
            $price_adjust = currency::format_raw($value['price_adjust']);
            $tax_adjust = currency::format(tax::get_price($value['price_adjust'], $product->tax_class_id));

            if ($value['price_adjust']) {
              $price_adjust_text = currency::format(tax::get_price($value['price_adjust'], $product->tax_class_id));
              if ($value['price_adjust'] > 0) {
                $price_adjust_text = ' <br />+'. currency::format(tax::get_price($value['price_adjust'], $product->tax_class_id));
              }
            }

            echo functions::form_draw_textarea('options['.$group['name'][$_GET['language_code']].']', isset($_POST['options'][$group['name'][$_GET['language_code']]]) ? true : $value['value'], 'data-group="'. $group['name'][$_GET['language_code']] .'"' . (!empty($group['required']) ? 'required="required"' : '')) . $price_adjust_text. PHP_EOL;
            break;
        }

        echo '</div>';
      }

      echo '</div>';
    }

    echo functions::form_draw_hidden_field('option_stock_combination', '');
?>

        <div class="row">
          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_price', 'Price'); ?></label>
            <div>
              <?php echo functions::form_draw_hidden_field('price', $price = currency::format_raw(!empty($product->campaign['price']) ? $product->campaign['price'] : $product->price)); ?>
              <?php echo !empty($product->campaign['price']) ? '<del>'. currency::format($product->price, true, false, $_GET['currency_code'], $_GET['currency_value']) .'</del>' : null; ?>
              <?php echo currency::format($price, true, false, $_GET['currency_code'], $_GET['currency_value']); ?>
            </div>
          </div>

          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_tax', 'Tax'); ?></label>
            <div>
              <?php echo functions::form_draw_hidden_field('tax', $tax = tax::get_tax($price, $product->tax_class_id, $_GET['customer'])); ?>
              <?php echo !empty($product->campaign['price']) ? '<del>'. currency::format($tax, true, false, $_GET['currency_code'], $_GET['currency_value']) .'</del>' : null; ?>
              <?php echo currency::format(tax::get_tax(!empty($product->campaign['price']) ? $product->campaign['price'] : $product->price, $product->tax_class_id, $_GET['customer']), true, false, $_GET['currency_code'], $_GET['currency_value']); ?>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_sku', 'SKU'); ?></label>
            <div><?php echo functions::form_draw_hidden_field('sku', $product->sku); ?><?php echo $product->sku ? $product->sku : '-'; ?></div>
          </div>

          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_weight', 'Weight'); ?></label>
            <div><?php echo functions::form_draw_hidden_field('weight', $product->weight) . functions::form_draw_hidden_field('weight_class', $product->weight_class); ?><?php echo weight::format($product->weight, $product->weight_class); ?></div>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_quantity', 'Quantity'); ?></label>
            <div><?php echo functions::form_draw_decimal_field('quantity', !empty($_POST['quantity']) ? true : '1', 2); ?></div>
          </div>
        </div>

        <?php if (!empty($product->options_stock)) {?>
        <div class="row">
          <div class="form-group col-md-12">
          <table class="table table-default table-striped data-table">
            <tbody>
              <tr>
                <td><strong><?php echo language::translate('title_stock_option', 'Stock Option'); ?></strong></td>
                <td><strong><?php echo language::translate('title_sku', 'SKU'); ?></strong></td>
                <td><strong><?php echo language::translate('title_weight', 'Weight'); ?></strong></td>
                <td class="text-right"><strong><?php echo language::translate('title_in_stock', 'In Stock'); ?></strong></td>
              </tr>
              <?php foreach ($product->options_stock as $stock_option) { ?>
              <tr>
                <td><?php echo $stock_option['name'][$_GET['language_code']]; ?></td>
                <td><?php echo $stock_option['sku']; ?></td>
                <td><?php echo ($stock_option['weight'] > 0) ? weight::format($stock_option['weight'], $stock_option['weight_class']) : weight::format($product->weight, $product->weight_class); ?></td>
                <td class="text-right"><?php echo (float)$stock_option['quantity']; ?></td>
              </tr>
              <?php } ?>
            </tbody>
            <tfoot>
              <tr>
                <td></td>
                <td></td>
                <td></td>
                <td class="text-right"><strong><?php echo language::translate('title_total', 'Total'); ?>: </strong><?php echo (float)$product->quantity; ?></td>
              </tr>
            </tfoot>
          </table>
          <?php } ?>
          </div>
        </div>

        <p><?php echo functions::form_draw_button('add', language::translate('title_add', 'Add'), 'submit', 'class="btn btn-success btn-block"'); ?></p>

      <?php } ?>

      <?php echo functions::form_draw_form_end(); ?>
    </div>
  </div>
</div>

<script>
  $('select[name="product_id"]').change(function(){
    var url = '<?php echo document::ilink(null, array('product_id' => 'selected_product_id'), true); ?>';
    url = url.replace(/selected_product_id/, $(this).val());
    $('.ekko-lightbox.modal.in').modal('hide');
    $('<a href="'+ url +'" data-title="<?php echo functions::general_escape_js(language::translate('title_add_product', 'Add Product')); ?>" />').ekkoLightbox();
  });

  $('button[name="add"]').unbind('click').click(function(e){
    e.preventDefault();

    var error = false;

    var item = {
      id: '',
      product_id: $('select[name="product_id"]').val(),
      option_stock_combination: $('input[name="option_stock_combination"]').val(),
      options: {},
      name: $('input[name="name"]').val(),
      sku: $('input[name="sku"]').val(),
      weight: Number($('input[name="weight"]').val()),
      weight_class: $('input[name="weight_class"]').val(),
      quantity: Number($('input[name="quantity"]').val()),
      price: Number($('input[name="price"]').val()),
      tax: Number($('input[name="tax"]').val())
    };

    var selected_option_combinations = [];
    $('#options input[type="checkbox"]:checked').each(function(){
      if ($(this).val()) {
        if (!item.options[$(this).data('group')]) item.options[$(this).data('group')] = [];
        item.price += Number($(this).data('price-adjust'));
        item.tax += Number($(this).data('tax-adjust'));
        item.options[$(this).data('group')].push($(this).val());
        if ($(this).data('combination')) selected_option_combinations.push($(this).data('combination'));
      } else {
        if ($(this).attr('required')) {
          $(this).focus();
          error = true;
        }
      }
    });
    $('#options input[type="radio"]:checked').each(function(){
      if ($(this).val()) {
        item.price += Number($(this).data('price-adjust'));
        item.tax += Number($(this).data('tax-adjust'));
        item.options[$(this).data('group')] = $(this).val();
        if ($(this).data('combination')) selected_option_combinations.push($(this).data('combination'));
      } else {
        if ($(this).attr('required')) {
          $(this).focus();
          error = true;
        }
      }
    });
    $('#options select option:checked').each(function(){
      if ($(this).val()) {
        item.price += Number($(this).data('price-adjust'));
        item.tax += Number($(this).data('tax-adjust'));
        item.options[$(this).parent().data('group')] = $(this).val();
        if ($(this).data('combination')) selected_option_combinations.push($(this).data('combination'));
      } else {
        if ($(this).parent().attr('required')) {
          $(this).focus();
          error = true;
        }
      }
    });
    $('#options input[type!="radio"][type!="checkbox"]').each(function(){
      if ($(this).val()) {
        item.price += Number($(this).data('price-adjust'));
        item.tax += Number($(this).data('tax-adjust'));
        item.options[$(this).data('group')] = $(this).val();
        if ($(this).data('combination')) selected_option_combinations.push($(this).data('combination'));
      } else {
        if ($(this).attr('required')) {
          $(this).focus();
          error = true;
        }
      }
    });

    if (error) {
      alert("<?php echo htmlspecialchars(language::translate('error_missing_required_options', 'Missing required options')); ?>");
      return false;
    }

    selected_option_combinations.sort();
    var available_stock_options = <?php echo !empty($product) ? json_encode($product->options_stock) : '[]'; ?>;

    $.each(available_stock_options, function(i, stock_option) {
      var matched = false;
      $.each(stock_option.combination.split(','), function(j, current_stock_combination){
        if ($.inArray(current_stock_combination, selected_option_combinations) != -1) matched = true;
      });
      if (matched) {
        item.option_stock_combination = stock_option.combination;
        item.sku = stock_option.sku;
        if (item.weight > 0) {
          item.weight = stock_option.weight;
          item.weight_class = stock_option.weight_class;
        }
      }
    });

    addItem(item);
    $('.ekko-lightbox.modal.in').modal('hide');
  });
</script>