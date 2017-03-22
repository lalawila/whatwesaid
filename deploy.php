<?php
	$commands = array(
		'git pull',
	);
	foreach($commands AS $command){
		// Run it
		shell_exec($command);
	}
?>
