<?php

/**
 * This abstract class implements a response from the webservices that are used to get many evaluations in once : get_validstack.cgi by ref list, get_validstack.cgi by date, get_alert.cgi
 */
abstract class CertissimGetEvaluationsResponse extends CertissimDOMDocument {

  /**
   * returns the value of the attribute 'total' of the root element <stack>
   * 
   * @return int
   */
  public function getTotal() {
    return $this->root->getAttribute('total');
  }

  /**
   * returns the results stored into an array
   * 
   * @param $class class of the objects waited in collection
   * @return array
   */
  abstract public function getResults();
}