<?php

declare(strict_types=1);

namespace ecstsy\MonthlyCrates\commands;

use CortexPE\Commando\BaseCommand;
use ecstsy\MonthlyCrates\commands\subcommands\GiveSubCommand;
use ecstsy\MonthlyCrates\commands\subcommands\ReloadSubCommand;
use ecstsy\MonthlyCrates\Loader;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;

final class MonthlyCrateCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerSubCommand(new GiveSubCommand(Loader::getInstance(), "give", "Give player a monthly crate"));
        $this->registerSubCommand(new ReloadSubCommand(Loader::getInstance(), "reload", "Reload all configuration files"));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $language = Loader::getLanguageManager();

        foreach ($language->getNested("command.main", []) as $mainMessage) {
            $sender->sendMessage(C::colorize($mainMessage));
        }
    }

    public function getPermission(): string {
        return "martian_monthly_crates.command";
    }
}