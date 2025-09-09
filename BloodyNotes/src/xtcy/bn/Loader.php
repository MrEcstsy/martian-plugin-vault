<?php

namespace xtcy\bn;

use pocketmine\plugin\PluginBase;
use xtcy\bn\command\NoteCommand;
use xtcy\bn\listener\EventListener;
use xtcy\bn\utils\libs\CortexPE\Commando\exception\HookAlreadyRegistered;
use xtcy\bn\utils\libs\CortexPE\Commando\PacketHooker;

class Loader extends PluginBase {

    public static Loader $instance;

    public function onLoad(): void
    {
        self::$instance = $this;
    }

    /**
     * @throws HookAlreadyRegistered
     */
    public function onEnable(): void
    {
        if(!PacketHooker::isRegistered()) {
            PacketHooker::register($this);
        }
        $this->saveResource("messages.yml");
        $this->saveDefaultConfig();
        $this->getServer()->getCommandMap()->register("bloodynotes", new NoteCommand($this, "bloodynotes", "Base command for bloody notes plugin", ["bn"]));
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
    }

    public static function getInstance(): Loader {
        return self::$instance;
    }
}