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

  log::add('worxLandroidS', 'info', 'client id: ' . config::byKey('mqtt_client_id', 'worxLandroidS'));


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
          //$this->checkAndUpdateCmd('communicationStatus',false);
          //return false;
        } else
        {
          //		config::save('created_at', $json['created_at'],'worxLandroid');
          //		config::save('api_token', $json['api_token'],'worxLandroid');
          //		config::save('mqtt_client_id', $json['mqtt_client_id'],'worxLandroid');
          //		config::save('mqtt_endpoint', $json['mqtt_endpoint'],'worxLandroid');
          //		config::save('id', $json['id'],'worxLandroid');


          // get certificate
          $url =  "https://api.worxlandroid.com:443/api/v1/users/certificate";
          $api_token = $json['api_token'];
          $token = $json['api_token'];
          //$token = "qiJNz3waS4I99FPvTaPt2C2R46WXYdhw";

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
          log::add('worxLandroidS', 'info', 'mac_address '.$json3[0]['mac_address']);
        }


        // test client2
        config::save('mqtt_client_id', $json['mqtt_client_id'],'worxLandroidS');
        config::save('mqtt_endpoint', $json['mqtt_endpoint'],'worxLandroidS');
      //  log::add('worxLandroidS', 'info', 'mqtt_client_id '.$json['mqtt_endpoint']);



}

}


}

	  
	  
// Loop on jMQTT equipments and get ones that subscribed to the current message
        $elogics = array();
        foreach (eqLogic::byType('worxLandroidS', false) as $eqpt) {
            //if ($message->topicMatchesSub($msgTopic, $eqpt->getConfiguration('topic'))) {
		if ($eqpt->getIsEnable() == true){
                $elogics[] = $eqpt;}
            //}
        }
	  

       //log::add('worxLandroidS', 'info', 'mqtt_endpoint '.$root_ca);
 if(config::byKey('initCloud', 'worxLandroidS') ==  true || empty($elogics) == false ){

    config::save('initCloud', 0 ,'worxLandroidS');

    self::$_client = new Mosquitto\Client(config::byKey('mqtt_client_id', 'worxLandroidS'));
    self::$_client->onConnect('worxLandroidS::connect');
    self::$_client->onDisconnect('worxLandroidS::disconnect');
    self::$_client->onSubscribe('worxLandroidS::subscribe');
    self::$_client->onMessage('worxLandroidS::message');
    self::$_client->onLog('worxLandroidS::logmq');
    self::$_client->setTlsCertificates($root_ca,$certfile,$pkeyfile,null);




   //$client->setWill('/jeedom', "Client died :-(", 1, 0);
 try {

      self::$_client->connect(config::byKey('mqtt_endpoint', 'worxLandroidS'), 8883 , 60);
//      $client->connect('a1optpg91s0ydf-2.iot.eu-west-1.amazonaws.com', '8883', 60);

      $topic = 'DB510/'.config::byKey('mac_address','worxLandroidS').'/commandOut';
       self::$_client->subscribe($topic, 0); // !auto: Subscribe to root topic


self::$_client->publish("DB510/".config::byKey('mac_address','worxLandroidS')."/commandIn", "{}", 0, 0);


   //     log::add('worxLandroidS', 'debug', 'Subscribe to topic ' . $topic, 'worxLandroidS', '#'));
      //$client->loopForever();
      while (true) { self::$_client->loop(); }

   }
   catch (Exception $e){
     log::add('worxLandroidS', 'error', $e->getMessage());
   }

}
sleep(30);








}


  public static function connect( $r, $message ) {
    log::add('worxLandroidS', 'info', 'Connexion à Mosquitto avec code ' . $r . ' ' . $message);
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

  public static function message( $message ) {
    log::add('worxLandroidS', 'debug', 'Message ' . $message->payload . ' sur ' . $message->topic);
    if (is_string($message->payload) && is_array(json_decode($message->payload, true)) && (json_last_error() == JSON_ERROR_NONE)) {
      //json message
      $nodeid = $message->topic;
      $value = $message->payload;
      $json2_data = json_decode($value);

      $type = 'json';
      log::add('worxLandroidS', 'info', 'Message json : ' . $value . ' pour information sur : ' . $nodeid);
    } else {
      $topicArray = explode("/", $message->topic);
      $cmdId = end($topicArray);
      $key = count($topicArray) - 1;
      unset($topicArray[$key]);
      $nodeid = implode($topicArray,'/');
      $value = $message->payload;
      $type = 'topic';
      log::add('worxLandroidS', 'info', 'Message texte : ' . $value . ' pour information : ' . $cmdId . ' sur : ' . $nodeid);
    }



    $elogic = self::byLogicalId($nodeid, 'worxLandroidS');
    if (!is_object($elogic)) {
      $elogic = new worxLandroidS();
      $elogic->setEqType_name('worxLandroidS');
      $elogic->setLogicalId($nodeid);
      $elogic->setName('LandroidS-'. $json2_data->dat->mac);
      //$elogic->setConfiguration('topic', $nodeid);
      //$elogic->setConfiguration('type', $type);
// ajout des actions par défaut
      log::add('worxLandroidS', 'info', 'Saving device ' . $nodeid);
      
	    // Advise the desktop page (javascript) that a new equipment has been added
      
      $elogic->save();
	    event::add('worxLandroidS::includeEqpt', $elogic->getId());

      $commandIn = 'DB510/'. $json2_data->dat->mac .'/commandIn';
      self::newAction($elogic,'setRainDelay', $commandIn, '{"rd":"#message#"}','message');
      self::newAction($elogic,'start',$commandIn,'{"cmd":"1"}','other');
      self::newAction($elogic,'stop',$commandIn,'{"cmd":"3"}','other');
      self::newAction($elogic,'refreshValue',$commandIn,'{}','other');

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
        self::newInfo($elogic,'StatusDescription',self::getStatusDescription($json2_data->dat->ls),'string',1);
        self::newInfo($elogic,'batteryLevel',$json2_data->dat->bt->p,'numeric',1);
        self::newInfo($elogic,'Langue',$json2_data->cfg->lg,'string',0);

        self::newInfo($elogic,'Lastdate',$json2_data->cfg->dt,'string',1);
        self::newInfo($elogic,'LastTime',$json2_data->cfg->tm,'string',1);

        self::newInfo($elogic,'Firmware',$json2_data->dat->fw,'string',0);
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

      //  $schedule = json_decode($json2_data->cfg->sc->d,false);

        self::newInfo($elogic,'Planning/Sunday/Starttime',$json2_data->cfg->sc->d[0][0],'string',1);
        self::newInfo($elogic,'Planning/Sunday/Duration',$json2_data->cfg->sc->d[0][1],'string',1);
        self::newInfo($elogic,'Planning/Sunday/CutEdge',$json2_data->cfg->sc->d[0][2],'string',1);	    
	    
        self::newInfo($elogic,'Planning/Monday/Starttime',strval($schedule[1]),'string',1);
        self::newInfo($elogic,'Planning/Tuesday/Starttime',strval($schedule[2]),'string',1);
        self::newInfo($elogic,'Planning/wednesday/Starttime',strval($schedule[3]),'string',1);
        self::newInfo($elogic,'Planning/Thursday/Starttime',strval($schedule[4]),'string',1);
        self::newInfo($elogic,'Planning/Friday/Starttime',strval($schedule[5]),'string',1);
        self::newInfo($elogic,'Planning/Saturday/Starttime',strval($schedule[6]),'string',1);
  
    }
  }


  public static function getErrorDescription($errorcode)
  {


    switch ($errorcode) {

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
        // code...
        break;
    }


  }

  public static function getStatusDescription($statuscode)
  {

    switch ($statuscode) {

      case '0': return "Idle";       break;
      case '1': return "Home";      break;
      case '2': return "Start sequence";       break;
      case '3': return "Leaving home"; break;
      case '4': return "Follow wire"; break;
      case '5': return "Searching home"; break;
      case '6': return "Searching wire"; break;
      case '7': return "Mowing"; break;
      case '8': return "Lifted"; break;
      case '9': return "Trapped"; break;
      case '10': return "Blade blocked"; break;
      case '11': return "Debug"; break;
      case '12': return "Remote control"; break;
      case '30': return "Going home"; break;
      case '32': return "Cutting edge"; break;


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
    log::add('worxLandroidS', 'debug', 'Cmdlogic update'.$cmdId.$value);

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
      log::add('worxLandroidS', 'debug', 'Cmdlogic update'.$cmdId.$value);

      $elogic->checkAndUpdateCmd($cmdId,$value);


    }

  public static function publishMosquitto($_id, $_subject, $_message, $_retain) {

    $resource_path = realpath(dirname(__FILE__) . '/../../resources/');

    $certfile = $resource_path.'/cert.pem';
    $pkeyfile = $resource_path.'/pkey.pem';
    $root_ca = $resource_path.'/vs-ca.pem';



   //log::add('worxLandroidS', 'debug', 'Envoi du message ' . $_message . ' vers ' . $_subject. '/'.config::byKey('mqtt_endpoint', 'worxLandroidS'));
    $publish = new Mosquitto\Client(config::byKey('mqtt_client_id', 'worxLandroidS'));


    
    $publish->onPublish(function($mid) {
    //    worxLandroidS::confirm($mid);
   log::add('worxLandroidS', 'debug', 'Envoi du message ' . $mid );
  
	    //    //print_r(array('comfirm publish', MQ::$publish[$mid]));
    });

    //$publish->onMessage('worxLandroidS::message');
    $publish->setTlsCertificates($root_ca,$certfile,$pkeyfile,null);
    $publish->onConnect('worxLandroidS::connect');


    $publish->connect(config::byKey('mqtt_endpoint', 'worxLandroidS'), '8883', 70);

         // $topic = 'DB510/'.config::byKey('mac_address','worxLandroidS').'/commandOut';
    //$publish->publish($_subject, $_message, 0 , 0);
    //$publish->publish($_subject, '{"rd":123}', 0 , 0);
	  
	  
     try {
		$publish->loop();
		$mid = $publish->publish($_subject, '{"rd":123}', 0 , 0);
 log::add('worxLandroidS', 'debug', 'Envoi du message ' . $mid );
  
	     $publish->loop();
	     for ($i = 0; $i < 30; $i++) {
      // Loop around to permit the library to do its work
      $publish->loop(1);
    }
	     
		}catch(Mosquitto\Exception $e){
log::add('worxLandroidS', 'debug', 'exception ' . $e );
  				return;
			}
    $publish->disconnect();
	unset($publish);	  
	  

//  $topic = 'DB510/'.config::byKey('mac_address','worxLandroidS').'/commandOut';
    //$publish->publish("DB510/".config::byKey('mac_address','worxLandroidS')."/commandIn", '{"rd":100}', 0, 0);

      //  log::add('worxLandroidS', 'debug', 'Envoi: ' . "DB510/".config::byKey('mac_address','worxLandroidS')."/commandIn" . '{"rd":100}');
    //  while (true) {
    //  	$publish->loop();
      //  $msg = '{"rd":100}';
        //$mid = $publish->publish("DB510/".config::byKey('mac_address','worxLandroidS')."/commandIn", '{"rd":100}', 0, 0);
       // $mid = $_client->publish("DB510/".config::byKey('mac_address','worxLandroidS')."/commandIn", '{"rd":100}', 0, 0);

	  //      worxLandroidS::addPublish($mid, $msg);
   //     sleep(1);


   //   	$publish->exitloop();
      //	sleep(2);
    //  }
      //$publish->disconnect();
      //unset($publish);




//         $publish->subscribe($topic, 0); // !auto: Subscribe to root topic
    //$publish->publish($_subject, $_message, 0 , 0);

   // for ($i = 0; $i < 30; $i++) {
      // Loop around to permit the library to do its work
   //   $publish->loop();
   // }
   // $publish->disconnect();
   //  unset($publish);
    //$publish->disconnect();
   // unset(self::$_client);
 
//}

	

	

	
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

      worxLandroidS::publishMosquitto($this->getId(), $topic, $request, $this->getConfiguration('retain','0'));
      }
      return true;
    }


  }
