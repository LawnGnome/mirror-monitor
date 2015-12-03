<?php

namespace LawnGnome\MirrorMonitor;

use DateInterval;
use DateTimeImmutable;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument, InputInterface, InputOption};
use Symfony\Component\Console\Output\OutputInterface;

class MonitorCommand extends Command {
  protected function configure() {
    $this->setName('monitor')
         ->setDescription('Monitor a set of mirrored URLs for a substring')
         ->addOption('every', 'e', InputOption::VALUE_REQUIRED, 'How often to check in seconds', '120')
         ->addOption('search', 's', InputOption::VALUE_REQUIRED, 'The string to search for in the page text')
         ->addOption('timeout', 't', InputOption::VALUE_REQUIRED, 'Timeout in seconds', '60')
         ->addOption('urls', 'u', InputOption::VALUE_REQUIRED, 'File containing URLs to monitor');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $every = $this->validateDuration($input->getOption('every'));
    $needle = $input->getOption('search');
    $timeout = $this->validateDuration($input->getOption('timeout'));
    $urls = $this->validateURLs($input->getOption('urls'));

    if (!$needle) {
      throw new InvalidArgumentException('Needle must be provided');
    }

    $cycle = new MonitorCycle($urls, $timeout, $needle);

    while (true) {
      $last = new DateTimeImmutable;

      $output->writeln(sprintf('<bg=green>%s</>', $last->format('Y-m-d H:i:s')));
      list($success, $failed) = $cycle->run();
      $output->writeln(sprintf('<fg=green>%d</> successful; <fg=red>%d</> failed', count($success), count($failed)));
      if ($output->isVerbose()) {
        $output->writeln(sprintf('  <fg=red>Failed URLs:</> %s', implode("\n               ", $failed)));
      }
      $output->writeln('');

      $next = $last->add(new DateInterval("PT{$every}S"));
      do {
        sleep(1);
      } while ((new DateTimeImmutable) < $next);
    }
  }

  protected function validateDuration(string $input): int {
    if ($input && ctype_digit($input) && (0 != (int) $input)) {
      return (int) $input;
    }

    throw new InvalidArgumentException("Duration is not a positive integer");
  }

  protected function validateURLs(string $input): array {
    if (!$input || !is_readable($input)) {
      throw new InvalidArgumentException('URLs must be readable');
    }

    $urls = trim(file_get_contents($input));
    if (!$urls) {
      throw new RuntimeException('One or more URLs must be provided');
    }
    return preg_split('/\s+/', $urls);
  }
}
