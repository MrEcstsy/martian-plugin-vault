<?php

declare(strict_types=1);

namespace ecstsy\OreWatch\Player;

use ecstsy\OreWatch\Loader;
use ecstsy\OreWatch\Utils\Queries;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use Ramsey\Uuid\UuidInterface;

final class OreWatchPlayer
{


    private bool $isConnected = false;

    public function __construct(
        private UuidInterface $uuid,
        private string        $username,
        private int           $notify
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
     * Get the notification status of the player
     *
     * @return bool
     */
    public function getNotify(): bool
    {
        return $this->notify === 1;
    }

    /**
     * Set the notification status of the player
     *
     * @param bool $notify
     * @return void
     */
    public function setNotify(bool $notify): void
    {
        $this->notify = $notify ? 1 : 0;
        $this->updateDb();
    }

    /**
     * Toggle the notification status of the player
     *
     * @return void
     */
    public function toggleNotify(): void
    {
        $this->notify = !$this->getNotify() ? 1 : 0;
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
            "notify" => $this->notify,
        ]);
    }

}