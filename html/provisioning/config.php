<?

#########################################################################
########### SCRIPT FOR PROVISIONING LINKSYS AND SIPURA ATA'S ############
#########################################################################
### Author: Julian J. Menendez <julianjm@gmail.com>                   ###
### URL:    http://www.maxosystem.net/asterisk                        ###
### Date:   27-nov-2005                                               ###
#########################################################################
### Changelog

### 14-04-2008:	Improved the configuration file logic.
#		The initial cfg file retrieved by the phone (spa9x2.cfg)
#		should point to this script:
#		http://x.x.x.x/provisioning/config.php?model=9x2&mac=$MA
#		This script will load the configuration templates in this
#		order:
#		1) 9x2-default.cfg: Factory configuration. It's generated
#		   from the xml configuration (/admin/spacfg.xml). It
#		   should be left intact.
#		2) global.cfg: Custom modifications to the default
#		   settings. General ones, nothing specific to a phone or
#		   a deployment.
#		3) site.cfg: Specific settings for this deployment, such
#		   as proxy IP, syslog server, ntp server, etc
#		4) 9x2-{MAC}.cfg: Specific settings for this phone.
#		   Basically, extension number and password.
### 27-11-2005: Initial Release. Tested on Linksys NA-PAP2.
###             Should work as is on Sipura SPA-2000
#########################################################################

########### CONFIGURATION ############
#Setup paths...

//IMPORTANTE: Leave trailing slash /
$configdir="/etc/provisioning/";     //Base directory

$allowempty=false;		//Generate empty XML tags (not recomended)

########### DO NOT MODIFY BELOW THIS LINE ###########


if (isset($_GET['model']))
	$model=$_GET['model'];
else {
	echo "ERROR: NO MODEL SPECIFIED";
	exit;
}

$mac="";
if (isset($_GET['mac'])) {
	$mac=$_GET['mac'];
	$mac=strtoupper ($mac);
	preg_match("/(\w+)/",$mac,$m); //Avoid invalid macs
	$mac=$m[1];
}
if (empty($mac)) {
	echo "ERROR: EMPTY MAC";
	exit;
}

$defaultcfg=$configdir.$model."-default.cfg";
$globalcfg=$configdir."global.cfg";
$siteglobalcfg=$configdir."site.cfg";
$sitecfg=$configdir.$model."-site.cfg";
$phonecfg=$configdir.$model."-".$mac.".cfg";

$completesetup=array();
load_config($defaultcfg,$completesetup);
load_config($globalcfg,$completesetup);
load_config($siteglobalcfg,$completesetup);
load_config($sitecfg,$completesetup);
load_config($phonecfg,$completesetup);

header("Content-type: text/xml");

echo "<flat-profile>\n";
foreach ($completesetup as $key=>$val) {
	if (!$allowempty && empty($val))
		continue;
	echo "\t<{$key}>{$val}</{$key}>\n";
}
echo "</flat-profile>\n";



function load_config($file,&$config) {
	if (is_file($file)) {
		$fd=fopen($file,"r");
		$cfgplain=fread($fd,filesize($file));
		fclose($fd);
		$cfg=explode("\n",$cfgplain);
		foreach ($cfg as $row) {
		    if (!is_null($row)) {
			if ($row[0] <> '#') {
		            preg_match("/^(\w+):(.*)$/",$row,$matches);
			    $key=$matches[1];
			    $val=$matches[2];
			    if (!empty($key))
				$config[$key]=htmlentities(trim($val));
			}
		    }
		}
	}
}
?>

