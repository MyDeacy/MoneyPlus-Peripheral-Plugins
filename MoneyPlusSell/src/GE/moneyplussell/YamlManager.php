<?php

/*                                                                                 

___  ___                      ______ _           
|  \/  |                      | ___ \ |          
| .  . | ___  _ __   ___ _   _| |_/ / |_   _ ___ 
| |\/| |/ _ \| '_ \ / _ \ | | |  __/| | | | / __|
| |  | | (_) | | | |  __/ |_| | |   | | |_| \__ \
\_|  |_/\___/|_| |_|\___|\__, \_|   |_|\__,_|___/
                          __/ |                  
                         |___/                   
by gigantessbeta[みやりん]
*/
namespace GE\moneyplussell;

use GE\moneyplussell\MoneyPlusSell;

use pocketmine\scheduler\PluginTask;
use pocketmine\math\Vector3;
use pocketmine\block\Block;
use pocketmine\level\level;
use pocketmine\Server;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\event\Listener;

class YamlManager implements Listener{

	protected $m;
	public $sell;
	public $c;

	public function __construct(MoneyPlusSell $m){
		$this->m = $m;

		if(!file_exists($this->m->getDataFolder())){
			mkdir($this->m->getDataFolder(), 0744, true);
		}

		$this->sell = new Config($this->m->getDataFolder() . "sells.yml", Config::YAML);
		$this->c = new Config($this->m->getDataFolder() . "config.yml", Config::YAML, array(
			'sellkeytxt' => 'mpsell',
			'no-creative' => 'false'
			));
	}

	public function getData(String $key){
		return $this->c->get($key);
	}

	public function setSignData(Block $block, $event){
		$postxt = (Int)$block->getX().":".(Int)$block->getY().":".(Int)$block->getZ().":".$block->getLevel()->getFolderName();
		$this->sell->set($postxt, array(
			'price' => $event->getLine(1),
			'idmeta' => $event->getLine(2),
			'amount'=> $event->getLine(3)
			));

		$this->sell->save();

		return true;
	}

	public function scanPosition(Block $block){
		$postxt = (Int)$block->getX().":".(Int)$block->getY().":".(Int)$block->getZ().":".$block->getLevel()->getFolderName();
		return $this->sell->exists($postxt);
	}

	public function getSignData(Block $block){
		$postxt = (Int)$block->getX().":".(Int)$block->getY().":".(Int)$block->getZ().":".$block->getLevel()->getFolderName();
		return array($this->sell->get($postxt)["price"], $this->sell->get($postxt)["idmeta"], $this->sell->get($postxt)["amount"]);
	}

	public function delSignData(Block $block){
		$postxt = (Int)$block->getX().":".(Int)$block->getY().":".(Int)$block->getZ().":".$block->getLevel()->getFolderName();
		$this->sell->remove($postxt);
		$this->sell->save();
	}

	
}
	

