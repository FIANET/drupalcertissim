<?php

// relever le point de départ
$timestart = microtime(true);

require_once '../../includes/includes.inc.php';

$sac = new Sac();

//calls get_alert
$result = $sac->getAlert('all');

if (!isXMLstring($result))
  die('Get Alert error: ' . $result);

//stream response display
echo "<textarea cols='100' rows='10'>$result</textarea>";

$dom_result = new FianetGetValidstackByDateResponseResult($result);

echo "<hr />";
echo "<h1>".$dom_result->getCount()." transactions réévaluées</h1>";
echo "<hr />";

foreach ($dom_result->getTransactions() as $dom_transaction) {
  echo "<h2>Transaction de référence " . $dom_transaction->getRefid() . "</h2>";
  echo "<ul>";
  echo "<li>";
  echo "Etat de la transaction : " . $dom_transaction->getStatus();
  echo "</li>";
  echo "<li>";
  echo "Date de la transaction : " . $dom_transaction->getDate();
  echo "</li>";
  if ($dom_transaction->isScored()) {
    echo "<li>";
    echo "Score : " . $dom_transaction->getScore();
    echo "</li>";
    echo "<li>";
    echo "Critère du score : " . $dom_transaction->getEvaluationCriteria();
    echo "</li>";
    echo "<li>";
    echo "Profil déclenché : " . $dom_transaction->getProfile();
    echo "</li>";
  }
  if ($dom_transaction->hasXMLError()) {
    echo "<li>";
    echo "Erreur rencontrée : <i>" . $dom_transaction->getError() . "</i>";
    echo "</li>";
  }
  echo "</ul>";
}



echo "<hr />";

//Fin du code PHP
$timeend = microtime(true);
$time = $timeend - $timestart;

//Afficher le temps d'éxecution
$page_load_time = number_format($time, 3);
echo "Debut du script: " . date("H:i:s", $timestart);
echo "<br>Fin du script: " . date("H:i:s", $timeend);
echo "<br>Script execute en " . $page_load_time . " sec";