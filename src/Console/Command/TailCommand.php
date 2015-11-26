<?php

namespace Logue\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TailCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('tail')
            ->setDescription('Tail a log file')
            ->addArgument(
                'filename',
                InputArgument::REQUIRED,
                'Path to a log file'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('filename');
        $sleep = 1.0; // in seconds
        $numberOfLines = 3;

        $lineOffset = 0;
        $isFirstLoop = true;
        while (true) {
            if (!file_exists($filename)) {
                throw new \Exception('File not exists.');
            }
            if (!is_readable($filename)) {
                continue;
            }

            // Count all lines in file
//            $lines = file($filename, FILE_IGNORE_NEW_LINES);
//            $countLines = count($lines);
            $handle = fopen($filename, 'r');
            $countLines = 0;
            while(!feof($handle)){
                $line = fgets($handle);
                $countLines++;
            }
            unset($line);

            // Check whether is file truncated
            if ($lineOffset > $countLines) {
                $output->writeln('File truncated.');
            }

            // Check whether it's a first loop
            if (true === $isFirstLoop) {
                // and get only last $numberOfLines lines if so
                $lineOffset = (int)($countLines - $numberOfLines);
                $lineOffset = 0 < $lineOffset ? $lineOffset : 0;
            }

            // Output new lines only
//            for ($i = $lineOffset; $i < $countLines; $i++) {
//                $output->writeln($lines[$i]);
//            }
            $i = 0;
            rewind($handle);
            while (!feof($handle)) {
                $line = rtrim(fgets($handle));
                if ($i++ < $lineOffset) {
                    continue;
                }
                $output->writeln($line);
            }
            unset($line);

            $lineOffset = $countLines;
            $isFirstLoop = false;
            fclose($handle);
            sleep($sleep);
        }
    }
}
