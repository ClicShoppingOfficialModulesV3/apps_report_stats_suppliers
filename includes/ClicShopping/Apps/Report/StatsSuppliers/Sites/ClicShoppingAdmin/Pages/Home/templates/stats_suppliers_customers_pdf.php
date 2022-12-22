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

  use ClicShopping\OM\Registry;

  use ClicShopping\Sites\Common\FPDF;
  use ClicShopping\Sites\Common\PDF;

  $CLICSHOPPING_Template = Registry::get('TemplateAdmin');
  $CLICSHOPPING_Db = Registry::get('Db');
  $CLICSHOPPING_Language = Registry::get('Language');
  $CLICSHOPPING_StatsSuppliers = Registry::get('StatsSuppliers');

  define('FPDF_FONTPATH', '../ext/fpdf/font/');
  require_once('../ext/fpdf/fpdf.php');

  Registry::set('PDF', new PDF());
  $PDF = Registry::get('PDF');

  $QsuppliersProducts = $CLICSHOPPING_StatsSuppliers->db->prepare('select distinct s.suppliers_id,
                                                                                   s.suppliers_name,
                                                                                   s.suppliers_manager,
                                                                                   s.suppliers_phone,
                                                                                   s.suppliers_email_address,
                                                                                   s.suppliers_fax,
                                                                                   s.suppliers_address,
                                                                                   s.suppliers_suburb,
                                                                                   s.suppliers_postcode,
                                                                                   s.suppliers_city,
                                                                                   s.suppliers_states,
                                                                                   s.suppliers_country_id
                                                                     from :table_orders_products  op
                                                                       left join :table_products  p ON op.products_id = p.products_id
                                                                       left join :table_suppliers s on p.suppliers_id = s.suppliers_id
                                                                       left join :table_orders  o ON op.orders_id = o.orders_id
                                                                       left join :table_orders_products_attributes opa ON op.orders_products_id = opa.orders_products_id
                                                                    where o.date_purchased between :start  and :end
                                                                    and o.orders_status = :orders_status
                                                                    and o.orders_archive  = 0
                                                                    and s.suppliers_id = :suppliers_id
                                                                    group by op.products_name,
                                                                             opa.products_options,
                                                                             opa.products_options_values
                                                                    order by p.products_model,
                                                                             op.products_name
                                                                    ');

  $QsuppliersProducts->bindValue(':start', $_GET['bDS']);
  $QsuppliersProducts->bindValue(':end', $_GET['bED']);
  $QsuppliersProducts->bindInt(':orders_status', (int)$_GET['bOS']);
  $QsuppliersProducts->bindInt(':suppliers_id', (int)$_GET['bID']);

  $QsuppliersProducts->execute();

// Classe pdf.php
  $pdf = new \FPDF();

// Marge de la page
  $pdf->SetMargins(10, 2, 6);

// Ajoute page
  $pdf->AddPage();

// Cadre pour l'adresse de commande
  $pdf->SetDrawColor(0);
  $pdf->SetLineWidth(0.2);
  $pdf->SetFillColor(255);

// Adresse de commande
  $pdf->SetFont('Arial', 'B', 8);
  $pdf->SetTextColor(0);
  $pdf->Text(113, 44, $CLICSHOPPING_StatsSuppliers->getDef('entry_ship_to'));
  $pdf->SetX(0);
  $pdf->SetY(47);
  $pdf->Cell(111);
  $pdf->Text(113, 50, utf8_decode($QsuppliersProducts->value('suppliers_address')));
  $pdf->Text(113, 55, utf8_decode($QsuppliersProducts->value('suppliers_suburb')));
  $pdf->Text(113, 60, utf8_decode($QsuppliersProducts->value('suppliers_postcode')));
  $pdf->Text(113, 65, utf8_decode($QsuppliersProducts->value('suppliers_city')));
  $pdf->Text(113, 70, utf8_decode($QsuppliersProducts->value('suppliers_states')));


// Information fournisseur
  $pdf->SetFont('Arial', 'B', 8);
  $pdf->SetTextColor(0);
  $pdf->Text(10, 85, $CLICSHOPPING_StatsSuppliers->getDef('entry_supplier_information'));


// Manager du fournisseur
  $pdf->SetFont('Arial', '', 8);
  $pdf->SetTextColor(0);
  $pdf->Text(113, 85, $CLICSHOPPING_StatsSuppliers->getDef('entry_manager') . ' ' . utf8_decode($QsuppliersProducts->value('suppliers_manager')));


// Email du fournisseur
  $pdf->SetFont('Arial', '', 8);
  $pdf->SetTextColor(0);
  $pdf->Text(113, 90, $CLICSHOPPING_StatsSuppliers->getDef('entry_email') . ' ' . $QsuppliersProducts->value('suppliers_email_address'));

// Manager
  $pdf->SetFont('Arial', '', 8);
  $pdf->SetTextColor(0);
  $pdf->Text(113, 95, utf8_decode($CLICSHOPPING_StatsSuppliers->getDef('entry_phone')) . ' ' . $QsuppliersProducts->value('suppliers_phone'));

// Telephone du client
  $pdf->SetFont('Arial', '', 8);
  $pdf->SetTextColor(0);
  $pdf->Text(113, 100, $CLICSHOPPING_StatsSuppliers->getDef('entry_fax') . ' ' . $QsuppliersProducts->value('suppliers_fax'));


// Cadre du numero de fournisseur, date debut analyse et date fin analyse
  $pdf->SetDrawColor(0);
  $pdf->SetLineWidth(0.2);
  $pdf->SetFillColor(245);
//  $pdf->roundedRect(6, 107, 192, 11, 2, 'DF');

// Numero de commande ou de facture
// Date de commande ou de facture
// Methode de paiement

  $pdf->Text(10, 113, utf8_decode($CLICSHOPPING_StatsSuppliers->getDef('entry_suppliers_number')) . '  ' . (int)$_GET['bID']);
  $pdf->Text(65, 113, utf8_decode($CLICSHOPPING_StatsSuppliers->getDef('start_analyse')) . ' ' . $_GET['bDS']);
  $pdf->Text(130, 113, $CLICSHOPPING_StatsSuppliers->getDef('end_analyse') . ' ' . $_GET['bED']);

// Cadre pour afficher du Titre
  $pdf->SetDrawColor(0);
  $pdf->SetLineWidth(0.2);
  $pdf->SetFillColor(245);
//  $pdf->roundedRect(108, 32, 90, 7, 2, 'DF');

// Affichage du titre
  $pdf->SetFont('Arial', '', 10);
  $pdf->SetY(32);
  $pdf->SetX(70);
  $pdf->MultiCell(90, 7, utf8_decode($QsuppliersProducts->value('suppliers_name')), 0, 'C');

// Fields Name position
  $Y_Fields_Name_position = 125;

// Table position, under Fields Name
  $Y_Table_Position = 131;


// Entete du tableau des produits a commander
  $PDF->outputTableCustomersSuppliers($Y_Fields_Name_position);

  $QsuppliersCustomers = $CLICSHOPPING_StatsSuppliers->db->prepare('select  o.customers_id,
                                                                      o.customers_name,
                                                                      s.suppliers_id,
                                                                      s.suppliers_name,
                                                                      p.products_model,
                                                                      op.products_name,
                                                                      sum(op.products_quantity) as sum_qty,
                                                                      opa.products_options,
                                                                      opa.products_options_values
                                                               from :table_orders_products  op
                                                                 left join :table_products  p ON op.products_id = p.products_id
                                                                 left join :table_suppliers s on p.suppliers_id = s.suppliers_id
                                                                 left join :table_orders  o ON op.orders_id = o.orders_id
                                                                 left join :table_orders_products_attributes opa ON op.orders_products_id = opa.orders_products_id
                                                              where o.date_purchased between :start and :end
                                                              and o.orders_status = :orders_status
                                                              and o.orders_archive  = 0
                                                              and s.suppliers_id = :suppliers_id
                                                              group by op.products_name,
                                                                       opa.products_options,
                                                                       opa.products_options_values
                                                              order by o.customers_name
                                                            ');

  $QsuppliersCustomers->bindInt(':start', $_GET['bDS']);
  $QsuppliersCustomers->bindInt(':end', $_GET['bED']);
  $QsuppliersCustomers->bindInt(':orders_status', (int)$_GET['bOS']);
  $QsuppliersCustomers->bindInt(':suppliers_id', (int)$_GET['bID']);

  $QsuppliersCustomers->execute();

  while ($QsuppliersCustomers->fetch()) {

// Quantite
    $pdf->SetFont('Arial', '', 7);
    $pdf->SetY($Y_Table_Position);
    $pdf->SetX(6);
    $pdf->MultiCell(9, 6, $QsuppliersCustomers->value('sum_qty'), 1, 'C');

// id client
    $pdf->SetY($Y_Table_Position);
    $pdf->SetX(15);
    $pdf->SetFont('Arial', '', 7);
    $pdf->MultiCell(13, 6, $QsuppliersCustomers->value('customers_id'), 1, 'C');

// id client
    $pdf->SetY($Y_Table_Position);
    $pdf->SetX(28);
    $pdf->SetFont('Arial', '', 7);
    $pdf->MultiCell(25, 6, utf8_decode($QsuppliersCustomers->value('customers_name')), 1, 'C');

// products model
    $pdf->SetY($Y_Table_Position);
    $pdf->SetX(53);
    $pdf->SetFont('Arial', '', 7);
    $pdf->MultiCell(30, 6, $QsuppliersCustomers->value('products_model'), 1, 'C');

// products name
    $pdf->SetY($Y_Table_Position);
    $pdf->SetX(83);
    $pdf->SetFont('Arial', '', 7);
    $pdf->MultiCell(60, 6, utf8_decode($QsuppliersCustomers->value('products_name')), 1, 'C');

// products options
    $pdf->SetY($Y_Table_Position);
    $pdf->SetX(143);
    $pdf->SetFont('Arial', '', 7);
    $pdf->MultiCell(40, 6, utf8_decode($QsuppliersCustomers->value('products_options')), 1, 'C');


// products options values
    $pdf->SetY($Y_Table_Position);
    $pdf->SetX(183);
    $pdf->SetFont('Arial', '', 7);
    $pdf->MultiCell(20, 6, $QsuppliersCustomers->value('products_options_values'), 1, 'C');
    $Y_Table_Position += 6;


// Check for product line overflow
    $item_count++;
    if ((is_long($item_count / 32) && $i >= 20) || ($i == 20)) {
      $pdf->AddPage();
// Fields Name position
      $Y_Fields_Name_position = 125;
// Table position, under Fields Name
      $Y_Table_Position = 70;
      $PDF->outputTableCustomersSuppliers($Y_Table_Position - 6);
      if ($i == 20) $item_count = 1;
    }
  }

// PDF's created now output the file
  $pdf->Output();