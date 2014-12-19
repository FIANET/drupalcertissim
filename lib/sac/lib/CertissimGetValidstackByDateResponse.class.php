<?php

/**
 * Implements the response of the webservice get_validstack.cgi called with a date in param
 */
class CertissimGetValidstackByDateResponse extends CertissimGetEvaluationsResponse {
  /**
   * returns a collection of objects CertissimGetValidstackByDateResponseResult
   * 
   * @return \CertissimGetValidstackByRefsResponseResult
   */
  public function getResults() {
    $results = array();
    $result_nodes = $this->getElementsByTagName('result');
    foreach ($result_nodes as $node) {
      $results[] = new CertissimGetValidstackByDateResponseResult($node->C14N());
    }
    return $results;
  }

}