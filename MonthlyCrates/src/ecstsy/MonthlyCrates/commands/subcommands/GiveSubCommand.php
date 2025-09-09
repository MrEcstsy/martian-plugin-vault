<?php 

declare(strict_types=1);

namespace ecstsy\MonthlyCrates\commands\subcommands;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\args\TargetPlayerArgument;
use CortexPE\Commando\BaseSubCommand;
use ecstsy\MartianUtilities\utils\GeneralUtils;
use ecstsy\MartianUtilities\utils\PlayerUtils;
use ecstsy\MonthlyCrates\Loader;
use ecstsy\MonthlyCrates\utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;

final class GiveSubCommand extends BaseSubCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());

        $this->registerArgument(0, new TargetPlayerArgument(false, "player"));
        $this->registerArgument(1, new RawStringArgument("crate"));
        $this->registerArgument(2, new IntegerArgument("amount", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $language = Loader::getLanguageManager();
        
        if (!isset($args["player"], $args["crate"])) {
            $sender->sendMessage(C::colorize("&cUsage: /monthlycrate give <player> <crate> [amount]"));
            return;
        }

        $player = PlayerUtils::getPlayerByPrefix($args['player']);

        if (!$player instanceof Player) {
            $sender->sendMessage(C::colorize("&cError: Player '{$args["player"]}' not found."));
            return;
        }

        $crateType = strtolower($args['crate']);
        $amount = $args['amount'] ?? 1;

        if ($amount < 1) {
            $sender->sendMessage(C::colorize("&r&4Error: &cThe amount must be at least 1."));
            return;
        }

        $crateItem = Utils::createMonthlyCrateItem($player, $crateType, $amount);

        if ($crateItem === null) {
            $sender->sendMessage(C::colorize($language->getNested("invalid-crate")));
            return;
        }

        if ($player->getInventory()->canAddItem($crateItem)) {
            $player->getInventory()->addItem($crateItem);
        } else {
            $player->getWorld()->dropItem($player->getPosition(), $crateItem);
            $player->sendMessage(C::colorize($language->getNested("inventory-full")));
        }

        $sender->sendMessage(C::colorize(str_replace(
            ["{player}", "{amount}", "{crate}"],
            [$player->getName(), $amount, ucfirst($crateType)],
            $language->getNested("given-crate")
        )));
    }

    public function getPermission(): ?string
    {
        return "martian_monthly_crates.give";
    }
}