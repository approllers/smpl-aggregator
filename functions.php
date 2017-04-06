<?php

function humanizeNum($num) {
	if ($num > 999) {
		$abbreviations = array(12 => 'T', 9 => 'B', 6 => 'M', 3 => 'K', 0 => '');
		foreach($abbreviations as $exponent => $abbreviation) {
			if ($num >= pow(10, $exponent)) {
				return round(floatval($num / pow(10, $exponent)), 1) . $abbreviation;
			}
		}
	} else { return $num; }
}


?>