<?php

  if (!empty($_GET['category_id'])) {
    $category = new ctrl_category($_GET['category_id']);
  } else {
    $category = new ctrl_category();
  }

  if (empty($_POST)) {
    foreach ($category->data as $key => $value) {
      $_POST[$key] = $value;
    }

    if (!empty($_GET['parent_id'])) $_POST['parent_id'] = $_GET['parent_id'];
  }

  breadcrumbs::add(!empty($category->data['id']) ? language::translate('title_edit_category', 'Edit Category') : language::translate('title_add_new_category', 'Add New Category'));

  // Save data to database
  if (isset($_POST['save'])) {

    if (empty($_POST['name'])) notices::add('errors', language::translate('error_must_enter_name', 'You must enter a name'));
    if (!empty($_POST['code']) && database::num_rows(database::query("select id from ". DB_TABLE_CATEGORIES ." where id != '". (isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0) ."' and code = '". database::input($_POST['code']) ."' limit 1;"))) notices::add('errors', language::translate('error_code_database_conflict', 'Another entry with the given code already exists in the database'));
    if (!empty($category->data['id']) && $category->data['parent_id'] == $category->data['id']) notices::add('errors', language::translate('error_cannot_mount_category_to_self', 'Cannot mount category to itself'));

    if (empty(notices::$data['errors'])) {

      $fields = array(
        'status',
        'parent_id',
        'code',
        'google_taxonomy_id',
        'list_style',
        'dock',
        'image',
        'name',
        'short_description',
        'description',
        'keywords',
        'head_title',
        'h1_title',
        'meta_description',
        'priority',
      );

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $category->data[$field] = $_POST[$field];
      }

      $category->save();

      if (is_uploaded_file($_FILES['image']['tmp_name'])) {
        $category->save_image($_FILES['image']['tmp_name']);
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link('', array('doc' => 'catalog', 'category_id' => $_POST['parent_id']), array('app')));
      exit;
    }
  }

  // Delete from database
  if (isset($_POST['delete']) && $category) {

    $category->delete();

    notices::add('success', language::translate('success_post_deleted', 'Post deleted'));
    header('Location: '. document::link('', array('doc' => 'catalog', 'category_id' => $_POST['parent_id']), array('app')));
    exit();
  }
?>
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo !empty($category->data['id']) ? language::translate('title_edit_category', 'Edit Category') .': '. $category->data['name'][language::$selected['code']] : language::translate('title_add_new_category', 'Add New Category'); ?></h1>

<?php
  if (!empty($category->data['image'])) {
    echo '<p><img src="'. functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $category->data['image'], 150, 150) .'" /></p>';
  }
?>
<?php echo functions::form_draw_form_begin('category_form', 'post', false, true, 'style="max-width: 640px;"'); ?>

  <div class="">

    <ul class="nav nav-tabs">
      <li role="presentation" class="active"><a data-toggle="tab" href="#tab-general"><?php echo language::translate('title_general', 'General'); ?></a></li>
      <li role="presentation"><a data-toggle="tab" href="#tab-information"><?php echo language::translate('title_information', 'Information'); ?></a></li>
    </ul>

    <div class="tab-content">
      <div id="tab-general" class="tab-pane active">

        <div class="row">
          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_status', 'Status'); ?></label>
            <?php echo functions::form_draw_toggle('status', isset($_POST['status']) ? $_POST['status'] : '0', 'e/d'); ?>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_code', 'Code'); ?></label>
            <?php echo functions::form_draw_text_field('code', true); ?>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_name', 'Name'); ?></label>
            <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_input_field($language_code, 'name['. $language_code .']', true, ''); ?>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_parent_category', 'Parent Category'); ?></label>
            <?php echo functions::form_draw_categories_list('parent_id', true); ?>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_dock', 'Dock'); ?></label>
            <div class="checkbox">
              <label><?php echo functions::form_draw_checkbox('dock[]', 'menu', isset($_POST['dock']) ? $_POST['dock'] : 'menu'); ?> <?php echo language::translate('text_dock_in_menu', 'Dock in top menu'); ?></label><br/>
              <label><?php echo functions::form_draw_checkbox('dock[]', 'tree', isset($_POST['dock']) ? $_POST['dock'] : 'tree'); ?> <?php echo language::translate('text_dock_in_tree', 'Dock in category tree'); ?></label>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_list_style', 'List Style'); ?></label>
<?php
  $options = array(
    array(language::translate('title_columns', 'Columns'), 'columns'),
    array(language::translate('title_rows', 'Rows'), 'rows'),
  );
  echo functions::form_draw_select_field('list_style', $options, true);
?>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_keywords', 'Keywords'); ?></label>
              <?php echo functions::form_draw_text_field('keywords', true, 'data-size="large"'); ?>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-6">
            <label><?php echo ((isset($category->data['image']) && $category->data['image'] != '') ? language::translate('title_new_image', 'New Image') : language::translate('title_image', 'Image')); ?></label>
            <?php echo functions::form_draw_file_field('image', ''); ?><?php if (isset($category->data['image']) && $category->data['image'] != '') echo '</label>' . PHP_EOL . $category->data['image']; ?>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_priority', 'Priority'); ?></label>
              <?php echo functions::form_draw_number_field('priority', true); ?>
          </div>
        </div>

        <?php if (!empty($category->data['id'])) { ?>
        <div class="row">
          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_date_updated', 'Date Updated'); ?></label>
            <div><?php echo strftime('%e %b %Y %H:%M', strtotime($category->data['date_updated'])); ?></div>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_date_created', 'Date Created'); ?></label>
            <div><?php echo strftime('%e %b %Y %H:%M', strtotime($category->data['date_created'])); ?></div>
          </div>
        </div>
        <?php } ?>

      </div>

      <div id="tab-information" class="tab-pane">

        <div class="row">
          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_h1_title', 'H1 Title'); ?></label>
            <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_input_field($language_code, 'h1_title['. $language_code .']', true, ''); ?>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-12">
            <label><?php echo language::translate('title_short_description', 'Short Description'); ?></label>
            <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_input_field($language_code, 'short_description['. $language_code .']', true, 'data-size="large"'); ?>
          </div>
        </div>

        <div class="row">

          <div class="form-group col-md-12">
            <label><?php echo language::translate('title_description', 'Description'); ?></label>
            <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_wysiwyg_field($language_code, 'description['. $language_code .']', true, 'data-size="large" style="height: 240px;"'); ?>
          </div>
        </div>

        <div class="row">

          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_head_title', 'Head Title'); ?></label>
            <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_input_field($language_code, 'head_title['. $language_code .']', true, ''); ?>
          </div>

          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_meta_description', 'Meta Description'); ?></label>
            <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_input_field($language_code, 'meta_description['. $language_code .']', true, 'data-size="large"'); ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <p class="btn-group">
    <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
    <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
    <?php echo (isset($category->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
  </p>

<?php echo functions::form_draw_form_end(); ?>