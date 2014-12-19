<?php

// relever le point de départ
$timestart = microtime(true);

require_once '../../includes/includes.inc.php';
//loads Sac params
$sac = new Sac();

include_once 'control.example.php';

/* * **** <stack> ************ */
$stack = new FianetStack();
$stack->addControl($control);


//order sending
$validstack = $sac->sendStacking($stack);
$dom = new FianetStackingResponse($validstack);
echo "<textarea cols='100' rows='20'>";
echo $dom->saveXML();
echo "</textarea>";

echo "<hr />";
if ($dom->hasFatalError())
  echo $dom->getFatalError();
else
  foreach ($dom->getResults() as $result) {
    echo "<table>";
    echo "<tr><td>";
    echo "<textarea cols='100' rows='20'>";
    echo $result->saveXML();
    echo "</textarea>";
    echo "</td>";
    echo "<td>";
    echo "Avancement : " . $result->getStatus();
    echo "<br />";
    echo "Refid : " . $result->getRefid();
    echo "<br />";
    echo "Siteid : " . $result->getSiteid();
    echo "<br />";
    echo "Detail : " . $result->getDetail();
    echo "<br />";
    echo "</td></tr>";
    echo "</table>";
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