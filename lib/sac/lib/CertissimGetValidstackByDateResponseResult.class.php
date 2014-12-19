<?php

/**
 * This class implement an element <result> got in the response of the webservice get_validstack.cgi called with a date in param or in the response of the webservice get_alert.cgi
 */
class CertissimGetValidstackByDateResponseResult extends CertissimGetEvaluationsResponseResult {

  /**
   * returns the value of the attribute <i>date</i> of the current root element (<result>), which is the reference of the order given through the XML stream sent to Certissim
   * 
   * @return string
   */
  public function getDate() {
    return $this->root->getAttribute('date');
  }

  /**
   * returns an array containing all the elements <transaction> child of current root element as CertissimGetValidstackByDateResponseResultTransaction objects
   * 
   * @return \CertissimGetValidstackByDateResponseResultTransaction
   */
  public function getTransactions() {
    $transactions = array();
    foreach ($this->getElementsByTagName('transaction') as $transaction)
      $transactions[] = new CertissimGetValidstackByDateResponseResultTransaction($transaction->C14N());

    return $transactions;
  }

}