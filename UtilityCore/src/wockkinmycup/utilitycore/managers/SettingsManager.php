<?php

namespace wockkinmycup\utilitycore\utils;

use pocketmine\utils\Config;

class SettingsManager {

    private Config $config;
    private array $settings;

    public function __construct(Config $config) {
        $this->config = $config;
        $this->settings = $this->config->getAll();
    }

    public function getSetting(string $playerName, string $settingName, $defaultValue = null) {
        return $this->settings[$playerName][$settingName] ?? $defaultValue;
    }

    /**
     * @throws \JsonException
     */
    public function setSetting(string $playerName, string $settingName, $value): void {
        $this->settings[$playerName][$settingName] = $value;
        $this->saveSettings();
    }

    public function hasPlayerData(string $playerName): bool {
        return isset($this->settings[$playerName]);
    }

    /**
     * @throws \JsonException
     */
    public function createPlayerData(string $playerName, array $defaultSettings = []): void {
        if (!$this->hasPlayerData($playerName)) {
            $this->settings[$playerName] = $defaultSettings;
            $this->saveSettings();
        }
    }

    /**
     * @throws \JsonException
     */
    private function saveSettings(): void {
        unset($this->settings["players"]);

        $this->config->setAll($this->settings);
        $this->config->save();
    }
}
