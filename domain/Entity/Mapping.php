<?php

declare(strict_types=1);

namespace Domain\Entity;

use CodeIgniter\I18n\Time;
use Webmozart\Assert\Assert;

final class Mapping
{
    public static function getBool(array $data, string $key): bool
    {
        Assert::keyExists($data, $key);
        Assert::integerish($data[$key]);

        return (bool) $data[$key];
    }

    public static function getId(array $data, string $key): int
    {
        Assert::keyExists($data, $key);
        Assert::integerish($data[$key]);

        $value = (int) $data[$key];
        Assert::positiveInteger($value);

        return $value;
    }

    public static function getInt(array $data, string $key): int
    {
        Assert::keyExists($data, $key);
        Assert::integer($data[$key]);

        return $data[$key];
    }

    public static function getString(array $data, string $key): string
    {
        Assert::keyExists($data, $key);
        Assert::string($data[$key]);

        return $data[$key];
    }

    public static function getTimestamp(array $data, string $key): Time
    {
        $value = Time::createFromFormat('!Y-m-d H:i:s', self::getString($data, $key));
        Assert::isInstanceOf($value, Time::class);

        return $value;
    }
}
