<?php

declare(strict_types=1);

namespace ecstsy\MonthlyCrates;

use ecstsy\MartianUtilities\managers\LanguageManager;
use ecstsy\MonthlyCrates\commands\MonthlyCrateCommand;
use ecstsy\MonthlyCrates\listeners\CrateListener;
use JackMD\ConfigUpdater\ConfigUpdater;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

final class Loader extends PluginBase {
    use SingletonTrait;

    private static LanguageManager $langManager;

    public function onLoad(): void {
        self::setInstance($this);
    }

    public function onEnable(): void {
        ConfigUpdater::checkUpdate($this, $this->getConfig(), "version", 1);

        $this->saveResource("crates.yml");
        $this->saveAllFilesInDirectory("locale");

        if (!InvMenuHandler::isRegistered()) {
            InvMenuHandler::register($this);
        }

        $language = $this->getConfig()->getNested("settings.language");
        self::$langManager = new LanguageManager($this, $language);

        $this->getServer()->getPluginManager()->registerEvents(new CrateListener(), $this);
        $this->getServer()->getCommandMap()->register("MartianMonthlyCrates", new MonthlyCrateCommand($this, $this->getConfig()->getNested("command.name"), $this->getConfig()->getNested("command.description"), $this->getConfig()->getNested("command.aliases")));
    }

    public static function getLanguageManager(): LanguageManager {
        return self::$langManager;
    }

    private function saveAllFilesInDirectory(string $directory): void {
        $resourcePath = $this->getFile() . "resources/$directory/";
        if (!is_dir($resourcePath)) {
            $this->getLogger()->warning("Directory $directory does not exist.");
            return;
        }

        $files = scandir($resourcePath);
        if ($files === false) {
            $this->getLogger()->warning("Failed to read directory $directory.");
            return;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $this->saveResource("$directory/$file");
        }
    }
}