<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);

require($argv[2]."/classes/class.curl.php");
require($argv[2]."/functions.php");

$vars = init_wp_variables($argv[3]);

if($vars['state'])
{
	try
	{
	        $pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
	        $bdd = new PDO('mysql:host='.$vars['bdd_host'].';dbname='.$vars['bdd_name'], $vars['bdd_user'], $vars['bdd_pass']);
	}
	catch (Exception $e)
	{
	        die('Error : ' . $e->getMessage());
	}

	switch($argv[1])
	{
		case "infos":
		wrush_infos($bdd, $vars['bdd_prefix']);
		break;

		case "blogs":
		wrush_blogs($bdd, $vars['bdd_prefix'], $vars['wp_multisite']);
		break;

		case "plugins":
		wrush_plugins($bdd, $vars['bdd_prefix'], $vars['wp_multisite']);
		break;

		case "crons":
		if(isset($argv[5])) {
			if(isset($argv[6])) {
				// Include Auth informations
				wrush_crons($bdd, $vars['bdd_prefix'], $vars['wp_multisite'], $argv[5], $argv[6]);
			} else {
				wrush_crons($bdd, $vars['bdd_prefix'], $vars['wp_multisite'], $argv[5]);
			}
		} else {
			wrush_crons($bdd, $vars['bdd_prefix'], $vars['wp_multisite']);
		}
		break;
	}


} else {
	echo "Wrush : An error occured with your wp-config.php file";
}
?>

