<?php

/**
 * This class implements the element <transaction> contained in the <result> element, got in the response of a webservice designed to get many evaluations in once : get_validstack.cgi by ref list, get_validstack.cgi by date, get_alert.cgi
 */
abstract class CertissimGetEvaluationsResponseResultTransaction extends CertissimDOMDocument {

  /**
   * returns true if the current transaction has an error in the XML stream, false otherwise
   * if true, the transaction has been received by Certissim and has to be fixed manually, it must not be sent again
   * 
   * @return bool
   */
  public function hasXMLError() {
    return $this->root->getAttribute('avancement') == 'error';
  }

  /**
   * returns the value of the attribute <i>cid</i> which is the unique identifier of the transaction in the Certissim system
   * 
   * @return int
   */
  public function getCommerceId() {
    return $this->root->getAttribute('cid');
  }

  /**
   * returns the status of the transaction
   * Possible values :
   * - error: an error has been found inside the XML stream. You have to connect to the Fia-Net portal and fix it manually.
   * - encours: the transaction score is being calculated
   * - traitee: the transaction has been evaluated and a score is available
   * 
   * @return string
   */
  public function getStatus() {
    return $this->root->getAttribute('avancement');
  }

  /**
   * returns the label of the error encountered if error has been encountered, empty string otherwise
   * 
   * @return string
   */
  public function getError() {
    $detail = $this->root->getElementsByTagName('detail')->item(0);
    if (is_object($detail) && (get_class($detail) == "DOMElement" || is_subclass_of($detail, "DOMElement")))
      return $detail->nodeValue;
    else
      return '';
  }

  /**
   * return true if the transaction has been evaluated, false otherwise
   * 
   * @return bool
   */
  public function isScored() {
    return $this->getStatus() == 'traitee';
  }

  /**
   * returns the vaue of the attribute <i>date</i> of the current root element, which indicates the date of this transaction's score, if the transaction has been score, returns an empty string otherwise
   * 
   * @return string
   */
  public function getDate() {
    return $this->getEvalAttribute('date');
  }

  /**
   * returns the score of the transaction if it's scored, returns an empty string otherwise
   * Possible values:
   * - 0: some element of the order has been detected by the Certissim system that allow it to think there is a real risk of fraud
   * - 100: some element of the order has been detected by the Certissim system that allow it to think this transaction is riskfree
   * - -1: the system did not find any element allowing to think there is a risk or not. By default it considers the risk is low (90% safe).
   * 
   * @return int
   */
  public function getScore() {
    $eval = $this->getEval();
    if (is_object($eval) && (get_class($eval) == "DOMElement" || is_subclass_of($eval, "DOMElement")))
      return $eval->nodeValue;
    else
      return '';
  }

  /**
   * returns the main criteria that made the score if transaction scored, empty string otherwise
   * 
   * @return string
   */
  public function getEvaluationCriteria() {
    return $this->getEvalAttribute('validation');
  }

  /**
   * returns the profile label if transaction scored, empty string otherwise
   * 
   * @return string
   */
  public function getProfile() {
    return $this->getEvalAttribute('info');
  }

  /**
   * returns the id of the classement of the transaction if transaction scored, empty string otherwise
   * 
   * @return string
   */
  public function getClassementId() {
    return $this->getClassementAttribute('id');
  }

  /**
   * return the classement label of the transaction if transaction scored, empty string otherwise
   * 
   * @return string
   */
  public function getClassementLabel() {
    $classement = $this->getClassement();
    if (is_object($classement) && (get_class($classement) == "DOMElement" || is_subclass_of($classement, "DOMElement")))
      return $classement->nodeValue;
    else
      return '';
  }

  /**
   * returns the value of the attribute $attrname of the element <classement>
   * 
   * @param string $attrname
   * @return string
   */
  private function getClassementAttribute($attrname) {
    $classement = $this->getClassement();
    if (is_object($classement) && (get_class($classement) == "DOMElement" || is_subclass_of($classement, "DOMElement")))
      return $classement->getAttribute($attrname);
    else
      return '';
  }

  /**
   * returns the element <classement>
   * 
   * @return DOMElement
   */
  private function getClassement() {
    return $this->getElementsByTagName('classement')->item(0);
  }

  /**
   * returns the value of the attribute $attrname of the element <eval>
   * 
   * @param type $attrname
   * @return string
   */
  private function getEvalAttribute($attrname) {
    $eval = $this->getEval();
    if (is_object($eval) && (get_class($eval) == "DOMElement" || is_subclass_of($eval, "DOMElement")))
      return $eval->getAttribute($attrname);
    else
      return '';
  }

  /**
   * returns the element <eval>
   * 
   * @return DOMElement
   */
  private function getEval() {
    return $this->getElementsByTagName('eval')->item(0);
  }

}