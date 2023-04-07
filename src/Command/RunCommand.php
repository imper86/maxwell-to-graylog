<?php

declare(strict_types=1);

namespace App\Command;

use App\Provider\MaxwellLogProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use function date;
use function sleep;
use function sprintf;

#[AsCommand(
    'app:run',
    'Runs app worker'
)]
final class RunCommand extends Command
{
    public function __construct(
        private readonly MaxwellLogProvider $maxwellLogProvider,
        private readonly LoggerInterface $graylogLogger,
        private readonly ParameterBagInterface $parameterBag,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'messages',
            'm',
            InputOption::VALUE_OPTIONAL,
            'How many messages worker should consume',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $messagesCount = (int) $input->getOption('messages');

        if (0 === $messagesCount) {
            $messagesCount = $this->parameterBag->get('worker.messages');
        }

        for ($i = 0; $i < $messagesCount; $i++) {
            if ($log = $this->maxwellLogProvider->get()) {
                $this->graylogLogger->info(
                    sprintf(
                        '[%s] %s %s %s %s',
                        date('Y-m-d H:i:s', (int) $log['ts']),
                        $log['database'] ?? null,
                        $log['table'] ?? null,
                        $log['type'] ?? null,
                        $log['primary_key'][0] ?? null,
                    ),
                    $log,
                );
            } else {
                sleep(1);
            }
        }

        sleep(1);

        return 0;
    }
}
