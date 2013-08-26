<?php
  
  if (!isset($_GET['query'])) $_GET['query'] = '';
  if (!isset($_GET['language_code'])) $_GET['language_code'] = 'sv';
  if (!isset($_GET['page'])) $_GET['page'] = 1;
  
  if (isset($_POST['save'])) {
  
    foreach ($_POST['translations'] as $translation) {
      $sql_update_fields = '';
      foreach (array_keys($system->language->languages) as $language) {
        $sql_update_fields .= "text_".$language ." = '". $system->database->input(trim($translation['text_'.$language]), !empty($translation['html']) ? true : false) ."', ";
      }
      $system->database->query(
        "update ". DB_TABLE_TRANSLATIONS ."
        set
        html = ". (!empty($translation['html']) ? 1 : 0) .",
          ". $sql_update_fields ."
          date_updated = '". date('Y-m-d H:i:s') ."'
        where id = '". $system->database->input($translation['id']) ."'
        limit 1;"
      );
    }
    
    $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes were successfully saved.'));
    
    header('Location: '. $system->document->link('', array(), true));
    exit;
  }
  
  if (isset($_POST['delete'])) {
    $system->database->query(
      "delete from ". DB_TABLE_TRANSLATIONS ."
      where id = '". $system->database->input($_POST['translation_id']) ."'
      limit 1;"
    );
    
    $system->notices->add('success', $system->language->translate('success_translated_deleted', 'Translation was successfully deleted'));
    
    header('Location: '. $system->document->link('', array(), true));
    exit;
  }
?>

<div style="float: right;">
  <?php echo $system->functions->form_draw_form_begin('search_form', 'get'); ?>
    <?php if (!empty($_GET)) foreach ($_GET as $key => $value) { if (!in_array($key, array('query', 'system'))) echo $system->functions->form_draw_hidden_field($key, $value); } ?>
    <?php echo $system->functions->form_draw_search_field('query', true, 'placeholder="'. $system->language->translate('text_search_phrase_or_keyword') .'"'); ?>
  <?php echo $system->functions->form_draw_form_end(); ?>
</div>

<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo $system->language->translate('title_search_translations', 'Search Translations'); ?></h1>

<?php echo $system->functions->form_draw_form_begin('translation_form', 'post'); ?>

<table align="center" width="100%" class="dataTable">
  <tr class="header">
    <th align="left"><?php echo $system->language->translate('title_code', 'Code');?></th>
    <?php foreach (array_keys($system->language->languages) as $language_code) echo '<th nowrap="nowrap" align="left">'. $system->language->languages[$language_code]['name'] .'</th>'; ?>
    <th>&nbsp;</th>
  </tr>
<?php
  $sql_where_fields = "";
  foreach (array_keys($system->language->languages) as $language) {
    $sql_where_fields .= " or text_".$language ." like '%". str_replace('%', "\\%", $system->database->input($_GET['query'])) ."%' ";
  }

  $translations_query = $system->database->query(
    "select * from ". DB_TABLE_TRANSLATIONS ."
    where code like '%". str_replace('%', "\\%", $system->database->input($_GET['query'])) ."%'". $sql_where_fields ."
    order by date_created desc;"
  );

  if ($system->database->num_rows($translations_query) > 0) {
    
  // Jump to data for current page
    if ($_GET['page'] > 1) $system->database->seek($translations_query, ($system->settings->get('data_table_rows_per_page') * ($_GET['page']-1)));
    
    $page_items = 0;
    while ($row=$system->database->fetch($translations_query)) {
    
      if (!isset($rowclass) || $rowclass == 'even') {
        $rowclass = 'odd';
      } else {
        $rowclass = 'even';
      }
      
      $row['pages'] = rtrim($row['pages'], ',');
?>
  <tr class="<?php echo $rowclass; ?>">
    <td align="left" nowrap="nowrap"><?php echo $row['code']; ?><br />
      (<a href="javascript:alert('<?php echo str_replace(array('\'', ','), array('', '\\n'), rtrim($row['pages'], ',')); ?>');"><?php echo sprintf($system->language->translate('text_shared_by_pages', 'Shared by %d pages'), count(explode(',', $row['pages']))); ?></a>)<br />
      <?php echo $system->functions->form_draw_checkbox('translations['. $row['code'] .'][html]', '1', (isset($_POST['translations'][$row['code']]['html']) ? $_POST['translations'][$row['code']]['html'] : $row['html'])); ?> <?php echo $system->language->translate('text_html_enabled', 'HTML enabled'); ?>
    </td>
    <?php foreach (array_keys($system->language->languages) as $language_code) echo '<td>'. $system->functions->form_draw_hidden_field('translations['. $row['code'] .'][id]', $row['id']) . $system->functions->form_draw_textarea('translations['. $row['code'] .'][text_'.$language_code.']', $row['text_'.$language_code], 'rows="2" style="width: 200px"') .'</td>'; ?>
    <td align="right"><a href="javascript:delete_translation('<?php echo $row['id']; ?>');" onclick="if (!confirm('<?php echo $system->language->translate('text_are_you_sure', 'Are you sure?'); ?>')) return false;"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" alt="<?php echo $system->language->translate('text_remove', 'Remove'); ?>" /></a></td>
  </tr>
<?php      
        if (++$page_items == $system->settings->get('data_table_rows_per_page')) break;
      }
    } else {
?>
  <tr class="odd">
    <td colspan="<?php echo 2+count($system->language->languages); ?>" align="left" nowrap="nowrap"><?php echo $system->language->translate('text_no_entries_found_in_database', 'No entries found in database'); ?></td>
  </tr>
<?php
    }
?>
</table>
<p align="right"><?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save'), 'submit', '', 'save'); ?></p>
<?php echo $system->functions->form_draw_form_end(); ?>
<script type="text/javascript">
  function delete_translation(id) {
    var form = $('<?php
      echo str_replace(array("\r", "\n"), '', $system->functions->form_draw_form_begin('delete_translation_form', 'post')
                                            . $system->functions->form_draw_hidden_field('translation_id', '\'+ id +\'')
                                            . $system->functions->form_draw_hidden_field('delete', 'true')
                                            . $system->functions->form_draw_form_end()
      );
    ?>');
    $(document.body).append(form);
    form.submit();
  }
</script>
<?php
// Display page links
  echo $system->functions->draw_pagination(ceil($system->database->num_rows($translations_query)/$system->settings->get('data_table_rows_per_page')));
?>