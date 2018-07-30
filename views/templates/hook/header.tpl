{*
 * 2016-2018 TIPSA.COM
 *
 * NOTICE OF LICENSE
 *
 *  @author Romell Jaramillo <integraciones@tip-sa.com>
 *  @copyright 2016-2018 TIPSA.COM
 *  @license GNU General Public License version 2
 *
 * You can not resell or redistribute this software.
 *}
<script type="text/javascript">
	var YP_MODULE_DIR = "{$yp_module_dir|escape:'htmlall':'UTF-8'}";
	var YP_TOKEN = "{$yp_token|escape:'htmlall':'UTF-8'}";
	var YP_ID_CARRIER = {$yp_carrier_id|json_encode};
	var yupickResultados = '';
	var order_opc = {$order_opc|escape:'htmlall':'UTF-8'};
	var isapi = typeof google === 'object';
	var cashondelivery_modules = {$cashondelivery_modules|json_encode};
if (!isapi)
	document.write("<script type='text/javascript' src='https://maps.googleapis.com/maps/api/js?key={$gmapsapikey|escape:'htmlall':'UTF-8'}&sensor=false'><\/script>");
</script>
