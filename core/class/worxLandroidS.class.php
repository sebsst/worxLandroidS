<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class worxLandroidS extends eqLogic {
  public static $_client;
  public static $_client_pub;	

  public static function health() {
    $return = array();
    $socket = socket_create(AF_INET, SOCK_STREAM, 0);
    $server = socket_connect ($socket , config::byKey('mqtt_endpoint', 'worxLandroidS', '127.0.0.1'), '8883');
    $return[] = array(
      'test' => __('Mosquitto', __FILE__),
      'result' => ($server) ? __('OK', __FILE__) : __('NOK', __FILE__),
      'advice' => ($server) ? '' : __('Indique si Mosquitto est disponible', __FILE__),
      'state' => $server,
    );
    return $return;
  }
	
	

//     * Fonction exécutée automatiquement toutes les heures par Jeedom
  public static function cron30() {
	  
       // $elogics = array();
        foreach (eqLogic::byType('worxLandroidS', false) as $eqpt) {
		if ($eqpt->getIsEnable() == true){
		    $i = date('w');
	            $start = $eqpt->getCmd(null, 'Planning/startTime/' . $i);
                    $startTime = is_object($start) ? $start->execCmd() : '';
                    $dur = $eqpt->getCmd(null, 'Planning/duration/' . $i);	
                    $duration = is_object($dur) ? $dur->execCmd() : '';         
	           
	            $initDate = DateTime::createFromFormat('H:i', $startTime);
		    $initDate->add(new DateInterval("PT".$duration."M")); 
		    $endTime = $initDate->format("H:i");
	// refresh value each hours if mower is sleeping at home :-)
		  //  if($startTime == '00:00' or $starTime > date('H:i') or date('H:i') > $endTime) {
			
		       $mosqId = config::byKey('mqtt_client_id', 'worxLandroidS') . '' . $id . '' . substr(md5(rand()), 0, 8);
                       $client = new Mosquitto\Client($mosqId);
                       self::connect_and_publish($client, '{}');	 
			
		  //  }
		
		}	  
	 }
  }
     	


  public static function deamon_info() {
    $return = array();
    $return['log'] = '';
    $return['state'] = 'nok';
    $cron = cron::byClassAndFunction('worxLandroidS', 'daemon');
    if (is_object($cron) && $cron->running()) {
      $return['state'] = 'ok';
    }
    $dependancy_info = self::dependancy_info();
    if ($dependancy_info['state'] == 'ok') {
      $return['launchable'] = 'ok';
    }
    return $return;
  }

	
    public static function deamon_start($_debug = false) {
    self::deamon_stop();
    $deamon_info = self::deamon_info();
    if ($deamon_info['launchable'] != 'ok') {
      throw new Exception(__('Veuillez vérifier la configuration', __FILE__));
    }
    $cron = cron::byClassAndFunction('worxLandroidS', 'daemon');
    if (!is_object($cron)) {
      throw new Exception(__('Tache cron introuvable', __FILE__));
    }
    $cron->run();
  }

  public static function deamon_stop() {
    $cron = cron::byClassAndFunction('worxLandroidS', 'daemon');
    if (!is_object($cron)) {
      throw new Exception(__('Tache cron introuvable', __FILE__));
    }
    $cron->halt();
  }

  public static function dependancy_info() {
    $return = array();
    $return['log'] = 'worxLandroidS_dep';
    $return['state'] = 'nok';
    $cmd = "dpkg -l | grep mosquitto";
    exec($cmd, $output, $return_var);
    //lib PHP exist
    $libphp = extension_loaded('mosquitto');
    if ($output[0] != "" && $libphp) {
      $return['state'] = 'ok';
    }
    return $return;
  }

  public static function dependancy_install() {
    log::add('worxLandroidS','info','Installation des dépéndances');
    $resource_path = realpath(dirname(__FILE__) . '/../../resources');



    passthru('sudo /bin/bash ' . $resource_path . '/install.sh ' . $resource_path . ' > ' . log::getPathToLog('worxLandroidS_dep') . ' 2>&1 &');
    return true;
  }

  public static function daemon() {

      
	  
      $resource_path = realpath(dirname(__FILE__) . '/../../resources/');

      $certfile = $resource_path.'/cert.pem';
      $pkeyfile = $resource_path.'/pkey.pem';
      $root_ca = $resource_path.'/vs-ca.pem';

 // log::add('worxLandroidS', 'info', 'client id: ' . config::byKey('mqtt_client_id', 'worxLandroidS'));


// init first connection
    if(config::byKey('initCloud', 'worxLandroidS') ==  true){


    log::add('worxLandroidS', 'info', 'Paramètres utilisés, Host : ' . config::byKey('worxLandroidSAdress', 'worxLandroidS', '127.0.0.1') . ', Port : ' . config::byKey('worxLandroidSPort', 'worxLandroidS', '1883') . ', ID : ' . config::byKey('worxLandroidSId', 'worxLandroidS', 'Jeedom'));


      $email = config::byKey('email', 'worxLandroidS');
      $passwd = config::byKey('passwd', 'worxLandroidS');
      // get mqtt config
      $url =  "https://api.worxlandroid.com:443/api/v1/users/auth";

      $token = "qiJNz3waS4I99FPvTaPt2C2R46WXYdhw";
      $content = "application/json";
      $ch = curl_init();
      $data = array("email" => $email, "password" => $passwd, "uuid" => "uuid/v1" , "type"=> "app" , "platform"=> "android");
      $data_string = json_encode($data);

      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data_string),
        'x-auth-token:' . $token
      )
    );



        $result = curl_exec($ch);
        log::add('worxLandroidS', 'info', 'Connexion result :'.$result);
        $json = json_decode($result,true);
        if (is_null($json))
        {
          log::add('worxLandroidS', 'info', 'Connexion KO for '.$equipement.' ('.$ip.')');
		
			event::add('jeedom::alert', array(
				'level' => 'warning',
				'page' => 'worxLandroidS',
				'message' => __('Données de connexion incorrectes', __FILE__),	
					));
          //$this->checkAndUpdateCmd('communicationStatus',false);
          //return false;
        } else
        {
   
          // get certificate
          $url =  "https://api.worxlandroid.com:443/api/v1/users/certificate";
          $api_token = $json['api_token'];
          $token = $json['api_token'];
   
          $content = "application/json";
          $ch = curl_init($url);
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'x-auth-token:' . $api_token
          )
        );

        $result = curl_exec($ch);
        log::add('worxLandroidS', 'info', 'Connexion result :'.$result);

        $json2 = json_decode($result,true);


        if (is_null($json2))
        {
        } else
        {
          $pkcs12 = base64_decode($json2['pkcs12']);
          openssl_pkcs12_read( $pkcs12, $certs, "" );
          file_put_contents($certfile, $certs['cert']);
          file_put_contents($pkeyfile, $certs['pkey']);

          // get product item (mac address)
          $url =  "https://api.worxlandroid.com:443/api/v1/product-items";

          $content = "application/json";
          $ch = curl_init($url);
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'x-auth-token:' . $api_token
          )
        );

        $result = curl_exec($ch);
        log::add('worxLandroidS', 'info', 'Connexion result :'.$result);

        $json3 = json_decode($result,true);


        if (is_null($json3))
        {
        } else
        {

          config::save('mac_address', $json3[0]['mac_address'],'worxLandroidS');
	  config::save('landroid_name', $json3[0]['name'],'worxLandroidS');
          log::add('worxLandroidS', 'info', 'mac_address '.$json3[0]['mac_address']);
        }


        // test client2
        config::save('mqtt_client_id', $json['mqtt_client_id'],'worxLandroidS');
        config::save('mqtt_endpoint', $json['mqtt_endpoint'],'worxLandroidS');
      //  log::add('worxLandroidS', 'info', 'mqtt_client_id '.$json['mqtt_endpoint']);



}

}


}

        $elogics = array();
        foreach (eqLogic::byType('worxLandroidS', false) as $eqpt) {
		if ($eqpt->getIsEnable() == true){
                $elogics[] = $eqpt;}
        }
	  

       //log::add('worxLandroidS', 'info', 'mqtt_endpoint '.$root_ca);
 if(config::byKey('initCloud', 'worxLandroidS') ==  true || empty($elogics) == false ){
        
	 if ( empty($elogics) == true or config::byKey('initCloud', 'worxLandroidS') ==  true ) {
           $mosqId = config::byKey('mqtt_client_id', 'worxLandroidS') . '' . $id . '' . substr(md5(rand()), 0, 8);
           $client = new Mosquitto\Client($mosqId);
           self::connect_and_publish($client, '{}');	
           config::save('initCloud', 0 ,'worxLandroidS');
	 } else
	 {
	 
	 $elogics = array();
	 
	 
        foreach (eqLogic::byType('worxLandroidS', false) as $eqpt) {
		if ($eqpt->getIsEnable() == true){
		    $i = date('w');
	        $start = $eqpt->getCmd(null, 'Planning/startTime/' . $i);
            $startTime = is_object($start) ? $start->execCmd() : '';
            $dur = $eqpt->getCmd(null, 'Planning/duration/' . $i);	
            $duration = is_object($dur) ? $dur->execCmd() : '';         
	        //log::add('worxLandroidS', 'debug', 'starttime' . $startTime);
	        $initDate = DateTime::createFromFormat('H:i', $startTime);
		    $initDate->add(new DateInterval("PT".$duration."M")); 
		    $endTime = $initDate->format("H:i");
	// refresh value each hours if mower is sleeping at home :-)
		    if($startTime != '00:00' && $starTime <= date('H:i') && date('H:i') <= $endTime) {			
		       $mosqId = config::byKey('mqtt_client_id', 'worxLandroidS') . '' . $id . '' . substr(md5(rand()), 0, 8);
                       $client = new Mosquitto\Client($mosqId);
                       self::connect_and_publish($client, '{}');	 
			
		    }
		
		}
	   }	
	 }
	 

    }


  }

  public static function connect_and_publish($client, $msg) {
      $resource_path = realpath(dirname(__FILE__) . '/../../resources/');

      $certfile = $resource_path.'/cert.pem';
      $pkeyfile = $resource_path.'/pkey.pem';
      $root_ca = $resource_path.'/vs-ca.pem';	  
    self::$_client = $client;
    self::$_client->clearWill();
    self::$_client->onConnect('worxLandroidS::connect');
    self::$_client->onDisconnect('worxLandroidS::disconnect');
    self::$_client->onSubscribe('worxLandroidS::subscribe');
    self::$_client->onMessage('worxLandroidS::message');
    self::$_client->onLog('worxLandroidS::logmq');
    self::$_client->setTlsCertificates($root_ca,$certfile,$pkeyfile,null);
      try {
         $topic = 'DB510/'.config::byKey('mac_address','worxLandroidS').'/commandOut';
         self::$_client->setWill("DB510/".config::byKey('mac_address','worxLandroidS')."/commandIn", $msg, 0, 0);
         self::$_client->connect(config::byKey('mqtt_endpoint', 'worxLandroidS'), 8883 , 5);
         self::$_client->subscribe($topic, 0); // !auto: Subscribe to root topic
	   log::add('worxLandroidS', 'debug', 'Subscribe to mqtt ' . config::byKey('mqtt_endpoint', 'worxLandroidS') . ' msg ' . $msg);
    //self::$_client->loop();  
    self::$_client->publish("DB510/".config::byKey('mac_address','worxLandroidS')."/commandIn", $msg, 0, 0);
      //self::$_client->loopForever();
      while (true) { self::$_client->loop(1);		   }
      }
       catch (Exception $e){
      // log::add('worxLandroidS', 'debug', $e->getMessage());
     } 
    
  }
	
	

  public static function connect( $r, $message ) {
    log::add('worxLandroidS', 'debug', 'Connexion à Mosquitto avec code ' . $r . ' ' . $message);
    config::save('status', '1',  'worxLandroidS');
  }
	
  public static function newconnect( $r, $message ) {
    log::add('worxLandroidS', 'debug', 'New Connexion à Mosquitto avec code ' . $r . ' ' . $message);
    config::save('status', '1',  'worxLandroidS');
  }	

  public static function disconnect( $r ) {
    log::add('worxLandroidS', 'debug', 'Déconnexion de Mosquitto avec code ' . $r);
    config::save('status', '0',  'worxLandroidS');
  }

  public static function subscribe( ) {
    log::add('worxLandroidS', 'debug', 'Subscribe to topics');
  }
	
  public static function logmq( $code, $str ) {
    if (strpos($str,'PINGREQ') === false && strpos($str,'PINGRESP') === false) {
      log::add('worxLandroidS', 'debug', $code . ' : ' . $str);
    }
  }

  public static function message($message) {
    //self::$_client->exitloop();
    //self::$_client->unsubscribe($message->topic);
    self::$_client->disconnect();  

  //  if(isset(self::$_client_pub){ self::$_client_pub->disconnect(); }
   // unset(self::$_client);	  
    log::add('worxLandroidS', 'debug', 'Message ' . $message->payload . ' sur ' . $message->topic);
    if (is_string($message->payload) && is_array(json_decode($message->payload, true)) && (json_last_error() == JSON_ERROR_NONE)) {
      //json message
      $nodeid = $message->topic;
      $value = $message->payload;
      $json2_data = json_decode($value);

      $type = 'json';
      log::add('worxLandroidS', 'debug', 'Message json : ' . $value . ' pour information sur : ' . $nodeid);
    } else {
      $topicArray = explode("/", $message->topic);
      $cmdId = end($topicArray);
      $key = count($topicArray) - 1;
      unset($topicArray[$key]);
      $nodeid = implode($topicArray,'/');
      $value = $message->payload;
      $type = 'topic';
      log::add('worxLandroidS', 'debug', 'Message texte : ' . $value . ' pour information : ' . $cmdId . ' sur : ' . $nodeid);
    }
//config::save('landroid_name', $json3[0]['name'],'worxLandroidS');


    $elogic = self::byLogicalId($nodeid, 'worxLandroidS');
    if (!is_object($elogic)) {
      $elogic = new worxLandroidS();
      $elogic->setEqType_name('worxLandroidS');
      $elogic->setLogicalId($nodeid);
      $elogic->setName(config::byKey('landroid_name', 'worxLandroidS', 'LandroidS'));
      //$elogic->setName('LandroidS-'. $json2_data->dat->mac);
      //$elogic->setConfiguration('topic', $nodeid);
      //$elogic->setConfiguration('type', $type);
// ajout des actions par défaut
      log::add('worxLandroidS', 'info', 'Saving device ' . $nodeid);
      
	    // Advise the desktop page (javascript) that a new equipment has been addedv

      $elogic->save();

      $elogic->setDisplay("width","450px");
      $elogic->setDisplay("height","250px");	    
      $elogic->setIsVisible(1);
      $elogic->setIsEnable(1);	    
      $elogic->checkAndUpdateCmd();
      $commandIn = 'DB510/'. $json2_data->dat->mac .'/commandIn';
      self::newAction($elogic,'setRainDelay', $commandIn, '{"rd":"#message#"}','message');
      self::newAction($elogic,'start',$commandIn,array(cmd=>1),'other');
      self::newAction($elogic,'stop',$commandIn,array(cmd=>3),'other');
      self::newAction($elogic,'refreshValue',$commandIn,"",'other');
      self::newAction($elogic,'off_today',$commandIn,"off_today",'other');
      self::newAction($elogic,'on_today',$commandIn,"on_today",'other');

	for ($i = 0; $i < 7; $i++) {
         self::newAction($elogic,'on_'.$i,$commandIn,'on_'.$i,'other');
         self::newAction($elogic,'off_'.$i,$commandIn,'off_'.$i,'other');
	}      
	    
	    
	    
      event::add('worxLandroidS::includeEqpt', $elogic->getId());
	    

    }
    $elogic->setStatus('lastCommunication', date('Y-m-d H:i:s'));
    $elogic->save();

    if ($type == 'topic') {
    $cmdlogic = worxLandroidSCmd::byEqLogicIdAndLogicalId($elogic->getId(),$cmdId);
    if (!is_object($cmdlogic)) {
      log::add('worxLandroidS', 'info', '1Cmdlogic n existe pas, creation');
      $cmdlogic = new worxLandroidSCmd();
      $cmdlogic->setEqLogic_id($elogic->getId());
      $cmdlogic->setEqType('worxLandroidS');
      $cmdlogic->setSubType('string');
      $cmdlogic->setLogicalId($cmdId);
      $cmdlogic->setType('info');
      $cmdlogic->setName( $cmdId );
      $cmdlogic->setConfiguration('topic', $message->topic);
      $cmdlogic->save();
    }
    $elogic->checkAndUpdateCmd($cmdId,$value);

  } else {
      // payload is json
      $json = json_decode($value, true);


/*
cfg->lg language: string;
cfg->dt dateTime: moment.Moment;
dat->mac macAddress: string;
dat->fw firmware: string;
dat->rsi wifiQuality: number;
active: boolean;
cfg->rd rainDelay: number;
timeExtension: number;
cfg->sn serialNumber: string;
dat->st->wt totalTime: number;
dat->st->d totalDistance: number;
dat->st->b totalBladeTime: number;
dat->bt->nr batteryChargeCycle: number;
dat->bt->c batteryCharging: boolean;
dat->bt->v batteryVoltage: number;
dat->bt->t batteryTemperature: number;
dat->bt->p batteryLevel: number;
dat->le errorCode: number;
errorDescription: string;
dat->ls statusCode: number;
statusDescription: string;
schedule: TimePeriod[];


        0: "Idle",
        1: "Home",
        2: "Start sequence",
        3: "Leaving home",
        4: "Follow wire",
        5: "Searching home",
        6: "Searching wire",
        7: "Mowing",
        8: "Lifted",
        9: "Trapped",
        10: "Blade blocked",
        11: "Debug",
        12: "Remote control",
        30: "Going home",
        32: "Cutting edge"
    };

    public static ERROR_CODES = {
        0: "No error",
        1: "Trapped",
        2: "Lifted",
        3: "Wire missing",
        4: "Outside wire",
        5: "Rain delay",
        6: "Close door to mow",
        7: "Close door to go home",
        8: "Blade motor blocked",
        9: "Wheel motor blocked",
        10: "Trapped timeout",
        11: "Upside down",
        12: "Battery low",
        13: "Reverse wire",
        14: "Charge error",
        15: "Timeout finding home"

*/

//        log::add('worxLandroidS', 'Debug', 'Langue : ' . $json2_data->cfg->lg. ' pour information : ' . $cmdId);
//        log::add('worxLandroidS', 'Debug', ' : ' . $json2_data->cfg->sc->m. ' pour information : ' . $cmdId);

        self::newInfo($elogic,'errorCode',$json2_data->dat->le,'string',1);
        self::newInfo($elogic,'errorDescription',self::getErrorDescription($json2_data->dat->le),'string',1);
        self::newInfo($elogic,'statusCode',$json2_data->dat->ls,'string',1);
        self::newInfo($elogic,'statusDescription',self::getStatusDescription($json2_data->dat->ls),'string',1);
        self::newInfo($elogic,'batteryLevel',$json2_data->dat->bt->p,'numeric',1);
        self::newInfo($elogic,'langue',$json2_data->cfg->lg,'string',0);

        self::newInfo($elogic,'lastDate',$json2_data->cfg->dt,'string',1);
        self::newInfo($elogic,'lastTime',$json2_data->cfg->tm,'string',1);

        self::newInfo($elogic,'firmware',$json2_data->dat->fw,'string',0);
        self::newInfo($elogic,'wifiQuality',$json2_data->dat->rsi,'string',0);
        self::newInfo($elogic,'rainDelay',$json2_data->cfg->rd,'string',1);

        self::newInfo($elogic,'totalTime',$json2_data->dat->st->wt,'string',0);
        self::newInfo($elogic,'totalDistance',$json2_data->dat->st->d,'string',0);
        self::newInfo($elogic,'totalBladeTime',$json2_data->dat->st->b,'string',0);
        self::newInfo($elogic,'batteryChargeCycle',$json2_data->dat->bt->nr,'string',0);
        self::newInfo($elogic,'batteryCharging',$json2_data->dat->bt->c,'string',0);
        self::newInfo($elogic,'batteryVoltage',$json2_data->dat->bt->v,'string',0);
        self::newInfo($elogic,'batteryTemperature',$json2_data->dat->bt->t,'string',0);


//        self::getStatusDescription($json2_data->dat->ls);

      //  date début + durée + bordure

	for ($i = 0; $i < 7; $i++) {
         self::newInfo($elogic,'Planning/startTime/'.$i,$json2_data->cfg->sc->d[$i][0],'string',1);
         self::newInfo($elogic,'Planning/duration/'.$i,$json2_data->cfg->sc->d[$i][1],'string',1);
         self::newInfo($elogic,'Planning/cutEdge/'.$i,$json2_data->cfg->sc->d[$i][2],'string',1);
	}
	    /*
        self::newInfo($elogic,'Planning/Monday/Starttime',$json2_data->cfg->sc->d[1][0],'string',1);
        self::newInfo($elogic,'Planning/Tuesday/Starttime',$json2_data->cfg->sc->d[2][0],'string',1);
        self::newInfo($elogic,'Planning/wednesday/Starttime',$json2_data->cfg->sc->d[3][0],'string',1);
        self::newInfo($elogic,'Planning/Thursday/Starttime',$json2_data->cfg->sc->d[4][0],'string',1);
        self::newInfo($elogic,'Planning/Friday/Starttime',$json2_data->cfg->sc->d[5][0],'string',1);
        self::newInfo($elogic,'Planning/Saturday/Starttime',$json2_data->cfg->sc->d[6][0],'string',1);
  */
    }
	  
	  $elogic->save();
	  $elogic->refreshWidget();
	  
  }


  public static function getErrorDescription($errorcode)
  {


    switch ($errorcode) {

	/*
      case '0': return 'No error';         break;
      case '1': return  'Trapped';         break;
      case '2': return  'Lifted';         break;
      case '3': return  'Wire missing';         break;
      case '4': return  'Outside wire';        break;
      case '5': return  'Rain delay';  break;
      case '6': return  'Close door to mow';        break;
      case '7': return  'Close door to go home';    break;
      case '8': return  'Blade motor blocked';       break;
      case '9': return  'Wheel motor blocked';       break;
      case '10': return  'Trapped timeout';         break;
      case '11': return  'Upside down';         break;
      case '12': return  'Battery low';         break;
      case '13': return  'Reverse wire';         break;
      case '14': return  'Charge error';         break;
      case '15': return  'Timeout finding home';        break;


      default: return 'Unknown';
      
      */
      case '0': return __('Aucune erreur',__FILE__);         break;
      case '1': return  __('Bloquée',__FILE__);         break;
      case '2': return  __('Soulevée',__FILE__);         break;
      case '3': return  __('Câble non trouvé',__FILE__);         break;
      case '4': return  __('En dehors des limites',__FILE__);        break;
      case '5': return  __('Délai pluie',__FILE__);  break;
      case '6': return  'Close door to mow';        break;
      case '7': return  'Close door to go home';    break;
      case '8': return  __('Moteur lames bloqué',__FILE__);       break;
      case '9': return  __('Moteur roues bloqué',__FILE__);       break;
      case '10': return  __('Timeout après blocage',__FILE__);         break;
      case '11': return  __('Renversée',__FILE__);         break;
      case '12': return  __('Batterie faible',__FILE__);         break;
      case '13': return  __('Câble inversé',__FILE__);         break;
      case '14': return  __('Erreur charge batterie',__FILE__);         break;
      case '15': return  __('Delai recherche station dépassé',__FILE__);        break;


      default: return 'Unknown';		    
		    
		    
        // code...
        break;
    }


  }

  public static function getStatusDescription($statuscode)
  {

    switch ($statuscode) {

      case '0': return __("Inactive",__FILE__);       break;
      case '1': return __("Sur la base",__FILE__);      break;
      case '2': return __("Séquence de démarrage",__FILE__);       break;
      case '3': return __("Quitte la base",__FILE__); break;
      case '4': return __("Suit le câble",__FILE__); break;
      case '5': return __("Recherche de la base",__FILE__); break;
      case '6': return __("Recherche du câble",__FILE__); break;
      case '7': return __("En cours de tonte",__FILE__); break;
      case '8': return __("Soulevée",__FILE__); break;
      case '9': return __("Coincée",__FILE__); break;
      case '10': return __("Lames bloquées",__FILE__); break;
      case '11': return "Debug"; break;
      case '12': return __("Remote control",__FILE__); break;
      case '30': return __("Retour à la base",__FILE__); break;
      case '32': return __("Coupe la bordure",__FILE__); break;

      default: return 'unkown';
        // code...
        break;
    }


  }



  public static function newInfo($elogic,$cmdId,$value,$subtype,$visible){
    $cmdlogic = worxLandroidSCmd::byEqLogicIdAndLogicalId($elogic->getId(),$cmdId);

    if (!is_object($cmdlogic)) {
      log::add('worxLandroidS', 'info', 'Cmdlogic n existe pas, creation');
      $cmdlogic = new worxLandroidSCmd();
      $cmdlogic->setEqLogic_id($elogic->getId());
      $cmdlogic->setEqType('worxLandroidS');
      $cmdlogic->setSubType($subtype);
      $cmdlogic->setLogicalId($cmdId);
      $cmdlogic->setType('info');
      $cmdlogic->setName( $cmdId );
      $cmdlogic->setIsVisible($visible);
		
	    
      $cmdlogic->setConfiguration('topic', $value);
      //$cmdlogic->setValue($value);
      $cmdlogic->save();
    }
	  
	  
    //log::add('worxLandroidS', 'debug', 'Cmdlogic update'.$cmdId.$value);

	  if(strstr($cmdId,"Planning/startTime") && $value != '00:00' ){
   // log::add('worxLandroidS', 'debug', 'savedValue time'. $value);
      $cmdlogic->setConfiguration('savedValue', $value);
      $cmdlogic->save();
      }
      if(strstr($cmdId,"Planning/duration") && $value != 0 ){
    //log::add('worxLandroidS', 'debug', 'savedValue duration'. $value);
	$cmdlogic->setConfiguration('savedValue', $value);
        $cmdlogic->save();

      }
      $cmdlogic->setConfiguration('topic', $value);
      //$cmdlogic->setValue($value);
      $cmdlogic->save();
	  
    $elogic->checkAndUpdateCmd($cmdId,$value);
	  


  }

    public static function newAction($elogic,$cmdId,$topic,$payload,$subtype){
      $cmdlogic = worxLandroidSCmd::byEqLogicIdAndLogicalId($elogic->getId(),$cmdId);

      if (!is_object($cmdlogic)) {
        log::add('worxLandroidS', 'info', 'nouvelle action par défaut'. $payload);
        $cmdlogic = new worxLandroidSCmd();
        $cmdlogic->setEqLogic_id($elogic->getId());
        $cmdlogic->setEqType('worxLandroidS');
        $cmdlogic->setSubType($subtype);
        $cmdlogic->setLogicalId($cmdId);
        $cmdlogic->setType('action');
        $cmdlogic->setName( $cmdId );
        $cmdlogic->setConfiguration('topic', $topic);
        $cmdlogic->setConfiguration('request', $payload);

        //$cmdlogic->setValue($value);
        $cmdlogic->save();
      }
//      log::add('worxLandroidS', 'debug', 'Cmdlogic update'.$cmdId.$value);

      $elogic->checkAndUpdateCmd($cmdId,$value);


    }
	
  public static function getSavedDaySchedule($_id,$i) {	
	 $cmdlogic = worxLandroidSCmd::byEqLogicIdCmdName($_id,'Planning/startTime/'.$i);
	 $day[0] = $cmdlogic->getConfiguration('savedValue', '10:00');
 
	  $cmdlogic = worxLandroidSCmd::byEqLogicIdCmdName($_id,'Planning/duration/'.$i);	
	 $day[1] = intval($cmdlogic->getConfiguration('savedValue', 420));		
	 $cmdlogic = worxLandroidSCmd::byEqLogicIdCmdName($_id,'Planning/cutEdge/'.$i);		
	 $day[2] = intval($cmdlogic->getConfiguration('topic', 0));	
	
         return $day;
  }
  public static function getSchedule($_id) {	
	$schedule = array();
	  
        $day = array();
	for ($i = 0; $i < 7; $i++) {

	 $cmdlogic = worxLandroidSCmd::byEqLogicIdCmdName($_id,'Planning/startTime/'.$i);
	 $day[0] = $cmdlogic->getConfiguration('topic', '10:00');
         $cmdlogic = worxLandroidSCmd::byEqLogicIdCmdName($_id,'Planning/duration/'.$i);	
	 $day[1] = intval($cmdlogic->getConfiguration('topic', 420));		
	 $cmdlogic = worxLandroidSCmd::byEqLogicIdCmdName($_id,'Planning/cutEdge/'.$i);		
	 $day[2] = intval($cmdlogic->getConfiguration('topic', 0));	
	
         $schedule[$i] = $day;
	}
	return $schedule;
	  
  }

  public static function setSchedule($_id, $schedule) {	
  	  $_message = '{"sc":'.json_encode(array('d'=>$schedule))."}";
  	 log::add('worxLandroidS', 'debug', 'message à publier' . $_message);	  
	  worxLandroidS::publishMosquitto($_id, "DB510/".$_id->getConfiguration('mac_address','worxLandroidS')."/commandIn", $_message, 0);
  }	
	

  public static function setDaySchedule($_id, $daynumber, $daySchedule) {	
          $schedule = array();
 	 // $elogic = self::byLogicalId($nodeid, 'worxLandroidS');	  
	  $schedule = worxLandroidS::getSchedule($_id);
	  $daySchedule[2] = $schedule[intval($daynumber)][2];
	  $schedule[intval($daynumber)] = $daySchedule;
	  $_message = '{"sc":'.json_encode(array('d'=>$schedule))."}" ;
	  return $_message ;
	//  worxLandroidS::setSchedule($eqlogic, $schedule);
  
	
  }
	
  public static function publishMosquitto($_id, $_subject, $_message, $_retain) {

    $resource_path = realpath(dirname(__FILE__) . '/../../resources/');

    $certfile = $resource_path.'/cert.pem';
    $pkeyfile = $resource_path.'/pkey.pem';
    $root_ca = $resource_path.'/vs-ca.pem';

// save schedule if setting to 0 - and retrieve from saved value (new values must be set from smartphone
      $cmd = worxLandroidSCmd::byId($_id);
     log::add('worxLandroidS', 'debug', 'Publication du message ' . $mosqId . ' '. $cmd->getName() . ' ' . $_message);
      $eqlogicid = $cmd->getEqLogic_id();
      $eqlogic = $cmd->getEqLogic();  
	  
      if(substr_compare($cmd->getName(),'off', 0, 3)==0){
        log::add('worxLandroidS', 'debug', 'Envoi du message OFF: ' . $_message);
	if($cmd->getName() == 'off_today'){
		$_message = 'off_' . date('w');
	}

        $sched = array('00:00', 0, 1);
	$_message = self::setDaySchedule($eqlogicid, substr($_message,4,1), $sched);//  $this->saveConfiguration('savedValue',
      }	    
      if(substr_compare($cmd->getName(),'on', 0, 2)==0){
      log::add('worxLandroidS', 'debug', 'Envoi du message On: ' . $_message);
	if($cmd->getName() == 'on_today'){
		$_message = 'on_' . date('w');
	}

	$sched = self::getSavedDaySchedule($eqlogicid,  substr($_message,3,1));
	$_message = self::setDaySchedule($eqlogicid, substr($_message,3,1), $sched);//  $this->saveConfiguration('savedValue',
       }	    
	
	  if($cmd->getName()=='refreshValue'){ $_message = '{}';}
	  
	  // send start command
	  if($_message == 'cmd:1')
	  { 
		  $_message = '{"cmd":1}';
	  }
	  // send stop
	  if($_message == 'cmd:3')
	  { 
		  $_message = '{"cmd":3}';
	  }
	  
	  $mosqId = config::byKey('mqtt_client_id', 'worxLandroidS') . '' . $id . '' . substr(md5(rand()), 0, 8);
          $client = new Mosquitto\Client($mosqId);
	  self::connect_and_publish($client, $_message);
	  
  /*
	  
        $mosqId = config::byKey('mqtt_client_id', 'worxLandroidS'). '' . $id . '' . substr(md5(rand()), 0, 8);
        // FIXME: the static class variable $_client is not visible here as the current function
        // is not executed on the same thread as the deamon. So we do create a new client.
      //  $client = new Mosquitto\Client(config::byKey('mqtt_client_id', 'worxLandroidS'));

	//$mid = $_client->publish($_subject, $payload, 0, 0);	  
        $client = new Mosquitto\Client($mosqId);	  
        $client->setTlsCertificates($root_ca,$certfile,$pkeyfile,null);	  
	$qos = '0';
	$retain = '0';
	$payload = $_message; 
	$client->onConnect('worxLandroidS::newconnect');
	//$client->onMessage('worxLandroidS::message');  

        $client->onPublish(function() use ($client, $mosqId, $_subject, $payload, $qos, $retain) {
            log::add('worxLandroidS', 'debug', 'Publication du message ' . $_subject . ' ' . $payload);
             sleep(2);
        });	  
	  
         //$client->onPublish('publish');
        $client->connect(config::byKey('mqtt_endpoint', 'worxLandroidS'), 8883, 60);
       log::add('worxLandroidS', 'debug', 'Pub du message ' . config::byKey('mqtt_endpoint', 'worxLandroidS') . ' ' . $payload);
       $topic = 'DB510/'.config::byKey('mac_address','worxLandroidS').'/commandOut';
       $client->subscribe($topic, 0); // !auto: Subscribe to root topic	
	  
	$client->onMessage(function($msg) {
                log::add('worxLandroidS', 'debug', 'retour pub msg' . $msg);		
		self::message($msg);
		$client->clearWill();		
		$client->disconnect();
		unset($client);
	});	  
	  
        while (true) {
           try{
               for ($i = 0; $i < 100; $i++) {
                    // Loop around to permit the library to do its work
                    $client->loop(1);
                        }
                //$mid = $client->publish($_subject, $payload, $qos, $retain);
                $mid = $client->publish($_subject, $payload, 0, 0);
                
		for ($i = 0; $i < 100; $i++) {
                    // Loop around to permit the library to do its work
                    $client->loop(1);
                        }

          }catch(Mosquitto\Exception $e){
            //echo"{$e}" ;
		log::add('worxLandroidS', 'debug', 'exception (msg sent then disconnected) ' . $e);
                return;
          }
          sleep(2);

        }
	$client->clearWill();  
        $client->disconnect();

        unset($client);
*/
	
       }	
public static $_widgetPossibility = array('custom' => array(
      'visibility' => true,
      'displayName' => true,
      'displayObjectName' => true,
      'optionalParameters' => false,
      'background-color' => true,
      'text-color' => true,
      'border' => true,
      'border-radius' => true,
      'background-opacity' => true,
)); 
	public function toHtml($_version = 'dashboard') {
		$jour = array("Dimanche","Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi");
		$replace = $this->preToHtml($_version);
		if (!is_array($replace)) {
			return $replace;
		}
		$version = jeedom::versionAlias($_version);
		$replace['#worxStatus#'] = '';
	        $today = date('w');
		//if ($version != 'mobile' || $this->getConfiguration('fullMobileDisplay', 0) == 1) {
			$worxStatus_template = getTemplate('core', $version, 'worxStatus', 'worxLandroidS');
			for ($i = 0; $i <= 6; $i++) {
				$replaceDay = array();
				$replaceDay['#day#'] = $jour[$i];
				$startTime = $this->getCmd(null, 'Planning/startTime/' . $i);
				$cutEdge = $this->getCmd(null, 'Planning/cutEdge/' . $i);
				$duration = $this->getCmd(null, 'Planning/duration/' . $i);				
				$replaceDay['#startTime#'] = is_object($startTime) ? $startTime->execCmd() : '';
				$replaceDay['#duration#'] = is_object($duration) ? $duration->execCmd() : '';
				$cmdS = $this->getCmd('action','on_'.$i);
				$replaceDay['#on_daynum_id#'] = $cmdS->getId();
				$cmdE = $this->getCmd('action','off_'.$i);
				$replaceDay['#off_daynum_id#'] = $cmdE->getId();

				//$replaceDay['#on_id#'] = $this->getCmd('action', 'on_1');
			        //$replaceDay['#off_id#'] = $this->getCmd('action', 'off_1');				
				// transforme au format objet DateTime 
				
				$initDate = DateTime::createFromFormat('H:i', $replaceDay['#startTime#']);
				$initDate->add(new DateInterval("PT".$replaceDay['#duration#']."M")); 
				$replaceDay['#endTime#'] = $initDate->format("H:i");
				
				$replaceDay['#cutEdge#'] = is_object($cutEdge) ? $cutEdge->execCmd() : '';
				if($replaceDay['#cutEdge#'] == '1')
				{ $replaceDay['#cutEdge#'] = 'Bord.';} else {  $replaceDay['#cutEdge#'] = '.'; }
				
				
				//$replaceDay['#icone#'] = is_object($condition) ? self::getIconFromCondition($condition->execCmd()) : '';
				//$replaceDay['#conditionid#'] = is_object($condition) ? $condition->getId() : '';
				$replace['#daySetup#'] .= template_replace($replaceDay, $worxStatus_template);

				if( $today == $i) 
				{
					$replace['#todayStartTime#'] = is_object($startTime) ? $startTime->execCmd() : '';
					$replace['#todayDuration#'] = is_object($duration) ? $duration->execCmd() : '';
					$replace['#today_on_daynum_id#'] = $cmdS->getId();
					$replace['#today_off_daynum_id#'] = $cmdE->getId();
					$replace['#todayEndTime#'] = $initDate->format("H:i");
					if($replace['#cutEdge#'] == '1')
					{ $replace['#cutEdge#'] = 'Bord.';} 
					$replace['#today#'] = $jour[$i];
				}
				
				
				
			}
		//}
                $errorCode = $this->getCmd(null, 'errorCode');
		$replace['#errorCode#'] = is_object($errorCode) ? $errorCode->execCmd() : '';
		$replace['#errorColor#'] = 'darkgreen';
		if($replace['#errorCode#'] != 0 ){$replace['#errorColor#'] = 'orange';}
		
		$replace['#errorID#'] = is_object($errorCode) ? $errorCode->getId() : '';
	        $errorDescription = $this->getCmd(null, 'errorDescription');
		$replace['#errorDescription#'] = is_object($errorDescription) ? $errorDescription->execCmd() : '';
	

	foreach ($this->getCmd('info') as $cmd) {
            $replace['#' . $cmd->getLogicalId() . '_history#'] = '';
            $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
            $replace['#' . $cmd->getLogicalId() . '#'] = $cmd->execCmd();
            $replace['#' . $cmd->getLogicalId() . '_collect#'] = $cmd->getCollectDate();
            if ($cmd->getLogicalId() == 'encours'){
                $replace['#batteryLevel#'] = $cmd->getDisplay('icon');
            }
		
 	  //  if($cmd->getIsVisible){
               $replace['#' . $cmd->getLogicalId() . '_visible#'] = 'block';	//}	
	  //  else {
          //     $replace['#' . $cmd->getLogicalId() . '_visible#'] = 'none';		

	    //}    
		    

		
            if ($cmd->getIsHistorized() == 1) {
                $replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor';
            }
        }
	foreach ($this->getCmd('action') as $cmd) {
            $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
        }	
		
		
		
		
		return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'worxMain', 'worxLandroidS')));

	}	
	

	
}

class worxLandroidSCmd extends cmd {
	
  public function execute($_options = null) {
    switch ($this->getType()) {
      case 'action' :
      $request = $this->getConfiguration('request','1');
      $topic = $this->getConfiguration('topic');
      switch ($this->getSubType()) {
        case 'slider':
        $request = str_replace('#slider#', $_options['slider'], $request);
        break;
        case 'color':
        $request = str_replace('#color#', $_options['color'], $request);
        break;
        case 'message':
        $request = str_replace('#title#', $_options['title'], $request);
        $request = str_replace('#message#', $_options['message'], $request);
        break;
      }

      $request = str_replace('\\', '', jeedom::evaluateExpression($request));
      $request = cmd::cmdToValue($request);
      //log::add('worxLandroidS', 'debug', 'Envoi de l action: ' . $topic. ' ' . $request );
// save schedule if setting to 0 - and retrieve from saved value (new values must be set from smartphone
  
	$eqlogic = $this->getEqLogic();
        log::add('worxLandroidS', 'debug', 'Eqlogicname: ' . $eqlogic->getName() );		
        worxLandroidS::publishMosquitto($this->getId(), $topic, $request, $this->getConfiguration('retain','0'));
      }
      return true;
    }


  }
