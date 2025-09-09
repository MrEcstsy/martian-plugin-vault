<?php

declare(strict_types=1);

namespace ecstsy\OreWatch\Player;

use ecstsy\OreWatch\Loader;
use ecstsy\OreWatch\Utils\Queries;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class PlayerManager
{
    use SingletonTrait;

    /** @var OreWatchPlayer[] */
    private array $sessions; // array to fetch player data

    public function __construct(
        public Loader $plugin
    ){
        self::setInstance($this);

        $this->loadSessions();
    }

    /**
     * Store all player data in $sessions property
     *
     * @return void
     */
    private function loadSessions(): void
    {
        Loader::getDatabase()->executeSelect(Queries::PLAYERS_SELECT, [], function (array $rows): void {
            foreach ($rows as $row) {
                $this->sessions[$row["uuid"]] = new OreWatchPlayer(
                    Uuid::fromString($row["uuid"]),
                    $row["username"],
                    $row["notify"],
                );
            }
        });
    }

    /**
     * Create a session
     *
     * @param Player $player
     * @return OreWatchPlayer
     * @throws \JsonException
     */
    public function createSession(Player $player): OreWatchPlayer
    {
        $args = [
            "uuid" => $player->getUniqueId()->toString(),
            "username" => $player->getName(),
            "notify" => 0,
        ];

        Loader::getDatabase()->executeInsert(Queries::PLAYERS_CREATE, $args);

        $this->sessions[$player->getUniqueId()->toString()] = new OreWatchPlayer(
            $player->getUniqueId(),
            $args["username"],
            $args["notify"],
        );
        return $this->sessions[$player->getUniqueId()->toString()];
    }

    /**
     * Get session by player object
     *
     * @param Player $player
     * @return OreWatchPlayer|null
     */
    public function getSession(Player $player) : ?OreWatchPlayer
    {
        return $this->getSessionByUuid($player->getUniqueId());
    }

    /**
     * Get session by player name
     *
     * @param string $name
     * @return OreWatchPlayer|null
     */
    public function getSessionByName(string $name) : ?OreWatchPlayer
    {
        foreach ($this->sessions as $session) {
            if (strtolower($session->getUsername()) === strtolower($name)) {
                return $session;
            }
        }
        return null;
    }

    /**
     * Get session by UuidInterface
     *
     * @param UuidInterface $uuid
     * @return OreWatchPlayer|null
     */
    public function getSessionByUuid(UuidInterface $uuid) : ?OreWatchPlayer
    {
        return $this->sessions[$uuid->toString()] ?? null;
    }

    public function destroySession(OreWatchPlayer $session) : void
    {
        Loader::getDatabase()->executeChange(Queries::PLAYERS_DELETE, ["uuid", $session->getUuid()->toString()]);

        # Remove session from the array
        unset($this->sessions[$session->getUuid()->toString()]);
    }

    public function getSessions() : array
    {
        return $this->sessions;
    }

}