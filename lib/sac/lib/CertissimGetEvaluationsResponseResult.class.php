<?php

/**
 * This class implement an element <result> got in the response of a webservice designed to get many evaluations in once : get_validstack.cgi by ref list, get_validstack.cgi by date, get_alert.cgi
 */
abstract class CertissimGetEvaluationsResponseResult extends CertissimDOMDocument {

  /**
   * returns the value of the attribute <i>siteid</i> of the current root element (<result>), which is the merchant identifier given through the XML stream sent to Certissim
   * 
   * @return int
   */
  public function getSiteid() {
    return $this->root->getAttribute('siteid');
  }

  /**
   * returns the value of the attribute <i>retour</i> of the current root element <result>, which indicates the state of the order research
   * Possible values :
   * - trouvee : means the order has been found
   * - absente : means the order has not been found
   * - param_error : means there is an error in the parameters sent. Check site_params.yml
   * - internal_error : means Certissim encountered an error. It may happen when there is an server overload for example. You have to wait before calling the webservice again. If keep getting this error please contact support.
   * 
   * @return string
   */
  public function getRetour() {
    return $this->root->getAttribute('retour');
  }

  /**
   * returns the value of the attribute <i>count</i> of the root element <result>, which is the number of transactions found for the current order ref
   * 
   * @return int
   */
  public function getCount() {
    return $this->root->getAttribute('count');
  }

  /**
   * returns true if the order analysis is not reachable because of an error, false otherwise
   * 
   * @return bool
   */
  public function hasError() {
    return in_array($this->root->getAttribute('retour'), array('param_error', 'internal_error'));
  }

  /**
   * returns the label of the error encountered
   * 
   * @return string
   */
  public function getError() {
    return $this->root->getAttribute('message');
  }

  /**
   * returns the code of the error encountered
   * 
   * @return int
   */
  public function getErrorCode() {
    return $this->root->getAttribute('errcode');
  }

  /**
   * returns an array containing all the elements <transaction> child of current root element
   * 
   * @return array
   */
  abstract public function getTransactions();
}