<?php

namespace xtcy\bn\command;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;
use xtcy\bn\command\subcommands\GiveSubCommand;
use xtcy\bn\Loader;
use xtcy\bn\utils\libs\CortexPE\Commando\BaseCommand;
use xtcy\bn\utils\Utils;

class NoteCommand extends BaseCommand
{

    protected function prepare(): void
    {
        $this->registerSubCommand(new GiveSubCommand(Loader::getInstance(), "give", "give a note to a player"));
        $this->setPermission("bloodynotes.command");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $config = Utils::getConfiguration(Loader::getInstance(), "messages.yml");
        $messages = $config->getNested("help", []);
        foreach ($messages as $message) {
            $sender->sendMessage(C::colorize($message));
        }
    }
}