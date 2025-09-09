<?php

namespace ecstsy\MartianUtilities\managers;

use ecstsy\MartianUtilities\utils\GeneralUtils;
use pocketmine\plugin\Plugin;
use pocketmine\utils\Config;

final class LanguageManager {

    private string $filePath;
    private Config $config;

    public function __construct(Plugin $plugin, string $languageKey)
    {
        $pluginData = $plugin->getDataFolder();
        $localeDir = $pluginData . '/locale/';

        $this->filePath = $localeDir . $languageKey . '.yml';

        if (!file_exists($this->filePath)) {
            throw new \RuntimeException("Language file not found for language key '$languageKey' at: " . $this->filePath);
        }

        $this->config = GeneralUtils::getConfiguration($plugin, 'locale/' . $languageKey . '.yml');
    }

    public function get(string $key): string {
        return $this->config->get($key, "Translation not found: " . $key);
    }

    public function getNested(string $key): mixed {
        return $this->config->getNested($key, "Translation not found: " . $key);
    }
    
    public function reload(): void {
        $this->config->reload();
    }

    public function getAll(): array {
        return $this->config->getAll();
    }
    
    public function getFilePath(): string {
        return $this->filePath;
    }
}
