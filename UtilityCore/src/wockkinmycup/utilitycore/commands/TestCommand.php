<?php

namespace wockkinmycup\utilitycore\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;

class TestCommand extends Command
{

    public function __construct(string $name) {
        parent::__construct($name);
        $this->setPermission("utility." . $name);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::DARK_RED . "You must run this command in-game");
            return false;
        }

        if (isset($args[0])) {
            switch(strtolower($args[0])) {
                case "apple":
                    break;
                default:
                    $sender->sendMessage("Enter a subcommand.");
            }
        }
        return true;
    }
}