<?php

/**
 * This class implement an element <result> got in the response of the webservice get_validstack.cgi called with ref list in param
 */
class CertissimGetValidstackByRefsResponseResult extends CertissimGetEvaluationsResponseResult {

  /**
   * returns the value of the attribute <i>refid</i> of the current root element (<result>), which is the reference of the order given through the XML stream sent to Certissim
   * 
   * @return string
   */
  public function getRefid() {
    return $this->root->getAttribute('refid');
  }

  /**
   * returns an array containing all the elements <transaction> child of current root element as CertissimGetValidstackByRefsResponseResultTransaction objects
   * 
   * @return \CertissimGetValidstackByRefsResponseResultTransaction
   */
  public function getTransactions() {
    $transactions = array();
    foreach ($this->getElementsByTagName('transaction') as $transaction)
      $transactions[] = new CertissimGetValidstackByRefsResponseResultTransaction($transaction->C14N());

    return $transactions;
  }

}