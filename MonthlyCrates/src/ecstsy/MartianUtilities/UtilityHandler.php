<?php

declare(strict_types=1);

namespace ecstsy\MartianUtilities;

use InvalidArgumentException;
use pocketmine\plugin\Plugin;

final class UtilityHandler {

    private static ?Plugin $plugin = null;

    public static function register(Plugin $plugin): void {
        if (self::isRegistered()) {
            throw new InvalidArgumentException("{$plugin->getName()} attempted to registe " . self::class . " twice.");
        }

        self::$plugin = $plugin;
    }

    public static function isRegistered(): bool {
        return self::$plugin instanceof Plugin;
    }
}
