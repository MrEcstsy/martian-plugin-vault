<?php

namespace wockkinmycup\LuckyPouches\commands;

use Exception;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginOwned;
use pocketmine\Server;
use pocketmine\utils\Config;
use wockkinmycup\LuckyPouches\Loader;
use pocketmine\utils\TextFormat as C;
use wockkinmycup\LuckyPouches\utils\PouchItem;
use wockkinmycup\LuckyPouches\utils\Utils;

class PouchCommand extends Command implements PluginOwned {

    public Loader $loader;

    public function __construct(Loader $loader) {
        parent::__construct("luckypouches");
        $this->setPermission("luckypouches.command");
        $this->setAliases([
            "lp",
        ]);
        $this->loader = $loader;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        $cfg = $this->loader->getConfig();
        $msg_cfg = new Config($this->loader->getDataFolder() . "messages.yml", Config::YAML);

        if (!$sender->hasPermission('luckypouches.command')) {
            $sender->sendMessage(C::DARK_RED . "You do not have permission to use this command.");
            return false;
        }

        if (empty($args)) {
            $sender->sendMessage(C::colorize($msg_cfg->get("prefix") . "§r§3Available Commands\n"));
            $sender->sendMessage("§r§3/lp reload §7- §fReload all the plugin configurations");
            $sender->sendMessage("§r§3/lp list §7- §fList all the pouches");
            $sender->sendMessage("§r§3/lp give §7- §f<player|all> <identifier> [amount] §7- §fGive players on or more pouches");
            return true;
        }

        $subcommand = array_shift($args);

        switch ($subcommand) {
            case "reload":
                if ($sender->hasPermission("luckypouches.command")) {
                    $cfg->reload();
                    $msg_cfg->reload();
                    $sender->sendMessage(C::GREEN . 'Successfully reloaded all configurations.');
                }
                break;

            case "list":
                $config = Loader::getInstance()->getConfig();
                $pouches = $config->get("pouches");

                if (is_array($pouches) && !empty($pouches)) {
                    $message = C::colorize($msg_cfg->get("prefix")) . "§r§3Available pouches\n";
                    foreach ($pouches as $pouchId => $pouchData) {
                        $message .= "§7-§r $pouchId\n";
                    }
                } else {
                    $message = "No pouches available.";
                }

                $sender->sendMessage($message);
                break;

            case "give":
                if ($sender->hasPermission("luckypouches.command")) {
                    if (count($args) < 2) {
                        $sender->sendMessage("Usage: /luckypouches give <player|all> <identifier> [amount]");
                        return true;
                    }

                    $playerOrAll = array_shift($args);
                    $identifier = array_shift($args);
                    $amount = empty($args) ? 1 : (int)array_shift($args);

                    if ($playerOrAll === "all") {
                        $onlinePlayers = Loader::getInstance()->getServer()->getOnlinePlayers();
                        foreach ($onlinePlayers as $onlinePlayer) {
                            $pouchData = $cfg->get("pouches.$identifier");
                            if ($pouchData === null) {
                                $sender->sendMessage("Pouch identifier '$identifier' doesn't exist. Double-check your configuration.");
                                return false;
                            }
                            try {
                                $pouchType = PouchItem::getPouchType($identifier, $amount);
                                $onlinePlayer->getInventory()->addItem($pouchType);
                                $give_all = $msg_cfg->get("give-all");
                                $give_all = str_replace(["{prefix}", "{type}"], [$msg_cfg->get("prefix"), $identifier], $give_all);
                                Server::getInstance()->broadcastMessage(C::colorize($give_all));
                            } catch (Exception $e) {
                                $sender->sendMessage("Error while giving pouch to players: " . $e->getMessage());
                            }
                        }
                        $sender->sendMessage(C::GREEN . "Successfully gave a pouch to all online players!");
                    } else {
                        $targetPlayer = Utils::customGetPlayerByPrefix($playerOrAll);
                        if ($targetPlayer !== null) {
                            $pouchData = $cfg->get("pouches.$identifier");
                            if ($pouchData === null) {
                                $sender->sendMessage("Pouch identifier '$identifier' doesn't exist. Double-check your configuration.");
                                return false;
                            }
                            try {
                                $pouchType = PouchItem::getPouchType($identifier, $amount);
                                $targetPlayer->getInventory()->addItem($pouchType);
                                $given_message = $msg_cfg->get("pouch-given");
                                $given_message = str_replace(['{prefix}', '{player}', '{amount}', '{type}'], [$msg_cfg->get("prefix"), $targetPlayer->getName(), $amount, $identifier], $given_message);
                                $sender->sendMessage(C::colorize($given_message));
                                return true;
                            } catch (Exception $e) {
                                $sender->sendMessage("Error while giving pouch to the player: " . $e->getMessage());
                            }
                        } elseif ($playerOrAll === null) {
                            $sender->sendMessage("Invalid player or 'all' parameter.");
                        }
                    }
                }
                break;
            default:
                $sender->sendMessage(C::colorize($msg_cfg->get("prefix") . "§r§3Available Commands\n"));
                $sender->sendMessage("§r§3/lp reload §7- §fReload all the plugin configurations");
                $sender->sendMessage("§r§3/lp list §7- §fList all the pouches");
                $sender->sendMessage("§r§3/lp give §7- §f<player|all> <identifier> [amount] §7- §fGive players on or more pouches");
        }
            return true;
    }

    public function getOwningPlugin() : Loader {
        return $this->loader;
    }
}
