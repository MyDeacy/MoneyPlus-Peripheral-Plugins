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
namespace GE\moneyplusshop;

use GE\moneyplusshop\MoneyPlusShop;

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
	public $shop;
	public $c;

	public function __construct(MoneyPlusShop $m){
		$this->m = $m;

		if(!file_exists($this->m->getDataFolder())){
			mkdir($this->m->getDataFolder(), 0744, true);
		}

		$this->shop = new Config($this->m->getDataFolder() . "shops.yml", Config::YAML);
		$this->c = new Config($this->m->getDataFolder() . "config.yml", Config::YAML, array(
			'shopkeytxt' => 'mpshop',
			'no-creative' => 'false'
			));
	}

	public function getData(String $key){
		return $this->c->get($key);
	}

	public function setSignData(Block $block, $event){
		$postxt = (Int)$block->getX().":".(Int)$block->getY().":".(Int)$block->getZ().":".$block->getLevel()->getFolderName();
		$this->shop->set($postxt, array(
			'price' => $event->getLine(1),
			'idmeta' => $event->getLine(2),
			'amount'=> $event->getLine(3)
			));

		$this->shop->save();

		return true;
	}

	public function scanPosition(Block $block){
		$postxt = (Int)$block->getX().":".(Int)$block->getY().":".(Int)$block->getZ().":".$block->getLevel()->getFolderName();
		return $this->shop->exists($postxt);
	}

	public function getSignData(Block $block){
		$postxt = (Int)$block->getX().":".(Int)$block->getY().":".(Int)$block->getZ().":".$block->getLevel()->getFolderName();
		return array($this->shop->get($postxt)["price"], $this->shop->get($postxt)["idmeta"], $this->shop->get($postxt)["amount"]);
	}

	public function delSignData(Block $block){
		$postxt = (Int)$block->getX().":".(Int)$block->getY().":".(Int)$block->getZ().":".$block->getLevel()->getFolderName();
		$this->shop->remove($postxt);
		$this->shop->save();
	}

	
}
	

