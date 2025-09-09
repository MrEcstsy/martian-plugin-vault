<?php

namespace wockkinmycup\enchantfragments;

use pocketmine\plugin\PluginBase;

class Loader extends PluginBase {

    public static Loader $instance;

    public function onLoad(): void
    {
        self::$instance = $this;
    }

    public function onEnable(): void
    {
        $this->saveDefaultConfig();
    }

    public static function getInstance() : Loader {
        return self::$instance;
    }
}