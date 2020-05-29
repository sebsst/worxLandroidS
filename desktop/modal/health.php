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

if (!isConnect('admin')) {
	throw new Exception('401 Unauthorized');
}
$eqLogics = worxLandroidS::byType('worxLandroidS');
?>

<table class="table table-condensed tablesorter" id="table_healthworxLandroidS">
	<thead>
		<tr>
			<th>{{Timestamp}}</th>
			<th>{{Statut}}</th>
			<th>{{Erreur}}</th>
  			<th>{{Zone}}</th>
			<th>{{Charge batterie}}</th>
			<th>{{Blocage}}</th>

  </tr>
	</thead>
	<tbody>
	 <?php
foreach ($eqLogics as $eqLogic) {

	// get history
	$sn = $eqLogic->getConfiguration('serialNumber');
	$api_token = config::byKey('api_token', 'worxLandroidS');

	$url       = 'https://api.worxlandroid.com/api/v2/product-items/'.$sn.'/activity-log';

	$content = "application/json";
	$ch      = curl_init($url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		"Content-Type: application/json",
		'Authorization: Bearer ' . $api_token
	));

	$jsonHistory = curl_exec($ch);
    log::add('worxLandroidS', 'info', 'Connexion result :' . $jsonHistory);
  if(is_null($jsonHistory)){}
   else{
		$hist = json_decode($jsonHistory);

		foreach ($hist as $value) {
			// code...


			echo '<tr><td><a href="' . $value->timestamp . '" style="text-decoration: none;">' . $value->payload->cfg->dt. ' '. $value->payload->cfg->tm . '</a></td>';
	echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' .worxLandroidS::getStatusDescription($value->payload->dat->ls)  . '</span></td>';

	if ($value->payload->dat->le == 0) {
		$status = '<span class="label label-success" style="font-size : 1em; cursor : default;">'.
          worxLandroidS::getErrorDescription($value->payload->dat->le)
          .'</span>';
	  }
    else{
		$status = '<span class="label label-danger" style="font-size : 1em; cursor : default;">'.
          worxLandroidS::getErrorDescription($value->payload->dat->le)
          .'</span>';
    }
			echo '<td>'.$status.'</td>';
			echo '<td>' .$value->payload->dat->lz  . '</td>';
			echo '<td>' .$value->payload->dat->bt->c  . '</td>';
			echo '<td>'. $value->payload->dat->lk  . '</td>';
		}
	}

	echo '<tr><td><a href="' . $eqLogic->getLinkToConfiguration() . '" style="text-decoration: none;">' . $eqLogic->getHumanName(true) . '</a></td>';
	echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getId() . '</span></td>';
	$status = '<span class="label label-success" style="font-size : 1em; cursor : default;">{{OK}}</span>';
	if ($eqLogic->getStatus('state') == 'nok') {
		$status = '<span class="label label-danger" style="font-size : 1em; cursor : default;">{{NOK}}</span>';
	}
	echo '<td>' . $status . '</td>';
	echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getStatus('lastCommunication') . '</span></td>';
	echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getConfiguration('createtime') . '</span></td></tr>';
}
?>
	</tbody>
</table>
