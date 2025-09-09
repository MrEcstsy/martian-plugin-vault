<?php

namespace wockkinmycup\utilitycore\commands\subcommands;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;
use pocketmine\player\Player;
use pocketmine\world\sound\XpCollectSound;
use wockkinmycup\utilitycore\utils\Utils;
use wockkinmycup\utilitycore\Loader;

class addExpSubCommand extends BaseSubCommand
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
        $player = Utils::getPlayerByPrefix($args["player"]);
        $amount = (int) $args["amount"];

        if (!$sender instanceof Player) {
            $sender->sendMessage(C::colorize(""));
            return;
        }

        if (!$sender->hasPermission('utility.admin')) {
            $message = $config->getNested("xp.no_permission", "&cYou do not have permission to use this command.");
            $sender->sendMessage(C::colorize($message));
            return;
        }

        if (!$player) {
            $message = $config->getNested("xp.player_not_found", "&cPlayer not found.");
            $sender->sendMessage(C::colorize($message));
            return;
        }

        if ($amount <= 0) {
            $message = $config->getNested("xp.invalid_amount", "&cAmount must be a positive integer.");
            $sender->sendMessage(C::colorize($message));
            return;
        }
        $player->getXpManager()->addXp($amount);
        $newXp = $player->getXpManager()->getCurrentTotalXp();
        $message = $config->getNested("xp.add_success", "&aAdded {amount} XP to {player}. Their new XP is {new_xp}.");
        Utils::sendUpdate($player);
        $sender->getWorld()->addSound($sender->getPosition(), new XpCollectSound());
        $sender->sendMessage(C::colorize(str_replace(["{amount}", "{player}", "{new_xp}"], [number_format($amount), $player->getName(), number_format($newXp)], $message)));
    }
}