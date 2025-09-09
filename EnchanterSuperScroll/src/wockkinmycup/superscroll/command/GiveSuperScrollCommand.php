<?php

namespace wockkinmycup\superscroll\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat;
use wockkinmycup\superscroll\Loader;
use wockkinmycup\superscroll\utils\Scrolls;
use wockkinmycup\utilitycore\utils\Utils;

class GiveSuperScrollCommand extends Command implements PluginOwned {

    public Loader $plugin;

    public function __construct(Loader $plugin) {
        parent::__construct("superscrolls");
        $this->setPermission("superscrolls.admin");
        $this->setAliases(["ss"]);
        $this->setDescription("Manage Super Scrolls");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::DARK_RED . "You must run this command in-game.");
            return false;
        }

        if (!$sender->hasPermission("superscrolls.admin")) {
            $sender->sendMessage(TextFormat::DARK_RED . "You do not have permission to use this command.");
            return false;
        }

        if (empty($args)) {
            $sender->sendMessage(TextFormat::YELLOW . "Usage: /superscrolls <list|give|reload>");
            return false;
        }

        if (isset($args[0])) {
            switch (strtolower($args[0])) {
                case "list":
                    if ($sender->hasPermission("superscrolls.admin")) {
                        $config = Loader::getInstance()->getConfig();
                        $scrolls = $config->get("superscrolls");

                        if (is_array($scrolls) && !empty($scrolls)) {
                            $message = "§r§6Available Super Scrolls\n";
                            foreach ($scrolls as $scrollId => $scrollData) {
                                $message .= "§7-§r $scrollId\n";
                            }
                        } else {
                            $message = "No scrolls available.";
                        }

                        $sender->sendMessage($message);
                    }
                    break;
                case "give":
                    if (count($args) < 3) {
                        $sender->sendMessage(TextFormat::YELLOW . "Usage: /superscrolls give <player> <scrollId> [amount]");
                        return false;
                    }

                    $targetPlayer = Utils::getPlayerByPrefix($args[1]);

                    if (!$targetPlayer instanceof Player) {
                        $sender->sendMessage(TextFormat::DARK_RED . "Player not found: " . TextFormat::WHITE . "'" . $args[1] . "'");
                        return true;
                    }

                    $scrollId = strtolower($args[2]);
                    $amount = isset($args[3]) ? (int)$args[3] : 1;

                    switch ($scrollId) {
                        case "enchanter":
                            $sender->getInventory()->addItem(Scrolls::getSuperScrolls("enchanter", $amount));
                            $sender->sendMessage(TextFormat::GREEN . "Enchanter Super Scroll given to '" . TextFormat::WHITE . $targetPlayer->getName() . TextFormat::GREEN . "'");
                            break;

                        default:
                            $sender->sendMessage(TextFormat::YELLOW . "Unknown scroll ID: " . TextFormat::WHITE . $scrollId);
                            break;
                    }
                    break;
                case "reload":
                    if ($sender->hasPermission("superscrolls.admin")) {
                        Loader::getInstance()->getConfig()->reload();
                        $sender->sendMessage(TextFormat::GREEN . "Successfully reloaded configurations");
                    }
                    break;
                default:
                    $sender->sendMessage(TextFormat::YELLOW . "Unknown command: " . TextFormat::WHITE . $args[0]);
                    return false;
            }
        }
        return false;
    }

    public function getOwningPlugin(): Loader {
        return $this->plugin;
    }
}