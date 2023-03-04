<?php

declare(strict_types=1);

namespace App\Provider;

use Iterator;
use JsonException;
use Redis;
use RedisException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use function is_string;
use function json_decode;

use const JSON_THROW_ON_ERROR;

final class MaxwellLogProvider
{
    public function __construct(
        private readonly Redis $redis,
        private readonly ParameterBagInterface $parameterBag,
    ) {
    }

    /**
     * @throws JsonException
     * @throws RedisException
     */
    public function get(): ?array
    {
        $row = $this->redis->rPop($this->parameterBag->get('redis_key'));

        if (is_string($row)) {
            return json_decode($row, true, 512, JSON_THROW_ON_ERROR);
        }

        return null;
    }
}
