<?php
$outputs = [
    'First iteration output',
    'Second iteration output',
    'One more iteration output',
    'This took more time',
];
$iterationTime = 10000;
foreach ($outputs as $output) {
    usleep($iterationTime);
    $iterationTime *= 10;
    echo $output."\n";
}
