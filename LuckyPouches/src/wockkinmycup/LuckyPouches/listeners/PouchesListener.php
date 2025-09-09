<?php

namespace wockkinmycup\LuckyPouches\listeners;

use Exception;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Config;
use wockkinmycup\LuckyPouches\Loader;
use wockkinmycup\LuckyPouches\tasks\ActionBarRevealTask;
use wockkinmycup\LuckyPouches\tasks\BossBarRevealTask;
use wockkinmycup\LuckyPouches\tasks\TitleRevealTask;
use pocketmine\utils\TextFormat as C;
use wockkinmycup\LuckyPouches\utils\Utils;

class PouchesListener implements Listener
{

    public array $pouchCooldown = [];

    public function onPlace(BlockPlaceEvent $ev)
    {
        $i = $ev->getItem();
        $t = $i->getNamedTag();

        if ($t->getTag("luckypouches")) {
            $ev->cancel();
        }
    }

    /**
     * @throws Exception
     */
    public function onUse(PlayerItemUseEvent $event): bool
    {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $tag = $item->getNamedTag();
        $config = new Config(Loader::getInstance()->getDataFolder() . "config.yml", Config::YAML);
        $messages = new Config(Loader::getInstance()->getDataFolder() . "messages.yml", Config::YAML);
        $currentTime = time();

        if (isset($this->pouchCooldown[$player->getName()])) {
            $remainingCooldown = $this->pouchCooldown[$player->getName()] - $currentTime;

            if ($remainingCooldown > 0) {
                $player->sendMessage(C::RED . C::BOLD . "[!]" . C::RESET . C::GRAY . " Please wait $remainingCooldown seconds before opening another pouch.");
                return false;
            }
        }
        if ($tag->getTag("luckypouches")) {
            $pouchTag = $tag->getString("luckypouches");
            $configArray = $config->getAll();
            if (!isset($configArray["pouches"][$pouchTag])) {
                throw new Exception("Invalid pouch identifier: $pouchTag");
            }
            $pouchData = $configArray["pouches"][$pouchTag];
            $currency = $pouchData["currency"];

            $animation = isset($pouchData["animation"]) ? strtoupper($pouchData["animation"]) : "NONE";

            if ($animation === "RANDOM") {
                $availableAnimations = ["TITLE", "ACTIONBAR", "BOSSBAR", "NONE"];
                $randomAnimation = $availableAnimations[array_rand($availableAnimations)];

                switch ($randomAnimation) {
                    case "TITLE":
                        $this->pouchCooldown[$player->getName()] = $currentTime + $config->getNested("animations.title.cooldown");
                        Loader::getInstance()->getScheduler()->scheduleDelayedTask(
                            new ClosureTask(function () use ($player) {
                                unset($this->pouchCooldown[$player->getName()]);
                            }),
                            $config->getNested("animations.title.cooldown") * 20
                        );
                        $item->setCount($item->getCount() - 1);
                        $player->getInventory()->setItemInHand($item);
                        $minAmount = $pouchData["min"];
                        $maxAmount = $pouchData["max"];
                        $winnings = mt_rand($minAmount, $maxAmount);
                        $task = new TitleRevealTask($player, $winnings, $pouchTag);
                        Loader::getInstance()->getScheduler()->scheduleRepeatingTask($task, 10);
                        break;

                    case "ACTIONBAR":
                        $this->pouchCooldown[$player->getName()] = $currentTime + $config->getNested("animations.actionbar.cooldown");
                        Loader::getInstance()->getScheduler()->scheduleDelayedTask(
                            new ClosureTask(function () use ($player) {
                                unset($this->pouchCooldown[$player->getName()]);
                            }),
                            $config->getNested("animations.actionbar.cooldown") * 20
                        );
                        $item->setCount($item->getCount() - 1);
                        $player->getInventory()->setItemInHand($item);
                        $minAmount = $pouchData["min"];
                        $maxAmount = $pouchData["max"];
                        $winnings = mt_rand($minAmount, $maxAmount);
                        $task = new ActionBarRevealTask($player, $winnings, $pouchTag);
                        Loader::getInstance()->getScheduler()->scheduleRepeatingTask($task, 10);
                        break;

                    case "BOSSBAR":
                        $this->pouchCooldown[$player->getName()] = $currentTime + $config->getNested("animations.bossbar.cooldown");
                        Loader::getInstance()->getScheduler()->scheduleDelayedTask(
                            new ClosureTask(function () use ($player) {
                                unset($this->pouchCooldown[$player->getName()]);
                            }),
                            $config->getNested("animations.bossbar.cooldown") * 20
                        );
                        $item->setCount($item->getCount() - 1);
                        $player->getInventory()->setItemInHand($item);
                        $minAmount = $pouchData["min"];
                        $maxAmount = $pouchData["max"];
                        $winnings = mt_rand($minAmount, $maxAmount);
                        $task = new BossBarRevealTask($player, $winnings, $pouchTag);
                        Loader::getInstance()->getScheduler()->scheduleRepeatingTask($task, 10);
                        break;

                    case "NONE":
                        $this->pouchCooldown[$player->getName()] = $currentTime + $config->getNested("animations.none.cooldown");
                        $item->setCount($item->getCount() - 1);
                        $player->getInventory()->setItemInHand($item);
                        $minAmount = $pouchData["min"];
                        $maxAmount = $pouchData["max"];
                        $money = mt_rand($minAmount, $maxAmount);
                        if ($currency === "BEDROCKECONOMY") {
                            $prize_msg = $messages->get("prize_message");
                            $prize_msg = str_replace(["{prefix}", "{prize}"], [$messages->get("prefix"), number_format($money)], $prize_msg);
                            $player->sendMessage(C::colorize($prize_msg));
                        }

                        if ($currency === "necrotic") {
                            $session = \xtcy\Necrotic\Loader::getPlayerManager()->getSession($player);
                            $session->addBalance($money);
                            $prize_msg = $messages->get("prize_message");
                            $player->sendMessage(C::colorize(str_replace(["{prefix}", "{prize}", "{type}"], [$messages->get("prefix"), number_format($money), $pouchTag], $prize_msg)));
                        }

                        if ($currency === "XP") {
                            $prize_msg = $messages->get("prize_xp_message");
                            $prize_msg = str_replace(["{prefix}", "{prize}", "{type}"], [$messages->get("prefix"), number_format($money), $pouchTag], $prize_msg);
                            $player->sendMessage(C::colorize($prize_msg));
                            $player->getXpManager()->addXp($money);
                        }
                        break;
                }
            } else {
                switch ($animation) {
                    case "TITLE":
                        $this->pouchCooldown[$player->getName()] = $currentTime + $config->getNested("animations.title.cooldown");
                        Loader::getInstance()->getScheduler()->scheduleDelayedTask(
                            new ClosureTask(function () use ($player) {
                                unset($this->pouchCooldown[$player->getName()]);
                            }),
                            $config->getNested("animations.title.cooldown") * 20
                        );
                        $item->setCount($item->getCount() - 1);
                        $player->getInventory()->setItemInHand($item);
                        $minAmount = $pouchData["min"];
                        $maxAmount = $pouchData["max"];
                        $winnings = mt_rand($minAmount, $maxAmount);
                        $task = new TitleRevealTask($player, $winnings, $pouchTag);
                        Loader::getInstance()->getScheduler()->scheduleRepeatingTask($task, 10);
                        break;

                    case "ACTIONBAR":
                        $this->pouchCooldown[$player->getName()] = $currentTime + $config->getNested("animations.actionbar.cooldown");
                        Loader::getInstance()->getScheduler()->scheduleDelayedTask(
                            new ClosureTask(function () use ($player) {
                                unset($this->pouchCooldown[$player->getName()]);
                            }),
                            $config->getNested("animations.actionbar.cooldown") * 20
                        );
                        $item->setCount($item->getCount() - 1);
                        $player->getInventory()->setItemInHand($item);
                        $minAmount = $pouchData["min"];
                        $maxAmount = $pouchData["max"];
                        $winnings = mt_rand($minAmount, $maxAmount);
                        $task = new ActionBarRevealTask($player, $winnings, $pouchTag);
                        Loader::getInstance()->getScheduler()->scheduleRepeatingTask($task, 10);
                        break;

                    case "BOSSBAR":
                        $this->pouchCooldown[$player->getName()] = $currentTime + $config->getNested("animations.bossbar.cooldown");
                        Loader::getInstance()->getScheduler()->scheduleDelayedTask(
                            new ClosureTask(function () use ($player) {
                                unset($this->pouchCooldown[$player->getName()]);
                            }),
                            $config->getNested("animations.bossbar.cooldown") * 20
                        );
                        $item->setCount($item->getCount() - 1);
                        $player->getInventory()->setItemInHand($item);
                        $minAmount = $pouchData["min"];
                        $maxAmount = $pouchData["max"];
                        $winnings = mt_rand($minAmount, $maxAmount);
                        $task = new BossBarRevealTask($player, $winnings, $pouchTag);
                        Loader::getInstance()->getScheduler()->scheduleRepeatingTask($task, 10);
                        break;

                    case "NONE":
                        $this->pouchCooldown[$player->getName()] = $currentTime + $config->getNested("animations.none.cooldown");
                        $item->setCount($item->getCount() - 1);
                        $player->getInventory()->setItemInHand($item);
                        $minAmount = $pouchData["min"];
                        $maxAmount = $pouchData["max"];
                        $money = mt_rand($minAmount, $maxAmount);
                        if ($currency === "BEDROCKECONOMY") {
                            $prize_msg = $messages->get("prize_message");
                            $prize_msg = str_replace(["{prefix}", "{prize}"], [$messages->get("prefix"), number_format($money)], $prize_msg);
                            $player->sendMessage(C::colorize($prize_msg));
                        }

                        if ($currency === "necrotic") {
                            $session = \xtcy\Necrotic\Loader::getPlayerManager()->getSession($player);
                            $session->addBalance($money);
                            $prize_msg = $messages->get("prize_message");
                            $player->sendMessage(C::colorize(str_replace(["{prefix}", "{prize}", "{type}"], [$messages->get("prefix"), number_format($money), $pouchTag], $prize_msg)));
                        }

                        if ($currency === "XP") {
                            $prize_msg = $messages->get("prize_xp_message");
                            $prize_msg = str_replace(["{prefix}", "{prize}", "{type}"], [$messages->get("prefix"), number_format($money), $pouchTag], $prize_msg);
                            $player->sendMessage(C::colorize($prize_msg));
                            $player->getXpManager()->addXp($money);
                        }
                        break;

                    default:
                        $player->sendMessage(C::RED . C::BOLD . "[!]" . C::RESET . C::GRAY . " Invalid animation type: $animation");
                }
            }
        }
        return true;
    }
}
