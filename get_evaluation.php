<?php

require_once ('/lib/includes/includes.inc.php');

$timestart = microtime(true);

$reflist = array($_GET['orderid']);
$sac = new Certissim();
$stack = $sac->getValidstackByReflist($reflist);

if (!isXMLstringCertissim($stack))
  die('Get Validstack by reflist error : ' . $stack);

echo "<hr />";

$dom_stack = new CertissimGetValidstackByRefsResponse($stack);
if ($dom_stack->getTotal() == 1)
    echo $dom_stack->getTotal() . " r&eacute;sultat.";
else 
    echo $dom_stack->getTotal() . " r&eacute;sultats.";

echo "<hr />";
foreach ($dom_stack->getResults() as $dom_result) 
    {
	echo "<h2>R&eacute;sultat pour refid " . $dom_result->getRefid() . "</h2>";
    echo "<ul>";
    echo "<li>";
    echo "Retour : " . $dom_result->getRetour();
    echo "</li>";
    echo "<li>";
    echo "Nombre de transactions pour cette r&eacute;ference : " . $dom_result->getCount();
    echo "</li>";
    
    if ($dom_result->hasError()) 
        {
            echo "<li>";
            echo $dom_result->getError();
            echo "</li>";
     }
  echo "</ul>";

  foreach ($dom_result->getTransactions() as $dom_transaction) 
      {
        echo "<h3>Transaction CID " . $dom_transaction->getCommerceId() . "</h3>";
        echo "<ul>";
        echo "<li>";
        echo "Etat de la transaction : " . $dom_transaction->getStatus();
        echo "</li>";
        echo "<li>";
        echo "Date de la transaction : " . $dom_transaction->getDate();
        echo "</li>";
    if ($dom_transaction->isScored()) 
        {
        echo "<li>";
        echo "Score : " . $dom_transaction->getScore();
        echo "</li>";
        echo "<li>";
        echo "Crit&egrave;re du score : " . $dom_transaction->getEvaluationCriteria();
        echo "</li>";
        echo "<li>";
        echo "Profil d&eacute;clench&eacute; : " . $dom_transaction->getProfile();
        echo "</li>";
        CertissimLogger::insertLogCertissim (__METHOD__, $dom_transaction->getStatus());
    }
    if ($dom_transaction->hasXMLError()) 
       {
        echo "<li>";
        echo "Erreur rencontr&eacute;e : <i>" . $dom_transaction->getError() . "</i>";
        echo "</li>";
    }
    CertissimLogger::insertLogCertissim(__METHOD__, $dom_transaction->getError());
    echo "</ul>";
  }
}
echo "<hr />";
$timeend = microtime(true);
$time = $timeend - $timestart;
echo "<br>Fin du script: " . date("H:i:s", $timeend);