<?php

namespace wockkinmycup\utilitycore\commands\subcommands;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;
use pocketmine\player\Player;
use wockkinmycup\utilitycore\utils\Utils;
use wockkinmycup\utilitycore\Loader;

class setExpSubCommand extends BaseSubCommand
{

    /**
     * @throws ArgumentOrderException
     */
    public function prepare(): void
    {
        $this->setPermission("utility.admin");
        $this->registerArgument(0, new RawStringArgument("player", false));
        $this->registerArgument(1, new IntegerArgument("amount", false));
        $this->setUsage(C::colorize(Utils::getConfiguration(Loader::getInstance(), "messages.yml")->getNested("xp.invalid_command", "&cUsage: /xp [add|remove|set|show] <player> <amount>")));

    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $config = Utils::getConfiguration(Loader::getInstance(), "messages.yml");
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::colorize($config->getNested("xp.in_game_only", "&cThis command must be used in-game.")));
            return;
        }

        if (!$sender->hasPermission('utility.admin')) {
            $message = $config->getNested("xp.no_permission", "&cYou do not have permission to use this command.");
            $sender->sendMessage(C::colorize($message));
            return;
        }

        $player = Utils::getPlayerByPrefix($args["player"]);
        if (!$player) {
            $message = $config->getNested("xp.player_not_found", "&cPlayer not found.");
            $sender->sendMessage(C::colorize($message));
            return;
        }
        $amount = (int) $args["amount"];
        if ($amount < 0) {
            $message = $config->getNested("xp.invalid_amount", "&cAmount must be a non-negative integer.");
            $sender->sendMessage(C::colorize($message));
            return;
        }
        $player->getXpManager()->setCurrentTotalXp($amount);
        $message = $config->getNested("xp.set_success", "&aSet {player}'s XP to {amount}.");
        Utils::sendUpdate($player);
        $sender->sendMessage(C::colorize(str_replace(["{player}", "{amount}"], [$player->getName(), number_format($amount)], $message)));
    }
}