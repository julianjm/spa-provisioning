#!/usr/bin/php
<?php

if (!isset($_SERVER['argv'][1])) {
	echo "Usage: xml2cfg <config.xml>\n";
	exit;
}

$xml=$_SERVER['argv'][1];

if (!is_file($xml)) {
	echo "xml2cfg error: \"$xml\" is not a valid file\n";
	exit;
}

$f=fopen($xml,"rb");

$config=array();
while( ($line=fgets($f))!==false ) {
	if (preg_match("/^\<(\w+)\sgroup=\"(\w+)\/(\w+)\"\>(\w+)\</",$line,$matches)) {
		$config[$matches[2]][$matches[3]][$matches[1]]=$matches[4];
	}
}

unset($config["Info"]);

echo "# CONFIGURATION TEMPLATE\n\n";
foreach($config as $group=>$sections) {
	echo "\n############################# $group ###################\n";
	foreach ($sections as $section=>$settings) {
		echo "\n##### $group / $section\n";
		foreach($settings as $key=>$val) {
			echo "$key:$val\n";
		}
	}
}
?>
