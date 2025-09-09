<?php

namespace ecstsy\RPGSkills\Commands;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use ecstsy\RPGSkills\Loader;
use ecstsy\RPGSkills\Utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class SetLevelCommand extends BaseCommand {

    private array $skillMethods = [
        "mining" => "setMiningLevel",
        "attack" => "setAttackLevel",
        "combat" => "setAttackLevel", // Same as attack, alias
        "farming" => "setFarmingLevel",
        "gathering" => "setGatheringLevel",
        "defense" => "setDefenseLevel",
        "magic" => "setMagicLevel",
        "building" => "setBuildingLevel",
        "agility" => "setAgilityLevel"
    ];


    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerArgument(0, new RawStringArgument("name", false));
        $this->registerArgument(1, new RawStringArgument("skill", false));
        $this->registerArgument(2, new IntegerArgument("level", false));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $playerName = $args["name"] ?? null;
        $skill = strtolower($args["skill"] ?? null);
        $level = $args["level"] ?? null;

        $player = $playerName !== null ? Utils::getPlayerByPrefix($playerName) : null;

        if ($player instanceof Player && $skill !== null && $level !== null) {
            $session = Loader::getPlayerManager()->getSession($player);

            if ($session !== null) {
                if (array_key_exists($skill, $this->skillMethods)) {
                    $method = $this->skillMethods[$skill];

                    if (method_exists($session, $method)) {
                        $session->{$method}($level);

                        $sender->sendMessage("Successfully set {$skill} level to {$level} for {$player->getName()}.");
                    } else {
                        $sender->sendMessage("Error: Method {$method} does not exist.");
                    }
                } else {
                    $sender->sendMessage("Error: Invalid skill name. Valid skills: " . implode(", ", array_keys($this->skillMethods)));
                }
            } else {
                $sender->sendMessage("Error: Player session not found.");
            }
        } else {
            $sender->sendMessage("Error: Invalid player name, skill, or level provided.");
        }
    }

    public function getPermission(): string
    {
        return "rpgskills.setlevel";
    }
}