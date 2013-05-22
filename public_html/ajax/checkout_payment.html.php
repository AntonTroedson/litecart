<?php
  if ($_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME'] == __FILE__) {
    require_once('../includes/app_header.inc.php');
    header('Content-type: text/html; charset='. $system->language->selected['charset']);
    $system->document->layout = 'default';
    $system->document->viewport = 'ajax';
  }
  
  if ($system->cart->data['total']['items'] == 0) return;
  
  $payment = new payment();
  
  if (empty($system->customer->data['country_code'])) return;
  
  if (!empty($_POST['set_payment'])) {
    list($module_id, $option_id) = explode(':', $_POST['selected_payment']);
    $payment->select($module_id, $option_id);
    header('Location: '. (($_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME'] == __FILE__) ? $_SERVER['REQUEST_URI'] : $system->document->link(WS_DIR_HTTP_HOME . 'checkout.php')));
    exit;
  }
  
  $options = $payment->options();
  
  if (!empty($payment->data['selected']['id'])) {
    list($module_id, $option_id) = explode(':', $payment->data['selected']['id']);
    if (!isset($options[$module_id]['options'][$option_id])) {
      $payment->data['selected'] = array();
    }
  }
  
  if (empty($options)) return;
  
  if (empty($payment->data['selected'])) {
    $payment->set_cheapest();
  }
  
// Hide if only 1 option
  //if (count($options) == 1
  //&& count($options[key($options)]['options']) == 1
  //&& empty($options[key($options)][key($options[key($options)]['options'])]['fields'])) return;
  
?>
<div class="box" id="box-checkout-payment">
  <div class="heading"><h2><?php echo $system->language->translate('title_payment', 'Payment'); ?></h2></div>
  <div class="content listing-wrapper">
<?php
  foreach ($options as $module) {
    foreach ($module['options'] as $option) {
?>
    <div class="option-wrapper<?php echo ($module['id'].':'.$option['id'] == $payment->data['selected']['id']) ? ' selected' : false; ?>">
      <?php echo $system->functions->form_draw_form_begin('payment_form', 'post') . $system->functions->form_draw_hidden_field('selected_payment', $module['id'].':'.$option['id'], $payment->data['selected']['id']); ?>
        <div class="icon"><?php echo is_file(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . $option['icon']) ? '<img src="'. $system->functions->image_resample(FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME . $option['icon'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 160, 60, 'FIT_USE_WHITESPACING') .'" width="160" height="60" />' : '&nbsp;'; ?></div>
        <div class="title"><?php echo $module['title']; ?></div>
        <div class="name"><?php echo $option['name']; ?></div>
        <div class="description"><?php echo $option['fields'] . $option['description']; ?></div>
        <div class="footer" style="position: relative;">
          <div class="price" style="position: absolute; left: 0; bottom: 0;"><?php echo $system->currency->format($system->tax->calculate($option['cost'], $option['tax_class_id'])); ?></div>
          <div class="select" style="position: absolute; right: 0; bottom: 0;">
<?php
  if ($module['id'].':'.$option['id'] == $payment->data['selected']['id']) {
    if (!empty($option['fields'])) {
      echo $system->functions->form_draw_button('set_payment', $system->language->translate('title_update', 'Update'), 'submit');
    } else {
      echo '<span class="button active">'. $system->language->translate('title_select', 'Select') .'</span>';
    }
  } else {
    echo $system->functions->form_draw_button('set_payment', $system->language->translate('title_select', 'Select'), 'submit');
  }
?>
          </div>
        </div>
      <?php echo $system->functions->form_draw_form_end(); ?>
    </div>
<?php
    }
  }
?>
  </div>
</div>
<?php
  if ($_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME'] == __FILE__) {
    require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
  }
?>