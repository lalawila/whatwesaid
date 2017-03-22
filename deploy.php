<?php

$commands = array('git pull', );
foreach ($commands as $command) {
    // Run it
    echo $command;
    echo shell_exec($command);
}

?>
