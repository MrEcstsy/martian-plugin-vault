<?php

namespace xtcy\bn\listener;

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\api\legacy\ClosureContext;
use Exception;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\utils\TextFormat as C;
use xtcy\bn\Loader;
use xtcy\bn\utils\Utils;

class EventListener implements Listener
{

    public function onPlace(BlockPlaceEvent $ev)
    {
        $i = $ev->getItem();
        $t = $i->getNamedTag();

        if ($t->getTag("notes")) {
            $ev->cancel();
        }
    }

    public function onUse(PlayerItemUseEvent $event)
    {
        $item = $event->getItem();
        $player = $event->getPlayer();
        $tag = $item->getNamedTag();
        $config = Utils::getConfiguration(Loader::getInstance(), "config.yml");
        $configArray = $config->getAll();

        if ($tag->getTag("notes")) {
            $noteTag = $tag->getString("notes");
            if (!isset($configArray["notes"][$noteTag])) {
                throw new Exception("Invalid note identifier: $noteTag");
            }
            $noteData = $configArray["notes"][$noteTag];
            $currency = $noteData["currency"];
            $minAmount = $noteData["min_amount"];
            $maxAmount = $noteData["max_amount"];
            $winnings = mt_rand($minAmount, $maxAmount);
            $settings = isset($configArray["settings"]['type']) ? ($configArray["settings"]['type']) : "message";
            switch (strtolower($settings)) {
                case "message":
                    $item->pop();
                    $player->getInventory()->setItemInHand($item);
                    if ($currency === "BedrockEconomy") {
                        BedrockEconomyAPI::legacy()->addToPlayerBalance(
                            $player->getName(),
                            $winnings,
                            ClosureContext::create(
                                function () use ($winnings, $currency, $config, $player, $item) {
                                    if ($config->getNested('settings.message') !== null) {
                                        $message = $config->getNested('settings.message');
                                        $player->sendMessage(C::colorize(str_replace(["{prize}", "{note}"], [number_format($winnings), $item->getCustomName()], $message)));
                                    }
                                }
                            )
                        );
                    }

                    if ($currency === "EXP") {
                        $player->getXpManager()->addXp($winnings);
                        if ($config->getNested('settings.message') !== null) {
                            $message = $config->getNested('settings.message');
                            $player->sendMessage(C::colorize(str_replace(["{prize}", "{note}"], [number_format($winnings), $item->getCustomName()], $message)));
                        }
                    }
                    break;
                case "title":
                    $item->pop();
                    $player->getInventory()->setItemInHand($item);
                    if ($currency === "BedrockEconomy") {
                        BedrockEconomyAPI::legacy()->addToPlayerBalance(
                            $player->getName(),
                            $winnings,
                            ClosureContext::create(
                                function () use ($winnings, $currency, $config, $player, $item) {
                                    if ($config->getNested('settings.title') !== null) {
                                        $titleText = $config->getNested('settings.title.text', '');
                                        $titleSubtext = $config->getNested('settings.title.subtext', '');
                                        $placeholders = [
                                            '{prize}' => number_format($winnings),
                                            '{note}' => $item->getCustomName(),
                                        ];
                                        $player->sendTitle(C::colorize(str_replace(array_keys($placeholders), array_values($placeholders), $titleText)));
                                        $player->sendSubTitle(C::colorize(str_replace(array_keys($placeholders), array_values($placeholders), $titleSubtext)));
                                    }
                                }
                            )
                        );
                    }

                    if ($currency === "EXP") {
                        $player->getXpManager()->addXp($winnings);
                        if ($config->getNested('settings.title') !== null) {
                            $titleText = $config->getNested('settings.title.text', '');
                            $titleSubtext = $config->getNested('settings.title.subtext', '');
                            $placeholders = [
                                '{prize}' => number_format($winnings),
                                '{note}' => $item->getCustomName(),
                            ];
                            $player->sendTitle(C::colorize(str_replace(array_keys($placeholders), array_values($placeholders), $titleText)));
                            $player->sendSubTitle(C::colorize(str_replace(array_keys($placeholders), array_values($placeholders), $titleSubtext)));
                        }
                    }
                    break;
                case "actionbar":
                    $item->pop();
                    $player->getInventory()->setItemInHand($item);
                    if ($currency === "BedrockEconomy") {
                        BedrockEconomyAPI::legacy()->addToPlayerBalance(
                            $player->getName(),
                            $winnings,
                            ClosureContext::create(
                                function () use ($winnings, $currency, $config, $player, $item) {
                                    if ($config->getNested('settings.actionbar') !== null) {
                                        $message = $config->getNested('settings.actionbar');
                                        $player->sendActionBarMessage(C::colorize(str_replace(["{prize}", "{note}"], [number_format($winnings), $item->getCustomName()], $message)));
                                    }
                                }
                            )
                        );
                    }

                    if ($currency === "EXP") {
                        $player->getXpManager()->addXp($winnings);
                        if ($config->getNested('settings.actionbar') !== null) {
                            $message = $config->getNested('settings.actionbar');
                            $player->sendActionBarMessage(C::colorize(str_replace(["{prize}", "{note}"], [number_format($winnings), $item->getCustomName()], $message)));
                        }
                    }
                    break;
            }
        }
    }
}