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
    $cron = cron::byClassAndFunction('worxLandroidS', 'daemon');
    if (is_object($cron)) {
    $cron->stop();
    $cron->remove(); 
    }
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
