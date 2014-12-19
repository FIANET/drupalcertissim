<?php

/**
 * This class implements the element <transaction> contained in the <result> element, got in the response of the webservice get_validstack.cgi called with date or in the response of the webservice get_alert.cgi
 */
class CertissimGetValidstackByDateResponseResultTransaction extends CertissimGetEvaluationsResponseResultTransaction {

  /**
   * returns the value of the attribute <i>refid</i> which is the reference of the order concerned as it was sent to Certissim
   * 
   * @return string
   */
  public function getRefid() {
    return $this->root->getAttribute('refid');
  }

}