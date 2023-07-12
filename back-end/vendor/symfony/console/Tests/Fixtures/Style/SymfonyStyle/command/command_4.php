<?php
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
return function (InputInterface $input, OutputInterface $output) {
    $output = new SymfonyStyle($input, $output);
    $output->write('Lorem ipsum dolor sit amet');
    $output->title('First title');
    $output->writeln('Lorem ipsum dolor sit amet');
    $output->title('Second title');
    $output->write('Lorem ipsum dolor sit amet');
    $output->write('');
    $output->title('Third title');
    $output->write('Lorem ipsum dolor sit amet');
    $output->write(['', '', '']);
    $output->title('Fourth title');
    $output->writeln('Lorem ipsum dolor sit amet');
    $output->writeln(['', '']); 
    $output->title('Fifth title');
    $output->writeln('Lorem ipsum dolor sit amet');
    $output->newLine(2); 
    $output->title('Fifth title');
};
