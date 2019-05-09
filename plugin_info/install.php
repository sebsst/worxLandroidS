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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function worxLandroidS_install() {
    $cron = cron::byClassAndFunction('worxLandroidS', 'daemon');
    config::save('initCloud', 1 ,'worxLandroidS');
    if (!is_object($cron)) {
        $cron = new cron();
        $cron->setClass('worxLandroidS');
        $cron->setFunction('daemon');
        $cron->setEnable(1);
        $cron->setDeamon(1);
        $cron->setDeamonSleepTime(120);
        $cron->setSchedule('* * * * *');
        $cron->setTimeout('1440');
        $cron->save();
    }
}

function worxLandroidS_update() {
    if (is_object($cron)) {
    $cron->stop();
    $cron->remove(); 
    unset($cron);    


    }
    $cron = cron::byClassAndFunction('worxLandroidS', 'daemon');
    config::save('initCloud', 1 ,'worxLandroidS');
    if (!is_object($cron)) {
        $cron = new cron();
        $cron->setClass('worxLandroidS');
        $cron->setFunction('daemon');
        $cron->setEnable(1);
        $cron->setDeamon(1);
        $cron->setDeamonSleepTime(120);
        $cron->setSchedule('* * * * *');
        $cron->setTimeout('1440');
        $cron->save();
    } else
    {
        $cron->setDeamonSleepTime(120);
        $cron->halt;
        $cron->run;
    }
    
    foreach (eqLogic::byType('worxLandroidS', false) as $eqpt) {
      // add actions if missing
         worxLandroidS::newAction($eqpt,'off_today',$commandIn,"off_today",'other');
         worxLandroidS::newAction($eqpt,'on_today',$commandIn,"on_today",'other');
         worxLandroidS::newAction($eqpt,'pause',$commandIn,"pause",'other');
         worxLandroidS::newAction($eqpt,'rain_delay_0',$commandIn,"0",'other');
         worxLandroidS::newAction($eqpt,'rain_delay_30',$commandIn,"30",'other');
         worxLandroidS::newAction($eqpt,'rain_delay_60',$commandIn,"60",'other');
         worxLandroidS::newAction($eqpt,'rain_delay_120',$commandIn,"120",'other');
         worxLandroidS::newAction($eqpt,'rain_delay_240',$commandIn,"240",'other');
                $display = array(
				'message_placeholder' => __('num jour;hh:mm;durée mn;bord(0 ou 1)', __FILE__),
				'isvisible' => 0,
                		'title_disable' => true);
	    
        worxLandroidS::newAction($eqpt, 'set_schedule', $commandIn, "", 'message', $display);
        worxLandroidS::newAction($eqpt, 'newBlades', $commandIn, "", 'other');	
	worxLandroidS::newInfo($eqpt, 'lastBladesChangeTime', '', 'numeric', 0);
	    
// ajout de la ommande pour le widget
	 worxLandroidS::newInfo($eqpt, 'virtualInfo', '', 'string', 0, 'statusCode,statusDescription,batteryLevel,wifiQuality,currentZone');
         foreach ($eqpt->getCmd('info', null, true) as $cmd)
	 {
		 if(strstr($cmd->getLogicalId(),'/')
		 {
		 	$cmd->setLogicalId(str_replace('/','_',$cmd->getLogicalId()));
			$cmd->save();					   
		 }
	 }
		 

    
    }
}

function worxLandroidS_remove() {
    $cron = cron::byClassAndFunction('worxLandroidS', 'daemon');
    if (is_object($cron)) {
        $cron->stop();
        $cron->remove();
    }
    log::add('worxLandroidS','info','Suppression extension');
    $resource_path = realpath(dirname(__FILE__) . '/../resources');
    passthru('sudo /bin/bash ' . $resource_path . '/remove.sh ' . $resource_path . ' > ' . log::getPathToLog('worxLandroidS_dep') . ' 2>&1 &');
    return true;
}

?>
