<?php
namespace Portal;

use pocketmine\math\Vector3 as Vector3;
use pocketmine\level\Position;
use pocketmine\entity\Entity;
use pocketmine\Server;
use pocketmine\utils\Config;

class Portal extends Area{
	public $name;
	public $destination;

	public function __construct(array $data){
		parent::__construct($data["p1"], $data["p2"]);
		$this->name = $data["name"];
		$this->destination = $data["des"];
	}

	public function save($path){
		$name = $this->name;
		$data = new Config($path . "$name.yml", Config::YAML);
		$data->set("pointLevel", $this->p1->getLevel()->getName());
		$data->set("point1X", $this->p1->x);
		$data->set("point1Y", $this->p1->y);
		$data->set("point1Z", $this->p1->z);
		$data->set("point2X", $this->p2->x);
		$data->set("point2Y", $this->p2->y);
		$data->set("point2Z", $this->p2->z);
		$data->set("destinationLevel", $this->destination->getLevel()->getName());
		$data->set("destinationX", $this->destination->x);
		$data->set("destinationY", $this->destination->y);
		$data->set("destinationZ", $this->destination->z);
		$data->save();
	}
	
	public function delete($path){
		$name = $this->name;
		@unlink($path . "$name.yml");
	}
	
	public function teleport(Entity $e){
		//$e->teleport($this->destination->getLevel()->getSafeSpawn());
		$e->teleport($this->destination);
	}
}
?>
