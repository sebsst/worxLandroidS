<?php

if (!isConnect('admin')) {
  throw new Exception('{{401 - Accès non autorisé}}');
}
sendVarToJS('eqType', 'worxLandroidS');
$eqLogics = eqLogic::byType('worxLandroidS');

?>

<div class="row row-overflow">
  <div class="col-lg-2 col-sm-3 col-sm-4">
    <div class="bs-sidebar">
      <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
        <a class="btn btn-default eqLogicAction" style="width : 100%;margin-top : 5px;display:none;margin-bottom: 5px;" data-action="add"><i class="fa fa-plus-circle"></i> {{Ajouter un équipement}}</a>
        <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
        <?php
        foreach ($eqLogics as $eqLogic) {
          echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
        }
        ?>
      </ul>
    </div>
  </div>

  <div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
    <legend><i class="fa fa-cog"></i>  {{Gestion}}</legend>
    <div class="eqLogicThumbnailContainer">
      <div class="cursor eqLogicAction" data-action="gotoPluginConf" style="background-color : #ffffff; height : 140px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;">
        <center>
          <i class="fa fa-wrench" style="font-size : 5em;color:#767676;"></i>
        </center>
        <span style="font-size : 1.1em;position:relative; top : 23px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#767676"><center>{{Configuration}}</center></span>
      </div>
      <div class="cursor" id="bt_healthworxLandroidS" style="background-color : #ffffff; height : 120px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
        <center>
          <i class="fa fa-medkit" style="font-size : 5em;color:#767676;"></i>
        </center>
        <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#767676"><center>{{Historique santé}}</center></span>
      </div>
    </div>


    <legend><i class="fa fa-table"></i>  {{Mes worxLandroidS}}
    </legend>
    <div class="eqLogicThumbnailContainer">
      <div class="cursor eqLogicAction" data-action="add" style="background-color : #ffffff; display:none;height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
        <center>
          <i class="fa fa-plus-circle" style="font-size : 7em;color:#00979c;"></i>
        </center>
        <span style="font-size : 1.1em;position:relative; top : 15px;display:none;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>{{Ajouter}}</center></span>
      </div>
      <?php
      $dir = dirname(__FILE__) . '/../../docs/images/';
      $files = scandir($dir);
      foreach ($eqLogics as $eqLogic) {
        $opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
        echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff ; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
        echo "<center>";
        $test = 'node_' . $eqLogic->getConfiguration('icone') . '.png';
        if (in_array($test, $files)) {
          $path = 'node_' . $eqLogic->getConfiguration('icone');
        } else {
          $path = 'worxLandroidS_icon';
        }
        echo '<img src="plugins/worxLandroidS/plugin_info/' . $path . '.png" height="105" width="95" />';
        echo "</center>";
        echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
        echo '</div>';
      }
      ?>
    </div>
  </div>
  <div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
    <a class="btn btn-success eqLogicAction pull-right" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
    <a class="btn btn-danger eqLogicAction pull-right" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
    <a class="btn btn-default eqLogicAction pull-right" data-action="configure"><i class="fa fa-cogs"></i> {{Configuration avancée}}</a>
    <ul class="nav nav-tabs" role="tablist">
      <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
      <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-tachometer"></i> {{Equipement}}</a></li>
      <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
      <li role="presentation"><a href="#horaires" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa divers-calendar2"></i> {{horaires}}</a></li>
      <li role="presentation"><a href="#zones" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa divers-table29"></i> {{zones}}</a></li>

    </ul>
    <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
      <div role="tabpanel" class="tab-pane active" id="eqlogictab">
        <form class="form-horizontal">
          <fieldset>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Nom de l'équipement}}</label>
              <div class="col-sm-3">
                <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement worxLandroidS}}"/>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label" >{{Objet parent}}</label>
              <div class="col-sm-3">
                <select class="form-control eqLogicAttr" data-l1key="object_id">
                  <option value="">{{Aucun}}</option>
                  <?php
                  foreach (jeeObject::all() as $object) {
                    echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                  }
                  ?>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Catégorie}}</label>
              <div class="col-sm-8">
                <?php
                foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                  echo '<label class="checkbox-inline">';
                  echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                  echo '</label>';
                }
                ?>

              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label" ></label>
              <div class="col-sm-8">
                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>

              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label" > {{Type Tondeuse}}</label>
              <div class="col-sm-3">
      			    <Input readonly type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="mowerDescription" />
      			    <Input readonly type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="MowerType" />
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label" > {{Adresse Mac}}</label>
              <div class="col-sm-3">
      			<Input readonly type="text" class="eqLogicAttr form-control" data-l1key="logicalId"  />
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label" > {{Numéro de série}}</label>
              <div class="col-sm-3">
      			<Input readonly type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="serialNumber"  />
              </div>
            </div>
             <div class="form-group">
              <label class="col-sm-3 control-label" > {{Date de fin de garantie}}</label>
              <div class="col-sm-3">
      			<Input readonly type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="warranty_expiration_date"  />
              </div>
            </div>
             <div class="form-group">
              <label class="col-sm-3 control-label" > {{Durée de vie estimée des lames (Hr)}}</label>
              <div class="col-sm-3">
      			<Input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="maxBladesDuration"  />
              </div>
            </div>


        </fieldset>
      </form>
    </div>
    <div role="tabpanel" class="tab-pane" id="commandtab">

      <form class="form-horizontal">
        <fieldset>
          <div class="form-actions">
            <a class="btn btn-success btn-sm cmdAction" id="bt_addworxLandroidSAction"><i class="fa fa-plus-circle"></i> {{Ajouter une commande action}}</a>
            <a class="btn btn-success btn-sm cmdInfo" id="bt_addworxLandroidSInfo"><i class="fa fa-plus-circle"></i> {{Ajouter une commande Info}}</a>

          </div>
        </fieldset>
      </form>
      <br />
      <table id="table_cmd" class="table table-bordered table-condensed">
        <thead>
          <tr>
            <th style="width: 50px;">#</th>
            <th style="width: 250px;">{{Nom}}</th>
            <th style="width: 120px;">{{Sous-Type}}</th>
            <th style="width: 250px;">{{Valeur}}</th>
            <th style="width: 250px;">{{Request}}</th>
            <th style="width: 150px;">{{Paramètres}}</th>

            <th style="width: 150px;">{{Options}}</th>

            <th style="width: 80px;"></th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>
    </div>

    <div role="tabpanel" class="tab-pane" id="horaires">
      <form class="form-horizontal">
        <fieldset>
          <div class="form-actions">
            <?php
              $userMessage = $eqLogic->getCmd('action','userMessage');
              $refrCmd = $eqLogic->getCmd('action','refreshValue');
              if (is_object($userMessage) && is_object($refrCmd)) {
                $userMessageId = $userMessage->getId();
                $refrCmdId = $refrCmd->getId();
                echo '<a class="btn btn-success eqLogicAction cmdAction pull-left" data-action="save" onclick="updatePlanning('.$userMessageId.','.$refrCmdId.');">';
                echo '<i class="fa fa-check-circle"></i> {{Enregistrer horaires}}</a><div>{{la tondeuse doit être connectée}}</div>';
              }
            ?>
          </div>
        </fieldset>
      </form>
      <br />
      <table id="table_horaires" class="table table-bordered table-condensed">
        <thead>
          <tr>
            <th style="width: 70px;">{{Jour}}</th>
            <th style="width: 20px;">{{heure début}}</th>
            <th style="width: 20px;">{{durée}}</th>
            <th style="width: 20px;">{{bordure}}</th>
            <th style="width: 150px;">{{}}</th>
          </tr>
        </thead>
        <tbody>
          <?php
              $planningCmd         = $eqLogic->getCmd(null, 'completePlanning');
              if (is_object($planningCmd)) {
                $planningCurrent     = $planningCmd->execCmd();

                $planning = explode('|',$planningCurrent);
                $jour            = array(
                  "Dimanche",
                  "Lundi",
                  "Mardi",
                  "Mercredi",
                  "Jeudi",
                  "Vendredi",
                  "Samedi"
                );
                echo '<fieldset>';
                $count = 0;

                foreach( $planning as $value){
                  if($count==7) break;
                  echo '<tr><td>'.$jour[$count].'</td>';
                  $detail = explode(',',$value);
                  $countDist = 0;
                  $checked = $detail[2]==1?'checked':'';
                  echo '<td><input id="startTime'.$count.'" class="form-control" type="time" value="'.$detail[0].'"></td>';
                  echo '<td><input id="duration'.$count.'" class="form-control" type="number" value="'.$detail[1].'"></td>';
                  echo '<td><input id="edge'.$count.'" class="form-control" type="checkbox" '.$checked.'></td>';

                  //echo '<td>'.$detail[1].'</td><td>'.$detail[2].'</td>';
                  //echo '<tr><td><input id="area'.$count.'" class="form-control" type="number" name="distance" min="0" max="999" STYLE="margin:1px;" value="'.$area.'" required></td>';

                  echo '</tr>';

                  $count += 1;
                }
                echo '</fieldset>';
              }
          ?>
        </tbody>
      </table>
    </div>


    <div role="tabpanel" class="tab-pane" id="zones">
      <form class="form-horizontal">
        <fieldset>
          <div class="form-actions">
            <?php
              $userMessage = $eqLogic->getCmd('action','userMessage');
              $refrCmd = $eqLogic->getCmd('action','refreshValue');
              if (is_object($userMessage) && is_object($refrCmd)) {
                $userMessageId = $userMessage->getId();
                $refrCmdId = $refrCmd->getId();
                echo '<a class="btn btn-success eqLogicAction cmdAction pull-left" data-action="save" onclick="updateAreas('.$userMessageId.','.$refrCmdId.');">';
                echo '<i class="fa fa-check-circle"></i> {{Enregistrer zones}}</a><div>{{la tondeuse doit être connectée}}</div>';
              }
            ?>
          </div>
        </fieldset>
      </form>
      <br />
      <table id="table_zones" class="table table-bordered table-condensed">
        <thead>
          <tr>
            <th style="width: 70px;">{{distance(m) / répartition zones départ }}</th>
            <th style="width: 20px;">{{10}}</th>
            <th style="width: 20px;">{{20}}</th>
            <th style="width: 20px;">{{30}}</th>
            <th style="width: 20px;">{{40}}</th>
            <th style="width: 20px;">{{50}}</th>
            <th style="width: 20px;">{{60}}</th>
            <th style="width: 20px;">{{70}}</th>
            <th style="width: 20px;">{{80}}</th>
            <th style="width: 20px;">{{90}}</th>
            <th style="width: 20px;">{{100}}</th>
            <th style="width: 150px;">{{}}</th>
          </tr>
        </thead>
        <tbody>
          <?php
              $areaListCmd         = $eqLogic->getCmd(null, 'areaList');
              $areaListDistCmd     = $eqLogic->getCmd(null, 'areaListDist');
              if (is_object($areaListCmd) && is_object($areaListDistCmd)) {
                $areaListCurrent     = $areaListCmd->execCmd();
                $areaListDistCurrent = $areaListDistCmd->execCmd();
                $areaList = explode('|',$areaListCurrent);
                $areaListDist = explode('|',$areaListDistCurrent);
                echo '<fieldset>';
                $count = 0;
                foreach( $areaList as $area){

                echo '<tr><td><input id="area'.$count.'" class="form-control" type="number" name="distance" min="0" max="999" STYLE="margin:1px;" value="'.$area.'" required></td>';

                  $countDist = 0;
                  foreach($areaListDist as $dist){
                    $checked = $dist==$count?'checked':'';
                   echo '<td><input id="dist'.$count.$countDist.'" type="radio"  name="areaDist'.$countDist.'" STYLE="margin:1px;"'.
                   ' value="distVal'.$count.$countDist.'" '.$checked.' >'
                   .'</td>';
                   $countDist += 1;
                  }
                  echo '</tr>';
                  echo '</fieldset>';
                  $count += 1;
                }
              }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>

<?php include_file('desktop', 'worxLandroidS', 'js', 'worxLandroidS'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>
