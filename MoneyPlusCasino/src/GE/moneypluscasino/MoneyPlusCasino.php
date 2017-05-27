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

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\block\Block;
use pocketmine\tile\Sign;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\scheduler\PluginTask;

use GE\moneypluscasino\YamlManager;

class MoneyPlusCasino extends PluginBase implements Listener{
	
	private $s;
	private $Mp;
	const Prefix = "§7[§bMPCasino§7]§f ";

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->Mp = $this->getServer()->getPluginManager()->getPlugin("MoneyPlusAPI");

		$this->s = new YamlManager($this);

		$this->unit = $this->Mp->getUnit();
	}

	public function Sign(SignChangeEvent $event){
		$line0 = $event->getLine(0);
		if($line0 == $this->s->getData("casinokeytxt")){
			$player = $event->getPlayer();
			if(!$player->isOp()){
				$player->sendMessage(MoneyPlusCasino::Prefix."§cあなたは権限者ではありません。");
				return true;

			}elseif(!is_numeric($event->getLine(1))){
				$player->sendMessage(MoneyPlusCasino::Prefix."§c正しい形式で記入してください。");
				return true;
			}

			$this->s->setSignData($event->getBlock(), $event);


			$event->setLine(0, "§7[§bMPCasino§7]");
			$event->setLine(1, "掛け金: ".$event->getLine(1)."".$this->unit."");
			$event->setLine(2, $event->getLine(2)); 
			$event->setLine(3, $event->getLine(3));

			$player->sendMessage(MoneyPlusCasino::Prefix."カジノを作成しました。");
		}
	}

	public function onBuy(PlayerInteractEvent $event){
		if($this->s->scanPosition($event->getBlock())){
			$data = $this->s->getSignData($event->getBlock());
			$player = $event->getPlayer();

			$money = $this->Mp->getMoney($player->getName());
			if($money < $data){
				$player->sendMessage(MoneyPlusCasino::Prefix."§6所持金が不足しています。");
				$event->setCancelled();
				return true;
			}

			if(!isset($this->re[$player->getName()])){
				$player->sendMessage(MoneyPlusCasino::Prefix."カジノを始めるにはもう一度タップ 掛け金: ".$data."".$this->unit);
				$this->re[$player->getName()] = true;
				$task = new Task($this, $player);
				$this->getServer()->getScheduler()->scheduleDelayedTask($task,20 * 2);

			}else{
				$this->Mp->takeMoney($player->getName(), $data);
				$num = mt_rand(1, 100);
				unset($this->re[$player->getName()]);
				if($num <= $this->s->getData("percentage")){
					if($num == 1){
						$result = $data * $this->s->getData("special");
						$player->sendMessage(MoneyPlusCasino::Prefix."おめでとうございます! 特別賞に当選いたしました!!".$data." → ".$result);
						$this->Mp->addMoney($player->getName(), $result);
						return true;
					}
					$result = $data * $this->s->getData("normal");
					$player->sendMessage(MoneyPlusCasino::Prefix."おめでとうございます! 当選いたしました!!".$data." → ".$result);
					$this->Mp->addMoney($player->getName(), $result);
					return true;
				}else{
					$player->sendMessage(MoneyPlusCasino::Prefix."ハズレ... また挑戦してね!");
				}
			}
		}
	}

	public function onBreak(BlockBreakEvent $event){
		if($this->s->scanPosition($event->getBlock())){
			$player = $event->getPlayer();
			if(!$player->isOp()){
				$player->sendMessage(MoneyPlusCasino::Prefix."§cあなたはカジノを破壊する権限を持っていません。");
				$event->setCancelled();
				return false;
			}
			$this->s->delSignData($event->getBlock());
			$player->sendMessage(MoneyPlusCasino::Prefix."§aカジノを撤去しました。");
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
