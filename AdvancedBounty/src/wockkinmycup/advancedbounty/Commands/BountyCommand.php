<?php

namespace wockkinmycup\advancedbounty\Commands;

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\BedrockEconomy;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat;
use wockkinmycup\advancedbounty\Loader;
use wockkinmycup\advancedbounty\Utils\BountyManager;
use wockkinmycup\advancedbounty\Utils\Utils;

class BountyCommand extends Command implements PluginOwned
{

    public Loader $plugin;

    public function __construct(Loader $plugin)
    {
        parent::__construct("bounty", "View all bounty subcommands", "/bounty help", ["b", "bounties"]);
        $this->setPermission("advancedbounty.default");
        $this->plugin = $plugin;
    }

    /**
     * @throws \JsonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used in-game.");
            return false;
        }

        if (empty($args)) {
            $sender->sendMessage("§r§l§6[!] §r§6Bounty Commands:");
            $sender->sendMessage("§r§e/bounty add <player> <amount> §f- §7Add a bounty to a player");
            $sender->sendMessage("§r§e/bounty leaderboard §f- §7View the 10 highest bounties");
            $sender->sendMessage("§r§e/bounty view [player] §f- §7View your or another players bounty statistics");
            if ($sender->hasPermission("advancedbounty.admin")) {
                $sender->sendMessage("§r§e/bounty remove §f- §7Remove a bounty from a player.");
                $sender->sendMessage("§r§e/bounty reload §f- §7Reloads the configurations.");
            }
            return false;
        }

        $bountyManager = new BountyManager(Utils::getConfigurations("bounty"));
        $symbol = BedrockEconomy::getInstance()->getCurrencyManager()->getSymbol();
        $subcommand = strtolower(array_shift($args));

        switch ($subcommand) {
            case "add":
            case "set":
            case "give":
                if (count($args) < 2) {
                    $sender->sendMessage("Usage: /bounty add <player> <amount>");
                    return false;
                }

                $targetPlayerName = array_shift($args);

                $amountShorthand = array_shift($args);
                $amount = Utils::parseShorthandAmount($amountShorthand);

                $targetPlayer = Utils::customGetPlayerByPrefix($targetPlayerName);

                if ($targetPlayer !== null) {
                    $bountyManager->setBounty($sender, $targetPlayer, $amount);
                    $broadcastMessage = Utils::getConfigurations("messages")->get("broadcasts.set", "§r§f{player} §eset a bounty on §f{target_player}§r§e! Kill them to get §f{amount}{currency_symbol}");
                    $broadcastMessage = str_replace(["{player}", "{target_player}", "{amount}", "{currency_symbol}"], [$sender->getName(), $targetPlayer->getName(), number_format($amount), $symbol], $broadcastMessage);
                    $sender->getServer()->broadcastMessage(TextFormat::colorize($broadcastMessage));
                    $totalBounty = $bountyManager->getBounty($targetPlayer->getName()) + $amount;
                    $totalMessage = Utils::getConfigurations("messages")->get("broadcasts.total", "§r§7§oTotal Bounty: {total_amount}{currency_symbol} (-{tax}%)");
                    $totalMessage = str_replace(["{total_amount}", "{currency_symbol}", "{tax}"], [$totalBounty, $symbol, Utils::getConfigurations()->get("tax")["bounty"]], $totalMessage);
                    $sender->getServer()->broadcastMessage(TextFormat::colorize($totalMessage));
                } else {
                    $notFoundMessage = Utils::getConfigurations("messages")->get("not-found", "§r§l§c[!] §r§cPlayer '{target_player}' not found or is not online.");
                    $notFoundMessage = str_replace("{target_player}", $targetPlayerName, $notFoundMessage);
                    $sender->sendMessage(TextFormat::colorize($notFoundMessage));
                }
                break;
            case "remove":
                if (!$sender->hasPermission("advancedbounty.admin")) {
                    $sender->sendMessage(Loader::NOPERMISSION);
                    return false;
                }
                if (count($args) !== 1) {
                    $sender->sendMessage("Usage: /bounty remove <player>");
                    return false;
                }
                $targetPlayerName = array_shift($args);
                $targetPlayer = Utils::customGetPlayerByPrefix($targetPlayerName);
                if ($targetPlayer === null) {
                    $notFoundMessage = Utils::getConfigurations("messages")->get("not-found", "§r§l§c[!] §r§cPlayer '{target_player}' not found or is not online.");
                    $notFoundMessage = str_replace("{target_player}", $targetPlayerName, $notFoundMessage);
                    $sender->sendMessage(TextFormat::colorize($notFoundMessage));
                } else {
                    $targetPlayerBounty = $bountyManager->getBounty($targetPlayer->getName());
                    if ($targetPlayerBounty <= 0) {
                        $noBountyMessage = Utils::getConfigurations("messages")->get("remove.no-bounty", "§r§l§c[!] §r§c'{target}' does not have a bounty.");
                        $noBountyMessage = str_replace("{target}", $targetPlayerName, $noBountyMessage);
                        $sender->sendMessage(TextFormat::colorize($noBountyMessage));
                    } else {
                        $bountyManager->removeBounty($targetPlayer->getName());

                        $removedMessage = Utils::getConfigurations("messages")->get("remove.removed", "§r§l§a[!] §r§aBounty for '{target}' has been removed.");
                        $removedMessage = str_replace("{target}", $targetPlayerName, $removedMessage);
                        $sender->sendMessage(TextFormat::colorize($removedMessage));
                    }
                }
                break;
            case "top":
            case "leaderboard":
                $bountyData = [];

                foreach ($sender->getServer()->getOnlinePlayers() as $player) {
                    $playerName = $player->getName();
                    $bountyAmount = $bountyManager->getBounty($playerName);
                    $bountyData[$playerName] = $bountyAmount;
                }

                arsort($bountyData);

                $topPlayers = array_slice($bountyData, 0, 10);

                $sender->sendMessage(TextFormat::colorize(Utils::getConfigurations("messages")->get("leaderboards.header", "§6Top 10 Most Wanted:")));
                $position = 1;
                foreach ($topPlayers as $playerName => $bountyAmount) {
                    $leaderboardInfo = Utils::getConfigurations("messages")->get("leaderboards.info", "§e#{position}: {player} - {amount}{currency_symbol}");
                    $leaderboardInfo = str_replace(["{position}", "{player}", "{amount}", "{currency_symbol}"], [$position, $playerName, $bountyAmount, $symbol], $leaderboardInfo);
                    $sender->sendMessage(TextFormat::colorize($leaderboardInfo));
                    $position++;
                }
                break;
            case "info":
            case "view":
                if (empty($args)) {
                    $bounty = $bountyManager->getBounty($sender->getName());
                    $viewMessage = Utils::getConfigurations("messages")->get("view.self", "§r§l§6[!] §r§fYour current bounty: {currency_symbol}{bounty}");
                    $viewMessage = str_replace(["{bounty}", "{currency_symbol}"], [number_format($bounty), $symbol], $viewMessage);
                    $sender->sendMessage(TextFormat::colorize($viewMessage));
                } else {
                    $targetPlayerName = array_shift($args);
                    $viewTargetPlayer = Utils::customGetPlayerByPrefix($targetPlayerName);

                    if ($viewTargetPlayer !== null) {
                        $bounty = $bountyManager->getBounty($viewTargetPlayer->getName());
                        $otherViewMessage = Utils::getConfigurations("messages")->get("view.other", "§r§l§6[!] §r§f{target_player}'s current bounty: {currency_symbol}{bounty}");
                        $otherViewMessage = str_replace(["{target_player}", "{bounty}", "{currency_symbol}"], [$viewTargetPlayer->getName(), number_format($bounty), $symbol], $otherViewMessage);
                        $sender->sendMessage(TextFormat::colorize($otherViewMessage));
                    } else {
                        $message = str_replace("{target_player}", $targetPlayerName, Utils::getConfigurations("messages")->get("not-found"));
                        $sender->sendMessage(TextFormat::colorize($message));
                    }
                }
                break;
            case "reload":
                if (!$sender->hasPermission("advancedbounty.admin")) {
                    $sender->sendMessage(Loader::NOPERMISSION);
                    return false;
                }
                Utils::getConfigurations()->reload();
                Utils::getConfigurations("messages")->reload();
                $sender->sendMessage(TextFormat::RESET . TextFormat::GREEN . TextFormat::BOLD . "[!] " . TextFormat::RESET . TextFormat::GREEN . "Successfully reloaded all configuration files.");
                break;
            default:
                $sender->sendMessage("Unknown subcommand. Usage: /bounty [subcommand] [args]");
                break;
        }

        return true;
    }

    public function getOwningPlugin() : Loader {
        return $this->plugin;
    }
}