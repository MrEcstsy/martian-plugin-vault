<?php

declare(strict_types=1);

namespace ecstsy\LevelSystem\Player;

use ecstsy\LevelSystem\Loader;
use ecstsy\LevelSystem\Utils\LevelUtils;
use ecstsy\LevelSystem\Utils\Queries;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use Ramsey\Uuid\UuidInterface;

final class LevelPlayer
{


    private bool $isConnected = false;

    public function __construct(
        private UuidInterface $uuid,
        private string        $username,
        private int           $plevel
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
     * @return int
     */
    public function getLevel(): int
    {
        return $this->plevel;
    }

    /**
     * @param int $amount
     * @return void
     */
    public function addLevel(int $amount): void 
    {
        $lvlCfg = LevelUtils::getConfiguration(Loader::getInstance(), "levels.yml");
        $cfg = LevelUtils::getConfiguration(Loader::getInstance(), "config.yml");
        $maxLevel = count($lvlCfg->get("levels"));

        $remeaningAmount = $maxLevel - $this->plevel;
        $amountToAdd = min($amount, $remeaningAmount);

        if ($amountToAdd <= 0) {
            $this->getPocketminePlayer()->sendMessage(TextFormat::colorize($cfg->getNested("messages.max-level")));
            return;
        }

        $this->plevel += $amountToAdd;
        $this->getPocketminePlayer()->sendMessage(TextFormat::colorize(str_replace("{level}", (string)$this->plevel, implode("\n", $cfg->getNested("messages.level-up")))));
        $this->updateDb();
    }

    /**
     * @param int $amount
     * @return void
     */
    public function takeLevel(int $amount): void
    {
        $this->plevel -= $amount;
        $this->updateDb();
    }

    /**
     * @param int $amount
     * @return void
     */
    public function setLevel(int $amount): void
    {
        $this->plevel = $amount;
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
            "plevel" => $this->plevel,
        ]);
    }

}