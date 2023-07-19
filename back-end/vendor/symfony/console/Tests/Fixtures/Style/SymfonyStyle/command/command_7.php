<?php
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
return function (InputInterface $input, OutputInterface $output) {
    $output = new SymfonyStyle($input, $output);
    $output->title('Title');
    $output->askHidden('Hidden question');
    $output->choice('Choice question with default', ['choice1', 'choice2'], 'choice1');
    $output->confirm('Confirmation with yes default', true);
    $output->text('Duis aute irure dolor in reprehenderit in voluptate velit esse');
};