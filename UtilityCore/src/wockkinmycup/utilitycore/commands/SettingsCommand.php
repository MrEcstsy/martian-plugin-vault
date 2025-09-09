<?php

namespace wockkinmycup\utilitycore\commands;

use JsonException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use wockkinmycup\utilitycore\Loader;
use wockkinmycup\utilitycore\utils\SettingsManager;
use wockkinmycup\utilitycore\utils\Utils;

class SettingsCommand extends Command
{

    public SettingsManager $settingsManager;

    public function __construct(SettingsManager $settingsManager)
    {
        parent::__construct("settings", "Modify your server settings to make the experience more enjoyable.", "/settings <setting> <value>");
        $this->setPermission("utility.settings");
        $this->settingsManager = $settingsManager;
    }

    /**
     * @throws JsonException
     * @throws \JsonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::colorize(Utils::getConfiguration(Loader::getInstance(), "messages")->getNested("settings.in-game-only")));
            return false;
        }

        if (count($args) === 0) {
            $sender->sendMessage(TextFormat::colorize(Utils::getConfiguration(Loader::getInstance(), "messages")->getNested("settings.invalid-usage")));
            return false;
        }

        $subCommand = strtolower($args[0]);
        $senderName = $sender->getName();

        if ($subCommand === "list") {
            $this->listSettings($sender);
            return true;
        }

        if ($subCommand === "chestguis") {
            if (count($args) === 1) {
                $this->swapChestGUISetting($sender);
                return true;
            } elseif (count($args) === 2) {
                $value = strtolower($args[1]);
                $this->setChestGUISetting($senderName, $value);
                return true;
            }
        }

        if ($subCommand === "announcements") {
            if (count($args) === 1) {
                $this->swapAnnouncementSetting($sender);
                return true;
            } elseif (count($args) === 2) {
                $value = strtolower($args[1]);
                $this->setAnnouncementSetting($senderName, $value);
                return true;
            }
        }

        $sender->sendMessage(TextFormat::colorize(Utils::getConfiguration(Loader::getInstance(), "messages")->getNested("settings.invalid-subcommand")));
        return true;
    }

    /**
     * @throws \JsonException
     */
    private function swapChestGUISetting(Player $player): void
    {
        $settingsManager = $this->getSettingsManager();
        $currentValue = $settingsManager->getSetting($player->getName(), "chestguis");
        $newValue = !$currentValue;
        $settingsManager->setSetting($player->getName(), "chestguis", $newValue);
        $player->sendMessage(TextFormat::colorize(SettingsCommand . phpUtils::getConfiguration(Loader::getInstance(), "messages")->getNested("settings.chestguis-setting-changed")));
    }

    private function swapAnnouncementSetting(Player $player): void
    {
        $settingsManager = $this->getSettingsManager();
        $currentValue = $settingsManager->getSetting($player->getName(), "announcement");
        $newValue = !$currentValue;
        $settingsManager->setSetting($player->getName(), "announcement", $newValue);
        $player->sendMessage(TextFormat::colorize(SettingsCommand . phpUtils::getConfiguration(Loader::getInstance(), "messages")->getNested("settings.announcements-setting-changed")));
    }

    /**
     * @throws \JsonException
     */
    private function setAnnouncementSetting(string $player, string $value): void
    {
        if ($value === "true") {
            $newValue = true;
        } elseif ($value === "false") {
            $newValue = false;
        } else {
            if ($player instanceof Player) {
                $player->sendMessage(TextFormat::colorize(Utils::getConfiguration(Loader::getInstance(), "messages")->getNested("settings.invalid-value", "invalid value, use true or false for settings.")));
                return;
            }
        }

        if ($player instanceof Player) {
            $settingsManager = $this->getSettingsManager();
            $settingsManager->setSetting($player->getName(), "announcements", $newValue);
            $player->sendMessage(TextFormat::colorize(SettingsCommand . phpUtils::getConfiguration(Loader::getInstance(), "messages")->getNested("settings.announcements-setting-changed", "Announcements setting is now ")));
        }
    }

    /**
     * @throws \JsonException
     */
    private function setChestGUISetting(string $player, string $value): void
    {
        if ($value === "true") {
            $newValue = true;
        } elseif ($value === "false") {
            $newValue = false;
        } else {
            if ($player instanceof Player) {
                $player->sendMessage(TextFormat::colorize(Utils::getConfiguration(Loader::getInstance(), "messages")->getNested("settings.invalid-value")));
                return;
            }
        }

        if ($player instanceof Player) {
            $settingsManager = $this->getSettingsManager();
            $settingsManager->setSetting($player, "chestguis", $newValue);
            $player->sendMessage(TextFormat::colorize(SettingsCommand . phpUtils::getConfiguration(Loader::getInstance(), "messages")->getNested("settings.chestguis-setting-changed")));
        }
    }

    public function listSettings(Player $player): void
    {
        $player->sendMessage(TextFormat::colorize(Utils::getConfiguration(Loader::getInstance(), "messages")->getNested("settings.available-settings")));

        $availableSettingsList = Utils::getConfiguration(Loader::getInstance(), "messages")->getNested("settings.available-settings-list", ["- chestguis"]);
        foreach ($availableSettingsList as $setting) {
            $player->sendMessage(TextFormat::colorize($setting));
        }
    }

    public function getSettingsManager(): SettingsManager
    {
        return $this->settingsManager;
    }
}