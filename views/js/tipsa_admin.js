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
$(document).ready(function() {
    $(".form-group-hidden").parent().parent().css({
        "display": "none"
    });
    $("#TIPSA_MODE_off").change(function() {
        if ($("#TIPSA_MODE_off").is(':checked')) {
            $(".mode_produccion").parent().parent().css({
                "display": "none"
            });
            $(".mode_desarrollo").parent().parent().css({
                "display": "block"
            });
        }
    });
    $("#TIPSA_MODE_on").change(function() {
        if ($("#TIPSA_MODE_on").is(':checked')) {
            $(".mode_desarrollo").parent().parent().css({
                "display": "none"
            });
            $(".mode_produccion").parent().parent().css({
                "display": "block"
            });
        }
    });
});