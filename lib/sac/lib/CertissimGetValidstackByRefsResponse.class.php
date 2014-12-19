<?php

/**
 * Implements the response of the webservice get_validstack.cgi called with ref list in param
 */
class CertissimGetValidstackByRefsResponse extends CertissimGetEvaluationsResponse {
  /**
   * returns a collection of objects CertissimGetValidstackByRefsResponseResult
   * 
   * @return \CertissimGetValidstackByRefsResponseResult
   */
  public function getResults() {
    $results = array();
    $result_nodes = $this->getElementsByTagName('result');
    foreach ($result_nodes as $node) {
      $results[] = new CertissimGetValidstackByRefsResponseResult($node->C14N());
    }
    return $results;
  }

}