<?php
namespace Portal;

use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use pocketmine\Server;
use pocketmine\plugin\Plugin;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\entity\Entity;

class PortalTriggerPluginTask extends PluginTask{
	private $portals = array();
	private $entities = array();
	
	public function __construct(Plugin $owner, array $portals = array()){
		parent::__construct($owner);
		$this->portals = $portals;
	}
	
	public function addEntity(Entity $e){
		$this->entities[] = $e;
	}

	public function addPortal(Portal $portal){
		$this->portals[] = $portal;
	}
	
	public function onRun($currentTick){
		//Server::getInstance()->broadcastMessage("[ExamplePlugin] I've ran on tick " . $currentTick);
		$entity = array_shift($this->entities);
		if($entity instanceof Entity){
			foreach($this->portals as $portal){
				if($portal->inside($entity)){
					$this->owner->getLogger()->info((string)$portal);
					//$portal->teleport($entity);
					return;
				}
			}
		}
	}
}
?>
