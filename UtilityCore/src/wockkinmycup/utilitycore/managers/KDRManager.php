<?php

namespace wockkinmycup\utilitycore\managers;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\player\Player;
use pocketmine\Server;
use wockkinmycup\utilitycore\Loader;
use wockkinmycup\utilitycore\utils\Utils;

class KDRManager
{
    public static array $data;

    public static string $kdrFile;

    public static mixed $kdrData;
    /**
     * @var null
     */
    private $previousLeader;

    public function __construct(string $kdrFilePath) {
        self::$kdrData = json_decode($kdrFilePath, true);
        self::$kdrFile = $kdrFilePath;
        self::$data = $this->loadData();

        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            $playerName = $player->getName();
            if (!isset($this->kdrData[$playerName])) {
                self::$kdrData[$playerName] = [
                    "kills" => 0,
                    "deaths" => 0
                ];
            }
        }
    }

    private function loadData(): array
    {
        $data = [];

        if (file_exists(self::$kdrFile)) {
            $jsonData = file_get_contents(self::$kdrFile);
            if ($jsonData !== false) {
                $loadedData = json_decode($jsonData, true);
                if (is_array($loadedData)) {
                    $data = $loadedData;
                }
            }
        }

        return $data;
    }

    public static function saveData(): void
    {
        file_put_contents(self::$kdrFile, json_encode(self::$data));
    }

    public static function updateKdr(string $playerName, int $kills, int $deaths): void
    {
        if (!isset(self::$data[$playerName])) {
            self::$data[$playerName] = [
                "kills" => 0,
                "deaths" => 0,
                "ratio" => 0
            ];
        }

        self::$data[$playerName]["kills"] += $kills;
        self::$data[$playerName]["deaths"] += $deaths;

        if (self::$data[$playerName]["deaths"] > 0) {
            self::$data[$playerName]["ratio"] = round(self::$data[$playerName]["kills"] / self::$data[$playerName]["deaths"], 2);
        } else {
            self::$data[$playerName]["ratio"] = self::$data[$playerName]["kills"];
        }

        self::saveData();
    }

    public static function getKdr(string $playerName): array {
        if (!isset(self::$data[$playerName])) {
            self::$data[$playerName] = [
                "kills" => 0,
                "deaths" => 0,
                "ratio" => 0
            ];
            self::saveData();
        }

        return self::$data[$playerName];
    }

    public static function resetKdr($player): void
    {
        if (isset(self::$data[$player])) {
            unset(self::$data[$player]);
            self::saveData();
        }
    }

    public static function getTopPlayers($limit = 10): array
    {
        $players = [];

        foreach (self::$data as $name => $kdr) {
            $kdrValue = self::getKdr($name);

            if ($kdrValue !== null) {
                $players[$name] = $kdrValue;
            }
        }

        arsort($players);

        return array_slice($players, 0, $limit, true);
    }

    public static function getRank($player): ?int
    {
        $kdrValue = self::getKdr($player);

        if ($kdrValue === null) {
            return null;
        }

        $rank = 1;

        foreach (self::$data as $playerName => $kdr) {
            $playerKdrValue = self::getKdr($player);

            if ($playerKdrValue !== null && $playerKdrValue > $kdrValue) {
                $rank++;
            }
        }

        return $rank;
    }

    public static function formatKdr($kdrValue): string
    {
        if ($kdrValue === null) {
            return "N/A";
        }

        return number_format($kdrValue, 2);
    }

    public static function getKills(): array {
        $kills = [];

        foreach (self::$data as $playerName => $kdr) {
            $kills[$playerName] = $kdr["kills"];
        }

        return $kills;
    }

    public static function getPlayerData(string $playerName): ?array {
        $jsonFile = self::$kdrFile;
        if (!file_exists($jsonFile)) {
            return null;
        }

        $jsonContents = file_get_contents($jsonFile);
        if ($jsonContents === false) {
            return null;
        }

        $playerData = json_decode($jsonContents, true);
        if ($playerData === null) {
            return null;
        }

        if (!isset($playerData[$playerName])) {
            return null;
        }

        return $playerData[$playerName];
    }

    public static function loadKdrData(): void
    {
        $json = file_get_contents(Utils::getConfiguration(Loader::getInstance(), "kdr")->getPath());

        if ($json !== false) {
            $data = json_decode($json, true);

            foreach ($data as $player => $kdr) {
                $kills = $kdr["kills"];
                $deaths = $kdr["deaths"];
                if (!isset(self::$kdrData[$player])) {
                    self::$kdrData[$player] = [
                        "kills" => 0,
                        "deaths" => 0
                    ];
                }
                self::$kdrData[$player]["kills"] += $kills;
                self::$kdrData[$player]["deaths"] += $deaths;
            }
        }
    }

    public static function saveKdrData(array $kdrData): void
    {
        $kdrJson = json_encode($kdrData, JSON_PRETTY_PRINT);

        if (!file_exists(self::$kdrFile)) {
            mkdir(dirname(self::$kdrFile), 0777, true);
        }

        file_put_contents(self::$kdrFile, $kdrJson);
    }
}