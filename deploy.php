<?php

$commands = array('git pull origin master', 'git status','echo $PWD', 'whoami');
foreach ($commands as $command) {
    echo shell_exec($command);
}

?>
