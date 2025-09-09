<?php

declare(strict_types=1);

namespace wockkinmycup\utilitycore\items\custom;

use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use src\Vecnavium\SkyBlocksPM\player\Player;

final class BankNoteItem extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier, string $name = "Bank Note")
    {
        parent::__construct($identifier, $name);

        $this->initComponent("wallet");
        $this->setupRenderOffsets(32, 32, false);
    }


    public function prepare(?Player $player = null, ?int $amount = null): void
    {
        $signer = "Console";
        $randAmount = rand(1, 10000000);
        $this->getNamedTag()->setInt("banknote", $amount);
        $tag = $this->getNamedTag()->getInt("banknote");

        if($player !== null){
            $signer = $player->getName();
        }

        if($amount !== null){
            $randAmount = $amount;
        }

        $this->setCustomName("§r§b§lBank Note §r§7(Right-Click)");

        $this->setLore(array(
            "§r§dValue §r§f$" . number_format($tag),
            "§r§dSigner §r§f$signer"
        ));

        \xtcy\odysseyrealm\Loader::getSessionManager()->getSession($player)->subtractBalance($tag);
    }

}