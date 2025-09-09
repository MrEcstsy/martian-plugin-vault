<?php

namespace wockkinmycup\utilitycore\utils;

use Ifera\ScoreHud\event\PlayerTagsUpdateEvent;
use Ifera\ScoreHud\scoreboard\ScoreTag;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Entity;
use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\Inventory;
use pocketmine\item\Armor;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\item\Tool;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use wockkinmycup\utilitycore\addons\customArmor\listener\CustomArmorListener;
use wockkinmycup\utilitycore\Loader;

class Utils {

    public static int $previousCount = 0;

    public static bool $activated = false;

    public static array $activeSets = [];

    /**
     * ===============================
     *   ____  _   _
     *  / __ \| | | |
     * | |  | | |_| |__   ___ _ __
     * | |  | | __| '_ \ / _ \ '__|
     * | |__| | |_| | | |  __/ |
     *  \____/ \__|_| |_|\___|_|
     *
     * ===============================
     *
     * This is the things like toggleable, sound & effect stuff too.
     * along with other base utilities.
     */

    public static function sendOdysseyUpdate(Player $player): void{
        (new PlayerTagsUpdateEvent($player, [
            new ScoreTag("odyssey.balance", number_format(\xtcy\odysseyrealm\Loader::getSessionManager()->getSession($player)->getBalance())),
            new ScoreTag("odyssey.kills", number_format(\xtcy\odysseyrealm\Loader::getSessionManager()->getSession($player)->getKills())),
            new ScoreTag("odyssey.deaths", number_format(\xtcy\odysseyrealm\Loader::getSessionManager()->getSession($player)->getDeaths())),
            new ScoreTag("odyssey.shards", number_format(\xtcy\odysseyrealm\Loader::getSessionManager()->getSession($player)->getShards())),
            new ScoreTag("odyssey.level", number_format(\xtcy\odysseyrealm\Loader::getSessionManager()->getSession($player)->getLevel())),
            new ScoreTag("odyssey.xp", number_format($player->getXpManager()->getCurrentTotalXp()))
        ]))->call();
    }
    public static function itemSerialize(array $items): string
    {
        $serializedItems = [];
        foreach ($items as $item) {
            $serializedItems[] = (new BigEndianNbtSerializer())->write(new TreeRoot($item->nbtSerialize()));
        }

        return serialize($serializedItems);
    }

    public static function itemDeserialize(string $str): array
    {
        $serializedItems = unserialize($str);
        $deserializedItems = [];

        foreach ($serializedItems as $serializedItem) {
            $tag = (new BigEndianNbtSerializer())->read($serializedItem);
            if ($tag instanceof CompoundTag) {
                $deserializedItems[] = Item::nbtDeserialize($tag->mustGetCompoundTag());
            }
        }

        return $deserializedItems;
    }

    public static function extractSerializedItems(string $collectionData): array
    {
        $itemDataArray = [];
        $startIndex = 0;

        // Deserialize each item from its data string
        while ($startIndex < strlen($collectionData)) {
            // Find the length of the item's serialized data
            $itemLength = ord($collectionData[$startIndex]);

            // Extract the serialized data of the item
            $itemData = substr($collectionData, $startIndex + 1, $itemLength);

            // Add item data to the array
            $itemDataArray[] = $itemData;

            // Move to the next item in the collection
            $startIndex += $itemLength + 1;
        }

        return $itemDataArray;
    }


    public static function saveCollection(Player $player, CompoundTag $collectionInventoryTag): void {
        $file = Loader::getInstance()->getDataFolder() . "collection_data/" . $player->getUniqueId()->toString() . ".dat";

        file_put_contents($file, zlib_encode((new LittleEndianNbtSerializer())->write(new TreeRoot($collectionInventoryTag)), ZLIB_ENCODING_GZIP));
    }


    public static function getSavedInventory(Player $player): ?CompoundTag {
        $file = Loader::getInstance()->getDataFolder() . "collection_data/" . $player->getUniqueId()->toString() . ".dat";

        if (is_file($file)) {
            $decompressed = @zlib_decode(file_get_contents($file));
            return (new LittleEndianNbtSerializer())->read($decompressed)->mustGetCompoundTag();
        }

        return null;
    }

    public static function hasActiveSet(Player $player, string $setTag): bool {
        return isset(self::$activeSets[$player->getName()]) && self::$activeSets[$player->getName()] === $setTag;
    }

    public static function setPlayerActiveSet(Player $player, string $setTag): void {
        self::$activeSets[$player->getName()] = $setTag;
    }

    public static function removePlayerActiveSet(Player $player): void {
        unset(self::$activeSets[$player->getName()]);
    }

    public static function getPermissionLockedStatus(Player $player, string $permission) : string {
        if ($player->hasPermission($permission)) {
            $text = TextFormat::RESET . TextFormat::GREEN . TextFormat::BOLD . "UNLOCKED";
        } else {
            $text = TextFormat::RESET . TextFormat::RED . TextFormat::BOLD . "LOCKED";
        }

        return $text;
    }

    public static function getConfigReplace(PluginBase $loader, string $path, array|string $replace = [], array|string $replacer = []): string
    {
        $return = str_replace("{prefix}", self::getConfiguration($loader, "warpsData.json")->get("prefix"), self::getConfiguration($loader, "warpsData.json")->get($path));
        return str_replace($replace, $replacer, $return);
    }

    /**
     * @param Player $player
     */
    public static function bless(Player $player): void
    {
        $config = self::getConfiguration(Loader::getInstance(), "messages.yml");
        $player->sendMessage(TextFormat::colorize($config->getNested("bless.blessed", "&r&e&l(!) &r&eYou have been &r&e&l** BLESSED **")));
        foreach ($player->getEffects()->all() as $effect) {
            if ($effect->getType()->isBad()){
                $player->getEffects()->remove($effect->getType());
                $level = $effect->getAmplifier();
                $effectName = Server::getInstance()->getLanguage()->translate($effect->getType()->getName());
                $removedMsg = $config->getNested("bless.removed-effects", "&r&l&c[-] &r&7{effect}{level}");
                $removedMsg = str_replace(["{effect}", "{level}"], [$effectName, $level], $removedMsg);
                $player->sendMessage(TextFormat::colorize($removedMsg));
            }
        }
    }

    public static function hasActiveAbility(Player $player, string $ability): bool {
        return isset(CustomArmorListener::$activeAbilities[$player->getName()]) &&
            is_array(CustomArmorListener::$activeAbilities[$player->getName()]) &&
            in_array($ability, CustomArmorListener::$activeAbilities[$player->getName()]);
    }


    public static function getConfiguration(PluginBase $plugin, string $fileName): Config {
        $pluginFolder = $plugin->getDataFolder();
        $filePath = $pluginFolder . $fileName;

        $config = null;

        if (!file_exists($filePath)) {
            $plugin->getLogger()->warning("Configuration file '$fileName' not found.");
        } else {
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);

            switch ($extension) {
                case 'yml':
                case 'yaml':
                    $config = new Config($filePath, Config::YAML);
                    break;

                case 'json':
                    $config = new Config($filePath, Config::JSON);
                    break;

                default:
                    $plugin->getLogger()->warning("Unsupported configuration file format for '$fileName'.");
                    break;
            }
        }

        return $config;
    }

    public static function repairAllItems(Player $player): void
    {
        $inventory = $player->getInventory();
        $armorInventory = $player->getArmorInventory();
        foreach ($inventory->getContents() as $slot => $item) {
            if (!$item->isNull()) {
                if ($item instanceof Durable) {
                    $item->setDamage(0);
                    $inventory->setItem($slot, $item);
                }
            }
        }
        for ($slot = 0; $slot < 9; $slot++) {
            $item = $inventory->getItem($slot);
            if (!$item->isNull()) {
                if ($item instanceof Durable) {
                    $item->setDamage(0);
                    $inventory->setItem($slot, $item);
                }
            }
        }
        foreach ($armorInventory->getContents() as $slot => $item) {
            if (!$item->isNull()) {
                if ($item instanceof Durable) {
                    $item->setDamage(0);
                    $armorInventory->setItem($slot, $item);
                }
            }
        }
    }

    /**
     * @param Item $item
     * @return bool
     */
    public static function hasTag(Item $item, string $name, string $value = "true"): bool {
        $namedTag = $item->getNamedTag();
        if ($namedTag instanceof CompoundTag) {
            $tag = $namedTag->getTag($name);
            return $tag instanceof StringTag && $tag->getValue() === $value;
        }
        return false;
    }

    /**
     * Returns an online player whose name begins with or equals the given string (case insensitive).
     * The closest match will be returned, or null if there are no online matches.
     *
     * @param string $name The prefix or name to match.
     * @return Player|null The matched player or null if no match is found.
     */
    public static function getPlayerByPrefix(string $name): ?Player {
        $found = null;
        $name = strtolower($name);
        $delta = PHP_INT_MAX;

        /** @var Player[] $onlinePlayers */
        $onlinePlayers = Server::getInstance()->getOnlinePlayers();

        foreach ($onlinePlayers as $player) {
            if (stripos($player->getName(), $name) === 0) {
                $curDelta = strlen($player->getName()) - strlen($name);

                if ($curDelta < $delta) {
                    $found = $player;
                    $delta = $curDelta;
                }

                if ($curDelta === 0) {
                    break;
                }
            }
        }

        return $found;
    }

    /**
     * @param Entity $player
     * @param string $sound
     * @param int $volume
     * @param int $pitch
     * @param int $radius
     */
    public static function playSound(Entity $player, string $sound, $volume = 1, $pitch = 1, int $radius = 5): void
    {
        foreach ($player->getWorld()->getNearbyEntities($player->getBoundingBox()->expandedCopy($radius, $radius, $radius)) as $p) {
            if ($p instanceof Player) {
                if ($p->isOnline()) {
                    $spk = new PlaySoundPacket();
                    $spk->soundName = $sound;
                    $spk->x = $p->getLocation()->getX();
                    $spk->y = $p->getLocation()->getY();
                    $spk->z = $p->getLocation()->getZ();
                    $spk->volume = $volume;
                    $spk->pitch = $pitch;
                    $p->getNetworkSession()->sendDataPacket($spk);
                }
            }
        }
    }

    public static function isToolOrArmor(Item $item): bool
    {
        return $item instanceof Tool || $item instanceof Armor;
    }

    /**
     * @param Entity $player
     * @param string $particleName
     * @param int $radius
     */
    public static function spawnParticle(Entity $player, string $particleName, int $radius = 5): void {
        $packet = new SpawnParticleEffectPacket();
        $packet->particleName = $particleName;
        $packet->position = $player->getPosition()->asVector3();

        foreach ($player->getWorld()->getNearbyEntities($player->getBoundingBox()->expandedCopy($radius, $radius, $radius)) as $p) {
            if ($p instanceof Player) {
                if ($p->isOnline()) {
                    $p->getNetworkSession()->sendDataPacket($packet);
                }
            }
        }
    }

    public static function checkArmorActivation(Player $player, ArmorInventory $inventory, string $setTag): void {
        $config = self::getConfiguration(Loader::getInstance(), "customarmor.yml");
        $activationMessage = $config->getNested("sets.$setTag.activation");
        $deactivationMessage = $config->getNested("sets.$setTag.deactivation");

        $equippedPieces = self::getEquippedArmorPieces($inventory, $setTag);
        $currentCount = count($equippedPieces);

        switch ($setTag) {
            case "supreme":
                if ($currentCount === 4 && !self::hasActiveSet($player, "supreme")) {
                    $player->sendMessage(TextFormat::colorize(TextFormat::RESET . $activationMessage));
                    self::playSound($player, 'mob.bat.takeoff');
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 20 * 9999, 3, false));
                    self::setPlayerActiveSet($player, "supreme");
                } elseif ($currentCount < 4 && self::hasActiveSet($player, "supreme")) {
                    $player->sendMessage(TextFormat::colorize(TextFormat::RESET . $deactivationMessage));
                    $player->getEffects()->remove(VanillaEffects::SPEED());
                    self::playSound($player, 'armor.equip_generic');
                    self::removePlayerActiveSet($player);
                }
                break;
            case "phantom":
                if ($currentCount === 4 && !self::hasActiveSet($player, "phantom")) {
                    $player->sendMessage(TextFormat::colorize(TextFormat::RESET . $activationMessage));
                    self::playSound($player, 'mob.bat.takeoff');
                    self::setPlayerActiveSet($player, "phantom");
                } elseif ($currentCount < 4 && self::hasActiveSet($player, "phantom")) {
                    $player->sendMessage(TextFormat::colorize(TextFormat::RESET . $deactivationMessage));
                    self::playSound($player, 'armor.equip_generic');
                    self::removePlayerActiveSet($player);
                }
                break;
            case "ghoul":
                if ($currentCount === 4 && !self::hasActiveSet($player, "ghoul")) {
                    $player->sendMessage(TextFormat::colorize(TextFormat::RESET . $activationMessage));
                    self::playSound($player, 'mob.bat.takeoff');
                    self::setPlayerActiveSet($player, "ghoul");
                } elseif ($currentCount < 4 && self::hasActiveSet($player, "ghoul")) {
                    $player->sendMessage(TextFormat::colorize(TextFormat::RESET . $deactivationMessage));
                    self::playSound($player, 'armor.equip_generic');
                    self::removePlayerActiveSet($player);
                }
                break;
            case "titan":
                if ($currentCount === 4 && !self::hasActiveSet($player, "titan")) {
                    $player->sendMessage(TextFormat::colorize(TextFormat::RESET . $activationMessage));
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 20 * 9999, 2, false));
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 20 * 9999, 3, false));
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::SATURATION(), 20 * 9999, 1, false));
                    self::playSound($player, 'mob.bat.takeoff');
                    self::setPlayerActiveSet($player, "titan");
                } elseif ($currentCount < 4 && self::hasActiveSet($player, "titan")) {
                    $player->sendMessage(TextFormat::colorize(TextFormat::RESET . $deactivationMessage));
                    $player->getEffects()->remove(VanillaEffects::REGENERATION());
                    $player->getEffects()->remove(VanillaEffects::SPEED());
                    $player->getEffects()->remove(VanillaEffects::SATURATION());
                    self::playSound($player, 'armor.equip_generic');
                    self::removePlayerActiveSet($player);
                }
                break;
            case "ethereal_enforcer":
                if ($currentCount === 4 && !self::hasActiveSet($player, "ethereal_enforcer")) {
                    $player->sendMessage(TextFormat::colorize(TextFormat::RESET . $activationMessage));
                    self::playSound($player, 'mob.bat.takeoff');
                    self::setPlayerActiveSet($player, "ethereal_enforcer");
                } elseif ($currentCount < 4 && self::hasActiveSet($player, "ethereal_enforcer")) {
                    $player->sendMessage(TextFormat::colorize(TextFormat::RESET . $deactivationMessage));
                    self::playSound($player, 'armor.equip_generic');
                    self::removePlayerActiveSet($player);
                }
                break;
        }
    }

    public static function getEquippedArmorPieces(ArmorInventory $inventory, string $setTag): array
    {
        $equippedPieces = [];

        foreach ($inventory->getContents() as $item) {
            if ($item instanceof Item && self::hasTag($item, "customarmor", $setTag)) {
                $equippedPieces[] = $item;
            }
        }

        return $equippedPieces;
    }

    /**
     * ==============================================
     *   _____                              _
     *  / ____|                            (_)
     * | |     ___  _ ____   _____ _ __ ___ _  ___  _ __  ___
     * | |    / _ \| '_ \ \ / / _ \ '__/ __| |/ _ \| '_ \/ __|
     * | |___| (_) | | | \ V /  __/ |  \__ \ | (_) | | | \__ \
     *  \_____\___/|_| |_|\_/ \___|_|  |___/_|\___/|_| |_|___/
     *
     * ==============================================
     *
     * Under this are the conversions
     */

    /**
     * @param int $level
     * @return int
     */
    public static function getExpToLevelUp(int $level): int
    {
        if ($level <= 15) {
            return 2 * $level + 7;
        } else if ($level <= 30) {
            return 5 * $level - 38;
        } else {
            return 9 * $level - 158;
        }
    }

    public static function parseShorthandAmount($shorthand): float|int
    {
        $multipliers = [
            'k' => 1000,
            'm' => 1000000,
            'b' => 1000000000,
        ];
        $lastChar = strtolower(substr($shorthand, -1));
        if (isset($multipliers[$lastChar])) {
            $multiplier = $multipliers[$lastChar];
            $shorthand = substr($shorthand, 0, -1);
        } else {
            $multiplier = 1;
        }

        return intval($shorthand) * $multiplier;
    }

    public static function translateShorthand($amount): string
    {
        $multipliers = [
            1000000000 => 'b',
            1000000 => 'm',
            1000 => 'k',
        ];

        foreach ($multipliers as $multiplier => $shorthand) {
            if ($amount >= $multiplier) {
                $result = number_format($amount / $multiplier, 2) . $shorthand;
                return $result;
            }
        }

        return (string)$amount;
    }

    public static function translateTime(int $seconds): string
    {
        $timeUnits = [
            'w' => 60 * 60 * 24 * 7,
            'd' => 60 * 60 * 24,
            'h' => 60 * 60,
            'm' => 60,
            's' => 1,
        ];

        $parts = [];

        foreach ($timeUnits as $unit => $value) {
            if ($seconds >= $value) {
                $amount = floor($seconds / $value);
                $seconds %= $value;
                $parts[] = $amount . $unit;
            }
        }

        return implode(', ', $parts);
    }

    /**
     * @param int $integer
     * @return string
     */
    public static function getRomanNumeral(int $integer): string
    {
        $romanString = "";
        while ($integer > 0) {
            $romanNumeralConversionTable = [
                'M' => 1000,
                'CM' => 900,
                'D' => 500,
                'CD' => 400,
                'C' => 100,
                'XC' => 90,
                'L' => 50,
                'XL' => 40,
                'X' => 10,
                'IX' => 9,
                'V' => 5,
                'IV' => 4,
                'I' => 1
            ];
            foreach ($romanNumeralConversionTable as $rom => $arb) {
                if ($integer >= $arb) {
                    $integer -= $arb;
                    $romanString .= $rom;
                    break;
                }
            }
        }
        return $romanString;
    }

    public static function secondsToTicks(int $seconds) : int {
        return $seconds * 20;
    }

    /**
     * Fill the borders of the inventory with gray glass.
     *
     * @param Inventory $inventory
     */
    public static function fillBorders(Inventory $inventory, Item $glassType, array $excludedSlots = []): void
    {
        $size = $inventory->getSize();
        $rows = $size / 9; // Calculate the number of rows

        for ($row = 0; $row < $rows; $row++) {
            for ($col = 0; $col < 9; $col++) {
                $slot = $row * 9 + $col;

                if (!in_array($slot, $excludedSlots) && ($col === 0 || $col === 8 || $row === 0 || $row === $rows - 1)) {
                    $inventory->setItem($slot, clone $glassType->setCustomName("§r§8Ethereal Hub"));
                }
            }
        }
    }


    public static function fillInventory(Inventory $inventory, string $glassType, array $excludedSlots = []): void
    {
        $glass = StringToItemParser::getInstance()->parse($glassType);
        $size = $inventory->getSize();

        for ($slot = 0; $slot < $size; $slot++) {
            if (!in_array($slot, $excludedSlots)) {
                $inventory->setItem($slot, $glass->setCustomName(" "));
            }
        }
    }

    public static function fillSide(Inventory $inventory, string $glassType, string $side, array $excludedSlots = []): void
    {
        $glass = StringToItemParser::getInstance()->parse($glassType);
        $size = $inventory->getSize();

        switch ($side) {
            case "left":
                for ($row = 0; $row < $inventory->getSize() / 9; $row++) {
                    $leftBorderSlot = $row * 9;
                    if ($leftBorderSlot < $size && !in_array($leftBorderSlot, $excludedSlots)) {
                        $inventory->setItem($leftBorderSlot, $glass->setCustomName(" "));
                    }
                }
                break;

            case "right":
                for ($row = 0; $row < $inventory->getSize() / 9; $row++) {
                    $rightBorderSlot = ($row + 1) * 9 - 1;
                    if ($rightBorderSlot < $size && !in_array($rightBorderSlot, $excludedSlots)) {
                        $inventory->setItem($rightBorderSlot, $glass->setCustomName(" "));
                    }
                }
                break;

            case "top":
                for ($slot = 0; $slot < 9; $slot++) {
                    if ($slot < $size && !in_array($slot, $excludedSlots)) {
                        $inventory->setItem($slot, $glass->setCustomName(" "));
                    }
                }
                break;

            case "bottom":
                for ($slot = $size - 9; $slot < $size; $slot++) {
                    if ($slot >= 0 && !in_array($slot, $excludedSlots)) {
                        $inventory->setItem($slot, $glass->setCustomName(" "));
                    }
                }
                break;

            default:
                break;
        }
    }

}