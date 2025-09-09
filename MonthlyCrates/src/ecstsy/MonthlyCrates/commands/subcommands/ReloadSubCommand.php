<?php 

declare(strict_types=1);

namespace ecstsy\MonthlyCrates\commands\subcommands;

use CortexPE\Commando\BaseSubCommand;
use ecstsy\MartianUtilities\utils\GeneralUtils;
use ecstsy\MonthlyCrates\Loader;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;

final class ReloadSubCommand extends BaseSubCommand {

    private const FILES = ["config.yml", "crates.yml", "locale/en-us.yml"];

    public function prepare(): void {
        $this->setPermission($this->getPermission());
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $language = Loader::getLanguageManager();
        
        foreach (self::FILES as $file) {
            $config = GeneralUtils::getConfiguration(Loader::getInstance(), $file);
            $config->reload();
        }

        $sender->sendMessage(C::colorize($language->getNested("command.reload")));
    }

    public function getPermission(): ?string
    {
        return "martian_monthly_crates.reload";
    }
}