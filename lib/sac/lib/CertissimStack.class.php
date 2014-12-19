<?php

/**
 * This class implements an element <stack>
 */
class CertissimStack extends DOMDocument {
  
  /**
   * @var CertissimXMLElement
   */
  public $root;

  public function __construct() {
    parent::__construct('1.0', 'UTF-8');
    $this->root = $this->appendChild(new CertissimXMLElement('stack'));
  }
  
  /**
   * append a child <control> to the current root <stack> element
   * 
   * @param CertissimControl $control
   */
  public function addControl(CertissimControl $control){
    $control_node = $this->importNode($control->getRootElement(), true);
    return $this->root->appendChild($control_node);
  }
}