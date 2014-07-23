<?php

namespace Portal;

use pocketmine\math\Vector3 as Vector3;
use pocketmine\level\Position;
use pocketmine\Server;

class Area{
	public $p1;
	public $p2;
	
	public function __construct(Position $p1, Position $p2){
		$this->p1 = $p1;
		$this->p2 = $p2;
	}
	
	public function inside(Position $p){
		if($p->getLevel()->getName() == $this->p1->getLevel()->getName()){
			return ($this->between($this->p1->x, $p->x, $this->p2->x) and $this->between($this->p1->y, $p->y, $this->p2->y) and $this->between($this->p1->z, $p->z, $this->p2->z));
		}
		else{
			return false;
		}
	}
	
	public function between($l, $m, $r){
		$lm = abs($l - $m);
		$rm = abs($r - $m);
		$lrm = $lm + $rm;
		$lr = abs($l - $r);
		//Server::getInstance()->broadcastMessage("lrm:".$lrm." lr:".$lr);
		return ($lrm <= $lr);
	}
	
	public function __toString(){
		return (string)$this->p1.(string)$this->p2;
	}
}
?>
