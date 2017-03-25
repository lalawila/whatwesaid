<?php

$commands = array('git pull origin master',
			'git status');
foreach ($commands as $command) {
    echo shell_exec($command);
}

?>
