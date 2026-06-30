<?php

namespace App\Services\Sunat;

use App\Models\ComprobanteSunat;
use App\Models\Venta;

/**
 * XmlGeneratorService — genera XML UBL 2.1 compatible con SUNAT Perú.
 *
 * Estructura según:
 * - Resolución SUNAT N° 097-2012/SUNAT
 * - UBL 2.1 Invoice / CreditNote
 * - Anexo N° 8: Estructura XML de boletas y facturas
 */
class XmlGeneratorService
{
    /**
     * Genera el XML de una boleta o factura electrónica.
     */
    public function generar(Venta $venta, ComprobanteSunat $comprobante): string
    {
        $venta->load(['detalles.producto', 'cliente', 'usuario.tenant']);
        $tenant = $venta->usuario->tenant;

        $tipoDoc = $comprobante->tipo === 'factura' ? '01' : '03'; // 01=Factura, 03=Boleta
        $serie   = $comprobante->serie;
        $numero  = str_pad((string) $comprobante->correlativo, 8, '0', STR_PAD_LEFT);
        $fecha   = $venta->created_at->format('Y-m-d');
        $hora    = $venta->created_at->format('H:i:s');

        $subtotal = number_format((float) $venta->subtotal, 2, '.', '');
        $igv      = number_format((float) $venta->igv, 2, '.', '');
        $total    = number_format((float) $venta->total, 2, '.', '');

        // Datos del emisor (tenant)
        $rucEmisor    = $tenant->ruc ?? '00000000000';
        $nombreEmisor = $this->escapeXml($tenant->razon_social);

        // Datos del receptor
        $tipoDocReceptor = '1'; // 1=DNI, 6=RUC
        $numDocReceptor  = '00000000';
        $nombreReceptor  = 'CLIENTE VARIOS';

        if ($venta->cliente) {
            $tipoDocReceptor = $venta->cliente->tipo_documento === 'RUC' ? '6' : '1';
            $numDocReceptor  = $this->escapeXml($venta->cliente->numero_documento ?? '00000000');
            $nombreReceptor  = $this->escapeXml($venta->cliente->nombre ?? 'CLIENTE VARIOS');
        }

        // Generar líneas de detalle
        $lineas = $this->generarLineas($venta);

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
         xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
         xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
         xmlns:ccts="urn:un:unece:uncefact:documentation:2"
         xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
         xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2"
         xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2"
         xmlns:sac="urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1"
         xmlns:udt="urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2"
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <ext:UBLExtensions>
    <ext:UBLExtension>
      <ext:ExtensionContent/>
    </ext:UBLExtension>
  </ext:UBLExtensions>
  <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
  <cbc:CustomizationID>2.0</cbc:CustomizationID>
  <cbc:ID>{$serie}-{$numero}</cbc:ID>
  <cbc:IssueDate>{$fecha}</cbc:IssueDate>
  <cbc:IssueTime>{$hora}</cbc:IssueTime>
  <cbc:InvoiceTypeCode listID="0101">{$tipoDoc}</cbc:InvoiceTypeCode>
  <cbc:Note languageLocaleID="1000"><![CDATA[{$this->numeroEnLetras((float)$total)}]]></cbc:Note>
  <cbc:DocumentCurrencyCode>PEN</cbc:DocumentCurrencyCode>
  <cac:Signature>
    <cbc:ID>{$rucEmisor}</cbc:ID>
    <cac:SignatoryParty>
      <cac:PartyIdentification>
        <cbc:ID>{$rucEmisor}</cbc:ID>
      </cac:PartyIdentification>
      <cac:PartyName>
        <cbc:Name><![CDATA[{$nombreEmisor}]]></cbc:Name>
      </cac:PartyName>
    </cac:SignatoryParty>
    <cac:DigitalSignatureAttachment>
      <cac:ExternalReference>
        <cbc:URI>#signatureKG</cbc:URI>
      </cac:ExternalReference>
    </cac:DigitalSignatureAttachment>
  </cac:Signature>
  <cac:AccountingSupplierParty>
    <cbc:CustomerAssignedAccountID>{$rucEmisor}</cbc:CustomerAssignedAccountID>
    <cbc:AdditionalAccountID>6</cbc:AdditionalAccountID>
    <cac:Party>
      <cac:PartyName>
        <cbc:Name><![CDATA[{$nombreEmisor}]]></cbc:Name>
      </cac:PartyName>
      <cac:PostalAddress>
        <cbc:ID>150101</cbc:ID>
        <cbc:AddressTypeCode>0000</cbc:AddressTypeCode>
        <cbc:CitySubdivisionName>-</cbc:CitySubdivisionName>
        <cbc:CityName>LIMA</cbc:CityName>
        <cbc:CountrySubentity>LIMA</cbc:CountrySubentity>
        <cbc:District>LIMA</cbc:District>
        <cac:Country>
          <cbc:IdentificationCode>PE</cbc:IdentificationCode>
        </cac:Country>
      </cac:PostalAddress>
      <cac:PartyTaxScheme>
        <cbc:RegistrationName><![CDATA[{$nombreEmisor}]]></cbc:RegistrationName>
        <cbc:CompanyID schemeID="6">{$rucEmisor}</cbc:CompanyID>
        <cac:TaxScheme>
          <cbc:ID>VAT</cbc:ID>
        </cac:TaxScheme>
      </cac:PartyTaxScheme>
      <cac:PartyLegalEntity>
        <cbc:RegistrationName><![CDATA[{$nombreEmisor}]]></cbc:RegistrationName>
        <cbc:CompanyID schemeID="6">{$rucEmisor}</cbc:CompanyID>
      </cac:PartyLegalEntity>
    </cac:Party>
  </cac:AccountingSupplierParty>
  <cac:AccountingCustomerParty>
    <cbc:CustomerAssignedAccountID>{$numDocReceptor}</cbc:CustomerAssignedAccountID>
    <cbc:AdditionalAccountID>{$tipoDocReceptor}</cbc:AdditionalAccountID>
    <cac:Party>
      <cac:PartyLegalEntity>
        <cbc:RegistrationName><![CDATA[{$nombreReceptor}]]></cbc:RegistrationName>
      </cac:PartyLegalEntity>
    </cac:Party>
  </cac:AccountingCustomerParty>
  <cac:TaxTotal>
    <cbc:TaxAmount currencyID="PEN">{$igv}</cbc:TaxAmount>
    <cac:TaxSubtotal>
      <cbc:TaxableAmount currencyID="PEN">{$subtotal}</cbc:TaxableAmount>
      <cbc:TaxAmount currencyID="PEN">{$igv}</cbc:TaxAmount>
      <cac:TaxCategory>
        <cbc:ID>S</cbc:ID>
        <cac:TaxScheme>
          <cbc:ID>1000</cbc:ID>
          <cbc:Name>IGV</cbc:Name>
          <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
        </cac:TaxScheme>
      </cac:TaxCategory>
    </cac:TaxSubtotal>
  </cac:TaxTotal>
  <cac:LegalMonetaryTotal>
    <cbc:LineExtensionAmount currencyID="PEN">{$subtotal}</cbc:LineExtensionAmount>
    <cbc:TaxExclusiveAmount currencyID="PEN">{$subtotal}</cbc:TaxExclusiveAmount>
    <cbc:TaxInclusiveAmount currencyID="PEN">{$total}</cbc:TaxInclusiveAmount>
    <cbc:PayableAmount currencyID="PEN">{$total}</cbc:PayableAmount>
  </cac:LegalMonetaryTotal>
{$lineas}
</Invoice>
XML;

        return $xml;
    }

    private function generarLineas(Venta $venta): string
    {
        $lineas = '';
        $i = 1;

        foreach ($venta->detalles as $detalle) {
            $precioSinIgv = number_format((float)$detalle->precio_unit / 1.18, 2, '.', '');
            $subtotalLinea = number_format((float)$detalle->subtotal / 1.18, 2, '.', '');
            $igvLinea      = number_format((float)$detalle->subtotal - (float)$subtotalLinea, 2, '.', '');
            $totalLinea    = number_format((float)$detalle->subtotal, 2, '.', '');
            $nombre        = $this->escapeXml($detalle->producto->nombre ?? 'PRODUCTO');

            $lineas .= <<<XML
  <cac:InvoiceLine>
    <cbc:ID>{$i}</cbc:ID>
    <cbc:InvoicedQuantity unitCode="NIU">{$detalle->cantidad}</cbc:InvoicedQuantity>
    <cbc:LineExtensionAmount currencyID="PEN">{$subtotalLinea}</cbc:LineExtensionAmount>
    <cac:PricingReference>
      <cac:AlternativeConditionPrice>
        <cbc:PriceAmount currencyID="PEN">{$totalLinea}</cbc:PriceAmount>
        <cbc:PriceTypeCode>01</cbc:PriceTypeCode>
      </cac:AlternativeConditionPrice>
    </cac:PricingReference>
    <cac:TaxTotal>
      <cbc:TaxAmount currencyID="PEN">{$igvLinea}</cbc:TaxAmount>
      <cac:TaxSubtotal>
        <cbc:TaxableAmount currencyID="PEN">{$subtotalLinea}</cbc:TaxableAmount>
        <cbc:TaxAmount currencyID="PEN">{$igvLinea}</cbc:TaxAmount>
        <cac:TaxCategory>
          <cbc:ID>S</cbc:ID>
          <cbc:Percent>18</cbc:Percent>
          <cbc:TaxExemptionReasonCode>10</cbc:TaxExemptionReasonCode>
          <cac:TaxScheme>
            <cbc:ID>1000</cbc:ID>
            <cbc:Name>IGV</cbc:Name>
            <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
          </cac:TaxScheme>
        </cac:TaxCategory>
      </cac:TaxSubtotal>
    </cac:TaxTotal>
    <cac:Item>
      <cbc:Description><![CDATA[{$nombre}]]></cbc:Description>
      <cac:SellersItemIdentification>
        <cbc:ID>{$detalle->producto_id}</cbc:ID>
      </cac:SellersItemIdentification>
    </cac:Item>
    <cac:Price>
      <cbc:PriceAmount currencyID="PEN">{$precioSinIgv}</cbc:PriceAmount>
    </cac:Price>
  </cac:InvoiceLine>
XML;
            $i++;
        }

        return $lineas;
    }

    private function escapeXml(string $text): string
    {
        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function numeroEnLetras(float $monto): string
    {
        $entero   = (int) $monto;
        $decimal  = (int) round(($monto - $entero) * 100);
        return "SON {$entero} CON {$decimal}/100 SOLES";
    }
}
