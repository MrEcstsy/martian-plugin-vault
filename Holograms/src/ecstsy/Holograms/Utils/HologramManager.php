<?php

namespace ecstsy\Holograms\Utils;

use ecstsy\Holograms\Loader;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddItemActorPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\Config;

class HologramManager {

    public static array $holograms = [];
    public static int $entityIdCounter = 1000; 
    public static Config $hologramConfig;

    public const RESPAWN_TIME = 600; // 5 minutes in seconds

    public static function init(Config $config): void {
        self::$hologramConfig = $config;
        self::loadHolograms();
        self::scheduleHologramRespawn();
    }

    public static function createHologram(Player $player, Item $item): void {
        $entityId = self::$entityIdCounter++;
        
        $location = $player->getLocation();
        $location->y += 1.5;

        self::sendItemPacket($player, $entityId, $item, $location);

        self::$holograms[$entityId] = [
            'owner' => $player->getName(),
            'item' => $item,
            'location' => $location,
            'creation_time' => $data['creation_time'] ?? time()
        ];

        self::saveHolograms(); 
    }

    public static function sendItemPacket(Player $player, int $entityId, Item $item, Vector3 $location): void {
        $network = $player->getNetworkSession();
        $pk = new AddItemActorPacket();
        $pk->actorUniqueId = $entityId;
        $pk->actorRuntimeId = $entityId;
        
        $pk->item = ItemStackWrapper::legacy($network->getTypeConverter()->coreItemStackToNet($item));
    
        $pk->position = $location;
        $pk->motion = new Vector3(0, 0, 0);  
    
        $network->sendDataPacket($pk);
    }

    public static function removeHologram(Player $player, int $entityId): bool {
        if (isset(self::$holograms[$entityId])) {
            unset(self::$holograms[$entityId]);
            self::saveHolograms();
            return true;
        }
        return false;
    }

    public static function loadHolograms(): void {
        $hologramData = self::$hologramConfig->getAll();
        
        foreach ($hologramData as $entityId => $data) {
            $item = self::deserializeItem($data['item']);

            if ($item === null) {
                unset(self::$holograms[$entityId]);
                continue; 
            }

            $location = new Vector3($data['location']['x'], $data['location']['y'], $data['location']['z']);
            $player = Server::getInstance()->getPlayerExact($data['owner']);

            if ($player !== null) {
                self::sendItemPacket($player, (int)$entityId, $item, $location);
            }

            self::$holograms[$entityId] = [
                'owner' => $data['owner'],
                'item' => $item,
                'location' => $location,
                'creation_time' => $data['creation_time'] ?? time()

            ];
        }
    }

    public static function saveHolograms(): void {
        $hologramData = [];

        foreach (self::$holograms as $entityId => $hologram) {
            $hologramData[$entityId] = [
                'owner' => $hologram['owner'],
                'item' => self::serializeItem($hologram['item']),
                'location' => [
                    'x' => $hologram['location']->x,
                    'y' => $hologram['location']->y,
                    'z' => $hologram['location']->z
                ],
                'creation_time' => $hologram['creation_time']
            ];
        }

        self::$hologramConfig->setAll($hologramData);
        self::$hologramConfig->save();
    }

    public static function serializeItem(Item $item): array {
        return [
            'name' => $item->getName(),
            'count' => $item->getCount(),
        ];
    }

    public static function deserializeItem(array $data): ?Item {
        $itemName = $data['name'];
        $item = StringToItemParser::getInstance()->parse($data['name']); 

        if ($item === null) {
            error_log("Failed to parse item: $itemName");
            return null;
        }

        $item->setCount($data['count']);
        return $item;
    }

    private static function scheduleHologramRespawn(): void {
        Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new class extends Task {
            public function onRun(): void {
                foreach (HologramManager::$holograms as $entityId => $hologram) {
                    if (time() - $hologram['creation_time'] > HologramManager::RESPAWN_TIME) {
                        $player = Loader::getInstance()->getServer()->getPlayerExact($hologram['owner']);
                        if ($player !== null) {
                            HologramManager::sendItemPacket($player, $entityId, $hologram['item'], $hologram['location']);
                        }
                        HologramManager::$holograms[$entityId]['creation_time'] = time();
                        HologramManager::saveHolograms();
                    }
                }
            }
        }, 1200); 
    }

    public static function getHolograms(): array {
        return self::$holograms;
    }
}
