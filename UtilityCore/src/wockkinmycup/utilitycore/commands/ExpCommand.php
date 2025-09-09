<?php

namespace wockkinmycup\utilitycore\commands;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;
use pocketmine\player\Player;
use wockkinmycup\utilitycore\commands\subcommands\addExpSubCommand;
use wockkinmycup\utilitycore\commands\subcommands\removeExpSubCommand;
use wockkinmycup\utilitycore\commands\subcommands\setExpSubCommand;
use wockkinmycup\utilitycore\commands\subcommands\showExpSubCommand;
use wockkinmycup\utilitycore\Loader;
use wockkinmycup\utilitycore\utils\Utils;

class ExpCommand extends BaseCommand {

    public function prepare(): void
    {
        $this->setPermission("utility.exp");
        $this->registerSubCommand(new addExpSubCommand(Loader::getInstance(), "add", "add exp to a player", ["give", "insert"]));
        $this->registerSubCommand(new removeExpSubCommand(Loader::getInstance(), "remove", "remove exp from a player", ["take", "deduct", "subtract"]));
        $this->registerSubCommand(new setExpSubCommand(Loader::getInstance(), "set", "set the exp of a player"));
        $this->registerSubCommand(new showExpSubCommand(Loader::getInstance(), "show", "show the exp of a player", ["get", "view"]));
        $this->setUsage(C::colorize(Utils::getConfiguration(Loader::getInstance(), "messages.yml")->getNested("xp.invalid_command", "&cUsage: /xp [add|remove|set|show] <player> <amount>")));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $config = Utils::getConfiguration(Loader::getInstance(), "messages.yml");
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::colorize($config->getNested("xp.in_game_only", "&cThis command must be used in-game.")));
            return;
        }
        $exp = number_format($sender->getXpManager()->getCurrentTotalXp(), 1);
        $level = $sender->getXpManager()->getXpLevel();
        $levelup = Utils::getExpToLevelUp($sender->getXpManager()->getCurrentTotalXp());
        $message = $config->getNested("xp.self_info", "{player} §r§6has §r§c{exp} EXP §r§6(level §r§c{level}§r§6) §r§6and needs {levelup} more exp to level up.");
        $sender->sendMessage(C::colorize(str_replace(["{player}", "{exp}", "{level}", "{levelup}"], [$sender->getNameTag(), $exp, $level, number_format($levelup)], $message)));
    }
}