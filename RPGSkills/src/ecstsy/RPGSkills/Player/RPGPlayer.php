<?php

declare(strict_types=1);

namespace ecstsy\RPGSkills\Player;

use ecstsy\RPGSkills\Loader;
use ecstsy\RPGSkills\Utils\Queries;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use Ramsey\Uuid\UuidInterface;

final class RPGPlayer
{


    private bool $isConnected = false;

    public function __construct(
        private UuidInterface $uuid,
        private string        $username,
        private int           $mining_level,
        private int           $attack_level,
        private int           $farming_level,
        private int           $gathering_level,
        private int           $defense_level,
        private int           $magic_level,
        private int           $building_level,
        private int           $agility_level
    )
    {
    }

    public function isConnected(): bool
    {
        return $this->isConnected;
    }

    public function setConnected(bool $connected): void
    {
        $this->isConnected = $connected;
    }

    /**
     * Get UUID of the player
     *
     * @return UuidInterface
     */
    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    /**
     * This function gets the PocketMine player
     *
     * @return Player|null
     */
    public function getPocketminePlayer(): ?Player
    {
        return Server::getInstance()->getPlayerByUUID($this->uuid);
    }

    /**
     * Get username of the session
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Set username of the session
     *
     * @param string $username
     * @return void
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
        $this->updateDb(); // Make sure to call updateDb function when you're making changes to the player data
    }

    /**
     * Get the player's Mining Level
     * 
     * @return string
     */
    public function getMiningLevel(): int
    {
        return $this->mining_level;
    }


    /**
     * Remove player's Mining Level
     * 
     * @return void
     */
    public function removeMiningLevel(int $level): void
    {
        $this->mining_level -= $level;
        $this->updateDb();
    }

    /**
     * @return void
     */
    public function setMiningLevel(int $level): void
    {
        $this->mining_level = $level;
        $this->updateDb();
    }

    /**
     * @return void
     */
    public function addMiningLevel(int $level): void {
        $this->mining_level += $level;
        $this->updateDb();
    }

    /**
     * Get the player's Attack Level
     */
    public function getAttackLevel(): int
    {
        return $this->attack_level;
    }


    /**
     * Remove player's Attack Level
     */
    public function removeAttackLevel(int $level): void
    {
        $this->attack_level -= $level;
        $this->updateDb();
    }


    /**
     * @return void 
     */
    public function setAttackLevel(int $level): void
    {
        $this->attack_level = $level;
        $this->updateDb();
    }

    /**
     * @return void
     */
    public function addAttackLevel(int $level): void
    {
        $this->attack_level += $level;
        $this->updateDb();
    }

    /**
     * Get the player's Farming Level
     */
    public function getFarmingLevel(): int
    {
        return $this->farming_level;
    }


    /**
     * Remove player's Farming Level
     */
    public function removeFarmingLevel(int $level): void
    {
        $this->farming_level -= $level;
        $this->updateDb();
    }

    /**
     * @return void
     */
    public function setFarmingLevel(int $level): void
    {
        $this->farming_level = $level;
        $this->updateDb();
    }   

    /**
     * @return void
     */
    public function addFarmingLevel(int $level): void
    {
        $this->farming_level += $level;
        $this->updateDb();
    }

    /**
     * Get the player's Gathering Level
     */
    public function getGatheringLevel(): int
    {
        return $this->gathering_level;
    }

    /**
     * Remove player's Gathering Level
     */
    public function removeGatheringLevel(int $level): void
    {
        $this->gathering_level -= $level;
        $this->updateDb();
    }

    /**
     * @return void
     */
    public function setGahteringLevel(int $level): void
    {
        $this->gathering_level = $level;
        $this->updateDb();
    }   

    /**
     * @return void
     */
    public function addGatheringLevel(int $level): void
    {
        $this->gathering_level += $level;
        $this->updateDb();
    }

    /**
     * Get the player's Defense Level
     */
    public function getDefenseLevel(): int
    {
        return $this->defense_level;
    }


    /**
     * Remove player's Defense Level
     */
    public function removeDefenseLevel(int $level): void
    {
        $this->defense_level -= $level;
        $this->updateDb();
    }

    /**
     * @return void
     */
    public function setDefenseLevel(int $level): void
    {
        $this->defense_level = $level;
        $this->updateDb();
    }   

    /**
     * @return void
     */
    public function addDefenseLevel(int $level): void
    {
        $this->defense_level += $level;
        $this->updateDb();
    }

    /**
     * Get the player's Magic Level
     */
    public function getMagicLevel(): int
    {
        return $this->magic_level;
    }

    /**
     * Remove player's Magic Level
     */
    public function removeMagicLevel(int $level): void
    {
        $this->magic_level -= $level;
        $this->updateDb();
    }

    /**
     * @return void
     */
    public function setMagicLevel(int $level): void
    {
        $this->magic_level = $level;
        $this->updateDb();
    }   

    /**
     * @return void
     */
    public function addMagicLevel(int $level): void
    {
        $this->magic_level += $level;
        $this->updateDb();
    }

    /**
     * Get the player's Building Level
     */
    public function getBuildingLevel(): int
    {
        return $this->building_level;
    }

    /**
     * Remove player's Building Level
     */
    public function removeBuildingLevel(int $level): void
    {
        $this->building_level -= $level;
        $this->updateDb();
    }

    /**
     * @return void
     */
    public function setBuildingLevel(int $level): void
    {
        $this->building_level = $level;
        $this->updateDb();
    }   

    /**
     * @return void
     */
    public function addBuildingLevel(int $level): void
    {
        $this->building_level += $level;
        $this->updateDb();
    }

    /**
     * Get the player's Agility Level
     */
    public function getAgilityLevel(): int
    {
        return $this->agility_level;
    }

    /**
     * Remove player's Agility Level
     */
    public function removeAgilityLevel(int $level): void
    {
        $this->agility_level -= $level;
        $this->updateDb();
    }

    /**
     * @return void
     */
    public function setAgilityLevel(int $level): void
    {
        $this->agility_level = $level;
        $this->updateDb();
    }   

    /**
     * @return void
     */
    public function addAgilityLevel(int $level): void
    {
        $this->agility_level += $level;
        $this->updateDb();
    }

    /**
     * Update player information in the database
     *
     * @return void
     */
    private function updateDb(): void
    {

        Loader::getDatabase()->executeChange(Queries::PLAYERS_UPDATE, [
            "uuid" => $this->uuid->toString(),
            "username" => $this->username,
            "mining_level" => $this->mining_level,
            "attack_level" => $this->attack_level,
            "farming_level" => $this->farming_level,
            "gathering_level" => $this->gathering_level,
            "defense_level" => $this->defense_level,
            "magic_level" => $this->magic_level,
            "building_level" => $this->building_level,
            "agility_level" => $this->agility_level
        ]);
    }

}