<?php

/**
 * @file
 * Certissim menu items.
 */

//insertion des données dans l'api
$newparams['siteid'] = variable_get('commerce_certissim_siteid');
$newparams['login'] = variable_get('commerce_certissim_login');
$newparams['password'] = variable_get('commerce_certissim_password');
$newparams['status'] = variable_get('commerce_certissim_status');

$newurl = Spyc::YAMLLoad(CERTISSIM_ROOT_DIR . '/lib/sac/const/url.yml');
$newurl['stacking']['test'] = variable_get('commerce_certissim_url_test');
$newurl['stacking']['prod'] = variable_get('commerce_certissim_url_prod');

//creates a yml string from the currect object's params
$yaml_string = Spyc::YAMLDump($newparams);
$yaml_string2 = Spyc::YAMLDump($newurl);
//opens the YAML file and puts the cursor at the beginning
$handle = fopen(CERTISSIM_ROOT_DIR . '/lib/sac/const/site_params.yml', 'w');
$handle2 = fopen(CERTISSIM_ROOT_DIR . '/lib/sac/const/url.yml', 'w');
//writes new params into the opened file
$written = @fwrite($handle, $yaml_string);
@fwrite($handle2, $yaml_string2);
fclose($handle);
fclose($handle2);

function commerce_Certissim_settings_form() {
	$form['fianet_certissim'] = array(
		'#type' => 'vertical_tabs',
	);
	
	$form['commerce_certissim_info'] = array(
		'#type' => 'fieldset',
		'#title' => t('Configuration Générale'),
		'#group' => 'fianet_certissim',
	);
  
	$form['commerce_certissim_info']['commerce_certissim_activation'] = array(
        '#type' => 'select',
        '#title' => t('Activation du service'),
        '#default_value' => variable_get('commerce_certissim_activation', ''),
        '#options' => array(
            '1' => t('Activé'),
            '0' => t('Désactivé'),
        ),
	);
   
	$form['commerce_certissim_info']['commerce_certissim_status'] = array(
        '#type' => 'select',
        '#title' => t('Environnement'),
        '#description' => t("Environnement d'utilisation du service (Test ou Production)."),
        '#default_value' => variable_get('commerce_certissim_status', ''),
        '#options' => array(
            'test' => t('Mode Test'),
            'prod' => t('Mode Production'),
        ),
	);
   
	$form['commerce_certissim_info']['commerce_certissim_siteid'] = array(
        '#type' => 'textfield',
        '#title' => t('SiteID'),
        '#description' => t('votre identifiant Certissim.'),
        '#default_value' => variable_get('commerce_certissim_siteid', ''),
        '#size' => 16,
	);

	$form['commerce_certissim_info']['commerce_certissim_login'] = array(
        '#type' => 'textfield',
        '#title' => t('Login'),
        '#description' => t('Votre login Certissim.'),
        '#default_value' => variable_get('commerce_certissim_login', ''),
        '#size' => 16,
	);
   
	$form['commerce_certissim_info']['commerce_certissim_password'] = array(
        '#type' => 'textfield',
        '#title' => t('Mot de passe'),
        '#description' => t('votre mot de passe Certissim.'),
        '#default_value' => variable_get('commerce_certissim_password', ''),
        '#size' => 16,
	);
    
	$form['commerce_certissim_categorie'] = array(
        '#type' => 'fieldset',
        '#title' => t('Configuration des types de catégories'),
		'#group' => 'fianet_certissim',
	);
   
    $result = db_query("SELECT type, name from {commerce_product_type}");
    foreach ($result as $type_produits) {  
		$form['commerce_certissim_categorie'][$type_produits->type] = array(
			'#type' => 'fieldset',
			'#title' => t('Configuration des Catégories : ' . $type_produits->type),
			'#attributes' => array('class' => array('commerce-inline-form', 'clearfix')),
		);
   
	   $form['commerce_certissim_categorie'][$type_produits->type]['commerce_certissim_product_types' .$type_produits->type] = array(
			'#type' => 'select',
			'#title' => t('Catégorie du produits ' . $type_produits->type),
			'#options' => array(
				'1' => t('Alimentation & gastronomie'),
				'2' => t('Auto & moto'),
				'3' => t('Culture & divertissements'),
				'4' => t('Maison & jardin'),
				'5' => t('Electroménager'),
				'6' => t('Enchères et achats groupés'),
				'7' => t('Fleurs & cadeaux'),
				'8' => t('Informatique & logiciels'),
				'9' => t('Santé & beauté'),
				'10' => t('Services aux particuliers'),
				'11' => t('Services aux professionnels'),
				'12' => t('Sport'),
				'13' => t('Vétements & accessoires'),
				'14' => t('Voyage & tourisme'),
				'15' => t('Hifi, photo & vidéos'),
				'16' => t('Téléphonie & communication'),
				'17' => t('Bijoux et métaux précieux'),
				'18' => t('Articles et accessoires pour bébé'),
				'19' => t('Sonorisation & lumière'),
			),
			'#default_value' => variable_get('commerce_certissim_product_types' .$type_produits->type, '1'),
	   );
	}
    
   $form['commerce_certissim_transport'] = array(
        '#type' => 'fieldset',
        '#title' => t('Configuration des types de livraison'),
        '#group' => 'fianet_certissim',
	);
    
	//récupération des informations des transporteurs
	$tab_transporteurs = array();
	$count_transporteurs = 0;
	if (db_table_exists("{commerce_socolissimo_rate}")) {
		$result = db_query('SELECT name from {commerce_socolissimo_rate}');
		foreach ($result as $type_transports) {
			$count_transporteurs++;
			$tab_transporteurs[$count_transporteurs]['id'] = $type_transports->name;
			$tab_transporteurs[$count_transporteurs]['name'] = $type_transports->name;
		}
	}
	if (db_table_exists("commerce_flat_rate_service")) {
		$result = db_query('SELECT name, display_title FROM {commerce_flat_rate_service}');
		foreach ($result as $type_transports) {
			$count_transporteurs++;
			$tab_transporteurs[$count_transporteurs]['id'] = $type_transports->name;
			$tab_transporteurs[$count_transporteurs]['name'] = $type_transports->display_title;
		}
	}
		
	if (!empty($tab_transporteurs)) {
		for ($i=1;$i<=$count_transporteurs;$i++) {
			$form['commerce_certissim_transport'][$tab_transporteurs[$i]['id']] = array(
				'#type' => 'fieldset',
				'#title' => t('Configuration du Transporteur :' . $tab_transporteurs[$i]['name']),
				'#attributes' => array('class' => array('commerce-inline-form', 'clearfix')),
			);
			
			$form['commerce_certissim_transport'][$tab_transporteurs[$i]['id']]['commerce_certissim_transport_types' . $tab_transporteurs[$i]['id']] = array(
				'#type' => 'select',
				'#title' => t('Types de Transports'),
				'#options' => array(
						'1' => t('Retrait de la marchandise chez le marchand'),
						'2' => t('Utilisation d\'un réseau de points-retraits tiers'),
						'3' => t('Retrait dans un aéroport, une gare ou une agence de voyage'),
						'4' => t('Transporteur'),
						'5' => t('Emission d\'un billet électronique, télèchargement'),
				),
				'#default_value' => variable_get('commerce_certissim_transport_types' . $tab_transporteurs[$i]['id'], '4'),
			);
	  
			$form['commerce_certissim_transport'][$tab_transporteurs[$i]['id']]['commerce_certissim_transport_speed' . $tab_transporteurs[$i]['id']] = array(
				'#type' => 'select',
				'#title' => t('Vitesse de transport'),
				'#options' => array(
						'1' => t('Express (- de 24 heures)'),
						'2' => t('Standard'),
				),
				'#default_value' => variable_get('commerce_certissim_transport_speed' . $tab_transporteurs[$i]['id'], '2'),
			);
		}
	}
	
	$form['commerce_certissim_retrait'] = array(
		'#type' => 'fieldset',
		'#title' => t('Adresse de votre boutique'),
		'#group' => 'fianet_certissim',
	);
	$form['commerce_certissim_retrait']['commerce_certissim_rue'] = array(
		'#type' => 'textfield',
		'#title' => t('Rue'),
		'#description' => t('Rue de votre boutique.'),
		'#default_value' => variable_get('commerce_certissim_rue', ''),
		'#size' => 30,
	);
	$form['commerce_certissim_retrait']['commerce_certissim_codepostal'] = array(
		'#type' =>'textfield',
		'#title' => t('Code Postal'),
		'#description' => t('Code Postal de votre boutique'),
		'#default_value' => variable_get('commerce_certissim_codepostal',''),
		'#size' => 5,
	);
	$form['commerce_certissim_retrait']['commerce_certissim_ville'] = array(
		'#type' =>'textfield',
		'#title' => t('Ville'),
		'#description' => t('Ville de votre boutique'),
		'#default_value' => variable_get('commerce_certissim_ville',''),
		'#size' => 30,
	);
    $form['commerce_certissim_retrait']['commerce_certissim_pays'] = array(
		'#type' =>'textfield',
		'#title' => t('Pays'),
		'#description' => t('Pays de votre boutique'),
		'#default_value' => variable_get('commerce_certissim_pays','France'),
		'#size' => 30,
	);
    
	$payment_methods = &drupal_static(__FUNCTION__);
	$payment_methods = array();

	foreach (module_implements('commerce_payment_method_info') as $module) {
		foreach (module_invoke($module, 'commerce_payment_method_info') as $method_id => $payment_method) {
			   $payment_method['method_id'] = $method_id;
			   $payment_method['module'] = $module;
			   $payment_methods[$method_id] = $payment_method;
		}
    }
    
   $form['commerce_certissim_method'] = array(
        '#type' => 'fieldset',
        '#title' => t('Configuration des méthodes de paiements'),
        '#group' => 'fianet_certissim',
	);
     
	drupal_alter('commerce_payment_method_info', $payment_methods);
	foreach ($payment_methods as $methodes) {
		$form['commerce_certissim_method']['commerce_certissim_payment_types' . $methodes['method_id']] = array(
			'#type' => 'select',
			'#title' => t('Type de Paiement - ' . $methodes['module']),
			'#options' => array(
				'1' => t('Carte bancaire'),
				'2' => t('Chèque'),
				'3' => t('Contre Remboursement'),
				'4' => t('Virement'),
				'5' => t('Carte bancaire en n fois'),
				'6' => t('Paypal'),
				'7' => t('1euro.com'),
				'8' => t('Kwixo'),
			),
			'#default_value' => variable_get('commerce_certissim_payment_types'. $methodes['method_id'], '1'),
		);
	}
	
	$form['commerce_certissim_urls'] = array(
        '#type' => 'fieldset',
        '#title' => 'URLs',
		'#group' => 'fianet_certissim',
	);
    
	$form['commerce_certissim_urls']['commerce_certissim_url_test'] = array(
        '#type' => 'textfield',
        '#title' => t('URL de Test'),
        '#default_value' => variable_get('commerce_certissim_url_test', 'https://secure.fia-net.com/pprod/engine/stacking.cgi'),
        '#size' => 40,
	);
   
	$form['commerce_certissim_urls']['commerce_certissim_url_prod'] = array(
        '#type' => 'textfield',
        '#title' => t('URL de Production'),
        '#default_value' => variable_get('commerce_certissim_url_prod', 'https://secure.fia-net.com/fscreener/engine/stacking.cgi'),
        '#size' => 40,
	);
    
	$form['commerce_certissim_log'] = array(
        '#type' => 'fieldset',
		'#title' => '<a target="_blank" href="'.dirname($_SERVER["PHP_SELF"]).'/'.drupal_get_path('module', 'commerce_Certissim').'/logs/fianet_log.txt">Accéder aux logs FIA-NET</a>',
	);
    
    return system_settings_form($form);
}
