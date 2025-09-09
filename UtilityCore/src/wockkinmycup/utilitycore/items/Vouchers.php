<?php

namespace wockkinmycup\utilitycore\items;

use pocketmine\block\utils\DyeColor;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use wockkinmycup\utilitycore\Loader;

class Vouchers {

    public static function give(string $voucher, int $amount): ?Item {
        $item = VanillaItems::AIR()->setCount($amount);

        switch (strtolower($voucher)){
            case "lore_crystal":
                $item = VanillaItems::DYE()->setColor(DyeColor::RED)->setCount($amount);

                $item->setCustomName(TextFormat::colorize("&r&l&6Item Lore Crystal  &r&7(Right Click)"));

                $item->setLore([
                    TextFormat::colorize("&r&7Apply a custom line of lore"),
                    TextFormat::colorize("&r&7to customize your equipment."),
                    "",
                    TextFormat::colorize("&r&6&l* &r&7Limited to 1 custom line of lore per item.")
                ]);

                $item->getNamedTag()->setString("voucher", "lore_crystal");
                break;
            case "name_tag":
                $item = StringToItemParser::getInstance()->parse("name_tag")->setCount($amount);

                $item->setCustomName(TextFormat::colorize("&r&l&6Item Name Tag &r&7(Right Click)"));

                $item->setLore([
                    TextFormat::colorize("&r&7Rename and customize your equipment"),
                ]);

                $item->getNamedTag()->setString("voucher", "rename_tag");
                break;
        }

        return $item;
    }

    public static function giveRankVoucher(string $rank, int $amount = 1): ?Item {
        $configPath = Loader::getInstance()->getDataFolder() . "config.yml";

        if (!file_exists($configPath)) {
            return null;
        }

        $configData = yaml_parse_file($configPath);
        if ($configData === false || !isset($configData['vouchers'][$rank])) {
            return null;
        }

        $configData = $configData['vouchers'][$rank];

        $item = StringToItemParser::getInstance()->parse($configData['item'])->setCount($amount);

        $item->setCustomName(TextFormat::colorize($configData['name']));

        if (isset($configData['lore']) && is_array($configData['lore'])) {
            $lore = [];
            foreach ($configData['lore'] as $line) {
                $color = TextFormat::colorize($line);
                $lore[] = $color;
            }
            $item->setLore($lore);
        }

        $item->getNamedTag()->setString("rank_voucher", $rank);
        return $item;
    }


    /**
     * @param Player|null $player
     * @param int|null $amount
     * @return Item|null
     */
    public static function createMoneyNote(?Player $player = null, ?int $amount = null): ?Item {
        $item = VanillaItems::PAPER();
        $signer = "Console";
        $randAmount = rand(1, 10000000);
        $item->getNamedTag()->setInt("banknote", $amount);
        $tag = $item->getNamedTag()->getInt("banknote");

        if($player !== null){
            $signer = $player->getName();
        }

        if($amount !== null){
            $randAmount = $amount;
        }

        $item->setCustomName("§r§b§lBank Note §r§7(Right-Click)");

        $item->setLore(array(
            "§r§dValue §r§f$" . number_format($tag),
            "§r§dSigner §r§f$signer"
        ));

        return $item;
    }

    /**
     * @param Player|null $player
     * @param float|null $amount
     * @param int $count
     * @param bool $subtract
     * @return Item|null
     */
    public static function createXPBottle(?Player $player = null, ?float $amount = null, int $count = 1, bool $subtract = false): ?Item
    {
        $item = VanillaItems::EXPERIENCE_BOTTLE()->setCount($count);
        $signer = "Console";
        $randAmount = rand(1, 500000);
        $item->getNamedTag()->setInt("xpbottle", $amount);
        $tag = $item->getNamedTag()->getInt("xpbottle");

        if ($player !== null) {
            $signer = $player->getName();
        }

        if ($amount !== null) {
            $randAmount = $amount;
        }

        $item->setCustomName("§r§a§lExperience Bottle §r§7(Throw)");

        $item->setLore(array(
            "§r§dValue §r§f" . number_format($tag) . " XP",
            "§r§dEnchanter §r§f$signer"
        ));
        if($subtract) $player->getXpManager()->subtractXp($amount);

        return $item;
    }
}