
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
$("#table_cmd").delegate(".listEquipementInfo", 'click', function () {
    var el = $(this)
    jeedom.cmd.getSelectModal({ cmd: { type: 'info' } }, function (result) {
        var calcul = el.closest('tr').find('.cmdAttr[data-l1key=configuration][data-l2key=' + el.data('input') + ']');
        calcul.atCaret('insert', result.human);
    });
});

$("#bt_addworxLandroidSAction").on('click', function (event) {
    var _cmd = { type: 'action' };
    addCmdToTable(_cmd);
});

$("#bt_addworxLandroidSInfo").on('click', function (event) {
    var _cmd = { type: 'info' };
    addCmdToTable(_cmd);
});

$('#bt_healthworxLandroidS').on('click', function () {
    $('#md_modal').dialog({ title: "{{Santé worxLandroidS}}" });
    $('#md_modal').load('index.php?v=d&plugin=worxLandroidS&modal=health').dialog('open');
});

$("#table_cmd").sortable({ axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true });

function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = { configuration: {} };
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }

    if (init(_cmd.type) == 'info') {
        var disabled = (init(_cmd.configuration.virtualAction) == '1') ? 'disabled' : '';
        var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
        tr += '<td>';//1
        tr += '<span class="cmdAttr" data-l1key="id"></span>';
        tr += '</td>';
        tr += '<td>';//2
        tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width : 140px;" placeholder="{{Nom de l\'info}}"></td>';
        tr += '<td>';//3
        tr += '<input class="cmdAttr form-control type input-sm" data-l1key="type" value="info" disabled style="margin-bottom : 5px;" />';
        tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>';
        tr += '</td><td>';//4
        tr += '<span class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="topic" style="height : 33px;" ' + disabled + ' placeholder="{{Topic}}" readonly=true>';
        tr += '</td><td>';//5
        tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="request" style="height : 33px;" ' + disabled + ' placeholder="{{Request}}">';
        tr += '<a class="btn btn-default btn-sm cursor listEquipementInfo" data-input="request" style="margin-left : 5px;"><i class="fa fa-list-alt "></i> {{Rechercher équipement}}</a>';
        tr += '<a class="btn btn-default btn-sm cursor listEquipementInfo" data-input="request" style="margin-left : 5px;display:none"><i class="fa fa-list-alt "></i> {{Rechercher équipement}}</a>';

        tr += '<input class="cmdAttr form-control input-sm" data-l1key="unite" style="width : 90px;" placeholder="{{Unité}}"></td><td>';
        tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';
        tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Afficher}}</label></span> ';
        tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="display" data-l2key="invertBinary" checked/>{{Inverser}}</label></span> ';
        tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width : 40%;display : inline-block;"> ';
        tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width : 40%;display : inline-block;">';
        tr += '</td>';
        tr += '<td>';//7
        if (is_numeric(_cmd.id)) {
            tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fa fa-cogs"></i></a> ';
            tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
        }
        tr += '<i class="fa fa-minus-circle cmdAction cursor" data-action="remove"></i></td>';
        tr += '</tr>';
        $('#table_cmd tbody').append(tr);
        $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
        if (isset(_cmd.type)) {
            $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
        }
        jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
    }

    if (init(_cmd.type) == 'action') {
        var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
        tr += '<td>';//1
        tr += '<span class="cmdAttr" data-l1key="id"></span>';
        tr += '</td>';
        tr += '<td>';//2
        tr += '<div class="row">';
        tr += '<div class="col-lg-6">';
        tr += '<a class="cmdAction btn btn-default btn-sm" data-l1key="chooseIcon"><i class="fa fa-flag"></i> Icone</a>';
        tr += '<span class="cmdAttr" data-l1key="display" data-l2key="icon" style="margin-left : 10px;"></span>';
        tr += '</div>';
        tr += '<div class="col-lg-6">';
        tr += '<input class="cmdAttr form-control input-sm" data-l1key="name">';
        tr += '</div>';
        tr += '</div>';
        tr += '<select class="cmdAttr form-control tooltips input-sm" data-l1key="value" style="display : none;margin-top : 5px;margin-right : 10px;" title="{{La valeur de la commande vaut par défaut la commande}}">';
        tr += '<option value="">Aucune</option>';
        tr += '</select>';
        tr += '</td>';
        tr += '<td>';//3
        tr += '<input class="cmdAttr form-control type input-sm" data-l1key="type" value="action" disabled style="margin-bottom : 5px;" />';
        tr += '<span class="subType" subType="' + init(_cmd.subType) + '" style=""></span>';
        //tr += '<input class="cmdAttr" data-l1key="configuration" data-l2key="virtualAction" value="1" style="display:none;" >';
        tr += '</td>';
        tr += '<td>';//4
        tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="topic" style="height : 33px;" ' + disabled + ' placeholder="{{Topic}}"><br/>';
        tr += '</td>';
        tr += '<td>';//5
        tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="request" style="height : 33px;" ' + disabled + ' placeholder="{{Payload}}">';
        tr += '<a class="btn btn-default btn-sm cursor listEquipementInfo" data-input="request" style="margin-left : 5px;display:none"><i class="fa fa-list-alt "></i> {{Rechercher équipement}}</a>';
        tr += '</select></span>';
        tr += '</td><td>';//6
        tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Afficher}}</label></span><br> ';
        tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="configuration" data-l2key="retain" checked/>{{Retain flag}}</label></span><br> ';
        tr += '</td>';
        tr += '<td>';//7
        if (is_numeric(_cmd.id)) {
            tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fa fa-cogs"></i></a> ';
            tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
        }
        tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></td>';
        tr += '</tr>';

        $('#table_cmd tbody').append(tr);
        //$('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
        var tr = $('#table_cmd tbody tr:last');
        jeedom.eqLogic.builSelectCmd({
            id: $(".li_eqLogic.active").attr('data-eqLogic_id'),
            filter: { type: 'info' },
            error: function (error) {
                $('#div_alert').showAlert({ message: error.message, level: 'danger' });
            },
            success: function (result) {
                tr.find('.cmdAttr[data-l1key=value]').append(result);
                tr.setValues(_cmd, '.cmdAttr');
                jeedom.cmd.changeType(tr, init(_cmd.subType));
            }
        });
    }
}

// Called by the plugin core to inform about the inclusion of an equipment
$('body').off('worxLandroidS::includeEqpt').on('worxLandroidS::includeEqpt', function (_event, _options) {
    if (modifyWithoutSave) {
        $('#div_newEqptMsg').showAlert({ message: '{{Un équipement vient d\'être inclu. Veuillez réactualiser la page}}', level: 'warning' });
    }
    else {
        $('#div_newEqptMsg').showAlert({ message: '{{Un équipement vient d\'être inclu. La page va se réactualiser.}}', level: 'warning' });
        // Reload the page after a delay to let the user read the message
        setTimeout(function () {
            if (_options == '') {
                window.location.reload();
            } else {
                window.location.href = 'index.php?v=d&p=worxLandroidS&m=worxLandroidS&id=' + _options;
            }
        }, 2000);
    }
});

function updatePlanning(cmdId, refreshId) {
    var result = '{"sc":{"d":[';

    for (let i = 0; i < 7; i++) {
        result += '["' + document.getElementById('startTime' + i).value;
        result += '",' + document.getElementById('duration' + i).value;
        result += ',';
        result += document.getElementById('edge' + i).checked ? 1 : 0;

        result += ']';
        if (i < 6) result += ',';

    }
    result += ']}}';
    //alert(result);

    jeedom.cmd.execute({ id: cmdId, value: { message: result } });
};

function updateAreas(cmdId, refreshId) {
    var result = '{"mz":[';
    var resultv = '"mzv":[';
    var dist = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
    var valeur = 0;

    for (let i = 0; i < 4; i++) {
        result += document.getElementById('area' + i).value;
        result += i == 3 ? '' : ',';

        for (let j = 0; j < 10; j++) {

            valeur = document.getElementById('dist' + i + j).checked == true ? i : dist[j];
            dist[j] = valeur;
        }
    }

    for (let j = 0; j < 10; j++) {
        resultv += dist[j];
        resultv += j == 9 ? '' : ',';
    }
    result += '],';
    resultv += ']}';
    result += resultv;
    //alert(result);

    jeedom.cmd.execute({ id: cmdId, value: { message: result } });
}
