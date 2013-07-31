<?php
  
  if (!empty($_POST['import'])) {
    
    if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
    
      $csv = file_get_contents($_FILES['file']['tmp_name']);
      
      $csv = $system->functions->csv_decode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset']);
      
      foreach ($csv as $row) {
        $translation_query = $system->database->query(
          "select code from ". DB_TABLE_TRANSLATIONS ."
          where code = '". $row['code'] ."'
          limit 1;"
        );
        $translation = $system->database->fetch($translation_query);
        
        if (empty($translation)) {
          $system->database->query(
            "insert into ". DB_TABLE_TRANSLATIONS ."
            (code)
            values ('". $system->database->input($row['code']) ."');"
          );
        }
        
        foreach (array_slice(array_keys($row), 1) as $language_code) {
          if (empty($row['text_'.$language_code]) || empty($_POST['overwrite'])) {
            $system->database->query(
              "update ". DB_TABLE_TRANSLATIONS ."
              set text_". $language_code ." = '". $row[$language_code] ."'
              where code = '". $row['code'] ."'
              limit 1;"
            );
          }
        }
      }
      
      $system->notices->add('success', $system->language->translate('success_translations_imported', 'Translations successfully imported'));
      
      header('Location: '. $system->document->link('', array('app' => $_GET['app'], 'doc' => $_GET['doc'])));
      exit;
    }
  }
  
  if (!empty($_POST['export'])) {
    
    if (empty($_POST['language_codes'])) $system->notices->add('errors', $system->language->translate('error_must_select_at_least_one_language', 'You must select at least one language'));
    
    if (empty($system->notices->data['errors'])) {
      
      ob_clean();
      
      $csv = array();
      
      $_POST['language_codes'] = array_filter($_POST['language_codes']);
      
      $translations_query = $system->database->query(
        "select * from ". DB_TABLE_TRANSLATIONS ."
        order by date_created asc;"
      );
      
      while ($translation = $system->database->fetch($translations_query)) {
      
        $row = array('code' => $translation['code']);
        foreach ($_POST['language_codes'] as $language_code) {
          $row[$language_code] = $translation['text_'.$language_code];
        }
        
        $csv[] = $row;
      }
      
      if ($_POST['output'] == 'screen') {
        header('Content-type: text/plain; charset='. $_POST['charset']);
      } else {
        header('Content-type: application/csv; charset='. $_POST['charset']);
        header('Content-Disposition: attachment; filename=translations-'. implode('-', $_POST['language_codes']) .'.csv');
      }
      
      switch($_POST['eol']) {
        case 'Linux':
          echo $system->functions->csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'], "\r");
          break;
        case 'Max':
          echo $system->functions->csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'], "\n");
          break;
        case 'Win':
        default:
          echo $system->functions->csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'], "\r\n");
          break;
      }
      
      exit;
    }
  }

?>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" border="0" align="absmiddle" style="margin-right: 10px;" /><?php echo $system->language->translate('title_csv_import_export', 'CSV Import/Export'); ?></h1>

<h2><?php echo $system->language->translate('title_translations', 'Translations'); ?></h2>
<p><strong><?php echo $system->language->translate('description_scan_before_importing_translations', 'It is recommended to always scan your installation for unregistered translations before performing an import or export.'); ?></strong></p>

<table style="width: 100%;">
  <tr>
    <td style="width: 50%; vertical-align: top;">
      <?php echo $system->functions->form_draw_form_begin('import_form', 'post', '', true); ?>
      <h3><?php echo $system->language->translate('title_import_from_csv', 'Import From CSV'); ?></h3>
      <table border="0" cellpadding="5" cellspacing="0" style="margin: -5px;">
        <tr>
          <td colspan="3"><?php echo $system->language->translate('title_csv_file', 'CSV File'); ?></br>
            <?php echo $system->functions->form_draw_file_field('file'); ?></td>
        </tr>
        <tr>
          <td><?php echo $system->language->translate('title_delimiter', 'Delimiter'); ?><br />
            <?php echo $system->functions->form_draw_select_field('delimiter', array(array(', ('. $system->language->translate('text_default', 'default') .')', ','), array(';'), array('TAB', "\t"), array('|'))); ?></td>
          <td><?php echo $system->language->translate('title_enclosure', 'Enclosure'); ?><br />
            <?php echo $system->functions->form_draw_select_field('enclosure', array(array('" ('. $system->language->translate('text_default', 'default') .')', '"'))); ?></td>
          <td><?php echo $system->language->translate('title_escape_character', 'Escape Character'); ?><br />
            <?php echo $system->functions->form_draw_select_field('escapechar', array(array('" ('. $system->language->translate('text_default', 'default') .')', '"'), array('\\', '\\'))); ?></td>
        </tr>
        <tr>
          <td><?php echo $system->language->translate('title_charset', 'Charset'); ?><br />
            <?php echo $system->functions->form_draw_select_field('charset', array(array('UTF-8'), array('ISO-8859-1'))); ?></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td colspan="3"><label><?php echo $system->functions->form_draw_checkbox('overwrite', '1'); ?> <?php echo $system->language->translate('text_overwrite_existing_entries', 'Overwrite existing entries'); ?></label></td>
        </tr>
        <tr>
          <td colspan="3"><?php echo $system->functions->form_draw_button('import', $system->language->translate('title_import', 'Import'), 'submit'); ?></td>
        </tr>
      </table>
      <?php echo $system->functions->form_draw_form_end(); ?>
    </td>
    <td style="width: 50%; vertical-align: top;">
      <?php echo $system->functions->form_draw_form_begin('export_form', 'post'); ?>
      <h3><?php echo $system->language->translate('title_export_to_csv', 'Export To CSV'); ?></h3>
      <?php echo $system->language->translate('title_languages', 'Languages'); ?><br />
      <table border="0" cellpadding="5" cellspacing="0" style="margin: -5px;">
        <tr>
          <td colspan="3"><?php echo $system->functions->form_draw_languages_list('language_codes[]', true, true).' '; ?></td>
        </tr>
        <tr>
          <td><?php echo $system->language->translate('title_delimiter', 'Delimiter'); ?><br />
            <?php echo $system->functions->form_draw_select_field('delimiter', array(array(', ('. $system->language->translate('text_default', 'default') .')', ','), array(';'), array('TAB', "\t"), array('|'))); ?></td>
          <td><?php echo $system->language->translate('title_enclosure', 'Enclosure'); ?><br />
            <?php echo $system->functions->form_draw_select_field('enclosure', array(array('" ('. $system->language->translate('text_default', 'default') .')', '"'))); ?></td>
          <td><?php echo $system->language->translate('title_escape_character', 'Escape Character'); ?><br />
            <?php echo $system->functions->form_draw_select_field('escapechar', array(array('" ('. $system->language->translate('text_default', 'default') .')', '"'), array('\\', '\\'))); ?></td>
        </tr>
        <tr>
          <td><?php echo $system->language->translate('title_charset', 'Charset'); ?><br />
            <?php echo $system->functions->form_draw_select_field('charset', array(array('UTF-8'), array('ISO-8859-1'))); ?></td>
          <td><?php echo $system->language->translate('title_line_ending', 'Line Ending'); ?><br />
            <?php echo $system->functions->form_draw_select_field('eol', array(array('Win'), array('Mac'), array('Linux'))); ?></td>
          <td><?php echo $system->language->translate('title_output', 'Output'); ?><br />
            <?php echo $system->functions->form_draw_select_field('output', array(array($system->language->translate('title_file', 'File'), 'file'), array($system->language->translate('title_screen', 'Screen'), 'screen'))); ?></td>
        </tr>
        <tr>
          <td colspan="3"><?php echo $system->functions->form_draw_button('export', $system->language->translate('title_export', 'Export'), 'submit'); ?></td>
        </tr>
      </table>
      <?php echo $system->functions->form_draw_form_end(); ?>
    </td>
  </tr>
</table>