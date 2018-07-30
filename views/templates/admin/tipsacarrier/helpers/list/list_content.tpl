{*
 * 2007-2015 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    TIPSA <integraciones@tip-sa.com>
 *  @copyright 2018 TIPSA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of TIPSA
 *}

{extends file="helpers/list/list_content.tpl"}
{block name="td_content"}
        {if isset($params.type) && $params.type == 'link'}
            {if !empty($tr.codigo_envio)}
                <a class="btn btn-primary" href="{$tr.url_track|escape:'html':'UTF-8'}" target="_blank" title="{l s='Check tracking' mod='tipsacarrier'}"><i class="icon-link"></i></a>
            {else}
                --
            {/if}
	{elseif isset($params.type) && $params.type == 'label'}
            {if !empty($tr.codigo_envio)}
                <a class="btn btn-primary" href="{$tr.codigo_barras|escape:'html':'UTF-8'}" target="_blank" title="{l s='Download label' mod='tipsacarrier'}"><i class="icon-barcode"></i></a>
            {else}
                --
            {/if}
	{else}
		{$smarty.block.parent}
	{/if}
{/block}
