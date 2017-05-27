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
namespace GE\moneypluscasino;

use GE\moneypluscasino\MoneyPlusCasino;

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
	public $casino;
	public $c;

	public function __construct(MoneyPlusCasino $m){
		$this->m = $m;

		if(!file_exists($this->m->getDataFolder())){
			mkdir($this->m->getDataFolder(), 0744, true);
		}

		$this->casino = new Config($this->m->getDataFolder() . "casinos.yml", Config::YAML);
		$this->c = new Config($this->m->getDataFolder() . "config.yml", Config::YAML, array(
			'casinokeytxt' => 'mpcasino',
			'percentage' => '10',
			'normal' => '2',
			'special' => '4'
			));
	}

	public function getData(String $key){
		return $this->c->get($key);
	}

	public function setSignData(Block $block, $event){
		$postxt = (Int)$block->getX().":".(Int)$block->getY().":".(Int)$block->getZ().":".$block->getLevel()->getFolderName();
		$this->casino->set($postxt, array(
			'price' => $event->getLine(1),
			));

		$this->casino->save();

		return true;
	}

	public function scanPosition(Block $block){
		$postxt = (Int)$block->getX().":".(Int)$block->getY().":".(Int)$block->getZ().":".$block->getLevel()->getFolderName();
		return $this->casino->exists($postxt);
	}

	public function getSignData(Block $block){
		$postxt = (Int)$block->getX().":".(Int)$block->getY().":".(Int)$block->getZ().":".$block->getLevel()->getFolderName();
		return $this->casino->get($postxt)["price"];
	}

	public function delSignData(Block $block){
		$postxt = (Int)$block->getX().":".(Int)$block->getY().":".(Int)$block->getZ().":".$block->getLevel()->getFolderName();
		$this->casino->remove($postxt);
		$this->casino->save();
	}

	
}
	

