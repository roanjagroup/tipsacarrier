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
<div class="panel">
  <div class="panel-heading">
    <i class="fa fa-edit"></i>{l s='TIPSA Carriers' mod='tipsacarrier'} <span class="tipsa_version">  {l s='Version module' mod='tipsacarrier'}: {$version|escape:'htmlall':'UTF-8'}</span>   
  </div>
  <div class="panel-body">
    <div class="row">
    	<div class="col-md-6 col-xs-12">
			<p class="size_l">Funcionalidades del m&oacute;dulo</p>
			<ul class="">
				<li>Creación de etiquetas de los pedidos</li>				
				<li>Crear pedidos desde el back-office</li>
				<li>Seguimiento de Envios</li>
				<li>Gestión de multitiendas</li>
        <li>Servicio Yupick!</li>
        <li>Vincular transportistas existentes con servicios TIPSA</li>        
			</ul>
			<hr>
    		<div class="btn-group">
    			<a class="btn btn-success btn-lg" href="{$link->getAdminLink('AdminModules')|escape:'htmlall':'UTF-8'}&configure=tipsacarrier&tab_module=shipping_logistics&module_name=tipsacarrier&registro=1" role="button">{l s='Registration' mod='tipsacarrier'}</a>    			   			
    		</div>
    		
    	</div>
    	<div class="col-md-6 col-xs-12 text-center">
    		<img src="{$imgDir|escape:'htmlall':'UTF-8'}/logo_tipsa.jpg" alt="{l s='TIPSA Nos gustan tus envios' mod='tipsacarrier'}">
    	</div>
    </div>
  </div>
</div>
