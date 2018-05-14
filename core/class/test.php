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
/*
-- info --
https://github.com/mjiderhamn/worx-landroid-nodejs (Home automation integration for Worx Landroid robotic mowers)
https://hackaday.io/project/6717-project-landlord (Open source firmware for Worx Landroid robotic mower.)
https://www.worxlandroid.com/en/software-update (firmware update)
https://github.com/ldittmar81/ioBroker.landroid
//Redpine Signals, Inc.

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class worxLandroid extends eqLogic {
  public static $_widgetPossibility = array('custom' => true);
  /*     * *************************Attributs****************************** */
  private static $_client;
  private static $_client2;
  public static $_infosMap = array();
  public static $_actionMap = array();


  /*     * ***********************Methode static*************************** */

  /*
  * Fonction exécutée automatiquement toutes les minutes par Jeedom
  public static function cron() {

}
*/


public static function initInfosMap(){

  self::$_actionMap = array(
    'refresh' => array(
      'name' => 'Rafraichir',
    ),
    'start' => array(
      'name' => 'Démarrage',
      'cmd' => 'data=%5B%5B%22settaggi%22%2C11%2C1%5D%5D', //data=[["settaggi",{},1]]
      ),
      'stop' => array(
        'name' => 'Stop',
        'cmd' => 'data=%5B%5B%22settaggi%22%2C12%2C1%5D%5D', //data=[["settaggi",{},1]]
        ),
        'startZoneTraining' => array(
          'name' => 'Démarrage Entrainement',
          'cmd' => 'data=%5B%5B%22settaggi%22%2C11%2C1%5D%5D', //data=[["settaggi",{},1]]
          ),
          // 			'setWorkingTimePercent' => array(
          // 				'name' => "Définir Poucentage de temps de travail",
          // 				'linkedInfo' => 'WorkingTimePercent',
          // 				'subtype' => 'slider',
          // 				'cmd' => 'data=%5B%5B%22percent_programmatore%22%2C0%2C[[[VALUE]]]%5D%5D', //data=[["percent_programmatore",0,100]]
          // 			),
          /* a verifier
          // 11 = start
          // 12 = stop (& return to base)
          // 13 = charging complete
          // 14 = manual stop
          // 15 = going home
          */
        );

        self::$_infosMap = array(
          //'default' => array(
          //	'type' => 'info',
          //	'subtype' => 'numeric',
          //	'isvisible' => true,
          //	'restkey' =>'',
          //),
          'communicationStatus' => array(
            'name' => "Status de connexion",
            'subtype' => 'binary',
            'isvisible' => true,
          ),
          'firmwareVersion' => array(
            'name' => "Version du firmware",
            'restkey' =>'versione_fw', //"versione_fw": 2.45,
            'isvisible' => true,
          ),
          'language' => array(
            'name' => "code Langue",
            'restkey' =>'lingua',//"lingua": 2,
          ),
          'languageStr' => array(
            'name' => "Langue",
            'restkey' =>'lingua',//"lingua": 2,
            'subtype' => 'string',
            'cbTransform' => function ($rawValue)
            {
              $langue =  array(
                '0' =>"Anglais",
                '1' =>"Italien",
                '2' =>"Allemand",
                '3' =>"Français",
                '4' =>"Espagnol",
                '5' =>"Portugais",
                '6' =>"Danois",
                '7' =>"Néerlandais",
                '8' =>"Finnois",
                '9' =>"Norvégien",
                '10' =>"Suédois",
              );
              return ($langue[$rawValue]);
              },
            ),
            'batteryPercentage' => array(
              'name' => "Pourcentage de batterie",
              'restkey' =>'perc_batt', //	"perc_batt": "100",
              'isvisible' => true,
              'unite' => '%',
            ),
            'workingTimePercent' => array(
              'name' => "Poucentage de temps de travail",
              //'linkedAction' => 'setWorkingTimePercent',
              'restkey' =>'percent_programmatore', //"percent_programmatore": 0,
              'isvisible' => true,
              'unite' => '%',
            ),
            'totalMowingHours' => array(
              'name' => "Temps total de tonte",
              'restkey' =>'ore_movimento', //"ore_movimento": 626, // Provided as 0.1h
              'isvisible' => true,
            ),
            'timeFormat' => array(
              'name' => "Format heure",
              'restkey' =>'time_format', //"time_format": 1,
            ),
            'dateFormat' => array(
              'name' => "Format date",
              'restkey' =>'date_format', //"date_format": 0,
            ),
            'rit_pioggia' => array(
              'name' => "Tondre apres la pluie",
              'unite' => 'min',
              'restkey' =>'rit_pioggia', //"rit_pioggia": 180,
              'isvisible' => true,
            ),
            'area' => array(
              'name' => "Area",
              'restkey' =>'area', //"area": 0,
            ),
            'enab_bordo' => array(
              'name' => "Activé la coupe des bordures",
              'restkey' =>'enab_bordo',
            ),
            'indice_area' => array(
              'name' => "Taille du jardin",
              'restkey' =>'indice_area',//"indice_area": 9,
              'unite' => 'm²',
              'cbTransform' => function ($rawValue)
              {
                return ($rawValue * 100) + 100;
                },
              ),
              'tempo_frenatura' => array(
                'name' => "Temps de freinage",
                'restkey' =>'tempo_frenatura',//"tempo_frenatura": 20,
              ),
              'perc_rallenta_max' => array(
                'name' => "Pourcentage de ralentissement max",
                'restkey' =>'perc_rallenta_max', //"perc_rallenta_max": 70,
              ),
              'canale' => array(
                'name' => "Canal",
                'restkey' =>'canale', //"canale": 0,
              ),
              'num_ricariche_batt' => array(
                'name' => "Nombres de recharge de la batterie",
                'restkey' =>'num_ricariche_batt', //"num_ricariche_batt": 0,
              ),
              'num_aree_lavoro' => array(
                'name' => "Numéro de la zone de travail",
                'restkey' =>'num_aree_lavoro', //"num_aree_lavoro": 1,
              ),
              'area_in_lavoro' => array(
                'name' => "Zone de travail",
                'restkey' =>'area_in_lavoro', //	"area_in_lavoro": 0,
              ),
              'email' => array(
                'name' => "Email",
                'subtype' => 'string',
                'restkey' =>'email', //"email": "xxxxxxx@xxxxxx.xxx",
              ),
              'ver_proto' => array(
                'name' => "ver_proto",
                'restkey' =>'ver_proto', //"ver_proto": 1,
              ),
              'state' => array(
                'name' => "Status",
                'subtype' => 'string',
                'restkey' =>'state', //"state": "home","grass cutting","following wire"
                'isvisible' => true,
              ),
              'workReq' => array(
                'name' => "workReq",
                'subtype' => 'string',
                'restkey' =>'workReq', //"workReq": "user req grass cut",
              ),
              'message' => array(
                'name' => "Message",
                'subtype' => 'string',
                'restkey' =>'message', //"message": "none",
              ),
              'batteryChargerState' => array(
                'name' => "Status du chargeur de batterie",
                'subtype' => 'string',
                'restkey' =>'batteryChargerState', //"batteryChargerState": "idle",
              ),
              'distance' => array(
                'name' => "Distance",
                'restkey' =>'distance', //"distance": 0
              ),
              'mac' => array(
                'name' => "Mac adresse",
                'subtype' => 'string',
                'restkey' =>'mac', //"mac": [0, 35, 167, 164, 213, 71],
                'cbTransform' => function ($rawValue)
                {
                  return strtoupper(implode(':',array_map("sprintf",array_fill(0,6,'%02x'),$rawValue)));
                  },
                ),
                'ore_funz' => array(// Decides for how long the mower will work each day, probably expressed as 0,1 h
                  'name' => "Heures de fonctionement",
                  'subtype' => 'string',
                  'restkey' =>'ore_funz', //"ore_funz": [0, 0, 0, 0, 0, 0, 0],
                  'cbTransform' => function ($rawValue)
                  {
                    return json_encode(array_combine(array('lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche'),$rawValue));
                    },
                  ),

                  'ora_on' => array(// Hour of day that the Landroid should mowing, per weekday
                    'name' => "Heure de la tonte par jours",
                    'subtype' => 'string',
                    'restkey' =>'ora_on', //"ora_on": [0, 0, 0, 0, 0, 0, 0],
                    'cbTransform' => function ($rawValue)
                    {
                      return json_encode(array_combine(array('lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche'),$rawValue));
                      },
                    ),
                    'min_on' => array(// Minutes on the hour (above) that the Landroid should start mowing, per weekday
                      'name' => "Minute de la tonte par jours",
                      'subtype' => 'string',
                      'restkey' =>'min_on', //"min_on": [0, 0, 0, 0, 0, 0, 0],
                      'cbTransform' => function ($rawValue)
                      {
                        return json_encode(array_combine(array('lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche'),$rawValue));
                        },
                      ),
                      'allarmi' => array( // Alarms - flags set to 1 when alarm is active
                        'name' => "Alarmes",
                        'subtype' => 'string',
                        'restkey' =>'allarmi', //"allarmi": [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                        'cbTransform' => function ($rawValue)
                        {
                          $alarmStr = array(
                            0 => "Blade blocked",
                            1 => "Repositioning error",
                            2 => "Wire bounced",
                            3 => "Blade blocked",
                            4 => "Outside wire",// ("Outside working area")
                            5 => "Mower lifted",// ("Lifted up")
                            6 => "Error 6",
                            7 => "Upside down",// (Set when "Lifted up" - "Upside down"?)
                            8 => "Error 8",
                            9 => "Collision sensor blocked",
                            10 => "Mower tilted",
                            11 => "Charge error",// (Set when "Lifted up"?)
                            12 => "Battery error",
                          );
                          $alarm = '';
                          foreach($rawValue as $idx=>$alarmed)
                          {
                            if(isset($alarmStr[$idx]) && ($alarmed==1))
                            $alarm .= $alarmStr[$idx].';';
                            }
                            return $alarm;
                            //return json_encode($rawValue);
                            },
                            // 				"allarmi": [ // Alarms - flags set to 1 when alarm is active
                            // 					0, // [0] "Blade blocked"                                               ERROR_MESSAGES[0] = "Blade blocked";
                            // 					0, // [1] "Repositioning error"                                         ERROR_MESSAGES[1] = "Repositioning error";
                            // 					0, // [2] "Wire bounced"                                                ERROR_MESSAGES[WIRE_BOUNCED_ALARM_INDEX] = "Wire bounced";
                            // 					0, // [3] "Blade blocked"                                               ERROR_MESSAGES[3] = "Blade blocked";
                            // 					0, // [4] "Outside wire" ("Outside working area")                       ERROR_MESSAGES[4] = "Outside wire";
                            // 					0, // [5] "Mower lifted" ("Lifted up")                                  ERROR_MESSAGES[5] = "Mower lifted";
                            // 					0, // [6] "error 6"                                                       ERROR_MESSAGES[6] = "Alarm 6";
                            // 					0, // [7] "Upside down" (Set when "Lifted up" - "Upside down"?)               ERROR_MESSAGES[7] = "Upside down";
                            // 					0, // [8] "error 8"                                                       ERROR_MESSAGES[8] = "Alarm 8";
                            // 					0, // [9] "Collision sensor blocked"                                    ERROR_MESSAGES[8] = "Collision sensor blocked";
                            // 					0, // [10] "Mower tilted"                                               ERROR_MESSAGES[10] = "Mower tilted";
                            // 					0, // [11] "Charge error" (Set when "Lifted up"?)                       ERROR_MESSAGES[11] = "Charge error";
                            // 					0, // [12] "Battery error"                                              ERROR_MESSAGES[12] = "Battery error";
                            // 					0, // Reserved for future use?
                            // 					0, // -- " --
                            // 					0, // -- " --
                            // 					0, // -- " --
                            // 					0, // -- " --
                            // 					0, // -- " --
                            // 					0, // -- " --
                            // 					0, // -- " --
                            // 					0, // -- " --
                            // 					0, // -- " --
                            // 					0, // -- " --
                            // 					0, // -- " --
                            // 					0, // -- " --
                            // 					0, // -- " --
                            // 					0, // -- " --
                            // 					0, // -- " --
                            // 					0, // -- " --
                            // 					0  // -- " --
                            // 				],
                          ),
                          'settaggi' => array(// Settings / state
                            'name' => "Parametres",
                            'subtype' => 'string',
                            'restkey' =>'settaggi', //"settaggi": [0, 0, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                            'cbTransform' => function ($rawValue)
                            {
                              return json_encode($rawValue);
                              },
                              // 				"settaggi": [ // Settings / state
                              // 					0,
                              // 					0,
                              // 					0,
                              // 					0,
                              // 					1,
                              // 					0, // "in base" ("charging" or "charging completed", see [13])
                              // 					0,
                              // 					1,
                              // 					1,
                              // 					1,
                              // 					0,
                              // 					0, // "start"
                              // 					0, // "stop"
                              // 					0, // "charging completed"
                              // 					0, // "manual stop"
                              // 					0, // "going home"
                              // 					0,
                              // 					0,
                              // 					0,
                              // 					0,
                              // 					0,
                              // 					0,
                              // 					0,
                              // 					0,
                              // 					0,
                              // 					0,
                              // 					0,
                              // 					0,
                              // 					0,
                              // 					0,
                              // 					0
                              // 				],
                            ),
                            'dist_area' => array( // Distance in meters to the zone starts
                              'name' => "Distance en metre de la zone de depart",
                              'subtype' => 'string',
                              'restkey' =>'dist_area', //"dist_area": [1, 1, 1, 1],
                              'cbTransform' => function ($rawValue)
                              {
                                return json_encode($rawValue);
                                },
                              ),
                              'perc_per_area' => array( // Percentage per zone, expressed in 10% increments (i.e. 3 = 30%)
                                'name' => "Pourcentage par zone",
                                'subtype' => 'string',
                                'restkey' =>'perc_per_area', //"perc_per_area": [1, 1, 1, 1],
                                'cbTransform' => function ($rawValue)
                                {
                                  return json_encode($rawValue);
                                  },
                                ),
                              );
                            }

                            public static function cron() {




                              foreach (eqLogic::byType('worxLandroid') as $worxLandroid)
                              {


                                //log::add('worxLandroid', 'debug', 'publish1:'.self::getMqttId());
                                //log::add('worxLandroid', 'debug', 'publish2:'.config::byKey('mqtt_client_id','worxLandroid'));

//																		self::publishMosquitto(config::byKey('mqtt_client_id','worxlandroid'), 'nom equip a mettre',  "DB510/" .config::byKey('mac_address','worxlandroid'). "/commandIn", "{}" , 0, 0);

                              //	self::$_client2->publish("DB510/" .config::byKey('mac_address','worxlandroid'). "/commandIn", "{}" , 0, 0);
                                //self::$_client2->disconnect();


                                //$worxLandroid->getInformations();
                                $mc = cache::byKey('worxLandroidWidgetmobile' . $worxLandroid->getId());
                                $mc->remove();
                                $mc = cache::byKey('worxLandroidWidgetdashboard' . $worxLandroid->getId());
                                $mc->remove();
                                $worxLandroid->toHtml('mobile');
                                $worxLandroid->toHtml('dashboard');
                                $worxLandroid->refreshWidget();
                              }

                            }

                            /*
                            * Fonction exécutée automatiquement toutes les heures par Jeedom
                            public static function cronHourly() {

                          }
                          */

                          /*
                          * Fonction exécutée automatiquement tous les jours par Jeedom
                          public static function cronDayly() {

                        }
                        */

                        /*     * *********************Méthodes d'instance************************* */

                        public function refresh() {
                          try {
                            $this->getInformations();
                          } catch (Exception $exc) {
                            log::add('worxLandroid', 'error', __('Erreur pour ', __FILE__) . $eqLogic->getHumanName() . ' : ' . $exc->getMessage());
                          }
                        }

                        public static function deamon_stop() {
                            log::add('worxLandroid', 'debug', 'deamon_stop');
                            if (isset(self::$_client2)) {
                                log::add('worxLandroid', 'debug', 'disconnect MQTT client');
                                self::$_client2->disconnect();
                            }
                            $cron = cron::byClassAndFunction('worxLandroid', 'daemon');
                            if (!is_object($cron)) {
                                throw new Exception(__('Tache cron introuvable', __FILE__));
                            }
                            $cron->halt();
                            // Unset the variable after calling halt as the deamon uses the client variable
                            self::$_client2 = NULL;
                        }
                        /**
                          * Connect to the broker and suscribes topics
                          * @param object client client to connect
                          */
                         private static function mqtt_connect_subscribe($client, $mac_address) {
                             $mosqHost = config::byKey('mqtt_endpoint', 'worxLandroid', 'localhost');
                             $mosqPort = config::byKey('mqttPort', 'worxLandroid', '8883');
                             log::add('worxLandroid', 'info', 'Connect to mosquitto: Host=' . $mosqHost . ', Port=' . $mosqPort .
                              ', Id=' . self::getMqttId());
                              $resource_path = realpath(dirname(__FILE__) . '/../../resources/');

                              $certfile = $resource_path.'/cert.pem';
                              $pkeyfile = $resource_path.'/pkey.pem';
                              $root_ca = $resource_path.'/vs-ca.pem';
                             //$client->setTlsCertificates($root_ca,$certfile,$pkeyfile,null);
                            // $client->connect($mosqHost, $mosqPort);

                             //log::add('worxLandroid', 'info', 'certificate' . $certfile);



                            //$client->connect($mosqHost, $mosqPort);
                            //$topic = config::byKey('mqttTopic', 'worxLandroid', 'DB510/'.$mac_address.'/commandOut');
                            // $client->subscribe($topic, 1);
                             //self::$_client2->publish("DB510/" .config::byKey('mac_address','worxlandroid'). "/commandIn", "{}" , 0, 0);
                             /*
                             if (config::byKey('include_mode', 'worxLandroid', 0) == 0) {  // manual inclusion mode
                                 // Loop on all equipments and subscribe
                                 foreach (eqLogic::byType('worxLandroid', true) as $mqtt) {
                                     $topic = $worxLandroid->getConfiguration('topic');
                                     $qos   = (int) $worxLandroid->getConfiguration('Qos', '1');
                                     if (empty($topic))
                                         log::add('worxLandroid', 'info', 'Equipment ' . $worxLandroid->getName() . ': no subscription (empty topic)');
                                     else {
                                         log::add('worxLandroid', 'info', 'Equipment ' . $worxLandroid->getName() . ': subscribes to "' . $topic .
                                    '" with Qos=' . $qos);
                                         $client->subscribe('DB510/'.config::byKey('mac_address','worxlandroid').'/commandOut', $qos);
                                     }
                                 }
                             }
                             else { // auto inclusion mode
                                 $topic = config::byKey('mqttTopic', 'worxLandroid', 'DB510/'.config::byKey('mac_address').'/commandOut');
                                 // Subscribe to topic (root by default)
                                 $client->subscribe($topic, 1);
                                 log::add('worxLandroid', 'debug', 'Subscribe to topic "' . $topic . '" with Qos=1');
                             }*/

                         }





                        public function getInformations($jsondata=null)
                        {
                          if ($this->getIsEnable() == 1)
                          {

                            if ($this->getConfiguration('mowertype') == 'LandroidM'){
                              $equipement = $this->getName();

                              if(is_null($jsondata))
                              {
                                $ip = $this->getConfiguration('addressip');
                                $user = $this->getConfiguration('user','admin');
                                $pin = $this->getConfiguration('pincode');

                                $url = "http://{$user}:{$pin}@{$ip}/jsondata.cgi";
                                log::add('worxLandroid', 'debug', __METHOD__.' '.__LINE__.' requesting '.$url);

                                //$jsondata = file_get_contents($url);
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $url);
                                curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                $jsondata = curl_exec($ch);
                                curl_close($ch);
                              }

                              log::add('worxLandroid', 'debug', __METHOD__.' '.__LINE__.' $jsondata '.$jsondata);

                              $json = json_decode($jsondata,true);

                              if (is_null($json))
                              {
                                log::add('worxLandroid', 'info', 'Connexion KO for '.$equipement.' ('.$ip.')');
                                $this->checkAndUpdateCmd('communicationStatus',false);
                                return false;
                              }
                              if (!isset($json['allarmi']))
                              {
                                log::add('worxLandroid', 'error', 'Check PinCode for '.$equipement.' ('.$ip.')');
                                $this->checkAndUpdateCmd('communicationStatus',false);
                                return false;
                              }

                              $this->checkAndUpdateCmd('communicationStatus',true);

                              self::initInfosMap();

                              //update cmdinfo value
                              foreach(self::$_infosMap as $cmdLogicalId=>$params)
                              {
                                if(isset($params['restkey'], $json[$params['restkey']]))
                                {
                                  //log::add('worxLandroid', 'debug',  __METHOD__.' '.__LINE__.' '.$cmdLogicalId.' => '.json_encode($json[$params['restkey']]));
                                  $value = $json[$params['restkey']];
                                  if(isset($params['cbTransform']) && is_callable($params['cbTransform']))
                                  {
                                    $value = call_user_func($params['cbTransform'], $value);
                                    //log::add('worxLandroid', 'debug', __METHOD__.' '.__LINE__.' Transform to => '.json_encode($value));
                                  }

                                  $this->checkAndUpdateCmd($cmdLogicalId,$value);
                                }
                              }
                              return true;
                            }else{

                              // landroidS

                              //$mqtt_client_id = worxLandroid->getConfiguration('mqtt_client_id');
                              //log::add('worxLandroid', 'debug', 'mqtt_client id = ', self::getMqttId());
                              $mqtt_client_id = $this->getConfiguration('mqtt_client_id');
                                log::add('worxLandroid', 'info', 'Connexion ok'.config::byKey('mqtt_client_id','worxLandroid'));

                              self::$_client2 = new Mosquitto\Client(self::getMqttId());
                              /*
                              $client2->onConnect(function() use ($client2) {
                                log::add('worxLandroid', 'info', 'Connexion ok'.config::byKey('mqtt_client_id','worxLandroid'));
                                $client2->disconnect();
                              }); */
                              //https://www.symantec.com/content/en/us/enterprise/verisign/roots/VeriSign-Class%203-Public-Primary-Certification-Authority-G5.pem
                              $resource_path = realpath(dirname(__FILE__) . '/../../resources/');

                              $certfile = $resource_path.'/cert.pem';
                              $pkeyfile = $resource_path.'/pkey.pem';
                              $root_ca = $resource_path.'/vs-ca.pem';

                              self::$_client2->setTlsCertificates($root_ca,$certfile,$pkeyfile,null);

                              //$client2->setTlsCertificates('/etc/mosquitto/ca_certificates/VeriSign.pem' );
                              //$client2->onConnect('worxLandroid::mosquittoConnect');

                              self::$_client2->onConnect('worxLandroid::mosquittoConnect');
                              self::$_client2->onDisconnect('worxLandroid::mosquittoDisconnect');
                              self::$_client2->onSubscribe('worxLandroid::mosquittoSubscribe');
                              self::$_client2->onUnsubscribe('worxLandroid::mosquittoUnsubscribe');
                              self::$_client2->onMessage('worxLandroid::mosquittoMessage');
                              self::$_client2->onLog('worxLandroid::mosquittoLog');



                          //		self::mqtt_connect_subscribe(self::$_client2, config::byKey('mac_address','worxlandroid'));
                              // Defines last will terminaison message
                              //$client2->setWill(self::getMqttId() . '/status', 'offline', 1);

// 														$client2->onConnect(function() use ($client2) {

//																log::add('worxLandroid', 'info', 'Connexion ok');
                              //$client2->subscribe('DB510/'.config::byKey('mac_address','worxlandroid').'/commandOut', 0);
//});
                                //$client2->publish("DB510/" .config::byKey('mac_address','worxlandroid'). "/commandIn", "{}")
                              //	$client2->exitLoop();
                              //	$client2->unsubscribe('DB510/'.config::byKey('mac_address','worxlandroid').'/commandOut', 0);

                                //$client2->disconnect();
                                /*
                                for ($i = 0; $i < 10; $i++) {
                                    $client2->loop();
                                }
                                $client2->unsubscribe('#');
                                for ($i = 0; $i < 10; $i++) {
                                    $client2->loop();
                                }


*/
log::add('worxLandroid', 'info', 'Connexion en cours '.config::byKey('mqtt_endpoint','worxLandroid'));


          self::$_client2->connect(config::byKey('mqtt_endpoint','worxLandroid'), 8883, 60);

//																																		$client2->subscribe('DB510/'.config::byKey('mac_address','worxlandroid').'/commandOut', 0);
//																																	$client2->publish("DB510/" .config::byKey('mac_address','worxlandroid'). "/commandIn", "{}");

                                                                //	$client2->loop();


/*
                              });*/

/*
  $client2->onPublish(function($mid) {
     // MQ::confirm($mid);
      //print_r(array('comfirm publish', MQ::$publish[$mid]));
  });

*/

                              //self::$_client2->connect(config::byKey('mqtt_endpoint','worxLandroid'), 8883,60);
      //												self::$_client2->subscribe('DB510/'.config::byKey('mac_address','worxlandroid').'/commandOut', 0);
                              //self::mqtt_connect_subscribe(self::$_client2, config::byKey('mac_address','worxlandroid'));
                              //self::$_client2->publish("DB510/" .config::byKey('mac_address','worxlandroid'). "/commandIn", "{}")
                              //self::publishMosquitto(self::getMqttId(), 'nom equip a mettre',  "DB510/" .config::byKey('mac_address','worxlandroid'). "/commandIn", "{}" , 0, 0);
                            //  self::$_client2->publish("DB510/" .config::byKey('mac_address','worxlandroid'). "/commandIn", "{}" , 0, 0);
                             //self::$_client2->loopForever();
                        /*			$client2->subscribe('DB510/'.config::byKey('mac_address','worxlandroid').'/commandOut', 0);

                              for ($i = 0; $i < 1; $i++) {
                                  $client2->loop();
                              }
                            //	$client2->unsubscribe('DB510/'.config::byKey('mac_address','worxlandroid').'/commandOut');
                              for ($i = 0; $i < 1; $i++) {
                                  $client2->loop();
                              }

*/
                            // $client2->subscribe('DB510/'.config::byKey('mac_address','worxlandroid').'/commandOut', 0);


                              //$client2->loopForever();

                              //log::add('worxLandroid', 'debug', 'daemon starts, pid is ' . getmypid());
                            //	log::add('worxLandroid', 'debug', 'mqtt_client id = ', getMqttId());
                            //	log::add('worxLandroid', 'debug', 'mqtt_end point = ', config::byKey('mqtt_endpoint','worxLandroid'));
                              // Create mosquitto client
                              /*
                              self::$_client = self::newMosquittoClient(self::getMqttId());
                              // Set callbacks
                              self::$_client->onConnect('worxLandroid::mosquittoConnect');
                              self::$_client->onDisconnect('worxLandroid::mosquittoDisconnect');
                              self::$_client->onSubscribe('worxLandroid::mosquittoSubscribe');
                              self::$_client->onUnsubscribe('worxLandroid::mosquittoUnsubscribe');
                              self::$_client->onMessage('worxLandroid::mosquittoMessage');
                              self::$_client->onLog('worxLandroid::mosquittoLog');
                              // Defines last will terminaison message
                              self::$_client->setWill(self::getMqttId() . '/status', 'offline', 1, 1);
                              // Suppress the exception management here. We let exceptions being thrown to the upper level
                              // and rely on the daemon management of the jeedom core: if automatic management is activated, the deamon
                              // is restarted every 5min.
                              //self::$_client->setTlsCertificates($root_ca,$certfile,$pkeyfile,null);
                              //self::mqtt_connect_subscribe(self::$_client);
                              //self::$_client->connect(config::byKey('mqtt_endpoint','worxLandroid'), 8883);
                               //$client->subscribe('DB510/'.config::byKey('mac_address','worxlandroid').'/commandOut', 0);
                              //self::$_client->loopForever();


*/


                              // end landroid S

                            }



                          }
                          else{

                            if(isset(self::$_client2))
                            {self::$_client2->disconnect();}
                          }
                        }

                        public function postSave() {






                          $mowertype = $this->getConfiguration('mowertype');
                          $email = $this->getConfiguration('email');
                          $passwd = $this->getConfiguration('passwd');
                          $mowernb = $this->getConfiguration('mowernb');
                        //	if($mowernb == null){$mowernb = 1};




                          log::add('worxLandroid', 'debug', __METHOD__.' '.__LINE__.' Landroid type '.$mowertype);
                          if($mowertype == 'LandroidS'){


                            // landroid S

                            $resource_path = realpath(dirname(__FILE__) . '/../../resources/');

                            $certfile = $resource_path.'/cert.pem';
                            $pkeyfile = $resource_path.'/pkey.pem';
                            $root_ca = $resource_path.'/vs-ca.pem';
                            //landroid S

                            //	$mowertype = $this->getConfiguration('mowertype');
                            //	$email = config::byKey('email','worxLandroid');
                            //	$passwd = config::byKey('passwd','worxLandroid');
                            //	$id = $this->getConfiguration('id');

                            //	log::add('worxLandroid', 'debug', __METHOD__.' '.__LINE__.' initialization mower type:'.$mowertype);


                            // get mqtt config
                            $url =  "https://api.worxlandroid.com:443/api/v1/users/auth";

                            $token = "qiJNz3waS4I99FPvTaPt2C2R46WXYdhw";
                            $content = "application/json";
                            //log::add('worxLandroid', 'debug', __METHOD__.' '.__LINE__.' requesting LandroidS'.$token);
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
                          log::add('worxLandroid', 'info', 'Connexion result :'.$result);

                          $json = json_decode($result,true);

                          if (is_null($json))
                          {
                            log::add('worxLandroid', 'info', 'Connexion KO for '.$equipement.' ('.$ip.')');
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
                          //log::add('worxLandroid', 'info', 'Connexion result :'.$result);

                          $json2 = json_decode($result,true);

                          if (is_null($json2))
                          {
                          } else
                          {



                            //  $pkcs12 = base64_encode($json2['pkcs12']);
                            //$pkcs12 = $json2['pkcs12'];
                            $pkcs12 = base64_decode($json2['pkcs12']);

                            openssl_pkcs12_read( $pkcs12, $certs, "" );



                            file_put_contents($certfile, $certs['cert']);
                            file_put_contents($pkeyfile, $certs['pkey']);
                            //log::add('worxLandroid', 'info', 'Cert '.$certfile);
                            //log::add('worxLandroid', 'info', 'Cert '.$pkeyfile);


                            //$this->setConfiguration('pkcs12', $pkcs12);
                            //	log::add('worxLandroid', 'info', 'Certificate '.$pkcs12);
                            //log::add('worxLandroid', 'info', 'Cert '.$certs['cert']);
                            //log::add('worxLandroid', 'info', 'key '.$certs['pkey']);


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
                          //log::add('worxLandroid', 'info', 'Connexion result :'.$result);

                          $json3 = json_decode($result,true);

                          if (is_null($json3))
                          {
                          } else
                          {

                            config::save('mac_address', $json3[0]['mac_address'],'worxLandroid');
                            log::add('worxLandroid', 'info', 'mac_address '.$json3[0]['mac_address']);

                          }


                          // test client2
                          config::save('mqtt_client_id', $json['mqtt_client_id'],'worxLandroid');
                          config::save('mqtt_endpoint', $json['mqtt_endpoint'],'worxLandroid');
                          log::add('worxLandroid', 'info', 'mqtt_client_id '.$json['mqtt_client_id']);
                          /*
                          $client2 = new Mosquitto\Client($json['mqtt_client_id']);
                          $client2->onConnect(function() use ($client2) {
                            log::add('worxLandroid', 'info', 'Connexion ok');
                            $client2->disconnect();
                          });
                          //https://www.symantec.com/content/en/us/enterprise/verisign/roots/VeriSign-Class%203-Public-Primary-Certification-Authority-G5.pem

                          $client2->setTlsCertificates($root_ca,$certfile,$pkeyfile,null);
*/
                          //$client2->setTlsCertificates('/etc/mosquitto/ca_certificates/VeriSign.pem' );

                          //	$client2->connect($json['mqtt_endpoint'], 8883);
                          //	$client2->loopForever();


                        }

                      }


                      // end landroid S


                    }elseif ($mowertype == 'LandroidM') {
                      self::initInfosMap();
                    }


                    $order = 0;

                    //Cmd Actions
                    foreach(self::$_actionMap as $cmdLogicalId => $params)
                    {
                      $worxLandroidCmd = $this->getCmd('action', $cmdLogicalId);
                      if (!is_object($worxLandroidCmd))
                      {
                        log::add('worxLandroid', 'debug', __METHOD__.' '.__LINE__.' cmdAction create '.$cmdLogicalId.'('.__($params['name'], __FILE__).') '.($params['subtype'] ?: 'subtypedefault'));
                        $worxLandroidCmd = new worxLandroidCmd();

                        $worxLandroidCmd->setLogicalId($cmdLogicalId);
                        $worxLandroidCmd->setEqLogic_id($this->getId());
                        $worxLandroidCmd->setName(__($params['name'], __FILE__));
                        $worxLandroidCmd->setType($params['type'] ?: 'action');
                        $worxLandroidCmd->setSubType($params['subtype'] ?: 'other');
                        $worxLandroidCmd->setIsVisible($params['isvisible'] ?: true);
                        if(isset($params['tpldesktop']))
                        $worxLandroidCmd->setTemplate('dashboard',$params['tpldesktop']);
                        if(isset($params['tplmobile']))
                        $worxLandroidCmd->setTemplate('mobile',$params['tplmobile']);
                        $worxLandroidCmd->setOrder($order++);

                        if(isset($params['linkedInfo']))
                        $worxLandroidCmd->setValue($this->getCmd('info', $params['linkedInfo']));

                        $worxLandroidCmd->save();
                      }
                    }

                    //Cmd Infos
                    foreach(self::$_infosMap as $cmdLogicalId=>$params)
                    {
                      $worxLandroidCmd = $this->getCmd('info', $cmdLogicalId);
                      if (!is_object($worxLandroidCmd))
                      {
                        log::add('worxLandroid', 'debug', __METHOD__.' '.__LINE__.' cmdInfo create '.$cmdLogicalId.'('.__($params['name'], __FILE__).') '.($params['subtype'] ?: 'subtypedefault'));
                        $worxLandroidCmd = new worxLandroidCmd();

                        $worxLandroidCmd->setLogicalId($cmdLogicalId);
                        $worxLandroidCmd->setEqLogic_id($this->getId());
                        $worxLandroidCmd->setName(__($params['name'], __FILE__));
                        $worxLandroidCmd->setType($params['type'] ?: 'info');
                        $worxLandroidCmd->setSubType($params['subtype'] ?: 'numeric');
                        $worxLandroidCmd->setIsVisible($params['isvisible'] ?: false);
                        if(isset($params['unite']))
                        $worxLandroidCmd->setUnite($params['unite']);
                        $worxLandroidCmd->setTemplate('dashboard',$params['tpldesktop']?: 'badge');
                        $worxLandroidCmd->setTemplate('mobile',$params['tplmobile']?: 'badge');
                        $worxLandroidCmd->setOrder($order++);

                        $worxLandroidCmd->save();
                      }
                    }

                    //refreshcmdinfo
                    $this->getInformations();
                  }


//mosquitto
public static function mosquittoConnect($r, $message) {
    log::add('worxLandroid', 'debug', 'mosquitto: connection response is ' . $message);
  //	self::$_client->publish(self::getMqttId() . '/status', 'online', 1, 1);
  //	config::save('status', '1',  'worxLandroid');
}
public static function mosquittoDisconnect($r) {
    $msg = ($r == 0) ? 'on client request' : 'unexpectedly';
    log::add('worxLandroid', 'debug', 'mosquitto: disconnected' . $msg);
    self::$_client->publish(self::getMqttId() . '/status', 'offline', 1, 1);
    config::save('status', '0',  'worxLandroid');
}
public static function mosquittoSubscribe($mid, $qosCount) {
    // Note: qosCount is not representative, do not display it (fix #31)
    log::add('worxLandroid', 'debug', 'mosquitto: topic subscription accepted, mid=' . $mid);
}
public static function mosquittoUnsubscribe($mid) {
    log::add('worxLandroid', 'debug', 'mosquitto: topic unsubscription accepted, mid=' . $mid);
}
public static function mosquittoLog($level, $str) {
    switch ($level) {
        case Mosquitto\Client::LOG_DEBUG:
$logLevel = 'debug'; break;
        case Mosquitto\Client::LOG_INFO:
        case Mosquitto\Client::LOG_NOTICE:
$logLevel = 'info'; break;
        case Mosquitto\Client::LOG_WARNING:
$logLevel = 'warning'; break;
        default:
$logLevel = 'error'; break;
    }
    log::add('worxLandroid', $logLevel, 'mosquitto: ' . $str);
//		self::$_client2->publish("DB510/" .config::byKey('mac_address','worxlandroid'). "/commandIn", "{}" , 0, 0);

}
/**
 * Callback called each time a subscirbed topic is dispatched by the broker.
 * @param strting $message dispatched message
 */
public static function mosquittoMessage($message) {
//$_client2->disconnect();
    $msgTopic = $message->topic;
    $msgValue = $message->payload;
    log::add('worxLandroid', 'debug', 'Message ' . $msgValue . ' sur ' . $msgTopic);
    // In case of topic starting with /, remove the starting character (fix Issue #7)
    // And set the topic prefix (fix issue #15)
    if ($msgTopic[0] === '/') {
        log::add('worxLandroid', 'debug', 'message topic starts with /');
        $topicPrefix = '/';
        $topicContent = substr($msgTopic, 1);
    }
    else {
        $topicPrefix = '';
        $topicContent = $msgTopic;
    }
    // Return in case of invalid topic
    if(!ctype_print($msgTopic) || empty($topicContent)) {
        log::add('worxLandroid', 'warning', 'Message skipped : "' . $msgTopic . '" is not a valid topic');
        return;
    }
    $msgTopicArray = explode("/", $topicContent);
    // Loop on worxLandroid equipments and get ones that subscribed to the current message
    $elogics = array();
    foreach (eqLogic::byType('worxLandroid', false) as $eqpt) {
        if ($message->topicMatchesSub($msgTopic, $eqpt->getConfiguration('topic'))) {
            $elogics[] = $eqpt;
        }
    }
    // If no equipment listening to the current message is found and the
    // automatic inclusion mode is active => create a new equipment
    // subscribing to all sub-topics starting with the first topic of the
    // current message
    if (empty($elogics) && config::byKey('include_mode', 'worxLandroid', 0) == 1) {
        $eqpt = worxLandroid::newEquipment($msgTopicArray[0], $topicPrefix . $msgTopicArray[0] . '/#');
  $elogics[] = $eqpt;
  // Advise the desktop page (javascript) that a new equipment has been added
  event::add('worxLandroid::includeEqpt', $eqpt->getId());
    }
    // No equipment listening to the current message is found
    // Should not occur: log a warning
    if (empty($elogics)) {
        log::add('worxLandroid', 'warning', 'No equipment listening to topic ' . $msgTopic);
        return;
    }
    //
    // Loop on enabled equipments listening to the current message
    //
    foreach($elogics as $eqpt) {
        if ($eqpt->getIsEnable()) {
            $eqpt->setStatus('lastCommunication', date('Y-m-d H:i:s'));
            $eqpt->save();
            // Determine the name of the command.
            // Suppress starting topic levels that are common with the equipment suscribing topic
            $sbscrbTopicArray = explode("/", $eqpt->getLogicalId());
            $msgTopicArray = explode("/", $msgTopic);
            foreach($sbscrbTopicArray as $s) {
                if ($s == '#' || $s == '+')
                    break;
                else
                    next($msgTopicArray);
            }
            $cmdName = current($msgTopicArray) === false ? end($msgTopicArray) : current($msgTopicArray);
            while(next($msgTopicArray) !== false) {
                $cmdName = $cmdName . '/' . current($msgTopicArray);
            }
            // Look for the command related to the current message
            $cmdlogic = worxLandroidCmd::byEqLogicIdAndLogicalId($eqpt->getId(), $msgTopic);
            // If no command has been found, try to create one
            // Note: worxLandroidCmd::newCmd returns NULL if parameter auto_add_cmd is not true
            if (!is_object($cmdlogic)) {
                $cmdlogic = worxLandroidCmd::newCmd($eqpt, $cmdName, $msgTopic);
            }
            if (is_object($cmdlogic)) {
                // If the found command is an action command, skip
                if ($cmdlogic->getType() == 'action') {
                    log::add('worxLandroid', 'debug', $eqpt->getName() . '|' . $cmdlogic->getName() . ' is an action command: skip');
                    continue;
                }
                // Update the command value
                $cmdlogic->updateCmdValue($msgValue);
                // Decode the JSON payload if requested
                if ($cmdlogic->getConfiguration('parseJson') == 1) {
                    $jsonArray = json_decode($msgValue, true);
                    if (is_array($jsonArray) && json_last_error() == JSON_ERROR_NONE)
                        worxLandroidCmd::decodeJsonMessage($eqpt, $jsonArray, $cmdName, $msgTopic);
                }
            }
        }
    }
}
/**
 * Return the MQTT id (default value = jeedom)
 * @return MQTT id.
 */
public static function getMqttId() {
    return config::byKey('mqtt_client_id', 'worxLandroid', 'jeedom');
}
/**
 * Create a mosquitto client based on the plugin parameters (mqttUser and mqttPass) and the given ID
 * @param string $_mosqIdSuffix suffix to concatenate to mqttId if the later is not empty
 */
private static function newMosquittoClient($_id = '') {
    //$mosqUser = config::byKey('mqttUser', 'worxLandroid', '');
    //$mosqPass = config::byKey('mqttPass', 'worxLandroid', '');
    // Création client mosquitto
    // Documentation passerelle php ici:
    //    https://github.com/mqtt/mqtt.github.io/wiki/mosquitto-php
    $client = ($_id == '') ? new Mosquitto\Client() : new Mosquitto\Client($_id);
    // Credential configuration when needed
    //if ($mosqUser != '') {
    //		$client->setCredentials($mosqUser, $mosqPass);
    //}
    // Automatic reconnexion delay
    $client->setReconnectDelay(1, 16, true);
    return $client;
}
/** Publish a given message to the mosquitto broker
 * @param string $id id of the command
 * @param string $eqName equipment name (for log purpose)
 * @param string $topic topic
 * @param string $message payload
 * @param string $qos quality of service used to send the message  ('0', '1' or '2')
 * @param string $retain whether or not the message is a retained message ('0' or '1')
 */
public static function publishMosquitto($id, $eqName, $topic, $payload, $qos , $retain) {
    $mosqHost = config::byKey('mqtt_endpoint', 'worxLandroid', 'localhost');
    $mosqPort = config::byKey('mqttPort', 'worxLandroid', '8883');
    $payloadMsg = (($payload == '') ? '(null)' : $payload);
    log::add('worxLandroid', 'info', '<- ' . $eqName . '|' . $topic . ' ' . $payloadMsg);
    // To identify the sender (in case of debug need), bvuild the client id based on the worxLandroid connexion id
    // and the command id.
    // Concatenates a random string to have a unique id (in case of burst of commands, see issue #23).
    $mosqId = self::getMqttId() . '/' . $id . '/' . substr(md5(rand()), 0, 8);
    // FIXME: the static class variable $_client is not visible here as the current function
    // is not executed on the same thread as the deamon. So we do create a new client.
//				$client = self::newMosquittoClient($mosqId);
    $client = self::newMosquittoClient($id);

    $client->onConnect(function() use ($client, $topic, $payload, $qos, $retain) {
        log::add('worxLandroid', 'debug', 'Publication du message ' . $topic . ' ' . $payload . ' (pid=' .
           getmypid() . ', qos=' . $qos . ', retain=' . $retain . ')');
        $client->publish($topic, $payload, $qos, (($retain) ? true : false));
        // exitLoop instead of disconnect:
        //   . otherwise disconnect too early for Qos=2 see below  (issue #25)
        //   . to correct issue #30 (action commands not run immediately on scenarios)
        $client->exitLoop();
    });
    // Connect to the broker
    $client->connect($mosqHost, $mosqPort, 60);
    // Loop around to permit the library to do its work
    // This function will call the callback defined in `onConnect()` and exit properly
    // when the message is sent and the broker disconnected.
    $client->loopForever();
    // For Qos=2, it is nessary to loop around more to permit the library to do its work (see issue #25)
    if ($qos == 2) {
        for ($i = 0; $i < 30; $i++) {
            $client->loop(1);
        }
    }
    $client->disconnect();
    log::add('worxLandroid', 'debug', 'Message publié');
}



    /// end mosquitto








    public function toHtml($_version = 'dashboard') {
      $replace = $this->preToHtml($_version);
      if (!is_array($replace)) {
        return $replace;
      }
      $version = jeedom::versionAlias($_version);
      $cmd_html = '';
      $br_before = 0;
      foreach ($this->getCmd(null, null, true) as $cmd) {
        if (isset($replace['#refresh_id#']) && $cmd->getId() == $replace['#refresh_id#']) {
          continue;
        }
        if ($br_before == 0 && $cmd->getDisplay('forceReturnLineBefore', 0) == 1) {
          $cmd_html .= '<br/>';
        }

        $cmd_html .= $cmd->toHtml($_version, '', $replace['#cmd-background-color#']);
        $br_before = 0;
        if ($cmd->getDisplay('forceReturnLineAfter', 0) == 1) {
          $cmd_html .= '<br/>';
          $br_before = 1;
        }
      }
      $replace['#cmd#'] = $cmd_html;
      return template_replace($replace, getTemplate('core', $version, 'worxLandroid', 'worxLandroid'));
    }

    /*     * **********************Getteur Setteur*************************** */
  }

  class worxLandroidCmd extends cmd {
    /*     * *************************Attributs****************************** */

    /*     * ***********************Methode static*************************** */

    /*     * *********************Methode d'instance************************* */

    /*
    * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
    public function dontRemoveCmd() {
    return true;
  }
  */


      /**
       * Create a new command if equipement parameter auto_add_cmd is TRUE.
       * Command is not saved.
       * @param eqLogic $_eqLogic equipment the command belongs to
       * @param string $_name command name
       * @param string $_topic command mqtt topic
       * @return new command (NULL if not created)
       */
      public static function newCmd($_eqLogic, $_name, $_topic) {
    if ($_eqLogic->getConfiguration('auto_add_cmd', 1)) {
        $cmd = new worxLandroidCmd();
        $cmd->setEqLogic_id($_eqLogic->getId());
        $cmd->setEqType('worxLandroid');
        $cmd->setIsVisible(1);
        $cmd->setIsHistorized(0);
        $cmd->setSubType('string');
        $cmd->setLogicalId($_topic);
        $cmd->setType('info');
        $cmd->setName($_name);
        $cmd->setConfiguration('topic', $_topic);
        $cmd->setConfiguration('parseJson', 0);
        log::add('worxLandroid', 'info', 'Creating command of type info ' . $_eqLogic->getName() . '|' . $_name);
    }
    else {
        $cmd = NULL;
        log::add('worxLandroid', 'debug', 'Command ' . $_eqLogic->getName() . '|' . $_name .
                 ' not created as automatic command creation is disabled');
    }
    return $cmd;
      }
      /**
       * Update this command value, save and inform all stakeholders
       * @param string $value new command value
       */
      public function updateCmdValue($value) {
    // Update the configuration value that is displayed inside the equipment command tab
    $this->setConfiguration('value', $value);
    $this->save();
    // Update the command value
    $eqLogic = $this->getEqLogic();
    $eqLogic->checkAndUpdateCmd($this, $value);
    log::add('worxLandroid', 'info', '-> ' . $eqLogic->getName() . '|' . $this->getName() . ' ' . $value);
      }
      /**
       * Decode the given JSON decode array and update command values.
       * Commands are created when they do not exist.
       * If the given JSON structure contains other JSON structure, call this routine recursively.
       * @param eqLogic $_eqLogic current equipment
       * @param array $jsonArray JSON decoded array to parse
       * @param string $_cmdName command name prefix
       * @param string $_topic mqtt topic prefix
       */
      public static function decodeJsonMessage($_eqLogic, $_jsonArray, $_cmdName, $_topic) {
    foreach ($_jsonArray as $id => $value) {
        $jsonTopic = $_topic    . '{' . $id . '}';
        $jsonName  = $_cmdName  . '{' . $id . '}';
        $cmd = worxLandroidCmd::byEqLogicIdAndLogicalId($_eqLogic->getId(), $jsonTopic);
        // If no command has been found, try to create one
        // Note: worxLandroidCmd::newCmd returns NULL if parameter auto_add_cmd is not true
        if (!is_object($cmd)) {
      $cmd = worxLandroidCmd::newCmd($_eqLogic, $jsonName, $jsonTopic);
        }
        if (is_object($cmd)) {
      // json_encode is used as it works whatever the type of $value (array, boolean, ...)
      $cmd->updateCmdValue(json_encode($value));
      // If the current command is a JSON structure that shall be decoded, call this routine recursively
      if ($cmd->getConfiguration('parseJson') == 1 && is_array($value))
          worxLandroidCmd::decodeJsonMessage($_eqLogic, $value, $jsonName, $jsonTopic);
        }
    }
  }



  public function execute($_options = array())
  {
    log::add('worxLandroid', 'debug', __METHOD__.'('.json_encode($_options).') Type: '.$this->getType().' logicalId: '.$this->getLogicalId());

    if ($this->getLogicalId() == 'refresh')
    {
      $this->getEqLogic()->refresh();
      return;
    }

    if( $this->getType() == 'action' )
    {

      if( $this->getSubType() == 'slider' && $_options['slider'] == '')
      return;

      worxLandroid::initInfosMap();
      if (isset(worxLandroid::$_actionMap[$this->getLogicalId()]))
      {
        $params = worxLandroid::$_actionMap[$this->getLogicalId()];

        if(isset($params['callback']) && is_callable($params['callback']))
        {
          log::add('worxLandroid', 'debug', __METHOD__.'calling back');
          call_user_func($params['callback'], $this);
        }elseif(isset($params['cmd']))
        {
          $cmdval = $params['cmd'];
          if($this->getSubType() == 'slider')
          $cmdval = str_replace('[[[VALUE]]]',$_options['slider'],$cmdval);

          $eqLogic = $this->getEqLogic();
          $ip = $eqLogic->getConfiguration('addressip');
          $user = $eqLogic->getConfiguration('user','admin');
          $pin = $eqLogic->getConfiguration('pincode');
          $url = "http://{$user}:{$pin}@{$ip}/jsondata.cgi";

          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_POST, true);
          curl_setopt($ch, CURLOPT_POSTFIELDS,$cmdval);
          $jsondata = curl_exec($ch);
          curl_close($ch);
          log::add('worxLandroid', 'debug', __METHOD__.'('.$url.' with '.$cmdval.') '.$jsondata);

          $eqLogic->getInformations($jsondata);
        }

        return true;
      }
    } else {
      throw new Exception(__('Commande non implémentée actuellement', __FILE__));
    }
    return false;
  }

  /*     * **********************Getteur Setteur*************************** */
}
/*
{
"versione_fw": 2.45,
"lingua": 2,
"ore_funz": [0, 0, 0, 0, 0, 0, 0],
"ora_on": [0, 0, 0, 0, 0, 0, 0],
"min_on": [0, 0, 0, 0, 0, 0, 0],
"allarmi": [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
"settaggi": [0, 0, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
"mac": [0, 35, 167, 164, 213, 71],
"time_format": 1,
"date_format": 0,
"rit_pioggia": 180,
"area": 0,
"enab_bordo": 1,
"percent_programmatore": 0,
"indice_area": 9, //taille du jardin
"tempo_frenatura": 20,
"perc_rallenta_max": 70,
"canale": 0,
"num_ricariche_batt": 0,
"num_aree_lavoro": 1,
"dist_area": [1, 1, 1, 1],
"perc_per_area": [1, 1, 1, 1],
"area_in_lavoro": 0,
"email": "xxxxxxx@xxxxxx.xxx",
"perc_batt": "100",
"ver_proto": 1,
"state": "home",
"workReq": "user req grass cut",
"message": "none",
"batteryChargerState": "idle",
"distance": 0
}

{
"CntProg": 95, // Firmware version?????
"lingua": 0, // Language in use
"ore_funz": [ // Decides for how long the mower will work each day, probably expressed as 0,1 h
100,
122,
100,
120,
110,
40,
50
],
"ora_on": [ // Hour of day that the Landroid should mowing, per weekday
4,
4,
2,
3,
3,
2,
2
],
"min_on": [ // Minutes on the hour (above) that the Landroid should start mowing, per weekday
0,
0,
0,
0,
0,
0,
0
],
"allarmi": [ // Alarms - flags set to 1 when alarm is active
0, // [0] "Blade blocked"
0, // [1] "Repositioning error"
0, // [2] "Outside wire" ("Outside working area")
0, // [3] "Blade blocked"
0, // [4] "Outside wire" ("Outside working area")
0, // [5] "Mower lifted" ("Lifted up")
0, // [6] "error"
0, // [7] "error" (Set when "Lifted up" - "Upside down"?)
0, // [8] "error"
0, // [9] "Collision sensor blocked"
0, // [10] "Mower tilted"
0, // [11] "Charge error" (Set when "Lifted up"?)
0, // [12] "Battery error"
0, // Reserved for future use?
0, // -- " --
0, // -- " --
0, // -- " --
0, // -- " --
0, // -- " --
0, // -- " --
0, // -- " --
0, // -- " --
0, // -- " --
0, // -- " --
0, // -- " --
0, // -- " --
0, // -- " --
0, // -- " --
0, // -- " --
0, // -- " --
0  // -- " --
],
"settaggi": [ // Settings / state
0,
0,
0,
0,
1,
0, // "in base" ("charging" or "charging completed", see [13])
0,
1,
1,
1,
0,
0, // "start"
0, // "stop"
0, // "charging completed"
0, // "manual stop"
0, // "going home"
0,
0,
0,
0,
0,
0,
0,
0,
0,
0,
0,
0,
0,
0,
0
],
"mac": [ // The MAC address of the Landroid WiFi
...,
...,
...,
...,
...,
...
],
"time_format": 1, // Time format
"date_format": 2, // Date format
"rit_pioggia": 180, // Time to wait after rain, in minutes
"area": 0,
"enab_bordo": 1, // Enable edge cutting
"g_sett_attuale": 1, // Is charging???
"g_ultimo_bordo": 0,
"ore_movimento": 626, // Total time the mower has been mowing, expressed in 0,1 h
"percent_programmatore": 50, // Working time percent (increase)
"indice_area": 9, // taille jardin


"tipo_lando": 8,
"beep_hi_low": 0,
"gradi_ini_diritto": 30, // Something "right"?
"perc_cor_diritto": 103, // Something "right"?
"coef_angolo_retta": 80, // Something "straigt line"?
"scost_zero_retta": 1,   // Something "straigt line"?
"offset_inclinometri": [ // Probably the calibration of the sensors?
2039,
2035,
2672
],
"gr_rall_inizio": 80,
"gr_rall_finale": 300,
"gr_ini_frenatura": 130,
"perc_vel_ini_frenatura": 50, // Something "brake" (battery percent when returning to charger???)
"tempo_frenatura": 20,
"perc_rallenta_max": 50,
"canale": 0,
"num_ricariche_batt": 0,
"num_aree_lavoro": 4, // Number of zones in use
"Dist_area": [ // Distance in meters to the zone starts
18,
71,
96,
129
],
"perc_per_area": [ // Percentage per zone, expressed in 10% increments (i.e. 3 = 30%)
1,
2,
3,
4
],
"area_in_lavoro": 5,
"email": "...", // The e-mail address used to log into the app
"perc_batt": "100" // Charge level of the battery
}
*/
?>
