<?php
	$commands = array(
		'git pull origin master',
	);
	foreach($commands AS $command){
		// Run it
		echo shell_exec($command);
	}
?>
