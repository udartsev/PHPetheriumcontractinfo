<?php
/********* ETHERIUM CONTRACT REVIEW *********/
/************* UDARTSEV.RU 2018 **********/

/*SETTING UP PHP TO RUNNING FROM SERVER*/
ini_set('max_execution_time', '60000');

/*SETTING VARS*/
$contractAddr = '0x36f3Ff438cd96f095b1bcC03D1B197Bd33777578'; // CHANGE REVIEWING CONTRACT HERE
$ethUsd = '726.89'; // CHANGE ETH/USD CURRENCY HERE
$pages = 81; // CHANGE ETHERIUM CONTRACT PAGES FROM ETHERSCAN.IO

/*PROGRAM VARS*/
$etherscanUrl = 'https://etherscan.io/txs?a=' . $contractAddr . '&p=';
$badCount = 0;
$badValue = 0;
$goodValue = 0;
$goodCount = 0;

/*ADDING PHPQUERY LIBRARY*/
require './phpQuery/phpQuery.php';

for ($i = 0; $i < $pages; $i++) {

	/*GETTING HTML*/
	$html = file_get_contents($etherscanUrl . $i);

	/*PHPQUERY INIT*/
	$pq = phpQuery::newDocument($html);

	/*FINDING DATA*/
	$dataTable = $pq->find('div[id="ContentPlaceHolder1_mainrow"] tbody tr');
	foreach ($dataTable as $data) {

		/*GETTING VALUE OF SENDUNG FUNDS*/
		$value = pq($data)->find('td:nth-child(7)')->text();
		$value = substr($value, 0, -6);
		$valueArray[] = $value;

		/*GETTING SENDER ADDR*/
		$from = pq($data)->find('td:nth-child(4) a')->attr('href');
		$from = substr($from, -42, 42);

		/*COUNTING ANNR TRANSACTIONS AND ETH VALUE*/
		if (isset($results[$from]['tnxs'])) {$results[$from]['tnxs']++;} else { $results[$from]['tnxs'] = 1;}
		if (isset($results[$from]['value'])) {
			$results[$from]['value'] = $results[$from]['value'] + $value;
		} else { $results[$from]['value'] = $value;}

		/*GETTING BAD TRANSACTION AND COUNTING IT VALUE*/
		$color = pq($data)->find('td:nth-child(1) font')->attr('color');
		if ($color == 'red') {
			if (!isset($results[$from]['badCount'])) {
				$results[$from]['badCount'] = 1;
				$results[$from]['badValue'] = $value;
			} else {
				$results[$from]['badCount']++;
				$results[$from]['badValue'] = $results[$from]['badValue'] + $value;
			}
			$badCount++;
			$badValue = $badValue + $results[$from]['badValue'];
		} else {
			if (!isset($results[$from]['goodCount'])) {
				$results[$from]['goodCount'] = 1;
				$results[$from]['goodValue'] = $value;
			} else {
				$results[$from]['goodCount']++;
				$results[$from]['goodValue'] = $results[$from]['goodValue'] + $value;
			}
			$goodCount++;
			$goodValue = $goodValue + $results[$from]['goodValue'];
		}
	}
}

/*SUMMING ALL CONTRACT TRANSACTIONS*/
$valueEth = array_sum($valueArray);
$valueUsd = $valueEth * $ethUsd;

/*COUNTING CONTRACT`S BAD AND GOOD TRANSACTIONS, VALUES, PRICES AND DIFFS*/
$goodValueUsd = $goodValue * $ethUsd;
$badValueUsd = $badValue * $ethUsd;
$goodBadDiff = $goodValue - $badValue;
$goodBadDiffUsd = $goodBadDiff * $ethUsd;
$goodBadCountDiff = $goodCount - $badCount;

/*PRINTING RESULTS ON THE SCREEN*/
echo "<h1>Contract address\t: $contractAddr<h1><hr>";
echo "<h1>All contract transactions: \t" . $valueEth . " [Ether] OR " . $valueUsd . " [USD] </h1><hr>";
echo "<h2>Good transactions: \t" . $goodValue . " [Ether] OR " . $goodValueUsd . " [USD] </h2>";
echo "<h2>Bad transactions: \t" . $badValue . " [Ether] OR " . $badValueUsd . " [USD] </h2><hr>";
echo "<h3>Good-Bad tnxs = \t" . $goodBadDiff . " [Ether] OR " . $goodBadDiffUsd . " [USD] </h3>";
echo "<h3>Good-Bad diff = \t" . $goodBadCountDiff . " [q]</h3><hr>";

/*PRINTING ALL INCOMINGS CONTRACT DATA ON THE SCREEN*/
echo "<table class='table' border='1' cellpadding='10'>";
echo "<thead>";
echo "<tr><td>No</td><td>FROM ADDR</td><td>BAD TXNS(q)</td><td>BAD VALUE(eth)</td><td>GOOD TXNS(q)</td><td>GOOD VALUE(eth)</td></tr>";
echo "</thead>";
echo "<tbody>";

$no = 0;
foreach ($results as $from => $value) {
	$no++;

	if (!isset($value['badCount'])) {$value['badCount'] = 0;}
	if (!isset($value['badValue'])) {$value['badValue'] = 0;}
	if (!isset($value['goodCount'])) {$value['goodCount'] = 0;}
	if (!isset($value['goodValue'])) {$value['goodValue'] = 0;}

	echo "<tr>";
	echo "<td>$no</td>";
	echo "<td>$from</td>";
	echo "<td>$value[badCount]</td>";
	echo "<td>$value[badValue]</td>";
	echo "<td>$value[goodCount]</td>";
	echo "<td>$value[goodValue]</td>";
	echo "</tr>";
}
echo "</tbody>";
echo "</table>";