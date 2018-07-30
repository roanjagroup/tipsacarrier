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
    if ($(".delivery_option_radio:checked").length) {
        var carrier_array = $('input[type=radio].delivery_option_radio:checked').val().split(',').map(function(x) {
            return parseInt(x)
        });
        selectedCarrier = carrier_array[0];
        if (YP_ID_CARRIER == selectedCarrier) {
            var position = $('input[type=radio].delivery_option_radio:checked').closest('.delivery_option');
            $('#yupick_content').appendTo(position);
            $('#yupick_content').css("display", "block");
            GetYupickPoint();
            sessionStorage.setItem('yupick_carrier_selecc', '1');
            updatePaymenOndelivery();
        } else {
            $('#yupick_content').css("display", "none");
            sessionStorage.removeItem("yupick_carrier_selecc");
        }
    }
});