<?php

namespace wockkinmycup\utilitycore\utils;

use xtcy\odysseyrealm\listeners\ItemListeners;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\XpLevelUpSound;
use wockkinmycup\utilitycore\items\Vouchers;
use wockkinmycup\utilitycore\Loader;

class ItemRenameManager {

    public static function handleCancel(Player $player): void
    {
        $player->sendMessage("§r§c§l** §r§cYou have unqueued your Itemtag for this usage.");
        Utils::playSound($player, "mob.enderdragon.flap", 2);
        $player->getInventory()->addItem(Vouchers::give("name_tag", 1));
        unset(ItemListeners::$itemRenamer[$player->getName()]);

        if (isset(ItemListeners::$renameMessages[$player->getName()])) {
            unset(ItemListeners::$renameMessages[$player->getName()]);
        }
    }

    public static function sendMessageFormats(Player $player, string $configKey): void
    {
        $messageFormats = Utils::getConfiguration(Loader::getInstance(), "messages.yml")->getNested($configKey, [""]);

        foreach ($messageFormats as $messageFormat) {
            $player->sendMessage(TextFormat::colorize($messageFormat));
        }
    }

    public static function handleConfirmation(Player $player): void
    {
        $messageFormats = Utils::getConfiguration(Loader::getInstance(), "messages.yml")->getNested("items.itemnametag.messages.success", [""]);
        $customName = ItemListeners::$renameMessages[$player->getName()];

        foreach ($messageFormats as $messageFormat) {
            $message = str_replace("{item_name}", $customName, $messageFormat);
            $player->sendMessage(TextFormat::colorize($message));
        }

        $player->getLocation()->getWorld()->addSound($player->getLocation(), new XpLevelUpSound(100));
        $hand = $player->getInventory()->getItemInHand();
        $hand->setCustomName($customName);
        $player->getInventory()->setItemInHand($hand);
        unset(ItemListeners::$itemRenamer[$player->getName()]);
        unset(ItemListeners::$renameMessages[$player->getName()]);
    }

    public static function handlePreview(Player $player, string $message): void
    {
        $formatted = TextFormat::colorize($message);
        $player->sendMessage("§r§e§l(!) §r§eItem Name Preview: $formatted");
        $player->sendMessage("§r§7Type '§r§aconfirm§7' if this looks correct, otherwise type '§ccancel§7' to start over.");
        ItemListeners::$renameMessages[$player->getName()] = $formatted;
    }
}