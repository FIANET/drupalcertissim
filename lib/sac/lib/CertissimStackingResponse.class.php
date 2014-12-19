<?php

/**
 * Represents the response of the script stacking.cgi
 */
class CertissimStackingResponse extends DOMDocument {

  public function __construct($xml) {
    parent::__construct('1.0', 'UTF-8');
    $this->loadXML($xml);
  }

  /**
   * returns true if the XML stream could not be received by Certissim, false otherwise
   * 
   * @return boolean
   */
  public function hasFatalError() {
    $unlucks = $this->getElementsByTagName('unluck');
    return !is_null($unlucks->item(0));
  }

  /**
   * returns the error label if an error occured, an empty string otherwise
   * 
   * @return string
   */
  public function getFatalError() {
    if (!$this->hasFatalError())
      return '';

    $unlucks = $this->getElementsByTagName('unluck');
    $unluck = $unlucks->item(0);
    return $unluck->nodeValue;
  }

  /**
   * returns a collection of objects CertissimStackingResponseResult
   * 
   * @return array
   */
  public function getResults() {
    $results = array();
    $result_nodes = $this->getElementsByTagName('result');
    foreach ($result_nodes as $node) {
      $results[] = new CertissimStackingResponseResult($node->C14N());
    }
    return $results;
  }

}