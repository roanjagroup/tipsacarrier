/**
 * 2016-2018 TIPSA.COM
 *
 * NOTICE OF LICENSE
 *
 *  @author Romell Jaramillo <integraciones@tip-sa.com>
 *  @copyright 2016-2018 TIPSA.COM
 *  @license GNU General Public License version 2
 *
 * You can not resell or redistribute this software.
 */
var yp_carrier_id = configYupick.yp_carrier_id;
var YP_MODULE_DIR = configYupick.yp_module_dir;
var YP_TOKEN = configYupick.yp_token;
var yupickResultados = '';
var order_opc = configYupick.order_opc;
var isapi = typeof google === 'object';
var cashondelivery_modules = configYupick.cashondelivery_modules;
if (!isapi) document.write("<script type='text/javascript' src='https://maps.googleapis.com/maps/api/js?key={$gmapsapikey|escape:'htmlall':'UTF-8'}&sensor=false'><\/script>");
$(document).ready(function() {
    updatePaymenOndelivery();
    if ($('.delivery-option input[type=radio]:checked').val()) {
        var carrieCheck = $('.delivery-option input[type=radio]:checked').val().replace(',', '');
    }
    if (yp_carrier_id == carrieCheck) {
        GetYupickPoint();
    }
    $('.delivery-option input[type=radio]').on('change', function() {
        var carrieCheck = $('.delivery-option input[type=radio]:checked').val().replace(',', '');
        if (yp_carrier_id == carrieCheck) {
            GetYupickPoint();
            sessionStorage.setItem('yupick_carrier_selecc', '1');
            event.preventDefault();
        } else {
            sessionStorage.removeItem("yupick_carrier_selecc");
        }
    });
    $("#yupick_cp").keypress(function(e) {
        if (e.which == 13) {
            GetYupickPoint();
        }
    });
    //captura evento de boton de procesar carrier
    $('button[name=confirmDeliveryOption]').on('click', function(event) {
        var carrieCheck = $('.delivery-option input[type=radio]:checked').val().replace(',', '');
        if (yp_carrier_id == carrieCheck) {
            //valida si hay correo o movil del cliente 
            if (validade_yupick(event)) {
                //guarda los datos de la oficina para enviarlos
                dataYupick();
            }
        }
    });

    function dataYupick() {
        var oficina_yupick = {
            acc: 'NewAddresYupick',
            oficina_yupick_id: $('#oficina_yupick_id').val(),
            nombre_yupick: $('#nombre_yupick').text(),
            direccion_yupick: $('#oficina_yupick').text(),
            cp_yupick: $('#cp_yupick').text(),
            localidad_yupick: $('#localidad_yupick').text(),
            provincia_yupick: $('#provincia_yupick').text(),
            yupick_type_alert_phone: $('#yupick_type_alert_phone').val(),
            yp_client_phone: $('#yp_client_phone').val(),
            yp_id_carrier: carrieCheck
        }
        updateAddress(oficina_yupick);
    }
});
var notretriedYet = true;

function updatePaymenOndelivery() {
    if (sessionStorage.yupick_carrier_selecc == '1') {
        $('.payment-options input').each(function() {
            if (cashondelivery_modules.indexOf($(this).attr('data-module-name')) !== -1) {
                $(this).closest('.payment-option').hide();
            }
        });
    }
}

function updateAddress(office) {
    $.ajax({
        type: 'POST',
        url: YP_MODULE_DIR + 'ajax.php',
        data: office,
        success: function(result) {
            //alert(result);
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError);
        }
    });
}

function GetYupickPoint() {
    if ($('#yupick_cp').val() !== '') {
        var postcode = $('#yupick_cp').val();
    } else {
        if ($('#yupick_cp').val() == '') {
            $('input#yupick_cp:text').val('28850');
            var postcode = $('#yupick_cp').val();
        } else {
            var postcode = '28850';
        }
    }
    $.ajax({
        type: 'POST',
        url: YP_MODULE_DIR + 'ajax.php',
        data: {
            'token': YP_TOKEN,
            'acc': 'GetPoint',
            'yupick_cp': postcode
        },
        beforeSend: function() {
            $('#yupick_loadingmask').show();
        },
        complete: function() {
            $('#yupick_loadingmask').hide();
        },
        success: function(JSONObject) {
            if (JSONObject !== false) {
                yupickResultados = eval('(' + JSONObject + ')');
                fillDropDownYupick(yupickResultados);
                $('#info-yp').show(2000);
            } else {
                alert('Debe tener un Código Postal');
            }
            $('#yupick_loadingmask').css("display", "none");
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError);
        }
    });
}

function fillDropDownYupick(data) {
    if (document.getElementById('oficinas_yupick')) {
        var field = document.getElementById('oficinas_yupick');
        for (i = field.options.length - 1; i >= 0; i--) {
            field.remove(i);
        }
        for (i = field.options.length - 1; i >= 0; i--) {
            field.remove(i);
        }
        $.each(data, function() {
            var _option = new Option(this.nombre + ' - ' + this.direccion + " - " + this.localidad + ' (' + this.provincia + ')', this.id);
            if (this.selected == 1) {
                _option.setAttribute("selected", "selected");
            }
            field.options.add(_option);
        });
        yupickInfo(true);
    }
}

function yupickInfo(refreshAll) {
    var markers = [];
    var puntoActual = document.getElementById('oficinas_yupick').value;
    var i = 0;
    $.each(yupickResultados, function() {
        markers[i] = this;
        if (this.id == puntoActual) {
            $('#oficina_yupick_id').val(this.id);
            $('#oficina_yupick_data').val(this.nombre + "|" + this.direccion + "|" + this.codigopostal + "|" + this.localidad + '|(' + this.provincia + ')');
            $('#oficina_yupick_data_text').empty().append("<span id='nombre_yupick'>" + this.nombre + "</span><br>Dir. <span id='oficina_yupick'>" + this.direccion + " </span><br>CP. <span id='cp_yupick'>" + this.codigopostal + " </span><br>Localidad.  <span id='localidad_yupick'>" + this.localidad + " </span><br>Provincia. <span id='provincia_yupick'>(" + this.provincia + ")");
            if (this.id == puntoActual) infoHorarios(this);
            $("#yp_opctps").html(this.nombre + " " + this.direccion + '<br><a href="#" onclick="opctps_displaypopup()">Editar punto de recogida</a>');
        }
        i++;
    });
    if (refreshAll) infoGoogleMaps(markers);
}

function infoGoogleMaps(markers) {
    var bounds = new google.maps.LatLngBounds();
    var mapOptions = {
        zoom: 16,
        mapTypeId: 'roadmap'
    };
    var imagen = new google.maps.MarkerImage(YP_MODULE_DIR + 'views/img/globo.png', new google.maps.Size(100, 47), new google.maps.Point(0, 0), new google.maps.Point(50, 47));
    var sombra = new google.maps.MarkerImage(YP_MODULE_DIR + 'views/img/globosombra.png', new google.maps.Size(100, 19), new google.maps.Point(0, 0), new google.maps.Point(31, 19));
    var map = new google.maps.Map(document.getElementById("yupick_info_map"), mapOptions);
    if (markers) {
        for (i = 0; i < markers.length; i++) {
            var position = new google.maps.LatLng(markers[i].poslatitud, markers[i].poslongitud);
            bounds.extend(position);
            marker = new google.maps.Marker({
                position: position,
                map: map,
                icon: imagen,
                shadow: sombra
            });
            google.maps.event.addListener(marker, 'click', (function(marker, i) {
                return function() {
                    document.getElementById("oficinas_yupick").selectedIndex = 1;
                    document.getElementById("oficinas_yupick").value = markers[i].id;
                    yupickInfo(false);
                }
            })(marker, i));
            map.fitBounds(bounds);
        }
    }
    $('#oficinas_yupick').on('change', function() {
        var newAddress_ = 'New address';
        pickupAddressChange(newAddress_);
        yupickInfo(false);
    });
}

function pickupAddressChange(newAddress_) {}

function infoHorarios(e) {
    var tablaHorarios = '<table><tr><th>&nbsp;</th><th>Ma&ntilde;ana</th><th>Tarde</td></tr>';
    tablaHorarios += '<tr class="odd"><td>Lunes</td><td>' + (typeof(e.horario.lunes.manana) == "object" ? 'Cerrado' : e.horario.lunes.manana) + '</td><td>' + (typeof(e.horario.lunes.tarde) == "object" ? 'Cerrado' : e.horario.lunes.tarde) + '</td></tr>';
    tablaHorarios += '<tr class="even"><td>Martes</td><td>' + (typeof(e.horario.martes.manana) == "object" ? 'Cerrado' : e.horario.martes.manana) + '</td><td>' + (typeof(e.horario.martes.tarde) == "object" ? 'Cerrado' : e.horario.martes.tarde) + '</td></tr>';
    tablaHorarios += '<tr class="odd"><td>Mi&eacute;rcoles</td><td>' + (typeof(e.horario.miercoles.manana) == "object" ? 'Cerrado' : e.horario.miercoles.manana) + '</td><td>' + (typeof(e.horario.miercoles.tarde) == "object" ? 'Cerrado' : e.horario.miercoles.tarde) + '</td></tr>';
    tablaHorarios += '<tr class="even"><td>Jueves</td><td>' + (typeof(e.horario.jueves.manana) == "object" ? 'Cerrado' : e.horario.jueves.manana) + '</td><td>' + (typeof(e.horario.jueves.tarde) == "object" ? 'Cerrado' : e.horario.jueves.tarde) + '</td></tr>';
    tablaHorarios += '<tr class="odd"><td>Viernes</td><td>' + (typeof(e.horario.viernes.manana) == "object" ? 'Cerrado' : e.horario.viernes.manana) + '</td><td>' + (typeof(e.horario.viernes.tarde) == "object" ? 'Cerrado' : e.horario.viernes.tarde) + '</td></tr>';
    tablaHorarios += '<tr class="even"><td>S&aacute;bado</td><td>' + (typeof(e.horario.sabado.manana) == "object" ? 'Cerrado' : e.horario.sabado.manana) + '</td><td>' + (typeof(e.horario.sabado.tarde) == "object" ? 'Cerrado' : e.horario.sabado.tarde) + '</td></tr>';
    tablaHorarios += '<tr class="odd"><td>Domingo</td><td>' + (typeof(e.horario.domingo.manana) == "object" ? 'Cerrado' : e.horario.domingo.manana) + '</td><td>' + (typeof(e.horario.domingo.tarde) == "object" ? 'Cerrado' : e.horario.domingo.tarde) + '</td></tr>';
    tablaHorarios += '</table>';
    document.getElementById('yupick_info_time').innerHTML = tablaHorarios;
}

function changeYupickOptions() {
    if ($('#yupick_carrier_secundario').is(":visible")) {
        $('#yupick_options_text').html('M&aacute;s opciones de b&uacute;squeda');
        $('#yupick_carrier_secundario').hide();
    } else {
        $('#yupick_options_text').html('Menos opciones de b&uacute;squeda');
        $('#yupick_carrier_secundario').show();
    }
}

function yupick_update_recoger(href) {
    $.ajax({
        type: 'POST',
        url: YP_MODULE_DIR + 'ajax.php',
        data: {
            'token': YP_TOKEN,
            'acc': 'UpdateRecoger',
            'cart_id': yp_cart_id,
            'yupick_type_alert': $('#yupick_type_alert').val(),
            'yupick_type_alert_phone': $('#yupick_type_alert_phone').val(),
            'yupick_type_alert_email': $('#yupick_type_alert_email').val(),
            'oficina_id': $('#oficina_yupick_id').val(),
            'oficina_yupick_data': $('#oficina_yupick_data').val()
        },
        success: function(result) {
            if (typeof href != 'undefined') location.href = href;
        }
    });
}

function opctps_displaypopup() {
    Fronted.createPopup(true, '', $("#oficinas_yupick_content"), true, true, true, false);
}
var delivery_option_radio = $('.delivery-option input');
var yupickloaded = false;
updateYupickCarrierOption(false);

function updateYupickCarrierOption(carrier_id) {
    var carrier_id_int = true;
    if (carrier_id !== false) {
        carrier_id_int = carrier_id.replace(/,/g, "");
        carrier_id_int = !isNaN(carrier_id_int);
    }
    if (!carrier_id_int) return;
    if (carrier_id === false) {
        $.each(delivery_option_radio, function(i) {
            if ($(this).find('input').is(":checked")) {
                var carrier_real_id = $(this).find('input').val();
                carrier_real_id = carrier_real_id.replace(/,/g, "");
                if (yp_carrier_id == carrier_real_id) {
                    if (yupickloaded !== true) {
                        GetYupickPoint();
                        yupickloaded = true;
                    }
                } else {
                    alert('No existe');
                }
            }
        });
    } else {
        carrier_real_id = carrier_id.replace(/,/g, "");
        if (yp_carrier_id == carrier_real_id) {
            if (yupickloaded !== true) {
                GetYupickPoint();
                yupickloaded = true;
            } else {
                $('#oficinas_yupick_content').show();
            }
        } else {
            $('#oficinas_yupick_content').css("display", "none");
        }
    }
};

function validade_yupick(event) {
    if ($('#yupick_type_alert:checked').val() == "Email") {
        var valor = $('#yupick_type_alert_email').val();
        var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
        if (!reg.test(valor)) {
            alert("Alerta Yupick!\nEl campo Email est\u00e1  vac\u00edo o es incorrecto");
            $('#yupick_type_alert_email').focus();
            $('#yupick_type_alert_email').addClass("required_text");
            event.preventDefault();
            return false;
        }
    } else {
        var valor = $('#yupick_type_alert_phone').val();
        valor = valor.replace(/[ ]+/gi, "");
        valor = valor.replace(/[-]+/gi, "");
        valor = valor.replace(/[.]+/gi, "");
        if (valor == "" || valor.length < 9) {
            alert('Alerta Yupick!\nN\xfamero de m\xf3vil no v\xe1lido.');
            $('#yupick_type_alert_phone').focus();
            event.preventDefault();
            return false;
        } else {
            if (valor.length == 9 && valor.substr(0, 1) != "6" && valor.substr(0, 1) != "7") {
                alert('Alerta Yupick!\nN\xfamero de m\xf3vil no pertenece a Espa\xf1a.');
                $('#yupick_type_alert_phone').focus();
                event.preventDefault();
                return false;
            } else if (valor.length == 11 && valor.substr(0, 2) == "34" && valor.substr(2, 1) != "6" && valor.substr(2, 1) != "7") {
                alert('Alerta Yupick!\nN\xfamero de m\xf3vil no pertenece a Espa\xf1a.');
                $('#yupick_type_alert_phone').focus();
                event.preventDefault();
                return false;
            } else if (valor.length == 12 && valor.substr(0, 3) == "+34" && valor.substr(3, 1) != "6" && valor.substr(3, 1) != "7") {
                alert('Alerta Yupick!\nN\xfamero de m\xf3vil no pertenece a Espa\xf1a.');
                $('#yupick_type_alert_phone').focus();
                event.preventDefault();
                return false;
            } else if (valor.length >= 13) {
                alert('Alerta Yupick!\nN\xfamero de m\xf3vil no pertenece a Espa\xf1a.');
                $('#yupick_type_alert_phone').focus();
                event.preventDefault();
                return false;
            }
        }
    }
    $('#yupick_type_alert_email').removeClass("required_text");
    return true;
}
var is_onepagecheckoutps = typeof Review === 'object';
if (is_onepagecheckoutps) {
    Review.placeOrder = (function(event) {
        var cached_function = null;
        cached_function = Review.placeOrder;
        return function(event) {
            if (yp_carrier_id == $('.delivery_option_radio:checked').val().replace(',', '') && Payment.validateSelected() && Review._validatePlaceOrder().valid) {
                if (validade_yupick(event)) {
                    yupick_update_recoger();
                } else {
                    return false;
                }
            }
            cached_function.apply(this, arguments);
        }
    }());
}