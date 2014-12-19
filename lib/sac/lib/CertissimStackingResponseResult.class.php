<?php

class CertissimStackingResponseResult extends DOMDocument {

  private $root;
  
  public function __construct($xml) {
    parent::__construct('1.0', 'UTF-8');
    $this->loadXML($xml);
    $this->root = $this->getElementsByTagName('result')->item(0);
  }

  /**
   * returns true if the current transaction has an error in the XML stream, false otherwise
   * if true, the transaction has been received by Certissim and has to be fixed manually, it must not be sent again
   * 
   * @return bool
   */
  public function hasXMLError() {
    return in_array($this->root->getAttribute('errorid'), array('101', '201'));
  }

  /**
   * returns true if an error has been encoutered and the transaction could not be received by Certissim, false otherwise
   * if true, the transaction has to be sent again
   * 
   * @return bool
   */
  public function hasFatalError() {
    return in_array($this->root->getAttribute('errorid'), array('102', '103', '104', '105', '106', '212', '213'));
  }

  /**
   * returns the status of the transaction
   * 
   * @return string values : error, encours
   */
  public function getStatus() {
    return $this->root->getAttribute('avancement');
  }

  public function getRefid() {
    return $this->root->getAttribute('refid');
  }
  
  public function getSiteid(){
    return $this->root->getAttribute('siteid');
  }
  
  public function getDetail(){
    $details = $this->root->getElementsByTagName('detail');
    return $details->item(0)->nodeValue;
  }

}