<div style="float: right;"><?php echo $system->functions->form_draw_link_button($system->document->link('', array('app' => $_GET['app'], 'doc' => 'edit_supplier')), $system->language->translate('title_add_new_supplier', 'Add New Supplier'), '', 'add'); ?></div>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo $system->language->translate('title_suppliers', 'Suppliers'); ?></h1>

<?php echo $system->functions->form_draw_form_begin('suppliers_form', 'post'); ?>
<table class="dataTable" width="100%">
  <tr class="header">
    <th><?php echo $system->functions->form_draw_checkbox('checkbox_toggle', '', ''); ?></th>
    <th width="100%" align="left"><?php echo $system->language->translate('title_name', 'Name'); ?></th>
    <th>&nbsp;</th>
  </tr>
<?php
    $suppliers_query = $system->database->query(
      "select id, name from ". DB_TABLE_SUPPLIERS ."
      order by name asc;"
    );
    
    if ($system->database->num_rows($suppliers_query) > 0) {
      while ($supplier = $system->database->fetch($suppliers_query)) {
        if (!isset($rowclass) || $rowclass == 'even') {
          $rowclass = 'odd';
        } else {
          $rowclass = 'even';
        }
?>
  <tr class="<?php echo $rowclass; ?>">
    <td><?php echo $system->functions->form_draw_checkbox('suppliers['. $supplier['id'] .']', $supplier['id']); ?></td>
    <td><a href="<?php echo $system->document->href_link('', array('doc' => 'edit_supplier', 'supplier_id' => $supplier['id']), array('app')); ?>"><?php echo $supplier['name']; ?></a></td>
    <td><a href="<?php echo $system->document->href_link('', array('app' => $_GET['app'], 'doc' => 'edit_supplier', 'supplier_id' => $supplier['id'])); ?>"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/edit.png" width="16" height="16" align="absbottom" /></a></td>
  </tr>
<?php
      }
    }
?>
  <tr class="footer">
    <td colspan="3" align="left"><?php echo $system->language->translate('title_suppliers', 'Suppliers'); ?>: <?php echo $system->database->num_rows($suppliers_query); ?></td>
  </tr>
</table>

<script>
  $(".dataTable input[name='checkbox_toggle']").click(function() {
    $(this).closest("form").find(":checkbox").each(function() {
      $(this).attr('checked', !$(this).attr('checked'));
    });
    $(".dataTable input[name='checkbox_toggle']").attr("checked", true);
  });

  $('.dataTable tr').click(function(event) {
    if ($(event.target).is('input:checkbox')) return;
    if ($(event.target).is('a, a *')) return;
    if ($(event.target).is('th')) return;
    $(this).find('input:checkbox').trigger('click');
  });
</script>

<?php echo $system->functions->form_draw_form_end(); ?>