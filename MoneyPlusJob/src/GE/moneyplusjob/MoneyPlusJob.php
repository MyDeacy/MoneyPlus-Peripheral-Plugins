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

namespace GE\moneyplusjob;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;


class MoneyPlusJob extends PluginBase implements Listener{
	
	private $c;
	private $config;
	private $MP;
	const Prefix = "§7[§bMPJob§7]§f ";

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$this->Mp = $this->getServer()->getPluginManager()->getPlugin("MoneyPlusAPI");

		if(!file_exists($this->getDataFolder())){
			mkdir($this->getDataFolder(), 0744, true);
		}

		$this->config = new Config($this->getDataFolder() . "players.yml", Config::YAML);
		$this->c = new Config($this->getDataFolder() . "jobs.yml", Config::YAML, array(
			'miner' => array(
				'4:0' => '2',
				'1:0' => '5',
				'description' => '石堀です。'
			),
			'digger' => array(
				'2:0' => '3',
				'3:0' => '2',
				'description' => '土堀です。'
				)
		));


	}

	public function getMoney(BlockBreakEvent $event){
		$player = $event->getPlayer();

		if($this->config->exists($player->getName())){
			$data1 = $this->config->get($player->getName());

			if($this->c->exists($data1)){
				$block = $event->getBlock();
				$data = $this->c->get($data1);
				if(isset($data[$block->getId().":".$block->getDamage()])){

					$price = $data[$block->getId().":".$block->getDamage()];
					$this->Mp->addMoney($player->getName(), $price);
				}
			}
		}
	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $args){
		switch(strtolower($command->getName())){
			case "mjob":
				if(!isset($args[0])){
					$sender->sendMessage(MoneyPlusJob::Prefix."コマンドヘルプ");
					$sender->sendMessage("/mjob register {職業} ... 職業に就く\n/mjob unregister ... 職業やめてニートになります。\n/mjob check ... 自分の職業確認\n/mjob list ... 職業リストを表示\n/mjob de {職業} ... 職業の説明を表示");
					return true;
				}
				
				switch($args[0]){

					case "register":
						if(!$this->config->exists($sender->getName())){
							if(!isset($args[1])){
								$sender->sendMessage(MoneyPlusJob::Prefix."職業を入力してください。");
								return true;
							}

							if($this->c->exists($args[1])){
								$sender->sendMessage(MoneyPlusJob::Prefix."以下の職業に就きました! \n => ".$args[1]);
									$this->config->set($sender->getName(), $args[1]);
									$this->config->save();
							}else{
								$sender->sendMessage(MoneyPlusJob::Prefix."そのような職業は存在しません。 /mjob list");
							}
						}else{
							$sender->sendMessage(MoneyPlusJob::Prefix."あなたは既に職業に就いています。 /mjob check");
						}
						return true;
						break;

						case "unregister":
							$name = $sender->getName();
							if($this->config->exists($name)){
								$sender->sendMessage(MoneyPlusJob::Prefix."職業を辞めました。 ");
									$this->config->remove($name);
									$this->config->save();
							}else{
							$sender->sendMessage(MoneyPlusJob::Prefix."あなたは職業に就いていません。 /mjob check");
						}
						return true;
						break;


					case "check":
						if($this->config->exists($sender->getName())){
							$job = $this->config->get($sender->getName());
							$sender->sendMessage(MoneyPlusJob::Prefix."あなたは以下のjobに就いています。\n => ".$job);
						}else{
								$sender->sendMessage(MoneyPlusJob::Prefix."あなたはまだ職業に就いていません。 /mjob list");
						}
						return true;
						break;

					case "list":
						$data = $this->c->getAll();
						$sender->sendMessage(MoneyPlusJob::Prefix."職業リスト");
						foreach($data as $result => $a){
							$sender->sendMessage("".$result."");

						}
						return true;
						break;

					case "de":
						if(!isset($args[1])){
								$sender->sendMessage(MoneyPlusJob::Prefix."職業を入力してください。");
							return true;
						}

						if($this->c->exists($args[1])){
							$job = $this->c->get($args[1]);
							if($job["description"] !== null){
								$sender->sendMessage("職業: ".$args[1]."\n説明: ".$job["description"]);
							}else{
								$sender->sendMessage("職業: ".$args[1]."\n説明: なし");
							}
						}else{
							$sender->sendMessage(MoneyPlusJob::Prefix."そのような職業は存在しません。 /mjob list");
						}
						return true;
						break;

					default:
						$sender->sendMessage(MoneyPlusJob::Prefix."コマンドヘルプ");
						$sender->sendMessage("/mjob register {職業} ... 職業に就く\n/mjob unregister ... 職業やめてニートになります。\n/mjob check ... 自分の職業確認\n/mjob list ... 職業リストを表示\n/mjob de {職業} ... 職業の説明を表示");
						return true;
						break;

				}

		}
	}
}