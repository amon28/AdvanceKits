<?php
namespace AdvanceKits;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\item\Item;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\inventory\PlayerInventory;
use pocketmine\inventory\BaseInventory;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat as C;
use pocketmine\utils\Config;
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\ModalForm;
use jojoe77777\FormAPI\CustomForm;

class Main extends PluginBase implements Listener{
    
    public $form;
    public $form1;
    public $List;
    public $d;
    public $list = [];
    public $kname;
    public $p;
    
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this,$this);
        
        if(!is_dir($this->getDataFolder())){
			@mkdir($this->getDataFolder());
		}
		if(!file_exists($this->getDataFolder() . "config.yml")){
			$this->saveDefaultConfig();
		}
		//sample config format
		/**$this->world = $this->getConfig()->get("World", "prk1");
		$this->minutes = $this->getConfig()->get("Minutes", 5);**/
				
    }
    
    public function onCommand(CommandSender $sender, Command $cmd, string $label,array $args) : bool {
    if(($cmd->getName()) == "kits"){
    if(!$sender instanceOf Player){
    return true;    
    }  
    //On use command Form
    $this->p = $sender;
    $this->kmenu();
    }
    return true;
    }
    
    public function kmenu(){
    $this->form = new SimpleForm(function (Player $sender, $data){
        if($data === null){
            return true;
        }
        $this->form1 = $this->form;
        if($sender->isOp()){
//op
   switch($data){
    case 0:
      $this->add();
        break;
    case 1:
      $this->del();
        break;
    case 2:
      $this->get();
        break;
   } 
        }else{
//not op   
   switch($data){
    case 0:
       //send to simple form with kit list
       $this->klist();
       
        break;
   }     
        }
   
    });
    $this->form->setTitle("Kits Menu");
    if($this->p->isOp()){
    $this->form->addButton("Add Kit"); 
    $this->form->addButton("Delete kit");
    }
    $this->form->addButton("Get Kits");
    $this->p->sendForm($this->form);
    }
    
    public function del(){
    $this->form = new SimpleForm(function (Player $sender, $data){
        if($data === null){
            return true;
        }
  $cf = new Config($this->getDataFolder() . "config.yml");
  $s = count($cf->get("name"));
  $this->d = $data-1;
  $cf = new Config($this->getDataFolder() . "config.yml");
  $nm = $cf->get("name");
  $this->form1 = $this->form;
  if($this->d == -1){
  $sender->sendForm($this->form);   
  }else{
  if($data == ($s+1)){
  $this->kmenu();
  }else{
  $this->del2(strval($nm[$this->d]));
  }
  }
  
    });
    $cf = new Config($this->getDataFolder() . "config.yml");
    $nm = $cf->get("name");
    $this->form->addButton(C::RED.C::UNDERLINE."Delete A Kit",0,"textures/blocks/barrier");
    foreach($nm as $n){
    $this->form->addButton($n);   
    }
    $this->form->addButton("Back",0,"textures/ui/arrow_dark_left_stretch");
    $this->p->sendForm($this->form);
    }
    
    public function del2(String $nm2){
    $this->form = new ModalForm(function (Player $sender, $data){ 
    if($data === null){
        return true;
    }
     switch($data){
         case 0:
          $sender->sendForm($this->form1);
             break;
             
             default:
         $cf = new Config($this->getDataFolder() . "config.yml");
  $nm = $cf->get("name");
  $nm1 = $cf->get($nm[$this->d]);
	   $ds = $cf->getall();
	   unset($ds[$nm[$this->d]]);
	   $cf->setAll($ds);
	   unset($nm[$this->d]);
	   $cf->set("name",$nm);
	   $cf->save();
	   $sender->sendMessage(C::GREEN.C::UNDERLINE."Kit Deleted");
	   $this->del();
         
     }
    });
   $this->form->setTitle("Kit Deletion");
   $this->form->setContent(C::RED.C::UNDERLINE."Are You Sure You Want To Delete Kit ".$nm2);
   $this->form->setButton1("Yes");
   $this->form->setButton2("No");
   $this->p->sendForm($this->form);
    }
    
    public function add(){
    $this->form = new CustomForm(function (Player $sender, $data){
        if($data === null){
            return true;
        }
   
        $cf = new Config($this->getDataFolder() . "config.yml");
      if($cf->get($data[1]) == null){
      $nm = $cf->get("name");
      $item = explode(" ",$data[2]);
      array_push($nm,$data[1]);
      $cf->set("name",$nm);
      $cf->set($data[1],$item);
      $cf->save();
      $sender->sendMessage(C::GREEN.C::UNDERLINE."Kit Created!");
   }else{
      if($cf->get("name") == "name"){
        $sender->sendMessage(C::RED.C::UNDERLINE."Unable to use that name");
      }
   $sender->sendMessage(C::RED.C::UNDERLINE."Another kit with that name");  
   } 
   
    });
    $this->form->setTitle("Add Kit");
    $this->form->addLabel(C::YELLOW.C::UNDERLINE."Adding Items: ID,META,DAMAGE,COUNT \nex.\n1,0,64 2,0,64");
    $this->form->addInput("Kit Name:","Awesome Kit");
    $this->form->addInput("Items:","1,0,64");
    $this->p->sendForm($this->form);
    }
    
    
    public function get(){
    $this->form = new SimpleForm(function (Player $sender, $data){
        if($data === null){
            return true;
        }
   $cf = new Config($this->getDataFolder() . "config.yml");
    $name = $cf->get("name");
   if($data != 0){
   if($data != (count($name))+1){
   switch($data){
    case -1:
    $this->p->sendForm($this->form);
        break;
        default:
        $list = $cf->get("name");
        $this->kname = $list[$data-1];
        $info = $cf->get($list[($data-1)]);
        $this->List = $info;
        $this->smodal();
   } 
   }else{
   $this->kmenu();   
   }
   }else{
   $sender->sendForm($this->form);   
   }
   
    });
    $cf = new Config($this->getDataFolder() . "config.yml");
    $name = $cf->get("name");
    $this->form->setTitle("Kit List");
    $this->form->addButton("Choose A Kit",0,"textures/items/diamond");
    foreach($name as $n){
    $this->form->addButton($n);
    }
    $this->form->addButton("Back",0,"textures/ui/arrow_dark_left_stretch");
    $this->form1 = $this->form;
    $this->p->sendForm($this->form);
    }
    
    public function smodal(){
    $this->form = new ModalForm(function (Player $sender, $data){
        if($data === null){
            return true;
        }
   switch($data){
    case 0:
      
       $sender->sendForm($this->form1);
       $this->list = [];
        break;
        default:
        $cf = new Config($this->getDataFolder() . "config.yml");
        $kit = $this->List;
        
        foreach($kit as $item){
        $i = explode(",",$item);
        $sender->getPlayer()->getInventory()->addItem(Item::get(((int)$i[0]),((int)$i[1]),((int)$i[2])));
        $this->list = [];
        }
        
   } 
    });
    $cf = new Config($this->getDataFolder() . "config.yml");
        $kit = $this->List;
        
        foreach($kit as $item){
        $i = explode(",",$item);
        
        $s = (Item::get((int)$i[0],(int)$i[1],(int)$i[2])->getName());
	  array_push($this->list,$s." x".$i[2]);
        }
	  
    $this->form->setTitle("Viewing Kit: ".$this->kname);
    $this->form->setContent(C::YELLOW.C::UNDERLINE."- ".implode("\n- ", $this->list));
    $this->form->setButton1("Get");
    //triggers case 0
    $this->form->setButton2("Back");
    $this->p->sendForm($this->form);
    
}

    
    public function onDisable(){
     $this->getLogger()->info("§cOffline");
    }
}
