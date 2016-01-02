<div id="checkout-shipping">
  <h2><?php echo language::translate('title_shipping', 'Shipping'); ?></h2>
  
  <ul id="shipping-options">
<?php
  foreach ($options as $module) {
    foreach ($module['options'] as $option) {
?>
    <li class="option<?php echo ($module['id'].':'.$option['id'] == $selected['id']) ? ' selected' : false; ?>">
    <?php echo functions::form_draw_form_begin('shipping_form') . functions::form_draw_hidden_field('selected_shipping', $module['id'].':'.$option['id'], $selected['id']); ?>
      <div class="icon-wrapper"><img src="<?php echo functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . $option['icon'], 200, 70, 'FIT_ONLY_BIGGER_USE_WHITESPACING'); ?>" /></div>
      <div class="title"><?php echo $module['title']; ?></div>
      <div class="name"><?php echo $option['name']; ?></div>
      <div class="description"><?php echo $option['fields'] . $option['description']; ?></div>
      <div class="footer">
        <div class="price"><?php if ($option['cost'] != 0) echo '+ ' . currency::format(tax::get_price($option['cost'], $option['tax_class_id'])); ?></div>
        <div class="select">
<?php
  if ($module['id'].':'.$option['id'] == $selected['id']) {
    if (!empty($option['fields'])) {
      echo functions::form_draw_button('set_shipping', language::translate('title_update', 'Update'), 'submit');
    } else {
      echo functions::form_draw_button('set_shipping', language::translate('title_selected', 'Selected'), 'submit', 'class="active"');
    }
  } else {
    echo functions::form_draw_button('set_shipping', language::translate('title_select', 'Select'), 'submit');
  }
?>
        </div>
      </div>
    <?php echo functions::form_draw_form_end(); ?>
    </li>
<?php
    }
  }
?>
  </ul>
</div>