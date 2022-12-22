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

  namespace ClicShopping\Apps\Report\StatsSuppliers\Sites\ClicShoppingAdmin\Pages\Home\Actions;

  use ClicShopping\OM\Registry;

  class StatsSuppliersOrders extends \ClicShopping\OM\PagesActionsAbstract
  {
    public function execute()
    {
      $CLICSHOPPING_StatsSuppliers = Registry::get('StatsSuppliers');

      $this->page->setFile('stats_suppliers_orders.php');

      $CLICSHOPPING_StatsSuppliers->loadDefinitions('Sites/ClicShoppingAdmin/stats_suppliers_orders');
    }
  }