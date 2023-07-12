<?php
declare(strict_types=1);
namespace SebastianBergmann\GlobalState\TestFixture;
class BlacklistedImplementor implements BlacklistedInterface
{
    private static $attribute;
}
