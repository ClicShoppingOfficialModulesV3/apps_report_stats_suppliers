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
  use ClicShopping\OM\CLICSHOPPING;

  use ClicShopping\Sites\ClicShoppingAdmin\HTMLOverrideAdmin;

  $CLICSHOPPING_Template = Registry::get('TemplateAdmin');
  $CLICSHOPPING_Language = Registry::get('Language');
  $CLICSHOPPING_StatsSuppliers = Registry::get('StatsSuppliers');

  $page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? (int)$_GET['page'] : 1;

  // initialisation des dates et recuperation
  $expires_date = HTML::sanitize($_GET['bED']);
  $date_scheduled = HTML::sanitize($_GET['bDS']);
  $suppliers_id = HTML::sanitize($_GET['bOS']);
  $orders_status = HTML::sanitize($_GET['bID']);

  $Qstatus = $CLICSHOPPING_StatsSuppliers->db->prepare('select orders_status_id,
                                                               orders_status_name
                                                        from :table_orders_status
                                                        where language_id = :language_id
                                                        and orders_status_id = :orders_status_id
                                                      ');
  $Qstatus->bindInt(':language_id', (int)$CLICSHOPPING_Language->getId());
  $Qstatus->bindInt(':orders_status_id', (int)$orders_status);
  $Qstatus->execute();
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

          <span
            class="col-md-2"> <?php echo $CLICSHOPPING_StatsSuppliers->getDef('entry_start_date') . '<br />' . $CLICSHOPPING_StatsSuppliers->getDef('period') . $_GET['bDS']; ?></span>
          <span
            class="col-md-2"><?php echo $CLICSHOPPING_StatsSuppliers->getDef('entry_to_date') . '<br />au ' . $_GET['bED']; ?></span>
          <span
            class="col-md-2"><?php echo $CLICSHOPPING_StatsSuppliers->getDef('entry_status') . '<br /> <br />' . $Qstatus->value('orders_status_name'); ?></span>
          <span class="col-md-2 text-end">
<?php
  echo HTML::form('print_pdf', $CLICSHOPPING_StatsSuppliers->link('StatsSuppliersCustomersPdf&bID=' . (int)$_GET['bID'] . '&bDS=' . $_GET['bDS'] . '&bED=' . HTMLOverrideAdmin::sanitizeReplace($_GET['bED'] . '&bOS=' . (int)$_GET['bOS'])));
  echo HTML::button($CLICSHOPPING_StatsSuppliers->getDef('button_print'), null, null, 'info');
?>
            </form>
<?php
  echo HTML::form('back', $CLICSHOPPING_StatsSuppliers->link('StatsSuppliers'));
  echo HTML::button($CLICSHOPPING_StatsSuppliers->getDef('button_back'), null, null, 'primary');
?>
            </form>
          </span>
        </div>
      </div>
    </div>
  </div>

  <div class="separator"></div>
  <table class="table table-sm table-hover table-striped">
    <thead>
    <tr class="dataTableHeadingRow">
      <th class="text-center"><?php echo $CLICSHOPPING_StatsSuppliers->getDef('table_heading_suppliers_id'); ?></th>
      <th
        class="text-center"><?php echo $CLICSHOPPING_StatsSuppliers->getDef('table_heading_suppliers_name'); ?></th>
      <th><?php echo $CLICSHOPPING_StatsSuppliers->getDef('table_heading_quantity'); ?></th>
      <th><?php echo $CLICSHOPPING_StatsSuppliers->getDef('table_heading_customers_id'); ?></th>
      <th><?php echo $CLICSHOPPING_StatsSuppliers->getDef('table_heading_cutomers_name'); ?></th>
      <th class="text-center"><?php echo $CLICSHOPPING_StatsSuppliers->getDef('table_heading_model'); ?></th>
      <th class="text-center"><?php echo $CLICSHOPPING_StatsSuppliers->getDef('table_heading_products_name'); ?></th>
      <th
        class="text-center"><?php echo $CLICSHOPPING_StatsSuppliers->getDef('table_heading_products_options'); ?></th>
      <th
        class="text-center"><?php echo $CLICSHOPPING_StatsSuppliers->getDef('table_heading_products_options_values'); ?></th>
      <th class="text-center"><?php echo $CLICSHOPPING_StatsSuppliers->getDef('table_heading_action'); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php

      $QsuppliersCustomer = $CLICSHOPPING_StatsSuppliers->db->prepare('select SQL_CALC_FOUND_ROWS o.customers_id,
                                                                                      o.customers_name,
                                                                                      o.orders_id,
                                                                                      s.suppliers_id,
                                                                                      s.suppliers_name,
                                                                                      p.products_model,
                                                                                      op.products_name,
                                                                                      op.products_quantity as sum_qty,
                                                                                      opa.products_options,
                                                                                      opa.products_options_values
                                                            from :table_orders_products op
                                                               left join :table_products p ON op.products_id = p.products_id
                                                               left join :table_suppliers s on p.suppliers_id = s.suppliers_id
                                                               left join :table_orders o ON op.orders_id = o.orders_id
                                                               left join :table_orders_products_attributes opa ON op.orders_products_id = opa.orders_products_id
                                                            where o.date_purchased between :date_scheduled and :expires_date
                                                            and s.suppliers_id = :suppliers_id
                                                            and o.orders_status = :orders_status
                                                            and o.orders_archive  = 0
                                                            order by o.customers_name
                                                        ');
      $QsuppliersCustomer->bindValue(':date_scheduled', $date_scheduled);
      $QsuppliersCustomer->bindValue(':expires_date', $expires_date);
      $QsuppliersCustomer->bindInt(':orders_status', $orders_status);
      $QsuppliersCustomer->bindInt(':suppliers_id', $suppliers_id);


      $QsuppliersCustomer->setPageSet((int)MAX_DISPLAY_SEARCH_RESULTS_ADMIN);
      $QsuppliersCustomer->execute();

      $listingTotalRow = $QsuppliersCustomer->getPageSetTotalRows();

      if ($listingTotalRow > 0) {

      while ($QsuppliersCustomer->fetch()) {
        ?>
        <tr onMouseOver="rowOverEffect(this)" onMouseOut="rowOutEffect(this)">
          <th scope="row"><?php echo $QsuppliersCustomer->valueInt('suppliers_id'); ?></th>
          <td><?php echo $QsuppliersCustomer->value('suppliers_name'); ?></td>
          <td><?php echo $QsuppliersCustomer->valueDecimal('sum_qty'); ?></td>
          <td><?php echo $QsuppliersCustomer->valueInt('customers_id'); ?></td>
          <td><?php echo $QsuppliersCustomer->value('customers_name'); ?></td>
          <td><?php echo $QsuppliersCustomer->value('products_model'); ?></td>
          <td><?php echo $QsuppliersCustomer->value('products_name'); ?></td>
          <td class="text-center"><?php echo $QsuppliersCustomer->value('products_options'); ?></td>
          <td><?php echo $QsuppliersCustomer->value('products_options_values'); ?></td>
          <td class="text-end">
            <?php
              echo HTML::link(CLICSHOPPING::link(null, 'A&Orders\Orders&Edit&oID=' . $QsuppliersCustomer->valueInt('orders_id')), HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/order.gif', $CLICSHOPPING_StatsSuppliers->getDef('icon_edit')));
              echo HTML::link(CLICSHOPPING::link(null, 'A&Customers\Customers&Edit&cID=  ' . $QsuppliersCustomer->valueInt('customers_id')), HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'icons/client_b2c.gif', $CLICSHOPPING_StatsSuppliers->getDef('icon_edit')));
            ?>
          </td>
        </tr>
        <?php
      }
    ?>
    </tbody>
  </table>
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

