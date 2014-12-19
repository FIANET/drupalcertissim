<?php

/**
 * @file
 * Definition of views_handler_field_url.
 */

/**
 * Field handler to provide simple renderer that turns a URL into a clickable link.
 *
 * @ingroup views_field_handlers
 */
class commerce_Certissim_handler_field_url_fianet extends views_handler_field {

    function option_definition() {
        $options = parent::option_definition();

        $options['display_as_link'] = array('default' => TRUE, 'bool' => TRUE);

        return $options;
    }

    /**
     * Provide link to the page being visited.
     */
    function options_form(&$form, &$form_state) {
        $form['display_as_link'] = array(
             '#title' => t('Display as link'),
             '#type' => 'checkbox',
             '#default_value' => !empty($this->options['display_as_link']),
        );
        parent::options_form($form, $form_state);
    }

    function render($values) {
        if (variable_get('commerce_certissim_activation', '') == "1") {
			$payment = $values->_field_data['order_id']['entity']->data['payment_method'];
			$tpayment = explode('|', $payment);
			
			if($tpayment[0] == 'kwixo' || $tpayment[0] == 'kwixocpt' || $tpayment[0] == 'kwixoc'){
				$img = 'Paiement kwixo';
			} else {
				$order = commerce_order_load($values->order_id);
				$order_total = db_query("SELECT commerce_order_total_amount FROM {field_data_commerce_order_total} as f, {commerce_order} as c WHERE f.revision_id = c.revision_id and f.revision_id = '" .$order->revision_id. "'")->fetchall();
				
				/*******  infos utilisateur   ***************** */      
				$profile_id = $order->commerce_customer_billing['und'][0]['profile_id'];	
				$address = db_query("SELECT commerce_customer_address_country as country, commerce_customer_address_locality as city, commerce_customer_address_postal_code as code_postal,
									commerce_customer_address_thoroughfare as street, commerce_customer_address_premise as street2, commerce_customer_address_first_name as first_name,
									commerce_customer_address_last_name as last_name, commerce_customer_address_name_line as firstlast_name, commerce_customer_address_organisation_name as company
									FROM {field_data_commerce_customer_address}
									WHERE entity_id = '".$profile_id."' ")->fetchAll();
							
				$last_name = "";
				$first_name = "";
				if (isset($address[0]->last_name)) {
					$last_name = $address[0]->last_name;
					$first_name = $address[0]->first_name;
				} else {
					$first_last = explode(' ',$address[0]->firstlast_name);
					if(isset($first_last[0])&&(isset($first_last[1]))){
						$last_name = $first_last[0];
						$first_name = $first_last[1];
					}
				}
			
				$profile_id_ship = "";			
				if(isset($order->commerce_customer_shipping['und'][0]['profile_id'])){
					$profile_id_ship = $order->commerce_customer_shipping['und'][0]['profile_id'];
				} else {
					$profile_id_ship = $order->commerce_customer_billing['und'][0]['profile_id'];
				}
				
				$address_ship = db_query("SELECT commerce_customer_address_country as country, commerce_customer_address_locality as city, commerce_customer_address_postal_code as code_postal,
										commerce_customer_address_thoroughfare as street, commerce_customer_address_premise as street2, commerce_customer_address_first_name as first_name,
										commerce_customer_address_last_name as last_name, commerce_customer_address_name_line as firstlast_name, commerce_customer_address_organisation_name as company
										FROM {field_data_commerce_customer_address}
										WHERE entity_id = '".$profile_id_ship."'")->fetchAll();
				
				$last_name_ship = "";
				$first_name_ship = "";
				if (isset($address_ship[0]->last_name)) {
					$last_name_ship = $address_ship[0]->last_name;
					$first_name_ship = $address_ship[0]->first_name;
				} else {
					$first_last_ship = explode(' ',$address_ship[0]->firstlast_name);
					if ((isset($first_last_ship[0])) && (isset($first_last_ship[1]))) {
						$last_name_ship = $first_last_ship[0];
						$first_name_ship = $first_last_ship[1];
					}
				}
				
				$dcompany = $address_ship['0']->company;

				/** **** * Champ telephone sans module (le nom de la table est à modifier en fonction de votre boutique)   ******** *****    */
				$phone_ship = '';
				if (db_table_exists("{field_data_field_phone_number}")) {	
					$phone_livr = db_query("SELECT field_phone_number_value as phone_livr
											FROM {field_data_field_phone_number}
											WHERE entity_id ='".$profile_id_ship."' ")->fetchAll();
					if(isset($phone_livr[0]->phone_livr))
									$phone_ship = $phone_livr[0]->phone_livr;
				}
				
				
				/*** *** Champ Telephone avec module Commerce_firstdata *** ******/
				$phone_billing = '';
				if (db_table_exists("{field_data_commerce_firstdata_billing_phone}")) {
					$phone = db_query("SELECT commerce_firstdata_billing_phone_value as phone
									FROM {field_data_commerce_firstdata_billing_phone}
									WHERE entity_id='".$profile_id."' ")->fetchAll();
				if(isset($phone[0]->phone))
				   $phone_billing = $phone[0]->phone;
				}
				
				/* *********** Recuperation du transporteur  ********** */			
				$transport = db_query ("SELECT line_item_label
									   FROM {commerce_line_item}
									   WHERE order_id ='".$order->order_id."' and type='shipping'")->fetchAll();
				
				 /* ********** recuperation des informations sur l'envoi de la commande  ***** */
				$valeur = $this->get_value($values);
				
				$val[] = Array();
				$val = explode("|",$valeur);
				
				$siteid = variable_get('commerce_certissim_siteid','');
				$login = variable_get('commerce_certissim_login', '');
				$pwd = urlencode(variable_get('commerce_certissim_password', ''));
				$refid = $order->order_id;  			 
				
				$env = '';
				$code = '';
				$this->options['alter']['make_link'] = TRUE;
				$this->options['alter']['path'] = "";
				
				if(isset($val[1])){
					if ($val[1] == 'test')
						$env = 'pprod_fiadmin';
					else
						$env = 'fscreener_fiadmin';
				
					$path = 'https://secure.fia-net.com/' .$env. '/BO/visucheck_detail.php?sid=' . $siteid . '&log=' . $login . '&pwd=' . $pwd . '&rid=' . $refid;
					$code = " <a href='" . dirname($_SERVER["PHP_SELF"]) . "/" . drupal_get_path('module', 'commerce_certissim') . "/get_evaluation.php?orderid=" . $refid. "' target='_blank'>Détail de l'évaluation</a>";
				}
      
				if ($order->status == "completed") {
					switch($val[0]) {
						case 'error' :
							$img = "<a href='" . $path . "' target='_blank'><img src=" . dirname($_SERVER["PHP_SELF"]) . '/' . drupal_get_path('module', 'commerce_certissim') . "/images/attention.gif /></a>";
							$img .= $code;
							break;
						case '0' :
							$img = "<a href='" . $path . "' target='_blank'><img src='https://secure.fia-net.com/pprod_fiadmin/images/rond_rouge.gif'/></a>";
							$img .= $code;
							break;
						case '90' :
							$img = "<a href='" . $path . "' target='_blank'><img src='https://secure.fia-net.com/pprod_fiadmin/images/rond_vertclair.gif'/></a>";
							$img .= $code;
							break;
						case '100' :
							$img = "<a href='" . $path . "' target='_blank'><img src='https://secure.fia-net.com/pprod_fiadmin/images/rond_vert.gif'/></a>";
							$img .= $code;
							break;
						case 'encours' :
							$img = "<a href='" . $path . "' target='_blank'><img src='" . dirname($_SERVER["PHP_SELF"]) . '/' . drupal_get_path('module', 'commerce_certissim') . "/images/fianet_sac.gif'/></a>";
							$img .= " En cours d'évaluation";
							break;
						default :
							if(!empty($_GET['order_id_fianet'])) {
								commerce_Certissim_commerce_order('update', $order,null);
							}
							$code = "";
							
							if($val[0] == "absente") {
								$code .= "<a href='" . $path . "' target='_blank'><img src=" . dirname($_SERVER["PHP_SELF"]) . '/' . drupal_get_path('module', 'commerce_certissim') . "/images/attention.gif /></a>";
								$code .= " erreur lors de la soumission, renvoyez le flux.</br>";
							}
							
							$code = "<form action='" . dirname($_SERVER["PHP_SELF"]) . '/' . drupal_get_path('module', 'commerce_certissim') . "/generate_order.php?id=" . $order->order_id . "' method='GET'>";
							$code .= "<input type=hidden name=DRUPAL_ROOT value='" . getcwd() . "'/>";
							$code .= "<input type=hidden name=refid value='" . $order->order_id . "'/>";
							$code .= "<input type=hidden name='civility' value='Monsieur'/>";
							$code .= "<input type=hidden name='lastname' value='" . $last_name . "'/>";
							$code .= "<input type=hidden name='firstname' value='" . $first_name . "'/>";
							$code .= "<input type=hidden name='email' value='" . $order->mail . "'/>";
							$code .= "<input type=hidden name='company' value='" . $address['0']->company . "'/>";
							$code .="<input type=hidden name=phone value='".$phone_billing."' />";
							$code .= "<input type=hidden name='street1' value='" . $address['0']->street . "'/>";
							$code .= "<input type=hidden name='city' value='" . $address['0']->city . "'/>";
							$code .= "<input type=hidden name='postalcode' value='" . $address['0']->code_postal . "'/>";
							$code .= "<input type=hidden name='country' value='" . $address['0']->country . "'/>";
							$code .= "<input type=hidden name='street2' value='". $address['0']->street2 ."'/>";
							$code .= "<input type=hidden name='dcivility' value='Monsieur'/>";
							$code .= "<input type=hidden name='dlastname' value='" . $last_name_ship . "'/>";
							$code .= "<input type=hidden name='dfirstname' value='" . $first_name_ship . "'/>";
							$code .= "<input type=hidden name='dcompany' value='" . $dcompany . "'/>";
							$code .="<input type=hidden name=dphone value='".$phone_ship."' />";
							$code .= "<input type=hidden name='dstreet1' value='" . $address_ship['0']->street . "'/>";
							$code .= "<input type=hidden name='dstreet2' value='". $address_ship['0']->street2 ."'/>";
							$code .= "<input type=hidden name='dcity' value='" . $address_ship['0']->city . "'/>";
							$code .= "<input type=hidden name='dpostalcode' value='" . $address_ship['0']->code_postal . "'/>";
							$code .= "<input type=hidden name='dcountry' value='" . $address_ship['0']->country . "'/>";
							$code .= "<input type=hidden name='amount' value='" . $order_total[0]->commerce_order_total_amount/100 . "'/>";
							$code .= "<input type=hidden name=refid value='" . $order->order_id . "'/>";
							$code .="<input type=hidden name=ip value='"  . $_SERVER["REMOTE_ADDR"] . "' />";
							$code .="<input type=hidden name=date value='"  . date('Y-m-d H:i:s') . "' />";
							foreach ($order->commerce_line_items['und'] as $line) {
								$line_item = commerce_line_item_load($line['line_item_id']);
								if (isset($line_item->commerce_product['und'])){
									$product_ids = $line_item->commerce_product['und'];
									foreach ($product_ids as $productid){
										foreach ($productid as $key => $pid){
											$produits = commerce_product_load_multiple(array($pid), array());
											foreach ($produits as $key => $product) {
												$nb_product =  explode('.',$line_item->quantity);
												$quantity = $nb_product[0];
												$type_produits = variable_get('commerce_certissim_product_types' .$product->type, '');
																   
												$code .= "<input type=hidden name=product_id[" . $product->product_id . "]  value='" . $product->product_id . "'/>";
												$code .= "<input type=hidden name=product_type[" . $product->product_id   . "]  value='" . $type_produits . "'/>";
												$code .= "<input type=hidden name=product_title[" . $product->product_id . "]  value='" . $product->title . "'/>";
												$code .= "<input type=hidden name=product_price[" . $product->product_id . "] value ='" . $product->commerce_price['und']['0']['amount']/100 .   "'/>";
												$code .="<input type=hidden name=product_qty[" .$product->product_id ."] value ='".$quantity."' >";
											}
										}
									}
								}
							}
							
							$transport_name = "";
							if (isset($transport[0]->line_item_label)) {
								switch ($transport[0]->line_item_label){
									case 'home delivery' :
										$transport_name = 'socolissimo_home_delivery';
										break;
									case 'appointment' :
										$transport_name = 'socolissimo_appointment';
										break;
									case 'cityssimo' :
										$transport_name = 'socolissimo_cityssimo';
										break;
									case 'poste office' :
										$transport_name = 'socolissimo_post_office';
										break;
									case 'local store' :
										$transport_name = 'socolissimo_local_store';
										break;
									default :
										if (db_table_exists("commerce_flat_rate_service")) {
											$result = db_query("SELECT name, display_title FROM {commerce_flat_rate_service} WHERE title='". $transport[0]->line_item_label ."'")->fetchall();
											if (!empty($result))
												$transport_name = $result[0]->name;
										}
										if ($transport_name == "")
											$transport_name = $transport[0]->line_item_label;
										break;
								}
								$code .= "<input type=hidden name=transport_type value='" . variable_get('commerce_certissim_transport_types' . $transport_name) .  "'/>";
								$code .= "<input type=hidden name=transport_speed value='" . variable_get('commerce_certissim_transport_speed' . $transport_name) .   "'/>";
								$code .= "<input type=hidden name=transport_name value='" . $transport_name .  "'  />";
							}
							
							
							if (isset($order->data['payment_method'])) {
								$payment_method = explode('|',$order->data['payment_method']);
								$code .= "<input type=hidden name=method value='" . $payment_method[0] . "'/>";
								$code .= "<input type=hidden name=methodfia value='" . variable_get('commerce_certissim_payment_types' . $payment_method[0], '') . "' />";
							}
							$code .= "<input type=hidden name=siteid value='" . variable_get('commerce_certissim_siteid', '') . "'/>";
							
							$code .= "<input type=submit value='Envoyer à  Certissim'>";
							$code .= "</form>";
							
							$img = $code;
							break;
					}
				} else {
					/******  Si le statut de la commande n'est pas en Paiement completed***********/
					$img = "Flux non envoyé à  FIA-NET <img width=20 src=" . dirname($_SERVER["PHP_SELF"]) . '/' . drupal_get_path('module', 'commerce_certissim') . "/images/false-icon.gif />";
				}
			}
		} else {
			$img = "Module Certissim désactivé <img width=20 src=" . dirname($_SERVER["PHP_SELF"]) . '/' . drupal_get_path('module', 'commerce_certissim') . "/images/attention.gif />";
		}
		return $img; 
	}
}
