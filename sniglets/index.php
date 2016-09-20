<?php
// returns json.encoded results from randomly selected MySQL row
require("config.inc.php");
    $sniglet = new sniglet();
    echo $sniglet->getRandomSniglet();
	
?>
