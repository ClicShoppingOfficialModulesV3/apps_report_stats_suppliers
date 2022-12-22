<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @licence MIT - Portion of osCommerce 2.4
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */

  use ClicShopping\OM\HTML;
  use ClicShopping\OM\Registry;

  use ClicShopping\Sites\ClicShoppingAdmin\HTMLOverrideAdmin;
  use ClicShopping\Apps\Configuration\OrdersStatus\Classes\ClicShoppingAdmin\OrderStatusAdmin;
  use ClicShopping\OM\ObjectInfo;

  $CLICSHOPPING_Template = Registry::get('TemplateAdmin');
  $CLICSHOPPING_Language = Registry::get('Language');
  $CLICSHOPPING_StatsSuppliers = Registry::get('StatsSuppliers');

  $page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? (int)$_GET['page'] : 1;

  // initialisation des dates et recuperation
  if (isset($_POST['expires_date'])) {
    $expires_date = HTML::sanitize($_POST['expires_date']);
  } else {
    $expires_date = null;
  }

  if (isset($_POST['date_scheduled'])) {
    $date_scheduled = HTML::sanitize($_POST['date_scheduled']);
  } else {
    $date_scheduled = null;
  }


  if (isset($_POST['orders_status'])) {
    $orders_status = HTML::sanitize($_POST['orders_status']);
  } else {
    $orders_status = '';
  }

  if (is_null($expires_date)) {
    $expires_date = date('Y-m-d 23:59:59');
    $end_date = $expires_date;
  } else {
    $end_date = $expires_date;
  }

  if (is_null($date_scheduled)) {
    $date_scheduled = date('Y-m-d 00:00:00');
    $start_date = $date_scheduled;
  } else {
    $start_date = $date_scheduled;
  }

  // Date management
  $parameters = ['expires_date' => '',
    'date_scheduled' => $start_date,
    'orders_status' => $orders_status
  ];

  $sInfo = new ObjectInfo($parameters);
?>
<div class="contentBody">
  <div class="row">
    <div class="col-md-12">
      <div class="card card-block headerCard">
        <div class="row">
          <span
            class="col-md-1 logoHeading"><?php echo HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'categories/suppliers.gif', $CLICSHOPPING_StatsSuppliers->getDef('entry_heading_title'), '40', '40'); ?></span>
          <span
            class="col-md-3 pageHeading"><?php echo '&nbsp;' . $CLICSHOPPING_StatsSuppliers->getDef('entry_heading_title'); ?></span>
          <span class="col-md-8">
            <?php echo HTML::form('date_range', $CLICSHOPPING_StatsSuppliers->link('StatsSuppliers'), 'post', 'class="form-inline" role="form"'); ?>
            <div class="row col-md-12">
              <div class="col-md-8">
<?php
  echo HTML::inputField('date_scheduled', $sInfo->date_scheduled, 'placeholder="' . $CLICSHOPPING_StatsSuppliers->getDef('entry_start_date') . '"', 'date') . ' ';
  echo HTML::inputField('expires_date', $sInfo->expires_date, 'placeholder="' . $CLICSHOPPING_StatsSuppliers->getDef('entry_to_date') . '"', 'date') . ' ';
  echo $CLICSHOPPING_StatsSuppliers->getDef('entry_status') . '&nbsp;' . OrderStatusAdmin::getDropDownOrderStatus('orders_status', $sInfo->orders_status, 'no');
?>
              </div>
              <div class="col-md-3 text-end">
<?php
  echo HTML::button($CLICSHOPPING_StatsSuppliers->getDef('button_update'), null, null, 'success') . ' ';

  if (isset($_POST['orders_status'])) {
    echo HTML::button($CLICSHOPPING_StatsSuppliers->getDef('button_reset'), null, $CLICSHOPPING_StatsSuppliers->link('StatsSuppliers'), 'warning');
  }
?>
              </div>
              </form>
            </div>
          </span>
        </div>
      </div>
    </div>
  </div>
  <div class="separator"></div>
  <table border="0" width="100%" cellspacing="0" cellpadding="2">
    <td>
      <table class="table table-sm table-hover table-striped">
        <thead>
        <tr class="dataTableHeadingRow">
          <th><?php echo $CLICSHOPPING_StatsSuppliers->getDef('table_heading_suppliers_id'); ?></th>
          <th class="text-center"><?php echo $CLICSHOPPING_StatsSuppliers->getDef('table_heading_suppliers'); ?></th>
          <th class="text-center"><?php echo $CLICSHOPPING_StatsSuppliers->getDef('table_heading_manager'); ?></th>
          <th class="text-center"><?php echo $CLICSHOPPING_StatsSuppliers->getDef('table_heading_phone'); ?></th>
          <th class="text-center"><?php echo $CLICSHOPPING_StatsSuppliers->getDef('table_heading_email'); ?></th>
          <th class="text-center"><?php echo $CLICSHOPPING_StatsSuppliers->getDef('table_heading_order'); ?></th>
          <th class="text-center"><?php echo $CLICSHOPPING_StatsSuppliers->getDef('table_heading_action'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
          $Qsuppliers = $CLICSHOPPING_StatsSuppliers->db->prepare('select  SQL_CALC_FOUND_ROWS s.suppliers_id,
                                                                             s.suppliers_name,
                                                                             s.suppliers_manager,
                                                                             s.suppliers_phone,
                                                                             s.suppliers_email_address,
                                                                             sum(op.products_quantity) as sum_qty
                                                    from :table_orders_products  op
                                                      left join :table_products  p ON op.products_id = p.products_id
                                                      left join :table_suppliers s on p.suppliers_id = s.suppliers_id
                                                      left join :table_orders  o ON op.orders_id = o.orders_id
                                                    where o.date_purchased between :date_scheduled  and :expires_date
                                                    and o.orders_status = :orders_status
                                                    and s.suppliers_id is not null
                                                    group by  s.suppliers_id
                                                    order by  s.suppliers_id
                                                    limit :page_set_offset,
                                                          :page_set_max_results
                                                    ');


          $Qsuppliers->bindValue(':date_scheduled', $date_scheduled);
          $Qsuppliers->bindValue(':expires_date', $expires_date);
          $Qsuppliers->bindInt(':orders_status', $sInfo->orders_status);
          $Qsuppliers->setPageSet((int)MAX_DISPLAY_SEARCH_RESULTS_ADMIN);
          $Qsuppliers->execute();

          $listingTotalRow = $Qsuppliers->getPageSetTotalRows();

          if ($listingTotalRow > 0) {

            while ($Qsuppliers->fetch()) {
?>
              <tr onMouseOver="rowOverEffect(this)" onMouseOut="rowOutEffect(this)">
                <th scope="row"><?php echo $Qsuppliers->valueInt('suppliers_id'); ?></th>
                <td><?php echo $Qsuppliers->value('suppliers_name'); ?></td>
                <td><?php echo $Qsuppliers->value('suppliers_manager'); ?></td>
                <td class="text-center"><?php echo $Qsuppliers->value('suppliers_phone'); ?></td>
                <td><?php echo $Qsuppliers->value('suppliers_email_address'); ?></td>
                <td class="text-center"><?php echo $Qsuppliers->valueInt('sum_qty'); ?></td>
                <td class="text-end">
                  <?php
                    echo '<a href="mailto:' . $Qsuppliers->value('suppliers_email_address') . '">' . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/email.gif', $CLICSHOPPING_StatsSuppliers->getDef('icon_edit')) . '</a>';
                    echo '&nbsp;';
                    echo '<a href="' . $CLICSHOPPING_StatsSuppliers->link('StatsSuppliersOrders&page=' . $page . '&bID=' . $Qsuppliers->valueInt('suppliers_id') . '&bDS=' . HTMLOverrideAdmin::sanitizeReplace($date_scheduled) . '&bED=' . HTMLOverrideAdmin::sanitizeReplace($expires_date) . '&bOS=' . $_POST['orders_status']) . '" target="_blank" rel="noopener">' . HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/order.gif', $CLICSHOPPING_StatsSuppliers->getDef('icon_edit')) . '</a>';
                  ?>
                </td>
              </tr>
<?php
            }
          } // end $listingTotalRow
?>
        </tbody>
      </table>
    </td>
  </table>
<?php
    if ($listingTotalRow > 0) {
?>
      <div class="row">
        <div class="col-md-12">
          <div
            class="col-md-6 float-start pagenumber hidden-xs TextDisplayNumberOfLink"><?php echo $Qsuppliers->getPageSetLabel($CLICSHOPPING_StatsSuppliers->getDef('text_display_number_of_link')); ?></div>
          <div class="float-end text-end"> <?php echo $Qsuppliers->getPageSetLinks(); ?></div>
        </div>
      </div>
<?php
    }
?>
</div>