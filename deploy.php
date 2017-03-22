<?php

$commands = array('git pull origin master',);
foreach ($commands as $command) {
    echo shell_exec($command);
}

?>
