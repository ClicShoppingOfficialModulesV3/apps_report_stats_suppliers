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

  namespace ClicShopping\Apps\Report\StatsSuppliers\Sites\ClicShoppingAdmin\Pages\Home;

  use ClicShopping\OM\Registry;

  use ClicShopping\Apps\Report\StatsSuppliers\StatsSuppliers;

  class Home extends \ClicShopping\OM\PagesAbstract
  {
    public mixed $app;

    protected function init()
    {
      $CLICSHOPPING_StatsSuppliers = new StatsSuppliers();
      Registry::set('StatsSuppliers', $CLICSHOPPING_StatsSuppliers);

      $this->app = Registry::get('StatsSuppliers');

      $this->app->loadDefinitions('Sites/ClicShoppingAdmin/main');
    }
  }
