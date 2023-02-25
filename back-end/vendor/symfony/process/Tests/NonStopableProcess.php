<?php
function handleSignal($signal)
{
    switch ($signal) {
        case SIGTERM:
            $name = 'SIGTERM';
            break;
        case SIGINT:
            $name = 'SIGINT';
            break;
        default:
            $name = $signal.' (unknown)';
            break;
    }
    echo "signal $name\n";
}
pcntl_signal(SIGTERM, 'handleSignal');
pcntl_signal(SIGINT, 'handleSignal');
echo 'received ';
$duration = isset($argv[1]) ? (int) $argv[1] : 3;
$start = microtime(true);
while ($duration > (microtime(true) - $start)) {
    usleep(10000);
    pcntl_signal_dispatch();
}
