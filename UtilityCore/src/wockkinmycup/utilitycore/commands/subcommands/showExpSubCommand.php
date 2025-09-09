<?php

namespace wockkinmycup\utilitycore\commands\subcommands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\utils\TextFormat as C;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use wockkinmycup\utilitycore\utils\Utils;
use wockkinmycup\utilitycore\Loader;

class showExpSubCommand extends BaseSubCommand
{

    /**
     * @throws ArgumentOrderException
     */
    public function prepare(): void
    {
        $this->setPermission("utility.admin");
        $this->registerArgument(0, new RawStringArgument("player", true));
        $this->setUsage(C::colorize(Utils::getConfiguration(Loader::getInstance(), "messages.yml")->getNested("xp.invalid_command", "&cUsage: /xp [add|remove|set|show] <player> <amount>")));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $config = Utils::getConfiguration(Loader::getInstance(), "messages.yml");
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::colorize($config->getNested("xp.in_game_only", "&cThis command must be used in-game.")));
            return;
        }

        if (!$sender->hasPermission('utility.exp.see')) {
            $message = $config->getNested("xp.no_permission", "&cYou do not have permission to use this command.");
            $sender->sendMessage(C::colorize($message));
            return;
        }

        $player = Utils::getPlayerByPrefix($args["player"]);
        if ($player === null) {
            $message = $config->getNested("xp.player_not_found", "&cPlayer not found.");
            $sender->sendMessage(C::colorize($message));
            return;
        }

        $xp = number_format($player->getXpManager()->getCurrentTotalXp(), 1);
        $level = $player->getXpManager()->getXpLevel();
        $levelup = Utils::getExpToLevelUp($player->getXpManager()->getCurrentTotalXp());
        $message = $config->getNested("xp.show_info", "{player} §r§6has §r§c{xp} EXP §r§6(level §r§c{level}§r§6) §r§6and needs {levelup} more exp to level up.");
        $sender->sendMessage(C::colorize(str_replace(["{player}", "{xp}", "{level}", "{levelup}"], [$player->getNameTag(), number_format($xp), showExpSubCommand . phpnumber_format($level)], $message)));
    }
}
