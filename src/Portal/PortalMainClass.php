<?php
namespace Portal;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\entity\Entity;
use pocketmine\level\Position;
use pocketmine\utils\Config;

class PortalMainClass extends PluginBase implements Listener, CommandExecutor{
    private $temp;
	private $editmode;
	public $portals;

	public function onSpawn(PlayerRespawnEvent $event){
		//$event->getPlayer()->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
		//Server::getInstance()->broadcastMessage($event->getPlayer()->getDisplayName() . " has just spawned!");
	}
	
	public function onLoad(){
		//$this->getLogger()->info(TextFormat::WHITE . "[Portal] I've been loaded!");
	}

	public function onEnable(){
		$this->temp = array();
		$this->editmode = array();
		$this->portals = array();
		$this->loadPortals();
		$this->listener = new EventListener($this);
		$this->getServer()->getPluginManager()->registerEvents($this->listener, $this);
		//$this->getLogger()->info(TextFormat::DARK_GREEN . "[Portal] I've been enabled!");
	}

	public function onDisable(){
		//$this->getLogger()->info(TextFormat::DARK_RED . "[Portal] I've been disabled!");
	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $args){
		return $this->myCommand($sender, $args);
	}
	
	public function myCommand(CommandSender $sender, array $args){
		if($args[0] !== "del" and $args[0] !== "delete" and $args[0] !== "list"){
			if(!($sender instanceof Player)){
				$sender->sendMessage(TextFormat::RED."This command only works in-game.");
				return true;
			}
		}
		if(!isset($args[0])){
			$sender->sendMessage("Please input parameter.");
			$this->send_usage($sender);
			return true;
		}
		switch($args[0]){
			case "test":
				$sender->sendMessage(TextFormat::DARK_GREEN . "This is a test.");
				$sender->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
				return true;
			case "p1":
			case "point1":
				$this->temp["p1"] = new Position($sender->x, $sender->y, $sender->z, $sender->getLevel());
				$sender->sendMessage("Point1:");
				$sender->sendMessage((string)$sender);
				return true;
			case "p2":
			case "point2":
				$this->temp["p2"] = new Position($sender->x, $sender->y, $sender->z, $sender->getLevel());
				$sender->sendMessage("Point2:");
				$sender->sendMessage((string)$sender);
				return true;
			case "tar":
			case "target":
			case "des":
			case "destination":
				$this->temp["des"] = new Position($sender->x, $sender->y, $sender->z, $sender->getLevel());
				$sender->sendMessage("Destination:");
				$sender->sendMessage((string)$sender);
				return true;
			case "cre":
			case "create":
				if(!isset($args[1])){
					$sender->sendMessage("Please input parameter.");
					$this->send_usage($sender);
					return true;
				}
				if(isset($this->portals[$args[1]])){
					$sender->sendMessage("A portal already exists with this name.");
					return true;
				}
				$this->temp["name"] = $args[1];
				if(!isset($this->temp["des"])){
					$this->temp["des"] = new Position($sender->x, $sender->y, $sender->z, $sender->getLevel());
				}
				if(($incomplete = $this->check_input_complete()) == ""){
					$this->addPortal(new Portal($this->temp));
					$this->temp = array();
					$sender->sendMessage("Successfully create Portal:".$args[1]);
				}
				else{
					$sender->sendMessage("Parameter(s) incomplete.");
					$sender->sendMessage("Incomplete:".$incomplete);
				}
				return true;
			case "del":
			case "delete":
				if(!isset($args[1])){
					$sender->sendMessage("Please input parameter.");
					$this->send_usage($sender);
					return true;
				}
				if($this->deletePortal($args[1])){
					$sender->sendMessage("Successfully delete Portal:".$args[1]);
				}
				else{
					$sender->sendMessage("This portal isn't existed.");
				}
				return true;
			case "ed":
			case "edit":
				$id = $sender->getID();
				if($this->is_on_edit_mode($id)){
					$this->editmode[$id] = false;
					$sender->sendMessage("Deactivate edit mode.");
				}
				else{
					$this->editmode[$id] = true;
					$sender->sendMessage("Activate edit mode.");
				}
				return true;
			case "list":
				if(count($this->portals) <= 0){
					$sender->sendMessage(TextFormat::RED."It seems you haven't got any portals.");
					return true;
				}
				
				if(isset($args[1]) and (int) $args[1] > 1)$page = min(count($list), (int) $args[1]);
				else $page = 1;
				
				$message = TextFormat::RED."-".TextFormat::RESET." showing portals (Page ".$page."/".count($list).") -\n";
				$list = array_chunk($this->portals, 3, true)[($page - 1)];
				
				foreach($list as $name => $portal){
					$message .= TextFormat::DARK_GREEN."- ".$name.":\n".TextFormat::RESET;
					$message .= "First ".(string) $portal->p1."\n";
					$message .= "Second ".(string) $portal->p2."\n";
				}
				
				$sender->sendMessage($message);
				return true;
			default:
				$sender->sendMessage("Wrong parameter.");
				$this->send_usage($sender);
				return false;
		}
	}
	
	public function trigger(Entity $e){
		if($this->is_on_edit_mode($e->getID())){
			return;
		}
		foreach($this->portals as $p){
			if($p->inside($e)){
				$p->teleport($e);
				break;
			}
		}
	}
	
	public function is_on_edit_mode($id){
		if(!isset($this->editmode[$id])){
			return false;
		}
		return $this->editmode[$id];
	}
	
	private function loadPortals(){
		$path = $this->getDataFolder() . "portals/";
		if(!file_exists($path)){
			@mkdir($this->getDataFolder());
			@mkdir($path);
			return;
		}
		$handler = opendir($path);
		while(($filename = readdir($handler)) !== false){
			if($filename != "." && $filename != ".."){
				$data = new Config($path . $filename, Config::YAML);
				if(($pLevel = Server::getInstance()->getLevelByName($data->get("pointLevel"))) === null) continue;
				if(($dLevel = Server::getInstance()->getLevelByName($data->get("destinationLevel"))) === null) continue;
				$name = str_replace(".yml", "", $filename);
				$p1 = new Position($data->get("point1X"), $data->get("point1Y"), $data->get("point1Z"), $pLevel);
				$p2 = new Position($data->get("point2X"), $data->get("point2Y"), $data->get("point2Z"), $pLevel);
				$destination = new Position($data->get("destinationX"), $data->get("destinationY"), $data->get("destinationZ"), $dLevel);
				$this->portals[$name] = new Portal(array("name" => $name, "p1" => $p1, "p2" => $p2, "des" => $destination));
			}
		}
		closedir($handler);
	}
	
	private function check_input_complete(){
		$incomplete = "";
		if(!isset($this->temp["p1"])) $incomplete .= "point1";
		if(!isset($this->temp["p2"])) $incomplete .= " point2";
		return $incomplete;
	}

	private function addPortal(Portal $p){
		$this->portals[$p->name] = $p;
		$p->save($this->getDataFolder() . "portals/");
	}
	
	private function deletePortal($name){
		if(!isset($this->portals[$name])) return false;
		$this->portals[$name]->delete($this->getDataFolder() . "portals/");
		unset($this->portals[$name]);
		return true;
	}
	
	private function send_usage(CommandSender $sender){
		$sender->sendMessage("/por p1  set point1");
		$sender->sendMessage("/por p2  set point2");
		$sender->sendMessage("/por des  set destination");
		$sender->sendMessage("/por cre [name]  create a portal");
		$sender->sendMessage("/por del [name]  delete a portal");
		$sender->sendMessage("/por ed  activate edit mode");
	}
}
?>
