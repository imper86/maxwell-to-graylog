<?php

declare(strict_types=1);

namespace App\Factory;

use Monolog\Formatter\GelfMessageFormatter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use function sprintf;

final class GelfFormatterFactory
{
    public function __construct(private readonly ParameterBagInterface $parameterBag)
    {
    }

    public function __invoke(): GelfMessageFormatter
    {
        return new GelfMessageFormatter(
            sprintf(
                '%s_%s',
                $this->parameterBag->get('kernel.environment'),
                $this->parameterBag->get('app.name'),
            ),
        );
    }
}
