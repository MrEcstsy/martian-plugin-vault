<?php

namespace wockkinmycup\utilitycore;

use pocketmine\plugin\PluginBase;
use wockkinmycup\utilitycore\commands\SettingsCommand;
use wockkinmycup\utilitycore\commands\TestCommand;
use wockkinmycup\utilitycore\listeners\EventListener;
use wockkinmycup\utilitycore\utils\SettingsManager;
use wockkinmycup\utilitycore\utils\Utils;
use WolfDen133\ServerSettings\API;

class Loader extends PluginBase {

    public static Loader $instance;

    public function onLoad(): void
    {
        self::$instance = $this;
    }

    public function onEnable(): void
    {
        $files = ["customarmor.yml", "messages.yml", "pets.yml"];
        foreach ($files as $file) {
            $this->saveResource($file);
        }
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        API::register($this);
    }

    public static function getInstance() : Loader {
        return self::$instance;
    }

    public function registerCommands() {
        $this->getServer()->getCommandMap()->registerAll("utilitycore", [
            new SettingsCommand(new SettingsManager(Utils::getConfiguration($this, "settings"))),
            new TestCommand("testcommand"),
        ]);
    }
}