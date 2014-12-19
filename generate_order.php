<?php
define('DRUPAL_ROOT', $_GET['DRUPAL_ROOT']);

require_once (DRUPAL_ROOT . '/includes/bootstrap.inc');

require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
require_once DRUPAL_ROOT . '/includes/common.inc';
require_once DRUPAL_ROOT . '/includes/file.inc';
require_once DRUPAL_ROOT . '/includes/module.inc';
require_once DRUPAL_ROOT . '/includes/ajax.inc';

// We prepare only a minimal bootstrap. This includes the database and
// variables, however, so we have access to the class autoloader registry.
drupal_bootstrap(DRUPAL_BOOTSTRAP_SESSION);

// This must go after drupal_bootstrap(), which unsets globals!
global $conf;

// We have to enable the user and system modules, even to check access and
// display errors via the maintenance theme.
$module_list['system']['filename'] = 'modules/system/system.module';
$module_list['user']['filename'] = 'modules/user/user.module';
module_list(TRUE, FALSE, FALSE, $module_list);
drupal_load('module', 'system');
drupal_load('module', 'user');

// We also want to have the language system available, but we do *NOT* want to
// actually call drupal_bootstrap(DRUPAL_BOOTSTRAP_LANGUAGE), since that would
// also force us through the DRUPAL_BOOTSTRAP_PAGE_HEADER phase, which loads
// all the modules, and that's exactly what we're trying to avoid.
drupal_language_initialize();

// Initialize the maintenance theme for this administrative script.
drupal_maintenance_theme();

$output = '';
$show_messages = TRUE;

require_once('lib/includes/includes.inc.php');

/* ***** loads Certissim params * ********* */
$control = new CertissimControl();

/* ***** initialisation du gestionnaire d'erreurs de flux * **** */
$error = 0;

/* ***** verification du pays facturation et livraison * **** */
$allowedCountries = array(
    'AT', //Autriche
    'BE', //Belgique
    'BG', //Bulgarie
    'CH', //Suisse
    'CY', //Chypre
    'CZ', //République tchèque
    'DE', //Allemagne
    'DK', //Danemark
    'EE', //Estonie
    'ES', //Espagne
    'FI', //Finlande
    'FR', //France
    'GB', //Royaume-Uni
    'GF', //Guyane française
    'GP', //Guadeloupe
    'GR', //Grèce
    'HU', //Hongrie
    'IE', //Irlande
    'IT', //Italie
    'LT', //Lituanie
    'LU', //Luxembourg
    'LV', //Lettonie
    'MC', //Monaco
    'MQ', //Martinique
    'MT', //Malte
    'NL', //Pays-Bas
    'PL', //Pologne
    'PT', //Portugal
    'RE', //Réunion
    'RO', //Roumanie
    'SE', //Suède
    'SI', //Slovénie
    'SK', //Slovaquie
    'YT', //Mayotte
);

// Pas d'envoi sur les paiements ayant pour adresse un pays non géré par Certissim
if (!(in_array($_GET['country'], $allowedCountries) && in_array($_GET['dcountry'], $allowedCountries))) {
    insertLogCertissim(__METHOD__ . ' : ' . __LINE__,"transaction reference " . $_GET['refid'] . " Erreur : pays non autorisé." );
    $error = 1;
}

if ($error != 1) {
	/* ***** Recupération des informations utilisateur Facturation  * *** */
	$control->createInvoiceCustomer($_GET['civility'], $_GET['lastname'], $_GET['firstname'], $_GET['email'], $_GET['company'], $_GET['phone']);
	$control->createInvoiceAddress($_GET['street1'], $_GET['postalcode'], $_GET['city'], $_GET['country'], $_GET['street2']);
	//Recupération des information utilisateur Livraison
	$control->createDeliveryCustomer ($_GET['dcivility'], $_GET['dlastname'], $_GET['dfirstname'], '', $_GET['dcompany'], $_GET['dphone']);
}

/* ***** Recupération des infos commande * ***** */
if ($_GET['siteid']) {
	$order_details = $control->createOrderDetails($_GET['refid'], $_GET['siteid'], $_GET['amount'],'EUR', $_GET['ip'], $_GET['date']);
} else {
	insertLogCertissim(__METHOD__ . ' : ' . __LINE__,"transaction reference " . $_GET['refid'] . " Erreur : SiteID non renseigné. " );
	$error = 1;
  }

/* ***** Recuperation des informations Transporteur * *** */
$transport_type = $_GET['transport_type'];
$transport_speed = $_GET['transport_speed'];
$transport_name = $_GET['transport_name'];

if ($transport_type && $transport_speed && $transport_name) {
	if ($transport_type == 1) {
		$carrier = $order_details->createCarrier($transport_name, $transport_type, $transport_speed);
		
		$ruemagasin = variable_get('commerce_certissim_rue', '');
		$cpmagasin = variable_get('commerce_certissim_codepostal', '');
		$villemagasin = variable_get('commerce_certissim_ville', '');
		$paysmagasin = variable_get('commerce_certissim_pays','');
		
		if($ruemagasin && $cpmagasin && $villemagasin) {
			$drop_off_point = $carrier->createDropOffPoint($transport_name);
			$drop_off_point->createAddress($ruemagasin, $cpmagasin, $villemagasin,$paysmagasin, '');
		}
	} elseif ($transport_type == 2 || $transport_type == 3) {
		$carrier = $order_details->createCarrier($transport_name, $transport_type, $transport_speed);		
	} elseif ($transport_type == "4") {
		$carrier = $order_details->createCarrier($transport_name, $transport_type, $transport_speed);
			
		//Recupération des information adresse Livraison
		$control->createDeliveryAddress($_GET['dstreet1'], $_GET['dpostalcode'], $_GET['dcity'], $_GET['dcountry'], $_GET['dstreet2']);
	}
} else {
	insertLogCertissim(__METHOD__ . ' : ' . __LINE__,"transaction reference " . $_GET['refid'] . " Erreur : transporteur non récupérable." );
	$error = 1;
}
  
/* ***** Recupération du/des produits du panier * *** */
$product_list = $order_details->createProductList();

if ($_GET['product_id']) {
	foreach($_GET['product_id'] as $key => $id){
		$product_list->createProduct($_GET['product_title'][$id], $id, $_GET['product_type'][$id], $_GET['product_price'][$id], $_GET['product_qty'][$id]);
		if (empty($_GET['product_title'][$id])){
			insertLogCertissim(__METHOD__ . ' : ' . __LINE__,"transaction reference " . $_GET['refid'] . " Erreur : ref produit " . $id . " Nom vide. " );
			$error = 1;
		}
		if (empty($_GET['product_type'][$id])){
			insertLogCertissim(__METHOD__ . ' : ' . __LINE__,"transaction reference " . $_GET['refid'] . " Erreur : ref produit " . $id . " Type produit vide. " );
			$error = 1;
		}
		if (empty($_GET['product_qty'][$id])){
			insertLogCertissim(__METHOD__ . ' : ' . __LINE__,"transaction reference " . $_GET['refid'] . " Erreur : ref produit " . $id . " Quantité produit vide. " );
			$error = 1;
		}
	}
}

/* ***** récupération des informations du paiement * **** */
if ($_GET['methodfia']) {
	switch ($_GET['methodfia']) {
		case 1:
			$_GET['methodfia'] = "carte";
			break;
		case 2:
			$_GET['methodfia'] = "cheque";
			break;
		case 3:
			$_GET['methodfia'] = "contre-remboursement";
			break;
		case 4:
			$_GET['methodfia'] = "virement";
			break;
		case 5:
			$_GET['methodfia'] = "cb en n fois";
			break;
		case 6:
			$_GET['methodfia'] = "paypal";
			break;
		case 7:
			$_GET['methodfia'] = "1euro.com";
			break;
		case 8:
			$_GET['methodfia'] = "Kwixo";
			break;
	}

	$control->createPayment($_GET['methodfia'], $_GET['method']);
}

/* ***** order sending * **** */
if (!$error) {
	if ($_GET['methodfia'] != "Kwixo") {
		$sac = new Certissim();
		$stack = new CertissimStack();
		$stack -> addControl($control);
		
		$validstack = $sac->sendStacking($stack);
		$dom = new CertissimStackingResponse($validstack);
		$dom->saveXML();
		
		if ($dom->hasFatalError())
			echo $dom->getFatalError();
		else {
			foreach ($dom->getResults() as $result) {
				db_query("UPDATE {commerce_order} SET order_status_certissim = '" . $result->getStatus() . "|". variable_get('commerce_certissim_status') ."' WHERE {commerce_order}.`order_id` ='" . $result->getRefid() . "'");
				insertLogCertissim(__METHOD__ . ' : ' . __LINE__,"transaction reference " . $_GET['refid'] . " Etat: " . $result->getDetail() . " .");
				insertLogCertissim(__METHOD__ . ' : ' . __LINE__,"transaction reference " . $_GET['refid'] . " Avancement: " . $result->getStatus() . " .");
			}
		}
	}
}

header("Location: $_SERVER[HTTP_REFERER]");