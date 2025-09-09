<?php

namespace wockkinmycup\superscroll;

use pocketmine\plugin\PluginBase;
use wockkinmycup\superscroll\command\GiveSuperScrollCommand;

class Loader extends PluginBase {

    public static Loader $instance;

    public function onLoad(): void
    {
        self::$instance = $this;
    }

    public function onEnable(): void
    {
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents(new SSListener(), $this);
        $this->getServer()->getCommandMap()->register("superscrolls", new GiveSuperScrollCommand($this));
    }

    public static function getInstance() : Loader {
        return self::$instance;
    }
}