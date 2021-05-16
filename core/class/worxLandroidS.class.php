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

class worxLandroidS extends eqLogic
{

  public static $_client;
  public static $_client_pub;
  public static $_permanent_client;
  // Dependancy installation log file
  private static $_depLogFile;
  // Dependancy installation progress value log file
  private static $_depProgressFile;

  public static function health()
  {
    $return   = array();
    $socket   = socket_create(AF_INET, SOCK_STREAM, 0);
    $server   = socket_connect($socket, config::byKey('mqtt_endpoint', 'worxLandroidS', '127.0.0.1'), '8883');
    $return[] = array(
      'test' => __('Mosquitto', __FILE__),
      'result' => ($server) ? __('OK', __FILE__) : __('NOK', __FILE__),
      'advice' => ($server) ? '' : __('Indique si Mosquitto est disponible', __FILE__),
      'state' => $server
    );
    return $return;
  }

  /*
  public static function cron30()
  {
    worxLandroidS::refresh_values("false");
  }
  */

  public static function refresh_values($checkMowingTime = "false")
  {
    $count      = 0;
    $eqptlist[] = array();
    foreach (eqLogic::byType('worxLandroidS', false) as $eqpt) {
      if ($eqpt->getIsEnable() == true) {
        if (config::byKey('status', 'worxLandroidS') == '0') { //on se connecte seulement si on est pas déjà connecté
          $i         = date('w');
          if( $start='' ) {
            $start = '08:00';
          }
          $start     = $eqpt->getCmd(null, 'Planning_startTime_' . $i);
          $startTime = is_object($start) ? $start->execCmd() : '00:00';
          $dur       = $eqpt->getCmd(null, 'Planning_duration_' . $i);
          $duration  = is_object($dur) ? $dur->execCmd() : 0;
          if($duration== ''){ $checkMowingTime = 'manual'; $duration=1;}; //correction pour l'initialisation
          if($startTime== ''){ $startTime = '00:00';}; //correction pour l'initialisation
          $initDate = DateTime::createFromFormat('H:i', $startTime);
          //log::add('worxLandroidS', 'debug', 'mower sleeping '.$duration);
          //if(empty($duration){$duration = 0};
          $initDate->add(new DateInterval("PT" . $duration . "M"));
          $endTime = $initDate->format("H:i");
          // refresh value each 30 minutes if mower is sleeping at home :-)
          if ($eqpt->getCmd(null, 'statusCode')->execCmd() != 1 ) {
            log::add('worxLandroidS', 'debug', 'La tondeuse n\'est pas sur la base : Refresh toutes les deux minutes');
            config::save('realTime', '1', 'worxLandroidS');
          } elseif ($checkMowingTime == "manual" or $checkMowingTime == "false" and ($startTime == '00:00' or $startTime > date('H:i') or date('H:i') > $endTime) or $startTime <= date('H:i') and date('H:i') <= $endTime and $checkMowingTime == "true") {
            log::add('worxLandroidS', 'debug', 'Ce n\'est pas l\'heure de tondre : pas de Refresh');
            config::save('realTime', '0', 'worxLandroidS');
          } else {
            log::add('worxLandroidS', 'debug', 'La tondeuse est sur la base mais c\'est l\'heure de tondre : Refresh toutes les deux minutes');
            config::save('realTime', '1', 'worxLandroidS');
          }

          if (config::byKey('realTime', 'worxLandroidS') == 1) {
            // populate message to be sent
            $eqptlist[$count] = array(
              $eqpt->getConfiguration('MowerType'),
              $eqpt->getLogicalId(),
              '{}'
            );
            $count++;
            if (!empty($eqptlist[0])) {
              self::connect_and_publish($eqptlist, '{}');
            }
          }
        }
      }


    }

  }

      public static function deamon_info()
      {
        $return          = array();
        $return['log']   = '';
        $return['state'] = 'nok';
        $cron            = cron::byClassAndFunction('worxLandroidS', 'daemon');
        $cronRefresh            = cron::byClassAndFunction('worxLandroidS', 'daemonRefresh');
        if (is_object($cron) && $cron->running() && is_object($cronRefresh) && $cronRefresh->running()) {
          $return['state'] = 'ok';
        }
        $dependancy_info = self::dependancy_info();
        if ($dependancy_info['state'] == 'ok') {
          $return['launchable'] = 'ok';
        }
        return $return;
      }

      public static function connect_permanent_client()
      {
        $RESOURCE_PATH = realpath(dirname(__FILE__) . '/../../resources/');
        $CERTFILE      = $RESOURCE_PATH . '/cert.pem';
        $PKEYFILE      = $RESOURCE_PATH . '/pkey.pem';
        $ROOT_CA       = $RESOURCE_PATH . '/vs-ca.pem';
        $count      = 0;
        $eqptlist[] = array();
        foreach (eqLogic::byType('worxLandroidS', false) as $eqpt) {
          if ($eqpt->getIsEnable() == true) {
            if (config::byKey('permanent_client', 'worxLandroidS') != 1) { //on se connecte seulement si on est pas déjà connecté
              // populate message to be sent
              $eqptlist[$count] = array(
                $eqpt->getConfiguration('MowerType'),
                $eqpt->getLogicalId()
              );
              $count++;
            }
          }
        }
        if (!empty($eqptlist[0])) {
          $mosqId = config::byKey('mqtt_client_id', 'worxLandroidS') . substr(md5(rand()), 0, 8);
          self::$_permanent_client = new Mosquitto\Client($mosqId, true);
          //self::$_permanent_client = $client;
          self::$_permanent_client->clearWill();
          self::$_permanent_client->onConnect('worxLandroidS::connect');
          self::$_permanent_client->onDisconnect('worxLandroidS::disconnect');
          self::$_permanent_client->onSubscribe('worxLandroidS::subscribe');
          self::$_permanent_client->onMessage('worxLandroidS::message');
          self::$_permanent_client->onLog('worxLandroidS::logmq');
          self::$_permanent_client->setTlsCertificates($ROOT_CA, $CERTFILE, $PKEYFILE, null);
          self::$_permanent_client->setTlsOptions(Mosquitto\Client::SSL_VERIFY_NONE, "tlsv1.2", null);
          try {
            //foreach ($eqptlist as $key => $value) {
              //$topic = $value[0] . '/' . $value[1] . '/commandOut';
              //self::$_client->setWill($value[0] . "/" . $value[1] . "/commandIn", $msg, 0, 0); // !auto: Subscribe to root topic
            //}
            //self::$_permanent_client->connect('localhost', 1883, 5);
            self::$_permanent_client->connect(config::byKey('mqtt_endpoint', 'worxLandroidS'), 8883, 300);
            foreach ($eqptlist as $key => $value) {
              $topic = $value[0] . '/' . $value[1] . '/commandOut';
              self::$_permanent_client->subscribe($topic, 0); // !auto: Subscribe to root topic
            }
            $start_time = time();
            config::save('permanent_client', '1', 'worxLandroidS');
            log::add('worxLandroidS', 'info', 'Client permanent en écoute : '.$mosqId);
            while (true) {
              self::$_permanent_client->loop();
            }
            config::save('permanent_client', '0', 'worxLandroidS');
          }
          catch (Exception $e) {
            // log::add('worxLandroidS', 'debug', $e->getMessage());
          }
          //if (config::byKey('status', 'worxLandroidS') == '1') {
            //self::$_permanent_client->disconnect();
          //}
        }
        config::save('permanent_client', 0, 'worxLandroidS');
      }
    
      public static function deamon_start($_debug = false)
      {
        self::deamon_stop();
        $deamon_info = self::deamon_info();
        if ($deamon_info['launchable'] != 'ok') {
          throw new Exception(__('Veuillez vérifier la configuration', __FILE__));
        }
        $cron = cron::byClassAndFunction('worxLandroidS', 'daemon');
        if (!is_object($cron)) {
          throw new Exception(__('Tache cron introuvable', __FILE__));
        }
        config::save('permanent_client', '0', 'worxLandroidS');
        config::save('status', '0', 'worxLandroidS');

        $cron->run();

        $cronRefresh = cron::byClassAndFunction('worxLandroidS', 'daemonRefresh');
        if (!is_object($cron)) {
          throw new Exception(__('Tache cron introuvable', __FILE__));
        }
        $cronRefresh->run();
      }

      public static function deamon_stop()
      {
        $cron = cron::byClassAndFunction('worxLandroidS', 'daemon');
        if (!is_object($cron)) {
          throw new Exception(__('Tache cron introuvable', __FILE__));
        }
        $cron->halt();

        $cronRefresh = cron::byClassAndFunction('worxLandroidS', 'daemonRefresh');
        if (!is_object($cron)) {
          throw new Exception(__('Tache cron introuvable', __FILE__));
        }
        $cronRefresh->halt();
      }

      /**
      * Provides dependancy information
      */
      public static function dependancy_info()
      {
        if (!isset(self::$_depLogFile))
        self::$_depLogFile = __CLASS__ . '_dep';
        if (!isset(self::$_depProgressFile))
        self::$_depProgressFile = jeedom::getTmpFolder(__CLASS__) . '/progress_dep.txt';
        $return                  = array();
        $return['log']           = log::getPathToLog(self::$_depLogFile);
        $return['progress_file'] = self::$_depProgressFile;
        // get number of mosquitto packages installed (should be 2 or 3 at least depending
        // on the installMosquitto config parameter)
        $mosq                    = exec(system::get('cmd_check') . 'mosquitto | wc -l');
        $minMosq                 = config::byKey('installMosquitto', 'worxLandroidS', 1) ? 3 : 2;
        // is lib PHP exists?
        $libphp                  = extension_loaded('mosquitto');
        // build the state status; if nok log debug information
        if ($mosq >= $minMosq && $libphp) {
          $return['state'] = 'ok';
        } else {
          $return['state'] = 'ok';

          //    log::add('worxLandroidS', 'debug', 'dependancy_info: NOK');
          //    log::add('worxLandroidS', 'debug', '   * Nb of mosquitto related packaged installed: ' . $mosq .
          //    ' (shall be greater equal than ' . $minMosq . ')');
          //    log::add('worxLandroidS', 'debug', '   * Mosquitto extension loaded: ' . $libphp);
        }
        return $return;
      }
      /**
      * Provides dependancy installation script
      */
      public static function dependancy_install()
      {
        log::add('worxLandroidS', 'info', 'Installation des dépendances, voir log dédié (' . self::$_depLogFile . ')');
        log::remove(self::$_depLogFile);
        return array(
          'script' => dirname(__FILE__) . '/../../resources/install.sh ' . self::$_depProgressFile . ' ' . config::byKey('installMosquitto', 'worxLandroidS', 1),
          'log' => log::getPathToLog(self::$_depLogFile)
        );
      }

      public static function daemon()
      {

        $RESOURCE_PATH = realpath(dirname(__FILE__) . '/../../resources/');
        $CERTFILE      = $RESOURCE_PATH . '/cert.pem';
        $PKEYFILE      = $RESOURCE_PATH . '/pkey.pem';
        $ROOT_CA       = $RESOURCE_PATH . '/vs-ca.pem';
        $default_message_file = $RESOURCE_PATH . '/message_default.json';
        // log::add('worxLandroidS', 'debug', '$RESOURCE_PATH: ' . $CERTFILE);
        // init first connection
        if (config::byKey('initCloud', 'worxLandroidS') == true) {
          //log::add('worxLandroidS', 'info', 'Paramètres utilisés, Host : ' . config::byKey('worxLandroidSAdress', 'worxLandroidS', '127.0.0.1') . ', Port : ' . config::byKey('worxLandroidSPort', 'worxLandroidS', '1883') . ', ID : ' . config::byKey('worxLandroidSId', 'worxLandroidS', 'Jeedom'));

          $email  = config::byKey('email', 'worxLandroidS');
          $passwd = config::byKey('passwd', 'worxLandroidS');
          // get mqtt config
          $url    = "https://api.worxlandroid.com:443/api/v2/oauth/token";

          $token       = "725f542f5d2c4b6a5145722a2a6a5b736e764f6e725b462e4568764d4b58755f6a767b2b76526457";
          $ch          = curl_init();
          $data        = array(
            "username" => $email,
            "password" => $passwd,
            "client_id" => 1,
            "grant_type" => "password",
            "type" => "app",
            "client_secret" => "nCH3A0WvMYn66vGorjSrnGZ2YtjQWDiCvjg7jNxK",
            "scope" => "*"
          );
          $data_string = json_encode($data);

          $ch = curl_init($url);
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
          curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            //'Content-Length: ' . strlen($data_string),
            'x-auth-token:' . $token
          ));
          $result = curl_exec($ch);
          $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          log::add('worxLandroidS', 'info', 'Connexion result :' . $result . $httpcode);
          $json = json_decode($result, true);
          if (is_null($json) or $httpcode <> '200') {
            log::add('worxLandroidS', 'info', 'Connexion KO');

            event::add('jeedom::alert', array(
              'level' => 'warning',
              'page' => 'worxLandroidS',
              'message' => __('Données de connexion incorrectes', __FILE__)
            ));
            //$this->checkAndUpdateCmd('communicationStatus',false);
            //return false;
          } else {

            // get users parameters
            $url       = "https://api.worxlandroid.com/api/v2/users/me";
            $api_token = $json['access_token'];
            $token     = $json['api_token'];

            $content = "application/json";
            $ch      = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
              "Content-Type: application/json",
              'Authorization: Bearer ' . $api_token
            ));

            $result_users = curl_exec($ch);
            log::add('worxLandroidS', 'info', 'Connexion result :' . $result_users);
            $json_users = json_decode($result_users, true);

            // get certificate
            $url = "https://api.worxlandroid.com:443/api/v2/users/certificate";
            //$api_token = $json['api_token'];
            //$token = $json['api_token'];

            $content = "application/json";
            $ch      = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
              'mqtt_endpoint:' . $json_users['mqtt_endpoint'],
              "Content-Type: application/json",
              'Authorization: Bearer ' . $api_token
            ));

            $result = curl_exec($ch);
            log::add('worxLandroidS', 'info', 'Connexion result :' . $result);

            $json2 = json_decode($result, true);


            if (is_null($json2)) {
            } else {
              $pkcs12 = base64_decode($json2['pkcs12']);
              openssl_pkcs12_read($pkcs12, $certs, "");
              file_put_contents($CERTFILE, $certs['cert']);
              file_put_contents($PKEYFILE, $certs['pkey']);

              // get product item (mac address)
              $url = "https://api.worxlandroid.com:443/api/v2/product-items";

              $content = "application/json";
              $ch      = curl_init($url);
              curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
              curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $api_token
              ));

              $result = curl_exec($ch);
              log::add('worxLandroidS', 'info', 'Connexion result :' . $result);

              $json3 = json_decode($result, true);
              config::save('api_token', $api_token, 'worxLandroidS'); //$json_users['id'],'worxLandroidS');

              config::save('mqtt_client_id', 'android-uuid/v1', 'worxLandroidS'); //$json_users['id'],'worxLandroidS');
              config::save('mqtt_endpoint', $json_users['mqtt_endpoint'], 'worxLandroidS');

              if (is_null($json3)) {
              } else {
                // get boards => id => code
                $url = "https://api.worxlandroid.com:443/api/v2/boards";
                curl_setopt($ch, CURLOPT_URL, $url);
                $boards = json_decode(curl_exec($ch), true);

                // get products => product_id => board_id
                $url = "https://api.worxlandroid.com:443/api/v2/products";
                curl_setopt($ch, CURLOPT_URL, $url);
                $products = json_decode(curl_exec($ch), true);
                foreach ($json3 as $key => $product) {
                  $typetondeuse     = 'DB510';
                  $found_key        = array_search($product['product_id'], array_column($products, 'id'));
                  $board_id         = $products[$found_key]['board_id'];
                  $mowerDescription = $products[$found_key]['code'];
                  log::add('worxLandroidS', 'info', 'board_id: ' . $board_id . ' / product id:' . $product['product_id']);
                  $found_key    = array_search($board_id, array_column($boards, 'id'));
                  $typetondeuse = $boards[$found_key]['code'];


                  log::add('worxLandroidS', 'info', 'mac_address ' . $product['mac_address'] . $typetondeuse);
                  // create Equipement if not already created
                  $elogic = self::byLogicalId($product['mac_address'], 'worxLandroidS');
                  if (!is_object($elogic)) {

                    $elogic_prev = self::byLogicalId($typetondeuse . '/' . $product['mac_address'] . '/commandOut', 'worxLandroidS');
                    if (is_object($elogic_prev)) {
                      // created equipement in previous plugin release*
                      message::add('worxLandroidS', 'Veuillez supprimer la tondeuse ajoutée précédemment: ' . $elogic_prev->getName(), null, null);
                      log::add('worxLandroidS', 'info', 'Suppress existing first : mac_address ' . $product['mac_address'] . $typetondeuse . $product['product_id']);
                    } else {

                      log::add('worxLandroidS', 'info', 'mac_address ' . $product['mac_address'] . $typetondeuse . $product['product_id']);
                      worxLandroidS::create_equipement($product, $typetondeuse, $mowerDescription);
                     // message par défault pour éviter code 500 à la première initialisation
                     // message par défault pour éviter code 500 à la première initialisation
                      $default_message = file_get_contents($default_message_file,true);
                      $topic = $product['product_id'].'/'.$product['mac_address'].'/dummy';
                      $message = json_decode($default_message,true);
                      $message->topic = $topic;//$product['product_id'].'/'.$product['mac_address'].'/dummy';
                      log::add('worxLandroidS', 'info', 'default msg:'. $message->payload. '/topic:'. $message->topic);
                      worxLandroidS::message($message);




                    }
                  }

                }
                config::save('initCloud', 0, 'worxLandroidS');
              }
            }
          }
        }

        if (config::byKey('permanent_client', 'worxLandroidS') == 0) {
          self::connect_permanent_client();
          //log::add('worxLandroidS', 'debug', 'sortie de connect_permanent_client, avec config = : '.config::byKey('permanent_client', 'worxLandroidS'));
        } else {
          log::add('worxLandroidS', 'debug', 'connect_permanent_client est déjà en cours');
        }
      }

      public static function daemonRefresh()
      {
        //log::add('worxLandroidS', 'debug', 'Début du Refresh');
        self::refresh_values();
        log::add('worxLandroidS', 'info', 'Fin du Refresh');
       
      }

      public function postSave()
      {
        $commandIn = $this->getConfiguration('MowerType') . '/' . $this->getLogicalId() . '/commandIn';
        $this->newAction('setRainDelay', $commandIn, '{"rd":#slider#}', 'slider', array( "minValue" => 0, "maxValue" => 300, "showNameOndashboard" => false,"showNameOnmobile" => false));
        $display = array( 'isvisible' => 1, 'name' => __('Lames remplacees', __FILE__));
        $this->newAction('newBlades', $commandIn, "", 'other', $display);
        $this->newAction('userMessage', $commandIn, "#message#", 'message');

        //self::refresh_values("manual");
      }

      public static function create_equipement($product, $MowerType, $mowerDescription)
      {
        $elogic = new worxLandroidS();
        $elogic->setEqType_name('worxLandroidS');
        $elogic->setLogicalId($product['mac_address']);
        $elogic->setName($product['name']);
        $elogic->setConfiguration('serialNumber', $product['serial_number']);
        $elogic->setConfiguration('warranty_expiration_date', $product['warranty_expires_at']);
        $elogic->setConfiguration('MowerType', $MowerType);
        $elogic->setConfiguration('maxBladesDuration', 300);
        $elogic->setConfiguration('mowerDescription', $mowerDescription);

        // ajout des actions par défaut
        log::add('worxLandroidS', 'info', 'Saving device with mac address' . $product['mac_address']);
        message::add('worxLandroidS', 'Tondeuse ajoutée (en cas d erreur faire un refresh_value dans la liste des commandes pour la premiere utilisation): ' . $elogic->getName(), null, null);

        $elogic->save();
        $elogic->setDisplay("width", "450px");
        $elogic->setDisplay("height", "260px");
        $elogic->setIsVisible(1);
        $elogic->setIsEnable(1);

        $commandIn = $MowerType . '/' . $product['mac_address'] . '/commandIn'; //config::byKey('MowerType', 'worxLandroidS').'/'. $json2_data->dat->mac .'/commandIn';
        //$elogic->newAction('setRainDelay', $commandIn, '{"rd":"#message#"}', 'message');
        $elogic->newAction('setRainDelay', $commandIn, '{"rd":#slider#}', 'slider', array( "minValue" => 0, "maxValue" => 300, "showNameOndashboard" => false,"showNameOnmobile" => false  ));
        $elogic->newAction('start', $commandIn, array('cmd' => 1), 'other');
        $elogic->newAction('pause', $commandIn, array('cmd' => 2), 'other');
        $elogic->newAction('stop', $commandIn, array('cmd' => 3), 'other');
        $elogic->newAction('cutEdge', $commandIn, array('cmd' => 4), 'other');
        $elogic->newAction('zoneTraining', $commandIn, array('cmd' => 4), 'other');

        $elogic->newAction('refreshValue', $commandIn, "", 'other');
        $elogic->newAction('off_today', $commandIn, "off_today", 'other');
        $elogic->newAction('on_today', $commandIn, "on_today", 'other');
        $elogic->newAction('rain_delay_0', $commandIn, "0", 'other');
        $elogic->newAction('rain_delay_30', $commandIn, "30", 'other');
        $elogic->newAction('rain_delay_60', $commandIn, "60", 'other');
        $elogic->newAction('rain_delay_120', $commandIn, "120", 'other');
        $elogic->newAction('rain_delay_240', $commandIn, "240", 'other');
        $elogic->newAction('userMessage', $commandIn, "#message#", 'message');

        $display = array( 'isvisible' => 1,
        'name' => __('Lames remplacees', __FILE__));
        $elogic->newAction('newBlades', $commandIn, "", 'other', $display);
        $elogic->newInfo('virtualInfo', '', 'string', 0, 'statusCode,statusDescription,batteryLevel,wifiQuality,currentZone');

        $display = array(
          'message_placeholder' => __('num jour;hh:mm;durée mn;bord(0 ou 1)', __FILE__),
          'isvisible' => 0,
          'title_disable' => true);

          $elogic->newAction('set_schedule', $commandIn, "", 'message', $display);

          for ($i = 0; $i < 7; $i++) {
            $elogic->newAction('on_' . $i, $commandIn, 'on_' . $i, 'other');
            $elogic->newAction('off_' . $i, $commandIn, 'off_' . $i, 'other');
          }

          event::add('worxLandroidS::includeEqpt', $elogic->getId());

          $elogic->setStatus('lastCommunication', date('Y-m-d H:i:s'));
          $elogic->save();
        }

        public static function old_connect_and_publish($eqptlist, $client, $msg)
        {

          $RESOURCE_PATH = realpath(dirname(__FILE__) . '/../../resources/');
          $CERTFILE      = $RESOURCE_PATH . '/cert.pem';
          $PKEYFILE      = $RESOURCE_PATH . '/pkey.pem';
          $ROOT_CA       = $RESOURCE_PATH . '/vs-ca.pem';

          self::$_client = $client;
          self::$_client->clearWill();
          self::$_client->onConnect('worxLandroidS::connect');
          self::$_client->onDisconnect('worxLandroidS::disconnect');
          self::$_client->onSubscribe('worxLandroidS::subscribe');
          self::$_client->onMessage('worxLandroidS::message');
          self::$_client->onLog('worxLandroidS::logmq');
          self::$_client->setTlsCertificates($ROOT_CA, $CERTFILE, $PKEYFILE, null);
          self::$_client->setTlsOptions(Mosquitto\Client::SSL_VERIFY_NONE, "tlsv1.2", null);
          try {
            foreach ($eqptlist as $key => $value) {
              $topic = $value[0] . '/' . $value[1] . '/commandOut';
              //'/'.$eqpt->getLogicalId().'/commandOut';
              self::$_client->setWill($value[0] . "/" . $value[1] . "/commandIn", $msg, 0, 0); // !auto: Subscribe to root topic
            }

            self::$_client->connect(config::byKey('mqtt_endpoint', 'worxLandroidS'), 8883, 300);

            //self::$_client->connect('localhost', 1883, 5);
            config::save('status', '1', 'worxLandroidS');
            /*
            foreach ($eqptlist as $key => $value) {
              $topic = $value[0] . '/' . $value[1] . '/commandOut';
              //'/'.$eqpt->getLogicalId().'/commandOut';
              self::$_client->subscribe($topic, 0); // !auto: Subscribe to root topic
            }
            */

            log::add('worxLandroidS', 'debug', 'Subscribe to mqtt ' . config::byKey('mqtt_endpoint', 'worxLandroidS') . ' msg ' . $msg);
            //self::$_client->loop();
            foreach ($eqptlist as $key => $value) {
              self::$_client->publish($value[0] . '/' . $value[1] . "/commandIn", $value[2], 0, 0);
            }

            //self::$_client->loopForever();
            $start_time = time();
            while (true) {
              self::$_client->loop(1000);
              if ((time() - $start_time) > 1) {
                log::add('worxLandroidS', 'debug', 'Timeout reached');
                foreach (eqLogic::byType('worxLandroidS', false) as $eqpt) {
                  //$eqpt->newInfo('statusDescription', __("Communication timeout", __FILE__), 'string', 1, '');
                  self::$_client->disconnect();
                  config::save('status', '0', 'worxLandroidS');
                }
                return false;
              }
            }
          }
          catch (Exception $e) {
            // log::add('worxLandroidS', 'debug', $e->getMessage());
          }
          if (config::byKey('status', 'worxLandroidS') == '1') {
            self::$_client->disconnect();
          }

        }


        public function connect_and_publish($eqptlist, $msg) {
          $RESOURCE_PATH = realpath(dirname(__FILE__) . '/../../resources/');
          $CERTFILE      = $RESOURCE_PATH . '/cert.pem';
          $PKEYFILE      = $RESOURCE_PATH . '/pkey.pem';
          $ROOT_CA       = $RESOURCE_PATH . '/vs-ca.pem';

          $mosqId = config::byKey('mqtt_client_id', 'worxLandroidS') . substr(md5(rand()), 0, 8);
          $client = new Mosquitto\Client($mosqId, true);

          $client->clearWill();
          $client->onDisconnect('worxLandroidS::disconnect');
          $client->onSubscribe('worxLandroidS::subscribe');
          $client->onMessage('worxLandroidS::message');
          $client->onLog('worxLandroidS::logmq');
          $client->setTlsCertificates($ROOT_CA, $CERTFILE, $PKEYFILE, null);
          $client->setTlsOptions(Mosquitto\Client::SSL_VERIFY_NONE, "tlsv1.2", null);
          $client->onConnect(
            function () use ($client, $eqptlist, $msg) {              
                config::save('status', '1', 'worxLandroidS');
                foreach ($eqptlist as $key => $value) {
                  $topic = $eqptlist[0][0] . '/' . $eqptlist[0][1] . "/commandIn";
                  log::add('worxLandroidS', 'debug', 'Publication du message : ' . $topic . ', msg : ' . $msg);
                  $client->publish($topic, $eqptlist[0][2], 0, 0);
                }
                $client->exitLoop();
            
          });

          $client->connect(config::byKey('mqtt_endpoint', 'worxLandroidS'), 8883, 300);       
          $client->loopForever();
          config::save('status', '0', 'worxLandroidS');
          $client->disconnect();
          log::add('worxLandroidS', 'debug', 'Message publié');
        }
        public static function onConnectRefresh($r, $message)
        {
          log::add('worxLandroidS', 'debug', 'Connexion à Mosquitto avec code ' . $r . ' ' . $message);
          //config::save('status', '1', 'worxLandroidS');
        }

        public static function connect($r, $message)
        {
          log::add('worxLandroidS', 'debug', 'Connexion à Mosquitto avec code ' . $r . ' ' . $message);
          //config::save('status', '1', 'worxLandroidS');
        }

        public static function newconnect($r, $message)
        {
          log::add('worxLandroidS', 'debug', 'New Connexion à Mosquitto avec code ' . $r . ' ' . $message);
          config::save('status', '1', 'worxLandroidS');
        }

        public static function disconnect($r)
        {
          log::add('worxLandroidS', 'debug', 'Déconnexion de Mosquitto avec code ' . $r);

          if ($r == '14') {
            message::add('worxLandroidS', "Vous devez mettre à jour Mosquitto (version minimum 1.4 requise)");
          }

          config::save('status', '0', 'worxLandroidS');
        }

        public static function subscribe()
        {
          log::add('worxLandroidS', 'debug', 'Subscribe to topics');
        }

        public static function logmq($code, $str)
        {
          if (strpos($str, 'PINGREQ') === false && strpos($str, 'PINGRESP') === false) {
            log::add('worxLandroidS', 'debug', $code . ' : ' . $str);
          }
        }

        public static function message($message)
        {
          log::add('worxLandroidS', 'debug', 'Message ' . $message->payload . ' sur ' . $message->topic);
          //json message
          $nodeid     = $message->topic;
          $value      = $message->payload;
          $json2_data = json_decode($value);

          $split_topic = explode('/', $nodeid);
          $mac_address = $split_topic[1];

          $elogic = eqlogic::byLogicalId($mac_address, 'worxLandroidS', false);

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


        /*
        if (config::byKey('status', 'worxLandroidS') == '1' && $split_topic[2] != 'dummy') { //&& config::byKey('mowingTime','worxLandroidS') == '0'){
          self::$_client->disconnect();
        }
        */

        $elogic->setConfiguration('retryNr', 0);
        $elogic->newInfo('errorCode', $json2_data->dat->le, 'numeric', 1, '' );
        $elogic->newInfo('errorDescription', self::getErrorDescription($json2_data->dat->le), 'string', 1, '');

        $elogic->newInfo('statusCode', $json2_data->dat->ls, 'numeric', 1, '');
        $elogic->newInfo('statusDescription', self::getStatusDescription($json2_data->dat->ls), 'string', 1, '');
        $elogic->newInfo('batteryLevel', $json2_data->dat->bt->p, 'numeric', 1, '');
        $elogic->newInfo('langue', $json2_data->cfg->lg, 'string', 0, '');

        $elogic->newInfo('lastDate', $json2_data->cfg->dt, 'string', 1, '');
        $elogic->newInfo('lastTime', $json2_data->cfg->tm, 'string', 1, '');

        $elogic->newInfo('firmware', $json2_data->dat->fw, 'string', 0, '');
        $elogic->newInfo('wifiQuality', $json2_data->dat->rsi, 'numeric', 0, '');
        $elogic->newInfo('rainDelay', $json2_data->cfg->rd, 'numeric', 1, '');

        $elogic->newInfo('pitch', $json2_data->dat->dmp[0], 'numeric', 1, '');
        $elogic->newInfo('roll', $json2_data->dat->dmp[1], 'numeric', 1, '');
        $elogic->newInfo('direction', $json2_data->dat->dmp[2], 'numeric', 1, '');

        $elogic->newInfo('totalTime', $json2_data->dat->st->wt, 'numeric', 1, '');
        $elogic->newInfo('totalDistance', $json2_data->dat->st->d, 'numeric', 1, '');
        $elogic->newInfo('totalBladeTime', $json2_data->dat->st->b, 'numeric', 0, '');
        $elogic->newInfo('batteryChargeCycle', $json2_data->dat->bt->nr, 'numeric', 1, '');
        $elogic->newInfo('batteryCharging', $json2_data->dat->bt->c, 'binary', 1, '');
        $elogic->newInfo('batteryVoltage', $json2_data->dat->bt->v, 'numeric', 0, '');
        $elogic->newInfo('batteryTemperature', $json2_data->dat->bt->t, 'numeric', 0, '');
        $elogic->newInfo('zonesList', $json2_data->dat->mz, 'string', 0, '');
        //area
        $elogic->newInfo('areaList', $json2_data->cfg->mz[0].'|'.$json2_data->cfg->mz[1].'|'.$json2_data->cfg->mz[2].'|'.$json2_data->cfg->mz[3], 'string', 1, '');
        $elogic->newInfo('areaListDist', $json2_data->cfg->mzv[0].'|'.$json2_data->cfg->mzv[1].'|'.$json2_data->cfg->mzv[2].'|'.$json2_data->cfg->mzv[3].'|'.
                         $json2_data->cfg->mzv[4].'|'.$json2_data->cfg->mzv[5].'|'.$json2_data->cfg->mzv[6].'|'.$json2_data->cfg->mzv[7].'|'.
                         $json2_data->cfg->mzv[8].'|'.$json2_data->cfg->mzv[9]
                         , 'string', 1, '');

        if (array_key_exists('conn', $json2_data->dat)) { // for mower with 4G modules
          $elogic->newInfo('connexion', $json2_data->dat->conn, 'string', 1, '');
          $elogic->newInfo('GPSLatitude',$json2_data->dat->modules->{'4G'}->gps->coo[0],'string',1, '');
          $elogic->newInfo('GPSLongitude',$json2_data->dat->modules->{'4G'}->gps->coo[1],'string',1, '');
        } else {
          $elogic->newInfo('connexion', ' ', 'string', 0, '');
          $elogic->newInfo('GPSLatitude', ' ', 'string', 0, '');
          $elogic->newInfo('GPSLongitude', ' ', 'string', 0, '');
        }

        $elogic->newInfo('currentZone', $json2_data->cfg->mzv[$json2_data->dat->lz] + 1, 'numeric', 0, '');
        //  date début + durée + bordure
        $completePlanning = '';
        for ($i = 0; $i < 7; $i++) {
          $completePlanning .= '';
          $elogic->newInfo('Planning_startTime_' . $i, $json2_data->cfg->sc->d[$i][0], 'string', 1, '');
          $elogic->newInfo('Planning_duration_' . $i, $json2_data->cfg->sc->d[$i][1], 'string', 1, '');
          $elogic->newInfo('Planning_cutEdge_' . $i, $json2_data->cfg->sc->d[$i][2], 'string', 1, '');
          $completePlanning .= $json2_data->cfg->sc->d[$i][0].','.$json2_data->cfg->sc->d[$i][1].','.$json2_data->cfg->sc->d[$i][2].'|';
          $elogic->newInfo('completePlanning', $completePlanning, 'string', 1, '');

        }

        // mise a jour des infos virtuelles séparées par des virgules
        $cmd = worxLandroidSCmd::byEqLogicIdCmdName($elogic->getId(), 'virtualInfo');
        $name = $cmd->getConfiguration('request', '');
        //log::add('worxLandroidS', 'info', 'liste commande' . $name);
        $cmdlist = explode(',', $name);
        $value = '';
        foreach ($cmdlist as $cmdname) {
          if (strstr($cmdname,'#')) {
            $value .= ',' . strval(jeedom::evaluateExpression($cmdname)); // name = command number
          } else {
            $cmdlogic = worxLandroidSCmd::byEqLogicIdCmdName($elogic->getId(), $cmdname);
            if(empty($value)) {
              $value = $cmdlogic->getConfiguration('topic', '');
            } else {
              $value .= ',' . $cmdlogic->getConfiguration('topic', '');
            }
            //log::add('worxLandroidS', 'info', 'liste commande/value:' . $cmdname . '/' . $value);
          }
        }
        //log::add('worxLandroidS', 'info', 'liste commande' . $value);

        $cmd->setConfiguration('topic', $value);
        $cmd->save();
        $elogic->checkAndUpdateCmd($cmd, $value);

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
          case '0':
          return __('Aucune erreur', __FILE__);
          break;
          case '1':
          return __('Bloquée', __FILE__);
          break;
          case '2':
          return __('Soulevée', __FILE__);
          break;
          case '3':
          return __('Câble non trouvé', __FILE__);
          break;
          case '4':
          return __('En dehors des limites', __FILE__);
          break;
          case '5':
          return __('Délai pluie', __FILE__);
          break;
          case '6':
          return 'Close door to mow';
          break;
          case '7':
          return 'Close door to go home';
          break;
          case '8':
          return __('Moteur lames bloqué', __FILE__);
          break;
          case '9':
          return __('Moteur roues bloqué', __FILE__);
          break;
          case '10':
          return __('Timeout après blocage', __FILE__);
          break;
          case '11':
          return __('Renversée', __FILE__);
          break;
          case '12':
          return __('Batterie faible', __FILE__);
          break;
          case '13':
          return __('Câble inversé', __FILE__);
          break;
          case '14':
          return __('Erreur charge batterie', __FILE__);
          break;
          case '15':
          return __('Delai recherche station dépassé', __FILE__);
          break;
          default:
          return 'communication tondeuse impossible';
          break;
        }
      }

      public static function getStatusDescription($statuscode)
      {
        switch ($statuscode) {
          case '0':
          return __("Inactive", __FILE__);
          break;
          case '1':
          return __("Sur la base", __FILE__);
          break;
          case '2':
          return __("Séquence de démarrage", __FILE__);
          break;
          case '3':
          return __("Quitte la base", __FILE__);
          break;
          case '4':
          return __("Suit le câble", __FILE__);
          break;
          case '5':
          return __("Recherche de la base", __FILE__);
          break;
          case '6':
          return __("Recherche du câble", __FILE__);
          break;
          case '7':
          return __("En cours de tonte", __FILE__);
          break;
          case '8':
          return __("Soulevée", __FILE__);
          break;
          case '9':
          return __("Coincée", __FILE__);
          break;
          case '10':
          return __("Lames bloquées", __FILE__);
          break;
          case '11':
          return "Debug";
          break;
          case '12':
          return __("Remote control", __FILE__);
          break;
          case '30':
          return __("Retour à la base", __FILE__);
          break;
          case '31':
          return __("Création de zones", __FILE__);
          break;
          case '32':
          return __("Coupe la bordure", __FILE__);
          break;
          case '33':
          return __("Départ vers zone de tonte", __FILE__);
          break;
          case '34':
          return __("Pause", __FILE__);
          break;

          default:
          return 'unkown';
          // code...
          break;
        }
      }

      public function newInfo($cmdId, $value, $subtype, $visible, $request=null)
      {
        $cmdlogic = $this->getCmd(null, $cmdId);

        if (!is_object($cmdlogic)) {
          log::add('worxLandroidS', 'info', 'Cmdlogic n existe pas, creation:'.$cmdId);
          $cmdlogic = new worxLandroidSCmd();
          $cmdlogic->setEqLogic_id($this->getId());
          $cmdlogic->setEqType('worxLandroidS');
          $cmdlogic->setSubType($subtype);
          $cmdlogic->setLogicalId($cmdId);
          $cmdlogic->setType('info');
          $cmdlogic->setName($cmdId);
          $cmdlogic->setIsVisible($visible);

          $cmdlogic->setConfiguration('topic', $value);
          if (!is_null($request)) {
            $cmdlogic->setConfiguration('request', $request);
          }
          $cmdlogic->setConfiguration('topic', $value);
          //$cmdlogic->setValue($value);
          $cmdlogic->save();
        }

        //   log::add('worxLandroidS', 'debug', 'Cmdlogic update'.$cmdId.$value);

        if (strstr($cmdId, "Planning_startTime") && $value != '00:00') {
          // log::add('worxLandroidS', 'debug', 'savedValue time'. $value);
          $cmdlogic->setConfiguration('savedValue', $value);
          $cmdlogic->save();
        }
        if (strstr($cmdId, "Planning_duration") && $value != 0) {
          //log::add('worxLandroidS', 'debug', 'savedValue duration'. $value);
          $cmdlogic->setConfiguration('savedValue', $value);
          $cmdlogic->save();

        }
        $cmdlogic->setConfiguration('topic', $value);
        //$cmdlogic->setValue($value);
        //$cmdlogic->save();

        $this->checkAndUpdateCmd($cmdId, $value);
      }

      public function newAction($cmdId, $topic, $payload, $subtype, $params = array())
      {
        $cmdlogic = $this->getCmd(null, $cmdId);

        if (!is_object($cmdlogic)) {
          log::add('worxLandroidS', 'info', 'nouvelle action par défaut' . $payload);
          $cmdlogic = new worxLandroidSCmd();
        }
        $cmdlogic->setEqLogic_id($this->getId());
        $cmdlogic->setEqType('worxLandroidS');
        $cmdlogic->setSubType($subtype);
        $cmdlogic->setLogicalId($cmdId);
        $cmdlogic->setType('action');
        //$cmdlogic->setName(json_encode($params['name']) ?: $cmdId);
        $cmdlogic->setName($cmdId);
        $cmdlogic->setConfiguration('listValue', json_encode($params['listValue']) ?: null);
        $cmdlogic->setConfiguration('minValue', json_encode($params['minValue']) ?: null);
        $cmdlogic->setConfiguration('maxValue', json_encode($params['maxValue']) ?: null);

        $cmdlogic->setDisplay('showNameOndashboard', isset($params['showNameOndashboard']) ? $params['showNameOndashboard']: true);
        $cmdlogic->setDisplay('showNameOnmobile', isset($params['showNameOnmobile'])? $params['showNameOndashboard']: true);
        $cmdlogic->setDisplay('forceReturnLineBefore', $params['forceReturnLineBefore'] ?: false);
        $cmdlogic->setDisplay('message_disable', $params['message_disable'] ?: false);
        $cmdlogic->setDisplay('title_disable', $params['title_disable'] ?: false);
        $cmdlogic->setDisplay('title_placeholder', $params['title_placeholder'] ?: false);
        $cmdlogic->setDisplay('icon', $params['icon'] ?: false);
        $cmdlogic->setDisplay('message_placeholder', $params['message_placeholder'] ?: false);
        $cmdlogic->setDisplay('title_possibility_list', json_encode($params['title_possibility_list'] ?: null));
        $cmdlogic->setDisplay('icon', $params['icon'] ?: null);
        $cmdlogic->setIsVisible($params['isvisible'] ?: 0);
        $cmdlogic->setConfiguration('topic', $topic);
        $cmdlogic->setConfiguration('request', $payload);
        $cmdlogic->save();

      }

      public static function getSavedDaySchedule($_id, $i)
      {
        $cmdlogic = worxLandroidSCmd::byEqLogicIdCmdName($_id, 'Planning_startTime_' . $i);
        $day[0]   = $cmdlogic->getConfiguration('savedValue', '10:00');

        $cmdlogic = worxLandroidSCmd::byEqLogicIdCmdName($_id, 'Planning_duration_' . $i);
        $day[1]   = intval($cmdlogic->getConfiguration('savedValue', 420));
        $cmdlogic = worxLandroidSCmd::byEqLogicIdCmdName($_id, 'Planning_cutEdge_' . $i);
        $day[2]   = intval($cmdlogic->getConfiguration('topic', 0));

        return $day;
      }
      public static function getSchedule($_id)
      {
        $schedule = array();

        $day = array();
        for ($i = 0; $i < 7; $i++) {

          $cmdlogic = worxLandroidSCmd::byEqLogicIdCmdName($_id, 'Planning_startTime_' . $i);
          $day[0]   = $cmdlogic->getConfiguration('topic', '10:00');
          $cmdlogic = worxLandroidSCmd::byEqLogicIdCmdName($_id, 'Planning_duration_' . $i);
          $day[1]   = intval($cmdlogic->getConfiguration('topic', 420));
          $cmdlogic = worxLandroidSCmd::byEqLogicIdCmdName($_id, 'Planning_cutEdge_' . $i);
          $day[2]   = intval($cmdlogic->getConfiguration('topic', 0));

          $schedule[$i] = $day;
        }
        return $schedule;
      }

      public static function setSchedule($_id, $schedule)
      {
        $_message = '{"sc":' . json_encode(array(
          'd' => $schedule
        )) . "}";
        log::add('worxLandroidS', 'debug', 'message à publier' . $_message);
        worxLandroidS::publishMosquitto($_id, config::byKey('MowerType', 'worxLandroidS') . "/" . $_id->getConfiguration('mac_address', 'worxLandroidS') . "/commandIn", $_message, 0);
      }


      public static function setDaySchedule($_id, $daynumber, $daySchedule)
      {
        $schedule                     = array();
        // $elogic = self::byLogicalId($nodeid, 'worxLandroidS');
        $schedule                     = worxLandroidS::getSchedule($_id);
        // $daySchedule[2]               = $schedule[intval($daynumber)][2];
        $schedule[intval($daynumber)] = $daySchedule;
        $_message                     = '{"sc":' . json_encode(array(
          'd' => $schedule
        )) . "}";
        log::add('worxLandroidS', 'debug', '$current schedule: ' . $_message);
        return $_message;
        //  worxLandroidS::setSchedule($eqlogic, $schedule);
      }

      public static function publishMosquitto($_id, $_subject, $_message, $_retain)
      {
        // save schedule if setting to 0 - and retrieve from saved value (new values must be set from smartphone
        $cmd = worxLandroidSCmd::byId($_id);
        log::add('worxLandroidS', 'debug', 'Publication du message ' . $cmd->getName() . ' ' . $_message);
        $eqlogicid = $cmd->getEqLogic_id();
        $eqlogic   = $cmd->getEqLogic();

        if (substr_compare($cmd->getName(), 'off', 0, 3) == 0) {
          log::add('worxLandroidS', 'debug', 'Envoi du message OFF: ' . $_message);
          if ($cmd->getName() == 'off_today') {
            $_message = 'off_' . date('w');
          }

          $sched    = array(
            '00:00',
            0,
            0
          );
          $_message = self::setDaySchedule($eqlogicid, substr($_message, 4, 1), $sched); //  $this->saveConfiguration('savedValue',
        }
        if (substr_compare($cmd->getName(), 'on', 0, 2) == 0) {
          log::add('worxLandroidS', 'debug', 'Envoi du message On: ' . $_message);
          if ($cmd->getName() == 'on_today') {
            $_message = 'on_' . date('w');
          }

          $sched = self::getSavedDaySchedule($eqlogicid, substr($_message, 3, 1));

          $_message = self::setDaySchedule($eqlogicid, substr($_message, 3, 1), $sched); //  $this->saveConfiguration('savedValue',
        }

        if ($cmd->getName() == 'refreshValue') {
          $_message = '{}';
          }

          // send start command
          if ($cmd->getName() == 'user_message') {
            $_message = trim($_message, '|');
          }

          // send start command
          if ($cmd->getName() == 'start') {
            $_message = '{"cmd":1}';
          }
          // send pause command
          if ($cmd->getName() == 'pause') {
            $_message = '{"cmd":2}';
          }

          // send stop
          if ($cmd->getName() == 'stop') {
            $_message = '{"cmd":3}';
          }

          // send cutedge
          if ($cmd->getName() == 'cutEdge') {
          //  $_message = '{"cmd":4}';
            $_message = '{"sc":{"ots":{"bc":1,"wtm":0}}}';
          }

          // send zoneTraining
          if ($cmd->getName() == 'zoneTraining') {
            $_message = '{"cmd":4}';
          }

          // send free command
          if ($cmd->getName() == 'set_schedule') {
            $req = explode(";", $_message); // format = numéro jour;heure:minute;durée en minutes;0 ou 1 pour la bordure
            $sched = array(
              $req[1],
              intval($req[2]),
              intval($req[3])
            );
            $_message = self::setDaySchedule($eqlogicid, intval($req[0]) , $sched);
          }
          // rain delay
          if (substr_compare($cmd->getName(), 'rain_delay', 0, 10) == 0) {
            $_message = '{"rd":' . $_message . '}';
            log::add('worxLandroidS', 'debug', 'Envoi du message rain delay: ' . $_message);
          }

          // if ( config::byKey('mowingTime', 'worxLandroidS') == '0' ){
          $eqptlist[]  = array();
          $eqptlist[0] = array(
            $eqlogic->getConfiguration('MowerType'),
            $eqlogic->getLogicalId(),
            $_message
          );
          self::connect_and_publish($eqptlist, $_message);
          // send cutedger
          if ($cmd->getName() == 'cutEdge') {
            sleep(7);
            $_message = '{"cmd":2}';
            // if ( config::byKey('mowingTime', 'worxLandroidS') == '0' ){
            $eqptlist[]  = array();
            $eqptlist[0] = array(
              $eqlogic->getConfiguration('MowerType'),
              $eqlogic->getLogicalId(),
              $_message
            );
            self::connect_and_publish($eqptlist, $_message);
            sleep(5);
            $_message = '{"cmd":3}';
            // if ( config::byKey('mowingTime', 'worxLandroidS') == '0' ){
            $eqptlist[]  = array();
            $eqptlist[0] = array(
              $eqlogic->getConfiguration('MowerType'),
              $eqlogic->getLogicalId(),
              $_message
            );
            self::connect_and_publish($eqptlist, $_message);

          }

        }

        public static $_widgetPossibility = array('custom' => array('visibility' => true, 'displayName' => true, 'displayObjectName' => true, 'optionalParameters' => false, 'background-color' => true, 'text-color' => true, 'border' => true, 'border-radius' => true, 'background-opacity' => true));


        public function toHtml($_version = 'dashboard')
        {

          $automaticWidget = config::byKey('automaticWidget', 'worxLandroidS');
          $jour            = array(
            "Dimanche",
            "Lundi",
            "Mardi",
            "Mercredi",
            "Jeudi",
            "Vendredi",
            "Samedi"
          );
          $replace         = $this->preToHtml($_version);
          if (!is_array($replace)) {
            return $replace;
          }
          $version                 = jeedom::versionAlias($_version);
          $replace['#worxStatus#'] = '';
          $today                   = date('w');
          //if ($version != 'mobile' || $this->getConfiguration('fullMobileDisplay', 0) == 1) {
          $worxStatus_template     = getTemplate('core', $version, 'worxStatus', 'worxLandroidS');
          for ($i = 0; $i <= 6; $i++) {
            $replaceDay                    = array();
            $replaceDay['#day#']           = $jour[$i];
            $replaceDay['#daynum#']           = $i;
            $startTime                     = $this->getCmd(null, 'Planning_startTime_' . $i);
            $cutEdge                       = $this->getCmd(null, 'Planning_cutEdge_' . $i);
            $duration                      = $this->getCmd(null, 'Planning_duration_' . $i);
            $replaceDay['#startTime#']     = is_object($startTime) ? $startTime->execCmd() : '';
            $replaceDay['#duration#']      = is_object($duration) ? $duration->execCmd() : '';
            $cmdS                          = $this->getCmd('action', 'on_' . $i);
            $replaceDay['#on_daynum_id#']  = $cmdS->getId();
            $cmdE                          = $this->getCmd('action', 'off_' . $i);
            $replaceDay['#off_daynum_id#'] = $cmdE->getId();

            //$replaceDay['#on_id#'] = $this->getCmd('action', 'on_1');
            //$replaceDay['#off_id#'] = $this->getCmd('action', 'off_1');
            // transforme au format objet DateTime
            if($replaceDay['#duration#'] == 0){
              $replaceDay['#checkedDaynum#'] = '';
            } else
            {
              $replaceDay['#checkedDaynum#'] = 'checked';
            }

            $initDate = DateTime::createFromFormat('H:i', $replaceDay['#startTime#']);
            if ($replaceDay['#duration#'] != '') {
              $initDate->add(new DateInterval("PT" . $replaceDay['#duration#'] . "M"));
              $replaceDay['#endTime#'] = $initDate->format("H:i");
            } else {
              $replaceDay['#endTime#'] = '00:00';
            }

            $replaceDay['#cutEdge#'] = is_object($cutEdge) ? $cutEdge->execCmd() : '';
            $replaceDay['#cutEdgeInfo#'] = $replaceDay['#cutEdge#'];
            if ($replaceDay['#cutEdge#'] == '1') {
              $replaceDay['#cutEdgeIcon#'] = 'fa nature-grass';
              $replaceDay['#cutEdge#'] = 'Bord.';
            } else {
              $replaceDay['#cutEdgeIcon#'] = 'fa fa-ban';
              $replaceDay['#cutEdge#'] = '.';
            }


            if ($startTime->getIsVisible()) {
              $replaceDay['#day_status_visible#'] = '';
            } else {
              $replaceDay['#day_status_visible#'] = 'display:none';
            }

            //$replaceDay['#icone#'] = is_object($condition) ? self::getIconFromCondition($condition->execCmd()) : '';
            //$replaceDay['#conditionid#'] = is_object($condition) ? $condition->getId() : '';
            $replace['#daySetup#'] .= template_replace($replaceDay, $worxStatus_template);

            if ($today == $i) {
              $replace['#todayStartTime#']      = is_object($startTime) ? $startTime->execCmd() : '';
              $replace['#todayDuration#']       = is_object($duration) ? $duration->execCmd() : '';
              $replace['#today_on_daynum_id#']  = $cmdS->getId();
              $replace['#today_off_daynum_id#'] = $cmdE->getId();
              if ($replaceDay['#duration#'] != '') {
                $replace['#todayEndTime#'] = $initDate->format("H:i");
              } else {
                $replace['#todayEndTime#'] = '00:00';
              }


              if ($replace['#cutEdge#'] == '1') {
                $replace['#cutEdge#'] = 'Bord.';
              }
              $replace['#today#'] = $jour[$i];
            }



          }
          //}

          $theme = jeedom::getThemeConfig();

          if(strstr($theme['current_desktop_theme'],'Light')  )
          { $replace['#theme#']  = "light";
           }
          elseif(strstr($theme['current_desktop_theme'],'Dark') )  {   $replace['#theme#']  = "dark";}
          else { $replace['#theme#']  = "dark"; $replace['#backgroundColor#']  = "background-color:black;opacity:0.8";}// legacy?or strstr($theme['current_desktop_theme'],'Legacy')


          $batteryLevelcmd = $this->getCmd(null, 'batteryLevel');
          $batteryLevel = is_object($batteryLevelcmd) ? $batteryLevelcmd->execCmd() : '';
          // BATTERIE
          if ( $batteryLevel > 90 )  $replace['#batteryIMG#']  = "batterie_full.png";
          else if ( $batteryLevel > 75  ) $replace['#batteryIMG#']  = "batterie_high.png";
          else if ( $batteryLevel> 50  ) $replace['#batteryIMG#']  = 'batterie_medium.png';
          else if ( $batteryLevel > 25  ) $replace['#batteryIMG#']  = 'batterie_low.png';
          else if ( $batteryLevel > 5  ) $replace['#batteryIMG#']  = 'batterie_highlow.png';
          //else  $('.cmd[data-cmd_uid=#uid#] .IMGbatterie#uid#').hide();
          //$('.cmd[data-cmd_uid=#uid#] .IMGbatterie#uid#').attr('title','Charge : '+batterie+' %');

          // WIFI
          $wifiQualitycmd = $this->getCmd(null, 'wifiQuality');
          $wifiQuality = is_object($wifiQualitycmd) ? $wifiQualitycmd->execCmd() : '';
          if ( $wifiQuality < 30 ) $replace['#wifiIMG#']  = 'wifi_high.png';
          else if ( $wifiQuality <= 80 ) $replace['#wifiIMG#']  = 'wifi_medium.png';
          else if ( $wifiQuality > 80 ) $replace['#wifiIMG#']  = 'wifi_low.png';
          //else  $('.cmd[data-cmd_uid=#uid#] .IMGwifi#uid#').hide();
          //$('.cmd[data-cmd_uid=#uid#] .IMGwifi#uid#').attr('title','Signal : '+wifi+' db');

          $cmdRainDelay = $this->getCmd(null, 'setRainDelay');
          $replace['#setRainDelay#'] =  $cmdRainDelay->toHtml($_version, '', $replace['#cmd-background-color#']);

          // calcul durée depuis dernier changement de lame
          $cmdLast = $this->getCmd(null, 'lastBladesChangeTime');
          $cmdTotal = $this->getCmd(null, 'totalBladeTime');
          $replace['#bladesDuration#'] = round( ( ( is_object($cmdTotal) ? $cmdTotal->execCmd() : 0 )
          - ( is_object($cmdLast) ? $cmdLast->execCmd() : 0) ) / 60 );

          $replace['#bladesDurationColor#'] = 'green';
          if ($replace['#bladesDuration#'] > $this->getConfiguration('maxBladesDuration')) {
            $replace['#bladesDurationColor#'] = 'orange';
          }


          $errorCode               = $this->getCmd(null, 'errorCode');
          $replace['#errorCode#']  = is_object($errorCode) ? $errorCode->execCmd() : '';
          $replace['#errorColor#'] = 'darkgreen';
          if ($replace['#errorCode#'] != 0) {
            $replace['#errorColor#'] = 'orange';
          }
          switch($replace['#errorCode#']){
            // affichage icone pluie
            case '0': $replace['#errorIcon#'] = 'jeedomapp-sun'; break;
            case '1': $replace['#errorIcon#'] = 'fas fa-exclamation-circle icon_red'; break;
            case '2': $replace['#errorIcon#'] = 'fas fa-arrow-up'; break;
            case '4': $replace['#errorIcon#'] = 'fas nature-wood6'; break;
            case '5': $replace['#errorIcon#'] = 'meteo-pluie'; break;
            case '8': $replace['#errorIcon#'] = 'jeedom-ventilo'; break;
            case '9': $replace['#errorIcon#'] = 'fas fa-ban'; break;
            case '12': $replace['#errorIcon#'] = 'jeedom-batterie0'; break;

            default : $replace['#errorIcon#'] = 'fas fa-exclamation-circle icon_red'; break;
          }
          $replace['#errorID#']          = is_object($errorCode) ? $errorCode->getId() : '';
          $errorDescription              = $this->getCmd(null, 'errorDescription');
          $replace['#errorDescription#'] = is_object($errorDescription) ? $errorDescription->execCmd() : '';

          $cmd_html = '';
          foreach ($this->getCmd('info') as $cmd) {
            $replace['#' . $cmd->getLogicalId() . '_history#'] = '';
            $replace['#' . $cmd->getLogicalId() . '_id#']      = $cmd->getId();
            $replace['#' . $cmd->getLogicalId() . '#']         = $cmd->execCmd();
            $replace['#' . $cmd->getLogicalId() . '_collect#'] = $cmd->getCollectDate();
            if ($cmd->getLogicalId() == 'encours') {
              $replace['#batteryLevel#'] = $cmd->getDisplay('icon');
            }

            if ($cmd->getIsVisible()) {
              $replace['#' . $cmd->getLogicalId() . '_visible#'] = '';
            } else {
              $replace['#' . $cmd->getLogicalId() . '_visible#'] = 'display:none';

            }


            if ($automaticWidget != true or $cmd->getLogicalId() == 'virtualInfo') {
              if ($cmd->getTemplate('dashboard', '') == '') {
                $cmd->setTemplate('dashboard', 'badge');
              }
              if (substr_compare($cmd->getName(), 'Planning', 0, 8) != 0) {
                $cmd_html .= $cmd->toHtml($_version, '', $replace['#cmd-background-color#']);
              }
            }
            if ($cmd->getIsHistorized() == 1) {
              $replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor';
            }
          }
          $cmdaction_html = '';
          foreach ($this->getCmd('action') as $cmd) {
            if ($cmd->getIsVisible()) {
              $replace['#' . $cmd->getLogicalId() . '_visible#'] = '';
            } else {
              $replace['#' . $cmd->getLogicalId() . '_visible#'] = 'display:none';

            }

            $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
            $replace['#cmdaction#'] = '';
            if($cmd->getIsVisible() and ( $cmd->getLogicalId() == 'set_schedule' )  ){

              $cmdaction_html .= $cmd->toHtml($_version,'', $replace['#cmd-background-color#']);
              $replace['#cmdaction#'] = $cmdaction_html;
            }
          }
          $code = $replace['#statusCode#'];
          if ($code <  5 or $code ==  10 or $code == 9 or $code == 34) {
            $replace['#moving#'] = 'display:none';
          }

          // nouveau template
          $replaceImg['#worxImg#'] = '';
          $replaceImg['#theme#'] = $replace['#theme#'];
          $worxImg_template     = getTemplate('core', $version, strval($code), 'worxLandroidS');
          $replace['#worxImg#'] .= template_replace($replaceImg, $worxImg_template);
          // fin nouveau template
          if ($cmd->getLogicalId() == 'virtualInfo') {
            $replace['#widget#'] = $cmd_html; // FIXME $cmd_html assigned to #widget# & #cmd# ?
          }
          $replace['#cmd#'] = $cmd_html; // FIXME $cmd_html assigned to #widget# & #cmd# ?

          if ($automaticWidget == true) {
            return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'worxMain', 'worxLandroidS')));
          } else {
            return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'worxMainOwn', 'worxLandroidS')));
          }
        }
      }

      class worxLandroidSCmd extends cmd
      {

        public function execute($_options = null)
        {
          if ($this->getLogicalId() == 'newBlades') {
            $elogic = $this->getEqLogic();
            $cmdin = worxLandroidSCmd::byEqLogicIdCmdName($elogic->getId(), 'totalBladeTime');
            $value = $cmdin->execCmd();
            $elogic->newInfo('lastBladesChangeTime', $value, 'numeric', 0);
            return true;
          } else {
            switch ($this->getType()) {
              case 'action':
              $request = $this->getConfiguration('request', '1');
              $topic   = $this->getConfiguration('topic');
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
              if ($this->getName() == 'set_schedule') {
                $request = $_options['message'];
              }
              $request = cmd::cmdToValue($request);
              // save schedule if setting to 0 - and retrieve from saved value (new values must be set from smartphone

              $eqlogic = $this->getEqLogic();
              log::add('worxLandroidS', 'debug', 'Eqlogicname: ' . $eqlogic->getName());
              worxLandroidS::publishMosquitto($this->getId(), $topic, $request, $this->getConfiguration('retain', '0'));
            }
          }
        }
      }
