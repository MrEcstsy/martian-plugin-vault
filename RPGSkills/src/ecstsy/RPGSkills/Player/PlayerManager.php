<?php

declare(strict_types=1);

namespace ecstsy\RPGSkills\Player;

use ecstsy\RPGSkills\Loader;
use ecstsy\RPGSkills\Utils\Queries;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class PlayerManager
{
    use SingletonTrait;

    /** @var RPGPlayer[] */
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
                $this->sessions[$row["uuid"]] = new RPGPlayer(
                    Uuid::fromString($row["uuid"]),
                    $row["username"],
                    $row["mining_level"],
                    $row["attack_level"],
                    $row["farming_level"],
                    $row["gathering_level"],
                    $row["defense_level"],
                    $row["magic_level"],
                    $row["building_level"],
                    $row["agility_level"]
                );
            }
        });
    }

    /**
     * Create a session
     *
     * @param Player $player
     * @return RPGPlayer
     * @throws \JsonException
     */
    public function createSession(Player $player): RPGPlayer
    {
        $args = [
            "uuid" => $player->getUniqueId()->toString(),
            "username" => $player->getName(),
            "mining_level" => 0,
            "attack_level" => 0,
            "farming_level" => 0,
            "gathering_level" => 0,
            "defense_level" => 0,
            "magic_level" => 0,
            "building_level" => 0,
            "agility_level" => 0
        ];

        Loader::getDatabase()->executeInsert(Queries::PLAYERS_CREATE, $args);

        $this->sessions[$player->getUniqueId()->toString()] = new RPGPlayer(
            $player->getUniqueId(),
            $args["username"],
            $args["mining_level"],
            $args["attack_level"],
            $args["farming_level"],
            $args["gathering_level"],
            $args["defense_level"],
            $args["magic_level"],
            $args["building_level"],
            $args["agility_level"]
        );
        return $this->sessions[$player->getUniqueId()->toString()];
    }

    /**
     * Get session by player object
     *
     * @param Player $player
     * @return RPGPlayer|null
     */
    public function getSession(Player $player) : ?RPGPlayer
    {
        return $this->getSessionByUuid($player->getUniqueId());
    }

    /**
     * Get session by player name
     *
     * @param string $name
     * @return RPGPlayer|null
     */
    public function getSessionByName(string $name) : ?RPGPlayer
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
     * @return RPGPlayer|null
     */
    public function getSessionByUuid(UuidInterface $uuid) : ?RPGPlayer
    {
        return $this->sessions[$uuid->toString()] ?? null;
    }

    public function destroySession(RPGPlayer $session) : void
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