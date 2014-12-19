<?php
require_once '../../includes/includes.inc.php';
//loads Sac params
$sac = new Sac();

/************************* Use case description ***********************
 * billing customer only (no delivery customer)
 * address without flat information
 * no <siteconso> element
 * delivery in a drop off point
 * many products, at least one of them in multiple copies
 * Kwixo payment in once with debit after receipt
 **********************************************************************/
//<control>
$control = new FianetControl();

//<utilisateur type='facturation' qualite='2'>
$control->createInvoiceCustomer('Monsieur', 'DUPONT', 'Michel', 'recetteScore0@fia-net.com', 'SARL DMICHEL', '0621085793', '0101010203');
//<adresse type='facturation' format='1'>
$control->createInvoiceAddress('13 rue de la gare', '75001', 'Paris', 'France', 'Bat. B');

/******** <infocommande> ***************/
$order_details = $control->createOrderDetails(generateRandomRefId(), $sac->getSiteid(), 165, 'EUR', $_SERVER['REMOTE_ADDR'], date('Y-m-d H:i:s'));
//<transport>
$carrier = $order_details->createCarrier('Kiala', 2, 2);
//<pointrelais>
$drop_off_point = $carrier->createDropOffPoint('Kiala les petits pains', 'KIALA115542');
//<adresse>
$drop_off_point->createAddress('1 rue de la gare', '75009', 'Paris', 'France', 'lot. des petit pins');

/******** <list> ***************/
$product_list = $order_details->createProductList();
$product_list->createProduct('libellé du produit 1', 'refprod1', 13, 30, 2);
$product_list->createProduct('libellé du produit 2', 'refprod2', 13, 35, 3);


/******** <paiement> ***************/
$control->createPayment('carte', 'Dupont Michel');


echo "<textarea cols='100' rows='25'>";
echo $control;
echo "</textarea>";