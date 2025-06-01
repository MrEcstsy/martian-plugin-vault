<?php

namespace ecstsy\LevelSystem\Commands;

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\database\exception\InsufficientFundsException;
use cooldogedev\BedrockEconomy\database\exception\RecordNotFoundException;
use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\BaseCommand;
use ecstsy\LevelSystem\Loader;
use ecstsy\LevelSystem\Utils\LevelUtils;
use pocketmine\block\utils\DyeColor;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat as C;
use poggit\libasynql\SqlError;

class LevelUpCommand extends BaseCommand {

    public function prepare(): void {
        $this->setPermission($this->getPermission());
        $this->registerArgument(0, new IntegerArgument("amount", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "This command can only be used in-game.");
            return;
        }
    
        $amount = isset($args["amount"]) ? (int)$args["amount"] : 1;
        $session = Loader::getPlayerManager()->getSession($sender);
        $lvlcfg = LevelUtils::getConfiguration(Loader::getInstance(), "levels.yml");
        $levels = $lvlcfg->get("levels");
        $maxLevel = count($levels);
        $totalPrice = 0;
        $addedLevels = 0;
        $accumulatedRewards = [];
    
        for ($i = 0; $i < $amount; $i++) {
            $nextLevel = $session->getLevel() + $i + 1; 
            if ($nextLevel <= $maxLevel) {
                $levelData = $levels[$nextLevel];
                $price = $levelData["price"];
                $totalPrice += $price;
                $addedLevels++; 
                if (isset($levelData["items"])) {
                    $accumulatedRewards[] = LevelUtils::setupItems($levelData["items"]);
                } elseif (isset($levelData["commands"])) {
                    foreach ($levelData["commands"] as $command) {
                        $accumulatedRewards[] = $command;
                    }
                }
            }
        }
        
        Loader::getEconomyProvider()->getMoney($sender, function (int|float $money) use ($totalPrice, $session, $sender, $addedLevels, $accumulatedRewards) {
            if ($money >= $totalPrice) {
                $session->addLevel($addedLevels); 
                Loader::getEconomyProvider()->takeMoney($sender, $totalPrice);
                $sender->sendMessage(C::GREEN . "You have purchased " . $addedLevels . " level(s)!");
                
                foreach ($accumulatedRewards as $reward) {
                    if (is_string($reward)) {
                        Server::getInstance()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), str_replace("{player}", $sender->getName(), $reward));   
                    } elseif (is_array($reward)) {
                        foreach ($reward as $item) {
                            $sender->getInventory()->addItem($item);
                        }
                    }
                } 

                foreach ($accumulatedRewards as $reward) {
                    if (is_array($reward)) {
                        foreach ($reward as $item) {
                            $sender->getInventory()->addItem($item);
                        }
                    } elseif (is_string($reward)) {
                        LevelUtils::setupCommandRewards([$reward], $sender); 
                    }
                }
            } else {
                $sender->sendMessage(C::colorize("&cYou don't have enough money to do that!"));
            }
        });
        
    }    

    public function getPermission(): string {
        return "levelsystem.default";
    }
}