<?php

namespace App\Services\Cfe;

/**
 * Generador de XML CFE según estándar UBL 2.1 DGI Uruguay
 */
class CFEXmlGenerator
{
    /**
     * Genera XML CFE válido según normativa DGI
     */
    public function generateCFE(array $data): string
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<ns0:CFE xmlns:ns0="http://cfe.dgi.gub.uy" version="1.0">
  <ns0:Encabezado>
    <ns0:IdDoc>
      <ns0:TipoCFE>{$data['tipo']}</ns0:TipoCFE>
      <ns0:Serie>{$data['serie']}</ns0:Serie>
      <ns0:Nro>{$data['numero']}</ns0:Nro>
      <ns0:FchEmis>{$data['fecha']}</ns0:FchEmis>
      <ns0:FmaPago>{$data['forma_pago']}</ns0:FmaPago>
      <ns0:FchVenc>{$data['fecha_vencimiento']}</ns0:FchVenc>
    </ns0:IdDoc>
    <ns0:Emisor>
      <ns0:RUCEmisor>{$data['emisor']['rut']}</ns0:RUCEmisor>
      <ns0:RznSoc>{$this->escapeXML($data['emisor']['razonSocial'])}</ns0:RznSoc>
      <ns0:NomComercial>{$this->escapeXML($data['emisor']['nombreComercial'])}</ns0:NomComercial>
      <ns0:DomFiscal>
        <ns0:DirCompleta>{$this->escapeXML($data['emisor']['direccion'])}</ns0:DirCompleta>
        <ns0:Ciudad>{$this->escapeXML($data['emisor']['ciudad'])}</ns0:Ciudad>
        <ns0:Departamento>{$this->escapeXML($data['emisor']['departamento'])}</ns0:Departamento>
      </ns0:DomFiscal>
    </ns0:Emisor>
    <ns0:Receptor>
      {$this->generateReceptorXML($data['receptor'])}
    </ns0:Receptor>
    <ns0:Totales>
      <ns0:TpoMoneda>{$data['totales']['moneda']}</ns0:TpoMoneda>
      <ns0:TpoCambio>{$this->formatMoney($data['totales']['tipo_cambio'])}</ns0:TpoCambio>
      <ns0:MntNoGrv>{$this->formatMoney($data['totales']['no_gravado'])}</ns0:MntNoGrv>
      <ns0:MntExpoyAsim>0</ns0:MntExpoyAsim>
      <ns0:MntImpPerc>0</ns0:MntImpPerc>
      <ns0:MntIVATasaMin>{$this->formatMoney($data['totales']['iva_tasa_min'])}</ns0:MntIVATasaMin>
      <ns0:MntIVATasaBasica>{$this->formatMoney($data['totales']['iva_tasa_basica'])}</ns0:MntIVATasaBasica>
      <ns0:IVATasaBasica>22</ns0:IVATasaBasica>
      <ns0:MntTotal>{$this->formatMoney($data['totales']['total'])}</ns0:MntTotal>
      <ns0:MontoNF>0</ns0:MontoNF>
      <ns0:MntPagar>{$this->formatMoney($data['totales']['total'])}</ns0:MntPagar>
    </ns0:Totales>
  </ns0:Encabezado>
  <ns0:Detalle>
    {$this->generateItemsXML($data['items'])}
  </ns0:Detalle>
</ns0:CFE>
XML;

        return $this->formatXML($xml);
    }

    /**
     * Genera XML del receptor
     */
    private function generateReceptorXML(array $receptor): string
    {
        $tipoDoc = isset($receptor['tipoDoc']) && $receptor['tipoDoc'] === 'CI' ? '2' : '1';
        $documento = $receptor['documento'] ?? '0';
        $nombre = $this->escapeXML($receptor['nombre'] ?? 'CLIENTE');

        return <<<XML
<ns0:TipoDocRecep>{$tipoDoc}</ns0:TipoDocRecep>
      <ns0:CodPaisRecep>UY</ns0:CodPaisRecep>
      <ns0:DocRecep>{$documento}</ns0:DocRecep>
      <ns0:RznSocRecep>{$nombre}</ns0:RznSocRecep>
XML;
    }

    /**
     * Genera XML de items/líneas de detalle
     */
    private function generateItemsXML(array $items): string
    {
        $itemsXml = '';
        foreach ($items as $index => $item) {
            $nroLinDet = $index + 1;
            $nomItem = $this->escapeXML($item['description'] ?? $item['name'] ?? 'Producto');
            $cantidad = (float) ($item['quantity'] ?? 1);
            $precioUnitario = (float) ($item['unitPrice'] ?? 0);
            $montoItem = $cantidad * $precioUnitario;
            $unidad = $this->escapeXML($item['unit'] ?? 'unidad');

            $itemsXml .= <<<XML
<ns0:Item>
      <ns0:NroLinDet>{$nroLinDet}</ns0:NroLinDet>
      <ns0:NomItem>{$nomItem}</ns0:NomItem>
      <ns0:Cantidad>{$cantidad}</ns0:Cantidad>
      <ns0:UniMed>{$unidad}</ns0:UniMed>
      <ns0:PrecioUnitario>{$this->formatMoney($precioUnitario)}</ns0:PrecioUnitario>
      <ns0:MontoItem>{$this->formatMoney($montoItem)}</ns0:MontoItem>
    </ns0:Item>

XML;
        }

        return $itemsXml;
    }

    /**
     * Valida XML CFE generado
     */
    public function validateCFE(string $xml): array
    {
        $errors = [];

        if (empty($xml)) {
            $errors[] = 'XML vacío';
        }

    if (strpos($xml, '<?xml') === false) {
            $errors[] = 'XML sin declaración de versión';
        }

    if (strpos($xml, '<ns0:CFE') === false) {
            $errors[] = 'XML sin elemento CFE';
        }

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;

        if (!@$dom->loadXML($xml)) {
            $errors[] = 'XML mal formado';
        }

        return [
            'valid' => count($errors) === 0,
            'errors' => $errors,
        ];
    }

    private function formatXML(string $xml): string
    {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        @$dom->loadXML($xml);
        $dom->formatOutput = true;

        return $dom->saveXML();
    }

    private function escapeXML(string $text): string
    {
        return htmlspecialchars($text, ENT_XML1, 'UTF-8');
    }

    private function formatMoney(float $value): string
    {
        return number_format($value, 2, '.', '');
    }
}
