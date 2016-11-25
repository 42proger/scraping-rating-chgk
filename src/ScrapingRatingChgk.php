<?php
// Парсер данных с сайта рейтинга сообщества знатоков rating.chgk.info

header("Content-Type: text/html; charset=UTF-8");
error_reporting(E_ERROR | E_PARSE);
date_default_timezone_set('Europe/Moscow');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$remove_urls=true;

/*
  order=&country=СТРАНА
  order=&region=РЕГИОН
  order=&town=ГОРОД 
*/
$q = "order=&town=Москва";
$params=$_SERVER['QUERY_STRING'];
if(!empty($params)){$q=$params;}

$source_url = "https://rating.chgk.info/teams.php?$q&r=".rand(0, 9999);

$ch = curl_init(); 
curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
$header = array("Cache-Control: no-cache");
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_URL,$source_url); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
$html = curl_exec($ch); 
curl_close($ch); 

$dom = new DOMDocument();
$dom->loadHTML($html);
$xpath = new DOMXPath($dom);

$data = Array();

foreach ($xpath->query('//table[@id="teams_table"]//tbody/tr') as $el) {

  $tr  = $el;
  $i   = 0;
  $bad = false;
  $tds = $tr->getELementsByTagName('td');

  $sub_array = Array();
  foreach ($tds as $td) {
    if ($i == 1) {
      $position = $td->nodeValue;

      if (strlen($position) < 1 || strpos($position, '-')) {
        $bad = true;
      }

    }

    array_push($sub_array, $dom->saveHTML($tds->item($i)));
    $i++;

  }

  if (!$bad) {
    array_push($data, $sub_array);
  }

}

echo '<table class="rating-mac">';
echo '<thead>';
echo '<tr>';

// Заголовки
$trs= $xpath->query('//table[@id="teams_table"]//thead/tr');
$i=0;
foreach ($trs as $tr)
{
  $td = $dom->saveHTML($trs->item($i));
  if($remove_urls){$td=preg_replace('/ href=".*?"/','',$td);}
  echo $td;
  $i++;
  }

echo '</tr>';
echo '</thead>';
echo '<tbody>';

foreach ($data as $tr) {
  echo '<tr>';

  $i=0;
  foreach ($tr as $td) {
  // Данные
  if($i==7)
  {
    $td = str_replace('href="/team','target="_blank" href="https://rating.chgk.info/team',$td);
  }
  else
  {
    if($remove_urls){$td=preg_replace('/ href=".*?"/','',$td);}
    }
    echo trim($td);
    $i++;
  }

  echo '</tr>';
}

echo '</tbody>';
echo '</table>';

?>