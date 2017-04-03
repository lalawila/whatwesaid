<?php

$commands = array('git fetch --all',
		  'git reset --hard origin/master',
		  'git pull origin master',
		  'git status');
foreach ($commands as $command) {
    echo $command . ":" . shell_exec($command) . "</br>";
}

?>
