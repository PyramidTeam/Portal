<?php
namespace Portal;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\command\Command;

class EventListener implements Listener{
	private $plugin;
	
	public function __construct(PortalMainClass $plugin){
		$this->plugin = $plugin;
	}
	
	public function onPlayerMove(PlayerMoveEvent $event){
		$this->plugin->trigger($event->getEntity());
	}

	public function onBlockBreak(BlockBreakEvent $event){
		$id = $event->getPlayer()->getID();
		if($this->plugin->is_on_edit_mode($id)){
			$this->plugin->myCommand($event->getPlayer(), array("p2"));
			return false;
		}
		return true;
		//Server::getInstance()->broadcastMessage("A block was broken.");
	}

	public function onBlockPlace(BlockPlaceEvent $event){
		$id = $event->getPlayer()->getID();
		if($this->plugin->is_on_edit_mode($id)){
			$this->plugin->myCommand($event->getPlayer(), array("p1"));
			return false;
		}
		return true;
		//Server::getInstance()->broadcastMessage("A block was placed.");
	}
}
?>
