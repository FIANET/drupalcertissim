<?php

// relever le point de départ
$timestart = microtime(true);

require_once '../../includes/includes.inc.php';

$sac = new Sac();

$stack = $sac->getValidstackByDate('18/03/2013', 1);

if (!isXMLstring($stack))
  die('Get Validstack by reflist error : ' . $stack);

echo "<textarea cols='100' rows='10'>$stack</textarea>";

echo "<hr />";

$dom_stack = new FianetGetValidstackByDateResponse($stack);
echo $dom_stack->getTotal() . " résultats.";

echo "<hr />";
foreach ($dom_stack->getResults() as $dom_result) {
  echo "<h2>Résultat pour date " . $dom_result->getDate() . "</h2>";
  echo "<ul>";
  echo "<li>";
  echo "Retour : " . $dom_result->getRetour();
  echo "</li>";
  echo "<li>";
  echo "Nombre de transactions à cette date : " . $dom_result->getCount();
  echo "</li>";
  if ($dom_result->hasError()) {
    echo "<li>";
    echo $dom_result->getError();
    echo "</li>";
  }
  echo "</ul>";

  foreach ($dom_result->getTransactions() as $dom_transaction) {
    echo "<h3>Transaction de référence " . $dom_transaction->getRefid() . "</h3>";
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