<?php
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
return function (InputInterface $input, OutputInterface $output) {
    $output = new SymfonyStyle($input, $output);
    $output->listing([
        'Lorem ipsum dolor sit amet',
        'consectetur adipiscing elit',
    ]);
    $output->success('Lorem ipsum dolor sit amet');
};
