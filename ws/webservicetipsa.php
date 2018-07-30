<?php
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

class WebServiceTipsa
{
    public static function wsLoginCli($codage, $codcli, $passw, $urlogin)
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>
              <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
              <soap:Body>
                <LoginWSService___LoginCli>
                  <strCodAge>' . $codage . '</strCodAge>
                  <strCod>' . $codcli . '</strCod>
                  <strPass>' . $passw . '</strPass>
                </LoginWSService___LoginCli>
              </soap:Body>
              </soap:Envelope>';

        $postResult = self::wscurlinit($xml, $urlogin);

        return $postResult;
    }

    public static function wsConsEnvEstado($idsesion, $codagecargo, $codageorig, $albaran, $urlws)
    {
        $xml = '<?xml version="1.0" encoding = "utf-8"?>
                  <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
                  <soap:Header>
                     <ROClientIDHeader xmlns="http://tempuri.org/">
                         <ID>' . $idsesion . '</ID>
                     </ROClientIDHeader>
                  </soap:Header>
                  <soap:Body>
                     <WebServService___ConsEnvEstados xmlns="http://tempuri.org/">
                         <strCodAgeCargo>' . $codagecargo . '</strCodAgeCargo>
                         <strCodAgeOri>' . $codageorig . '</strCodAgeOri>
                         <strAlbaran>' . $albaran . '</strAlbaran>
                     </WebServService___ConsEnvEstados>
                  </soap:Body>
                  </soap:Envelope>';

        $postResult = self::wscurlinit($xml, $urlws);
        return $postResult;
    }

    public static function wsGrabaEnvio($datosGrabaEnvio, $urlws)
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>
              <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
                <soap:Header>
                  <ROClientIDHeader xmlns="http://tempuri.org/">
                    <ID>' . $datosGrabaEnvio['idsesion'] . '</ID>
                  </ROClientIDHeader>
                </soap:Header>
                <soap:Body>
                  <WebServService___GrabaEnvio16 xmlns="http://tempuri.org/">
                    <strCodAgeCargo>' . $datosGrabaEnvio['codage'] . '</strCodAgeCargo>
                    <strCodAgeOri>' . $datosGrabaEnvio['codage'] . '</strCodAgeOri>
                    <dtFecha>' . date('Y/m/d') . '</dtFecha>
                    <strCodTipoServ>' . $datosGrabaEnvio['tipo_servicio'] . '</strCodTipoServ>
                    <strCodCli>' . $datosGrabaEnvio['codcli'] . '</strCodCli>
                    <strNomOri><![CDATA[' . $datosGrabaEnvio['nombre_remitente'] . ']]></strNomOri>
                    <strDirOri>' . $datosGrabaEnvio['dir_remitente'] . '</strDirOri>
                    <strPobOri>' . $datosGrabaEnvio['poblacion_remitente'] . '</strPobOri>
                    <strCPOri>' . $datosGrabaEnvio['cp_remitente'] . '</strCPOri>
                    <strTlfOri>' . $datosGrabaEnvio['telefono_remitente'] . '</strTlfOri>
                    <strNomDes>' . $datosGrabaEnvio['nombre_destino'] . '</strNomDes>
                    <strDirDes>' . $datosGrabaEnvio['dir_destinatario'] . '</strDirDes>
                    <strPobDes>' . $datosGrabaEnvio['poblacion_destinatario'] . '</strPobDes>
                    <strCodPuntoRec>' . $datosGrabaEnvio['codageyp'] . '</strCodPuntoRec>
                    <strCPDes>' . $datosGrabaEnvio['cp_destinatario'] . '</strCPDes>
                    <strTlfDes>' . $datosGrabaEnvio['telefono'] . '</strTlfDes>
                    <intPaq>' . $datosGrabaEnvio['numero_paquetes'] . '</intPaq>
                    <dPesoOri>' . $datosGrabaEnvio['peso_origen'] . '</dPesoOri>
                    <dReembolso>' . $datosGrabaEnvio['reembolso'] . '</dReembolso>
                    <strRef>' . $datosGrabaEnvio['referencia'] . '</strRef>
                    <strPersContacto>' . $datosGrabaEnvio['nombre_destinatario'] . '</strPersContacto>
                    <strObs><![CDATA['  . $datosGrabaEnvio['observaciones'] . ']]></strObs>
                    <boDesSMS>0</boDesSMS>
                    <boDesEmail>1</boDesEmail>
                    <strDesMoviles>' . $datosGrabaEnvio['phone_mobile'] . '</strDesMoviles>
                    <strDesDirEmails>' . $datosGrabaEnvio['email'] . '</strDesDirEmails>
                    <boInsert>' . true . '</boInsert>
                  </WebServService___GrabaEnvio16>
                </soap:Body>
              </soap:Envelope>';
        // print($xml);
        // die();
        $postResult = self::wscurlinit($xml, $urlws);

        return $postResult;
    }

    public static function consEtiquetaEnvio($idsesion, $num_albaran, $urlws)
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>
                <soap:Envelope
                  xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
                  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                  xmlns:xsd="http://www.w3.org/2001/XMLSchema">
                  <soap:Header>
                      <ROClientIDHeader xmlns="http://tempuri.org/">
                            <ID>' . $idsesion . '</ID>
                      </ROClientIDHeader>
                  </soap:Header>
                  <soap:Body>
                      <WebServService___ConsEtiquetaEnvio2>
                          <strAlbaran>' . $num_albaran . '</strAlbaran>
                          <intIdRepDet>233</intIdRepDet>
                      </WebServService___ConsEtiquetaEnvio2>
                  </soap:Body>
                </soap:Envelope>';

        $postResult = self::wscurlinit($xml, $urlws);

        return $postResult;
    }

    public static function wsPuntosYupick($yupickKey, $postCode, $urlYupick)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
          <SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
              <SOAP-ENV:Body>
              <BuscarPuntos>
                <strClave>' . $yupickKey . '</strClave>
                <strCodPostal>' . $postCode . '</strCodPostal>
              </BuscarPuntos>
            </SOAP-ENV:Body>
          </SOAP-ENV:Envelope>';

        //$postResult = self::wscurlinit($xml, $urlYupick);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_URL, $urlYupick);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));

        $postResult = curl_exec($ch);

        return $postResult;
    }

    public static function wscurlinit($xml, $url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $postResult = curl_exec($ch);

        if ($postResult === false) {
            PrestaShopLogger::addLog('Curl error in TipdsaCarrier Module WebServService: ' . curl_error($ch));
        }

        curl_close($ch);

        $dom = new DOMDocument;
        $dom->loadXML($postResult);
        $note = $dom->getElementsByTagName("Envelope");

        return $note;
    }
}
