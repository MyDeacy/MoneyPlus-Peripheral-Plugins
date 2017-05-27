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

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\utils\Config;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\block\Block;
use pocketmine\tile\Sign;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\scheduler\PluginTask;

use GE\moneyplusshop\YamlManager;

class MoneyPlusShop extends PluginBase implements Listener{
	
	private $c;
	private $config;
	private $Mp;
	const Prefix = "§7[§bMPShop§7]§f ";

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->Mp = $this->getServer()->getPluginManager()->getPlugin("MoneyPlusAPI");

		$this->s = new YamlManager($this);

		$this->unit = $this->Mp->getUnit();
	}

	public function Sign(SignChangeEvent $event){
		$line0 = $event->getLine(0);
		if($line0 == $this->s->getData("shopkeytxt")){
			$player = $event->getPlayer();
			if(!$player->isOp()){
				$player->sendMessage(MoneyPlusShop::Prefix."§cあなたは権限者ではありません。");
				return true;

			}elseif(!is_numeric($event->getLine(1)) || !is_numeric($event->getLine(3))){
				$player->sendMessage(MoneyPlusShop::Prefix."§c正しい形式で記入してください。");
				return true;
			}

			$this->s->setSignData($event->getBlock(), $event);

			$itemName = Item::fromString($event->getLine(2))->getName();
			$event->setLine(0, "§7[§bMPShop§7]");
			$event->setLine(1, "価格: ".$event->getLine(1)."".$this->unit."");
			$event->setLine(2, "商品: ".$itemName); 
			$event->setLine(3, "個数: ".$event->getLine(3));

			$player->sendMessage(MoneyPlusShop::Prefix."SHOPを作成しました。");
		}
	}

	public function onBuy(PlayerInteractEvent $event){
		if($this->s->scanPosition($event->getBlock())){
			$data = $this->s->getSignData($event->getBlock());
			$player = $event->getPlayer();
			if($this->s->getData("no-creative") != false){
				if($player->getGamemode() == 1){
					$player->sendMessage(MoneyPlusShop::Prefix."§cクリエイティブモードでは商品を買えません。");
					return true;
				}
			}
			$money = $this->Mp->getMoney($player->getName());
			if($money < $data[0]){
				$player->sendMessage(MoneyPlusShop::Prefix."§6所持金が不足しています。");
				$event->setCancelled();
				return true;
			}
			$item = Item::fromString($data[1]);
			if(!isset($this->re[$player->getName()])){
				$player->sendMessage(MoneyPlusShop::Prefix."購入するにはもう一度タップ 商品:".$item->getName()." 値段:".$data[0]."".$this->unit."");
				$this->re[$player->getName()] = true;
				$task = new Task($this, $player);
				$this->getServer()->getScheduler()->scheduleDelayedTask($task,20 * 2);

			}else{
				$player->sendMessage(MoneyPlusShop::Prefix."購入しました!  商品:".$item->getName()." 値段:".$data[0]."".$this->unit."");
				$this->Mp->takeMoney($player->getName(), $data[0]);
				$player->getInventory()->addItem(new Item($item->getId(), $item->getDamage(), $data[2]));
				unset($this->re[$player->getName()]);

			}
		}
	}

	public function onBreak(BlockBreakEvent $event){
		if($this->s->scanPosition($event->getBlock())){
			$player = $event->getPlayer();
			if(!$player->isOp()){
				$player->sendMessage(MoneyPlusShop::Prefix."§cあなたはSHOPを破壊する権限を持っていません。");
				$event->setCancelled();
				return false;
			}
			$this->s->delSignData($event->getBlock());
			$player->sendMessage(MoneyPlusShop::Prefix."§aSHOPを撤去しました。");
		}
	}


}

class Task extends PluginTask{
	public function __construct(PluginBase $owner,Player $player){
		parent::__construct($owner);
		$this->player = $player->getName();
		$this->owner = $owner;
	}
	public function onRun($tick){
		unset($this->owner->re[$this->player]);
	}
}