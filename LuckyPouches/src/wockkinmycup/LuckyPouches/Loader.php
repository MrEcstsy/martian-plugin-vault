<?php

namespace wockkinmycup\LuckyPouches;

use pocketmine\plugin\PluginBase;
use wockkinmycup\LuckyPouches\commands\PouchCommand;
use wockkinmycup\LuckyPouches\listeners\PouchesListener;

class Loader extends PluginBase {

    public static Loader $instance;

    public function onLoad(): void
    {
        self::$instance = $this;
    }

    public function onEnable(): void
    {
        $this->saveDefaultConfig();
        $this->saveResource("messages.yml");
        $this->getServer()->getCommandMap()->register("luckypouches", new PouchCommand($this));
        $this->getServer()->getPluginManager()->registerEvents(new PouchesListener(), $this);
    }

    public static function getInstance() : Loader {
        return self::$instance;
    }
}
