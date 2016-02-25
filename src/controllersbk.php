<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//Request::setTrustedProxies(array('127.0.0.1'));

$app->get('/login', function(Request $request) use ($app) {
    return $app['twig']->render('login.html', array(
        'error'         => $app['security.last_error']($request),
        'last_username' => $app['session']->get('_security.last_username'),
    ));
});

$app->get('/', function () use ($app) {
  
    return 'algo';

    $sql = "SELECT * FROM Cliente";
    $clientes = $app['db']->fetchAll($sql, array());
    
    return $app['twig']->render('index.html', array('clientes' => $clientes));
    
})
->bind('inicio');


$app->get('/vistapreviafactura/{idFactura}', function ($idFactura) use ($app) {

  $sql = "SELECT * FROM Cliente";
  $clientes = $app['db']->fetchAll($sql, array());

  $sql = "SELECT fi.*, i.nombre FROM FacturaItem as fi inner join Item as i on fi.idItem = i.idItem where fi.idFactura = $idFactura";
  $facturaItem = $app['db']->fetchAll($sql, array());

  $sql = "select f.*, bl.*, c.nombre nombreCliente, c.direccion, n.nombre nombreNaviera, r.nombre nombreRegimen, p.nombre nombreProducto,
  e.nombre nombreEquipo from Factura f join BL bl on f.idBL = bl.idBL join Cliente c on bl.idCliente = c.idCliente
  join Naviera n on bl.idNaviera = n.idNaviera join Regimen r on bl.idRegimen = r.idRegimen join Producto p on bl.idProducto = p.idProducto
  join Equipo e on bl.idEquipo = e.idEquipo where f.idFactura = $idFactura";
  $facturaBL = $app['db']->fetchAssoc($sql, array());

  $fechaEntrega = $facturaBL['fechaEntrega'] == NULL ? '' : date( 'd/m/Y', strtotime( $facturaBL['fechaEntrega'] ) );

  // mPDF PHP library
  include("mpdf/mpdf.php");
    
  $mpdf=new mPDF('win-1252','A4','','',20,15,48,25,10,10);
  $mpdf->useOnlyCoreFonts = true;    // false is default
  //$mpdf->SetProtection(array('print'));
  $mpdf->SetTitle("Logi. - Factura");
  $mpdf->SetAuthor("Logi.");
  /* $mpdf->SetWatermarkText("Pagado");
  $mpdf->showWatermarkText = true;
  $mpdf->watermark_font = 'DejaVuSansCondensed';
  $mpdf->watermarkTextAlpha = 0.1; */
  $mpdf->SetDisplayMode('fullpage');

  $html = '
  <html>
    <head>
      <style>
        body {
          font-family: sans-serif;
          font-size: 10pt;
        }
        p {
          margin: 0pt;
        }
        td {
          vertical-align: top;
        }
        .items td {
          border-left: 0.1mm solid #000000;
          border-right: 0.1mm solid #000000;
        }
        table thead th {
          background-color: #808080;
          text-align: center;
          border: 0.1mm solid #000000;
        }
        .items td.blanktotal {
          background-color: #FFFFFF;
          border: 0mm none #000000;
          border-top: 0.1mm solid #000000;
          border-right: 0.1mm solid #000000;
        }
        .items td.totals {
          border: 0.1mm solid #000000;
        }
        .info th {
          text-align: left;
        }
      </style>
    </head>
  <body>

    <!--mpdf
    <htmlpageheader name="myheader">
      <table width="100%">
        <tr>
          <td width="50%">
            <span style="font-weight: bold; font-size: 14pt;">Logi</span><br />Email: info@Logi.com<br />
            <span style="font-size: 15pt;">&#9742;</span> 66648929
          </td>
          <td width="50%" style="height:30px; background-color:red; color:#fff; font-size:22px;text-align:center">NOTA DE COBRO</td>
        </tr>
      </table>
    </htmlpageheader>

    <htmlpagefooter name="myfooter">
      <div style="border-top: 1px solid #000000; font-size: 9pt; text-align: center; padding-top: 3mm; ">
      Pagina {PAGENO} de {nb}
      </div>
    </htmlpagefooter>

    <sethtmlpageheader name="myheader" value="on" show-this-page="1" />
    <sethtmlpagefooter name="myfooter" value="on" />
    mpdf-->


    <table width="100%" style="font-family: serif;margin-bottom: 5mm;" cellpadding="10">
      <tr>
        <td width="40%">
          <table class="info">
            <tbody>
              <tr>
                <td style="width:100%;height:50px;">&nbsp;</td>
              </tr>
              <tr>
                <td><strong>Cobrar a:</strong></td>
              </tr>
              <tr>
                <td>' . $facturaBL['nombreCliente'] . '</td>
              </tr>
              <tr>
                <td>' . $facturaBL['direccion'] . '</td>
              </tr>
              <tr>
                <td><strong>Fecha de entrega</strong></td>
              </tr>
              <tr>
                <td>' . $fechaEntrega . '</td>
              </tr>
            </tbody>
          </table>
        </td>
        <td width="20%">&nbsp;</td>
        <td width="40%">
          <table class="info">
            <tbody>
              <tr>
                <th>Numero de contenedor</th>
                <td>' . $facturaBL['numeroContenedor'] . '</td>
              </tr>
              <tr>
                <th>Naviera</th>
                <td>' . $facturaBL['nombreNaviera'] . '</td>
              </tr>
              <tr>
                <th>Producto</th>
                <td>' . $facturaBL['nombreProducto'] . '</td>
              </tr>
              <tr>
                <th>Equipo</th>
                <td>' . $facturaBL['nombreEquipo'] . '</td>
              </tr>
              <tr>
                <th>Numero de cartones</th>
                <td>' . $facturaBL['numeroCartones'] . '</td>
              </tr>
              <tr>
                <th>Factura #</th>
                <td>' . $facturaBL['idFactura'] . '</td>
              </tr>
            </tbody>
          </table>
        </td>
      </tr>
    </table>

    <table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse;" cellpadding="8">
      <thead>
        <tr>
          <th width="80%" align="left">Item</th>
          <th width="5%" style="border-right:none"></th>
          <th width="15%" style="border-left:none" align="right">Precio</th>
        </tr>
      </thead>
      <tbody>';
        $htmlitems = '';
        $total = 0;
        foreach ($facturaItem as $key => $item) {
          $htmlitems .= '<tr>
            <td>' . $item['nombre'] . '</td><td style="border-right:none">Q</td><td style="border-left:none" align="right">' .  number_format(floatval($item['valor']), 2, '.', ',') . '</td>
          </tr>';
          $total += $item['valor'];
        }

        $html .= $htmlitems;

        $html .= '
      </tbody>
    </table>

    <table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse;" cellpadding="8">
      <tbody>
        <tr>
          <td width="60%" class="blanktotal" colspan="1" rowspan="6"></td>
          <td width="20%" class="totals"><b>TOTAL:</b></td>
          <td width="5%" style="border-right:none" class="totals">Q</td>
          <td width="15%" style="border-left:none" class="totals" align="right"><b>' . number_format($total, 2, '.', ',') . '</b></td>
        </tr>
      </tbody>
    </table>
    <div style="margin-top:50px;font-weight:bold">
    <p>Para cualquier informaci&oacute;n adicional, por favor contactenos.</p>
    <p>Gracias por confiarnos su negocio.</p>
    </div>
  </body>
  </html>
  ';

  $mpdf->WriteHTML($html);
  $nombrePDF = 'Factura ' . $facturaBL['nombreCliente'] . ' ' . $fechaEntrega;
  $email = "info@Logi.com";

  if ( isset( $_GET['email']) ) {

    $content = $mpdf->Output('', 'S');

    $content = chunk_split(base64_encode($content));
    $mailto = $_GET['email']; //Mailto here
    $from_name = $email; //Name of sender mail
    $from_mail = $email; //Mailfrom here
    $subject = 'Factura';
    $message = '';
    $filename = $nombrePDF; //Your Filename whit local date and time

    //Headers of PDF and e-mail
    $boundary = "XYZ-" . date("dmYis") . "-ZYX";

    $header = "--$boundary\r\n";
    $header .= "Content-Transfer-Encoding: 8bits\r\n";
    $header .= "Content-Type: text/html; charset=ISO-8859-1\r\n\r\n"; //plain
    $header .= "$message\r\n";
    $header .= "--$boundary\r\n";
    $header .= "Content-Type: application/pdf; name=\"".$filename."\"\r\n";
    $header .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n";
    $header .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $header .= "$content\r\n";

    $header2 = "MIME-Version: 1.0\r\n";
    $header2 .= 'From: Logi <info@Logi.com>' . "\r\n";
    $header2 .= "Reply-To: $from_mail\r\n";
    $header2 .= "Content-type: multipart/mixed; boundary=\"$boundary\"\r\n";

    mail($mailto,$subject,$header,$header2, "-r".$from_mail);

    $mpdf->Output($nombrePDF ,'I');

  } else if ( isset( $_GET['d']) ) {
    $mpdf->Output($nombrePDF , 'D');
  } else {
    $mpdf->Output($nombrePDF, 'I');
  }

  exit;
  return 1;
    
})
->bind('vistapreviafactura');

$app->get('/vistapreviafacturas', function () use ($app) {

  $getInvoices = $app['getInvoices'];
  $result = $getInvoices($_GET['idCliente'],$_GET['month'],$_GET['year']);

  // mPDF PHP library
  include("mpdf/mpdf.php");
    
  $mpdf=new mPDF('win-1252','A4','','',20,15,48,25,10,10);
  $mpdf->useOnlyCoreFonts = true;    // false is default
  //$mpdf->SetProtection(array('print'));
  $mpdf->SetTitle("Logi. - Facturas");
  $mpdf->SetAuthor("Logi.");
  /* $mpdf->SetWatermarkText("Pagado");
  $mpdf->showWatermarkText = true;
  $mpdf->watermark_font = 'DejaVuSansCondensed';
  $mpdf->watermarkTextAlpha = 0.1; */
  $mpdf->SetDisplayMode('fullpage');

  $html = '
  <html>
    <head>
      <style>
        body {
          font-family: sans-serif;
          font-size: 10pt;
        }
        p {
          margin: 0pt;
        }
        td {
          vertical-align: top;
        }
        .items td {
          border-left: 0.1mm solid #000000;
          border-right: 0.1mm solid #000000;
        }
        table thead th {
          background-color: #808080;
          text-align: center;
          border: 0.1mm solid #000000;
        }
        .items td.blanktotal {
          background-color: #FFFFFF;
          border: 0mm none #000000;
          border-top: 0.1mm solid #000000;
          border-right: 0.1mm solid #000000;
        }
        .items td.totals {
          border: 0.1mm solid #000000;
        }
        .info th {
          text-align: left;
        }
      </style>
    </head>
  <body>

    <!--mpdf
    <htmlpageheader name="myheader">
      <table width="100%">
        <tr>
          <td width="50%">
            <span style="font-weight: bold; font-size: 14pt;">Logi</span><br />Email: info@Logi.com<br />
            <span style="font-size: 15pt;">&#9742;</span> 66648929
          </td>
          <td width="50%" style="height:30px; background-color:red; color:#fff; font-size:22px;text-align:center">FACTURAS</td>
        </tr>
      </table>
    </htmlpageheader>

    <htmlpagefooter name="myfooter">
      <div style="border-top: 1px solid #000000; font-size: 9pt; text-align: center; padding-top: 3mm; ">
      Pagina {PAGENO} de {nb}
      </div>
    </htmlpagefooter>

    <sethtmlpageheader name="myheader" value="on" show-this-page="1" />
    <sethtmlpagefooter name="myfooter" value="on" />
    mpdf-->
    
    <table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse;" cellpadding="8">
      <thead>
        <tr>
          <th width="5%" align="right"></th>
          <th width="10%" align="left">F.ENTREGA</th>
          <th width="10%" align="left">CONTENEDOR</th>
          <th width="20%" align="left">CLIENTE</th>
          <th width="15%" align="left">PRODUCTO</th>
          <th width="15%" align="right">IMPORTE</th>
          <th width="15%" align="right">PENDIENTE</th>
          <th width="10%" align="left">STATUS</th>
        </tr>
      </thead>
      <tbody>';
        $htmlitems = '';
        $total = 0;
        //var_dump($result);exit;
        foreach ($result as $key => $item) {
          $htmlitems .= '<tr>
            <td align="right">' . ($key + 1) . '</td>
            <td>' . ( $item['fechaEntrega'] != "" ? date("d-m-Y", strtotime($item['fechaEntrega'])) : "" ) . '</td>
            <td>' . $item['numeroContenedor'] . '</td>
            <td>' . $item['nombreCliente'] . '</td>
            <td>' . $item['nombreProducto'] . '</td>
            <td align="right">Q ' . number_format(floatval($item['valor']), 2, '.', ',') . '</td>
            <td align="right">Q ' . number_format(floatval($item['pendiente']), 2, '.', ',') . '</td>
            <td>' . ( $item['pendiente'] == null ? 'Sin items' : $item['pendiente'] > 0 ? 'Pendiente' : 'Pagada' ) . '</td>
          </tr>';
          $total += $item['valor'];
        }

        $html .= $htmlitems;

        $html .= '<tr>
          <td class="totals"></td>
          <td class="totals">TOTAL</td>
          <td class="totals"></td>
          <td class="totals"></td>
          <td class="totals"></td>
          <td class="totals" align="right">Q ' . number_format($total, 2, '.', ',') . '</td>
          <td class="totals"></td>
          <td class="totals"></td>
        </tr>
        ';

        $html .= '
      </tbody>
    </table>

    <div style="margin-top:50px;font-weight:bold">
    <p>Para cualquier informaci&oacute;n adicional, por favor contactenos.</p>
    <p>Gracias por confiarnos su negocio.</p>
    </div>
  </body>
  </html>
  ';

  $mpdf->WriteHTML($html);
  $nombrePDF = 'Listado de facturas';
  $email = "info@Logi.com";

  if ( isset( $_GET['email']) ) {

    $content = $mpdf->Output('', 'S');

    $content = chunk_split(base64_encode($content));
    $mailto = $_GET['email']; //Mailto here
    $from_name = $email; //Name of sender mail
    $from_mail = $email; //Mailfrom here
    $subject = 'Factura';
    $message = '';
    $filename = $nombrePDF; //Your Filename whit local date and time

    //Headers of PDF and e-mail
    $boundary = "XYZ-" . date("dmYis") . "-ZYX";

    $header = "--$boundary\r\n";
    $header .= "Content-Transfer-Encoding: 8bits\r\n";
    $header .= "Content-Type: text/html; charset=ISO-8859-1\r\n\r\n"; //plain
    $header .= "$message\r\n";
    $header .= "--$boundary\r\n";
    $header .= "Content-Type: application/pdf; name=\"".$filename."\"\r\n";
    $header .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n";
    $header .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $header .= "$content\r\n";

    $header2 = "MIME-Version: 1.0\r\n";
    $header2 .= 'From: Logi <info@Logi.com>' . "\r\n";
    $header2 .= "Reply-To: $from_mail\r\n";
    $header2 .= "Content-type: multipart/mixed; boundary=\"$boundary\"\r\n";

    mail($mailto,$subject,$header,$header2, "-r".$from_mail);

    $mpdf->Output($nombrePDF ,'I');

  } else if ( isset( $_GET['d']) ) {
    $mpdf->Output($nombrePDF , 'D');
  } else {
    $mpdf->Output($nombrePDF, 'I');
  }

  exit;
  return 1;
    
})
->bind('vistapreviafacturas');

$app->get('/vistapreviapago/{idPago}', function ($idPago) use ($app) {

  $sql = "SELECT p.*, c.nombre nombreCliente FROM Pago p join Cliente c on p.idCliente = c.idCliente where p.idPago = $idPago";
  $pago = $app['db']->fetchAssoc($sql, array());

  $fecha = $pago['fecha'] == NULL ? '' : date( 'd/m/Y', strtotime( $pago['fecha'] ) );

  // mPDF PHP library
  include("mpdf/mpdf.php");
    
  $mpdf=new mPDF('win-1252','A4','','',20,15,48,25,10,10);
  $mpdf->useOnlyCoreFonts = true;    // false is default
  //$mpdf->SetProtection(array('print'));
  $mpdf->SetTitle("Logi. - Pago");
  $mpdf->SetAuthor("Logi.");
  /* $mpdf->SetWatermarkText("Pagado");
  $mpdf->showWatermarkText = true;
  $mpdf->watermark_font = 'DejaVuSansCondensed';
  $mpdf->watermarkTextAlpha = 0.1; */
  $mpdf->SetDisplayMode('fullpage');

  $html = '
  <html>
    <head>
      <style>
        body {
          font-family: sans-serif;
          font-size: 10pt;
        }
        p {
          margin: 0pt;
        }
        td {
          vertical-align: top;
        }
        .items td {
          border-left: 0.1mm solid #000000;
          border-right: 0.1mm solid #000000;
        }
        table thead th {
          background-color: #808080;
          text-align: center;
          border: 0.1mm solid #000000;
        }
        .items td.blanktotal {
          background-color: #FFFFFF;
          border: 0mm none #000000;
          border-top: 0.1mm solid #000000;
          border-right: 0.1mm solid #000000;
        }
        .items td.totals {
          border: 0.1mm solid #000000;
        }
        .info th {
          text-align: left;
        }
      </style>
    </head>
  <body>

    <!--mpdf
    <htmlpageheader name="myheader">
      <table width="100%">
        <tr>
          <td width="50%">
            <span style="font-weight: bold; font-size: 14pt;">Logi</span><br />Email: info@Logi.com<br />
            <span style="font-size: 15pt;">&#9742;</span> 66648929
          </td>
          <td width="50%" style="text-align: right;"><!--Invoice No.<br /><span style="font-weight: bold; font-size: 12pt;">0012345</span>--></td>
        </tr>
      </table>
    </htmlpageheader>

    <htmlpagefooter name="myfooter">
      <div style="border-top: 1px solid #000000; font-size: 9pt; text-align: center; padding-top: 3mm; ">
      Pagina {PAGENO} de {nb}
      </div>
    </htmlpagefooter>

    <sethtmlpageheader name="myheader" value="on" show-this-page="1" />
    <sethtmlpagefooter name="myfooter" value="on" />
    mpdf-->


    <table width="100%" style="font-family: serif;margin-bottom: 5mm;" cellpadding="10">
      <tr>
        <td width="40%">&nbsp;</td>
        <td width="10%">&nbsp;</td>
        <td width="50%">
          <table class="info">
            <tbody>
              <tr>
                <th>Cliente</th>
                <td>' . $pago['nombreCliente'] . '</td>
              </tr>
              <tr>
                <th>Fecha</th>
                <td>' . $fecha . '</td>
              </tr>
            </tbody>
          </table>
        </td>
      </tr>
    </table>

    <table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse;" cellpadding="8">
      <thead>
        <tr>
          <th width="80%">Pago</th>
          <th width="5%" style="border-right:none"></th>
          <th width="15%" style="border-left:none">Valor</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Pago realizado</td>
          <td style="border-right:none">Q</td><td style="border-left:none" align="right">' . number_format(floatval($pago['valor']), 2, '.', ',') . '</td>
        </tr>
      </tbody>
    </table>

    <table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse;" cellpadding="8">
      <tbody>
        <tr>
          <td width="60%" class="blanktotal" colspan="1" rowspan="6"></td>
          <td width="20%" class="totals"><b>TOTAL:</b></td>
          <td width="5%" style="border-right:none" class="totals">Q</td>
          <td width="15%" style="border-left:none" class="totals" align="right">' . number_format(floatval($pago['valor']), 2, '.', ',') . '</td>
        </tr>
      </tbody>
    </table>
  </body>
  </html>
  ';

  $mpdf->WriteHTML($html);
  $nombrePDF = 'Pago ' . $pago['nombreCliente'] . ' ' . $fecha;
  $email = "info@Logi.com";

  if ( isset( $_GET['email']) ) {

    $content = $mpdf->Output('', 'S');

    $content = chunk_split(base64_encode($content));
    $mailto = $_GET['email']; //Mailto here
    $from_name = $email; //Name of sender mail
    $from_mail = $email; //Mailfrom here
    $subject = 'Pago';
    $message = '';
    $filename = $nombrePDF; //Your Filename whit local date and time

    //Headers of PDF and e-mail
    $boundary = "XYZ-" . date("dmYis") . "-ZYX";

    $header = "--$boundary\r\n";
    $header .= "Content-Transfer-Encoding: 8bits\r\n";
    $header .= "Content-Type: text/html; charset=ISO-8859-1\r\n\r\n"; //plain
    $header .= "$message\r\n";
    $header .= "--$boundary\r\n";
    $header .= "Content-Type: application/pdf; name=\"".$filename."\"\r\n";
    $header .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n";
    $header .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $header .= "$content\r\n";

    $header2 = "MIME-Version: 1.0\r\n";
    $header2 .= 'From: Logi <info@Logi.com>' . "\r\n";
    $header2 .= "Reply-To: $from_mail\r\n";
    $header2 .= "Content-type: multipart/mixed; boundary=\"$boundary\"\r\n";

    mail($mailto,$subject,$header,$header2, "-r".$from_mail);

    $mpdf->Output($nombrePDF ,'I');

  } else if ( isset( $_GET['d']) ) {
    $mpdf->Output($nombrePDF , 'D');
  } else {
    $mpdf->Output($nombrePDF, 'I');
  }

  exit;
  return 1;
    
});

$app->get('/vistapreviapagos', function () use ($app) {

  $getPayments = $app['getPayments'];
  $result = $getPayments($_GET['idCliente'],$_GET['month'],$_GET['year']);

  // mPDF PHP library
  include("mpdf/mpdf.php");
    
  $mpdf=new mPDF('win-1252','A4','','',20,15,48,25,10,10);
  $mpdf->useOnlyCoreFonts = true;    // false is default
  //$mpdf->SetProtection(array('print'));
  $mpdf->SetTitle("Logi. - Pagos");
  $mpdf->SetAuthor("Logi.");
  /* $mpdf->SetWatermarkText("Pagado");
  $mpdf->showWatermarkText = true;
  $mpdf->watermark_font = 'DejaVuSansCondensed';
  $mpdf->watermarkTextAlpha = 0.1; */
  $mpdf->SetDisplayMode('fullpage');

  $html = '
  <html>
    <head>
      <style>
        body {
          font-family: sans-serif;
          font-size: 10pt;
        }
        p {
          margin: 0pt;
        }
        td {
          vertical-align: top;
        }
        .items td {
          border-left: 0.1mm solid #000000;
          border-right: 0.1mm solid #000000;
        }
        table thead th {
          background-color: #808080;
          text-align: center;
          border: 0.1mm solid #000000;
        }
        .items td.blanktotal {
          background-color: #FFFFFF;
          border: 0mm none #000000;
          border-top: 0.1mm solid #000000;
          border-right: 0.1mm solid #000000;
        }
        .items td.totals {
          border: 0.1mm solid #000000;
        }
        .info th {
          text-align: left;
        }
      </style>
    </head>
  <body>

    <!--mpdf
    <htmlpageheader name="myheader">
      <table width="100%">
        <tr>
          <td width="50%">
            <span style="font-weight: bold; font-size: 14pt;">Logi</span><br />Email: info@Logi.com<br />
            <span style="font-size: 15pt;">&#9742;</span> 66648929
          </td>
          <td width="50%" style="height:30px; background-color:red; color:#fff; font-size:22px;text-align:center">PAGOS</td>
        </tr>
      </table>
    </htmlpageheader>

    <htmlpagefooter name="myfooter">
      <div style="border-top: 1px solid #000000; font-size: 9pt; text-align: center; padding-top: 3mm; ">
      Pagina {PAGENO} de {nb}
      </div>
    </htmlpagefooter>

    <sethtmlpageheader name="myheader" value="on" show-this-page="1" />
    <sethtmlpagefooter name="myfooter" value="on" />
    mpdf-->
    
    <table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse;" cellpadding="8">
      <thead>
        <tr>
          <th width="5%" align="right"></th>
          <th width="10%" align="left">FECHA</th>
          <th width="10%" align="left">CLIENTE</th>
          <th width="20%" align="right">IMPORTE</th>
          <th width="15%" align="right">REFERENCIA</th>
        </tr>
      </thead>
      <tbody>';
        $htmlitems = '';
        $total = 0;
        //var_dump($result);exit;
        foreach ($result as $key => $item) {
          $htmlitems .= '<tr>
            <td align="right">' . ($key + 1) . '</td>
            <td>' . ( $item['fecha'] != "" ? date("d-m-Y", strtotime($item['fecha'])) : "" ) . '</td>
            <td>' . $item['nombreCliente'] . '</td>
            <td align="right">Q ' . number_format(floatval($item['importe']), 2, '.', ',') . '</td>
            <td align="right">' . $item['referencia'] . '</td>
          </tr>';
          $total += $item['valor'];
        }

        $html .= $htmlitems;

        $html .= '<tr>
          <td class="totals"></td>
          <td class="totals">TOTAL</td>
          <td class="totals"></td>
          <td class="totals" align="right">Q ' . number_format($total, 2, '.', ',') . '</td>
          <td class="totals"></td>
        </tr>
        ';

        $html .= '
      </tbody>
    </table>

    <div style="margin-top:50px;font-weight:bold">
    <p>Para cualquier informaci&oacute;n adicional, por favor contactenos.</p>
    <p>Gracias por confiarnos su negocio.</p>
    </div>
  </body>
  </html>
  ';

  $mpdf->WriteHTML($html);
  $nombrePDF = 'Listado de pagos';
  $email = "info@Logi.com";

  if ( isset( $_GET['email']) ) {

    $content = $mpdf->Output('', 'S');

    $content = chunk_split(base64_encode($content));
    $mailto = $_GET['email']; //Mailto here
    $from_name = $email; //Name of sender mail
    $from_mail = $email; //Mailfrom here
    $subject = 'Factura';
    $message = '';
    $filename = $nombrePDF; //Your Filename whit local date and time

    //Headers of PDF and e-mail
    $boundary = "XYZ-" . date("dmYis") . "-ZYX";

    $header = "--$boundary\r\n";
    $header .= "Content-Transfer-Encoding: 8bits\r\n";
    $header .= "Content-Type: text/html; charset=ISO-8859-1\r\n\r\n"; //plain
    $header .= "$message\r\n";
    $header .= "--$boundary\r\n";
    $header .= "Content-Type: application/pdf; name=\"".$filename."\"\r\n";
    $header .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n";
    $header .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $header .= "$content\r\n";

    $header2 = "MIME-Version: 1.0\r\n";
    $header2 .= 'From: Logi <info@Logi.com>' . "\r\n";
    $header2 .= "Reply-To: $from_mail\r\n";
    $header2 .= "Content-type: multipart/mixed; boundary=\"$boundary\"\r\n";

    mail($mailto,$subject,$header,$header2, "-r".$from_mail);

    $mpdf->Output($nombrePDF ,'I');

  } else if ( isset( $_GET['d']) ) {
    $mpdf->Output($nombrePDF , 'D');
  } else {
    $mpdf->Output($nombrePDF, 'I');
  }

  exit;
  return 1;
    
});

$app->get('/vistapreviaestadodecuenta/{idCliente}', function ($idCliente) use ($app) {

  //$sqlBalance = "select c.idCliente, c.nombre nombreCliente, sum(a.valor) pendiente from Cliente c join Asociacion a on c.idCliente = a.idCliente where c.idCliente = $idCliente and ( a.fecha < '" . $_GET['year'] . "-" . ( $_GET['startMonth'] == 'all' ? '01' : $_GET['startMonth'] ) . "-01' or a.tipo='saldo inicial') group by c.idCliente having pendiente > 0";

   $sqlBalance = "select sum(a.valor) pendiente from Asociacion a left join Cliente c on a.idCliente = c.idCliente left join Factura f on a.idFactura = f.idFactura left join BL bl on f.idBL = bl.idBL where a.idCliente = $idCliente and ( (case when a.idPago is null then bl.fechaEntrega when a.idPago is not null then a.fecha when bl.idBL is null then a.fecha else bl.fechaEntrega end) < '" . $_GET['year'] . "-" . ( $_GET['startMonth'] == 'all' ? '01' : $_GET['startMonth'] ) . "-01' or a.tipo='saldo inicial')";

//echo $sqlBalance;exit;
  if ( $_GET['startMonth'] != 'all' ) {

    $startDate = $_GET['year'] . "-" . $_GET['startMonth'] . "-01";
    $endDate = date("Y-m-t", strtotime($_GET['year'] . "-" . $_GET['endMonth'] . "-01"));

    // $sql = "select a.*, c.nombre nombreCliente, p.nombre nombreProducto, pa.referencia from Asociacion a join Factura f on a.idFactura = f.idFactura join Cliente c on f.idCliente = c.idCliente join BL bl on f.idBL = bl.idBL join Producto p on bl.idProducto = p.idProducto left join Pago pa on a.idPago = pa.idPago where a.valor is not NULL and f.idCliente = $idCliente and a.fecha >= '$startDate' and a.fecha <= '$endDate'";
    // query base
    // select a.*, SUM(a.valor) from Asociacion a where a.idCliente = 1 group by case when a.idPago is null then a.idAsociacion when a.idPago is not null then a.idPago end, a.idPago order by a.idAsociacion asc;

    $sql = "select a.*, SUM(a.valor) pendiente, c.nombre nombreCliente, bl.idBL, bl.fechaEntrega, p.nombre nombreProducto, pa.referencia, case when a.idPago is null then bl.fechaEntrega when a.idPago is not null then a.fecha when bl.idBL is null then a.fecha else bl.fechaEntrega end as fechaGeneral from Asociacion a left join Cliente c on a.idCliente = c.idCliente left join Factura f on a.idFactura = f.idFactura left join BL bl on f.idBL = bl.idBL left join Producto p on bl.idProducto = p.idProducto left join Pago pa on a.idPago = pa.idPago where a.idCliente = $idCliente and (case when a.idPago is null then bl.fechaEntrega when a.idPago is not null then a.fecha when bl.idBL is null then a.fecha else bl.fechaEntrega end) >= '$startDate' and (case when a.idPago is null then bl.fechaEntrega when a.idPago is not null then a.fecha when bl.idBL is null then a.fecha else bl.fechaEntrega end) <= '$endDate' group by case when a.idPago is null then a.idAsociacion when a.idPago is not null then a.idPago end, a.idPago having a.tipo != 'saldo inicial' order by fechaGeneral asc";

  } else {
    $sql = "select a.*, SUM(a.valor) pendiente, c.nombre nombreCliente, bl.idBL, bl.fechaEntrega, p.nombre nombreProducto, pa.referencia, case when a.idPago is null then bl.fechaEntrega when a.idPago is not null then a.fecha when bl.idBL is null then a.fecha else bl.fechaEntrega end as fechaGeneral from Asociacion a left join Cliente c on a.idCliente = c.idCliente left join Factura f on a.idFactura = f.idFactura left join BL bl on f.idBL = bl.idBL left join Producto p on bl.idProducto = p.idProducto left join Pago pa on a.idPago = pa.idPago where a.idCliente = $idCliente group by case when a.idPago is null then a.idAsociacion when a.idPago is not null then a.idPago end, a.idPago having a.tipo != 'saldo inicial' order by fechaGeneral asc";
  }

  /*echo $sql;
  echo $sqlBalance;exit;*/

  $balance = $app['db']->fetchAssoc($sqlBalance, array());
  $asociaciones = $app['db']->fetchAll($sql, array());

  $nombreCliente = sizeof($asociaciones) > 0 ? $asociaciones[0]['nombreCliente'] : "";

  //var_dump($asociaciones);exit;
  //$fechaEntrega = $facturaBL['fechaEntrega'] == NULL ? '' : date( 'd/m/Y', strtotime( $facturaBL['fechaEntrega'] ) );

  // mPDF PHP library
  include("mpdf/mpdf.php");
    
  $mpdf=new mPDF('win-1252','A4','','',20,15,48,25,10,10);
  $mpdf->useOnlyCoreFonts = true;    // false is default
  //$mpdf->SetProtection(array('print'));
  $mpdf->SetTitle("Logi. - Estado de cuenta");
  $mpdf->SetAuthor("Logi.");
  /* $mpdf->SetWatermarkText("Pagado");
  $mpdf->showWatermarkText = true;
  $mpdf->watermark_font = 'DejaVuSansCondensed';
  $mpdf->watermarkTextAlpha = 0.1; */
  $mpdf->SetDisplayMode('fullpage');

  $html = '
  <html>
    <head>
      <style>
        body {
          font-family: sans-serif;
          font-size: 10pt;
        }
        p {
          margin: 0pt;
        }
        td {
          vertical-align: top;
        }
        .items td {
          border-left: 0.1mm solid #000000;
          border-right: 0.1mm solid #000000;
        }
        table thead th {
          background-color: #808080;
          text-align: center;
          border: 0.1mm solid #000000;
        }
        .items td.blanktotal {
          background-color: #FFFFFF;
          border: 0mm none #000000;
          border-top: 0.1mm solid #000000;
          border-right: 0.1mm solid #000000;
        }
        .items td.totals {
          border: 0.1mm solid #000000;
        }
        .info th {
          text-align: left;
        }
      </style>
    </head>
  <body>

    <!--mpdf
    <htmlpageheader name="myheader">
      <table width="100%">
        <tr>
          <td width="50%">
            <span style="font-weight: bold; font-size: 14pt;">Logi</span><br />Email: info@Logi.com<br />
            <span style="font-size: 15pt;">&#9742;</span> 66648929
          </td>
          <td width="50%" style="height:30px; background-color:red; color:#fff; font-size:22px;text-align:center">ESTADO DE CUENTA</td>
        </tr>
      </table>
    </htmlpageheader>

    <htmlpagefooter name="myfooter">
      <div style="border-top: 1px solid #000000; font-size: 9pt; text-align: center; padding-top: 3mm; ">
      Pagina {PAGENO} de {nb}
      </div>
    </htmlpagefooter>

    <sethtmlpageheader name="myheader" value="on" show-this-page="1" />
    <sethtmlpagefooter name="myfooter" value="on" />
    mpdf-->


    <table width="100%" style="font-family: serif;margin-bottom: 5mm;" cellpadding="10">
      <tr>
        <td width="40%">&nbsp;</td>
        <td width="10%">&nbsp;</td>
        <td width="50%">
          <table class="info">
            <tbody>
              <tr>
                <th>Cliente</th>
                <td>' . $nombreCliente . '</td>
              </tr>
              <tr>
                <th>Fecha</th>
                <td>' . date('d/m/Y') . '</td>
              </tr>
            </tbody>
          </table>
        </td>
      </tr>
    </table>

    <table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse;" cellpadding="8">
      <thead>
        <tr>
          <th width="10%">Fecha</th>
          <th width="30%">Descripcion</th>
          <th width="20%">Abono</th>
          <th width="20%">Contenedor</th>
          <th width="20%">Saldo</th>
        </tr>
      </thead>
      <tbody>';

        $htmlasociaciones = '';

        $total = 0;
        $descripcion = "";
        $abono = "";
        $contenedor = "";
        $saldo = $balance['pendiente'] != "" ? $balance['pendiente'] : 0;

        $htmlasociaciones .= '<tr>
            <td></td>
            <td>Saldo anterior</td>
            <td align="right"></td>
            <td align="right"></td>
            <td align="right">' . "Q " . number_format(floatval($saldo), 2, '.', ',') . '</td>
          </tr>';

        foreach ($asociaciones as $key => $asociacion) {
          if ( $asociacion['idPago'] == null ) {
            $descripcion = $asociacion['nombreProducto'];
            $contenedor = $asociacion['pendiente'];
            $abono = "";
            $saldo += $contenedor;
          } else {
            $descripcion = "Abono " . $asociacion['referencia'] != null ? " Pago # de referencia: " . $asociacion['referencia'] : "";
            $contenedor = "";
            $abono = $asociacion['pendiente'];
            // convertir abono a positivo
            $abono = 0 - $abono;
            $saldo -= $abono;
          }

          $date = new DateTime($asociacion['fechaGeneral']);
          $asociacion['fecha'] = $date->format('d-m-Y');

          $htmlasociaciones .= '<tr>
            <td>' . $asociacion['fecha'] . '</td>
            <td>' . $descripcion . '</td>
            <td align="right">' . ($abono != "" ? "Q " . number_format(floatval($abono), 2, '.', ',') : "") . '</td>
            <td align="right">' . ($contenedor != "" ? "Q " . number_format(floatval($contenedor), 2, '.', ',') : "") . '</td>
            <td align="right">' . "Q " . number_format(floatval($saldo), 2, '.', ',') . '</td>
          </tr>';
          $total += $asociacion['valor'];
        }

        $html .= $htmlasociaciones;

        $html .= '
      </tbody>
    </table>
    <table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse;" cellpadding="8">
      <tbody>
        <tr>
          <td width="100%" class="blanktotal" colspan="7" rowspan="6"></td>
        </tr>
      </tbody>
    </table>
  </body>
  </html>
  ';

  $mpdf->WriteHTML($html);
  $nombrePDF = 'Factura ' . $facturaBL['nombreCliente'] . ' ' . $fechaEntrega;

  if ( isset( $_GET['d']) ) {
    $mpdf->Output($nombrePDF , 'D');
  } else {
    $mpdf->Output($nombrePDF, 'I');
  }

  exit;
  return 1;
    
})
->bind('vistapreviaestadodecuenta');

$app->post('/getBLs', function () use ($app,$db) {

    // Datatables
    $out = Editor::inst( $db, 'BL' )->pkey( 'idBL' )
    ->field(
        Field::inst( 'BL.numeroBL' )
          ->validator( 'Validate::notEmpty', array(
                "message" => "Campo requerido") )
          ->setFormatter( function ( $val, $data, $opts ) {
                if ($val == "") return "";
                else {
                    return strtoupper($val);
            } } ),
        Field::inst( 'BL.numeroContenedor' )
          ->validator( 'Validate::notEmpty', array(
                "message" => "Campo requerido") )
          ->setFormatter( function ( $val, $data, $opts ) {
                if ($val == "") return "";
                else {
                    return strtoupper($val);
            } } ),
        Field::inst( 'BL.idNaviera' )
          ->validator( 'Validate::notEmpty', array(
                "message" => "Campo requerido") ),
        Field::inst( 'Naviera.nombre' ),
        Field::inst( 'BL.idRegimen' )
          ->validator( 'Validate::notEmpty', array(
                "message" => "Campo requerido") ),
        Field::inst( 'Regimen.nombre' ),
        Field::inst( 'BL.idCliente' )
          ->validator( 'Validate::notEmpty', array(
                "message" => "Campo requerido") ),
        Field::inst( 'Cliente.nombre' ),
        Field::inst( 'BL.idProducto' )
          ->validator( 'Validate::notEmpty', array(
                "message" => "Campo requerido") ),
        Field::inst( 'Producto.nombre' ),
        Field::inst( 'BL.idEquipo' )
          ->validator( 'Validate::notEmpty', array(
                "message" => "Campo requerido") ),
        Field::inst( 'Equipo.nombre' ),
        Field::inst( 'BL.idEstadoBL' )
          ->validator( 'Validate::notEmpty', array(
                "message" => "Campo requerido") ),
        Field::inst( 'EstadoBL.nombre' ),
        Field::inst( 'BL.detalleEstado' ),
        Field::inst( 'BL.numeroCartones' )->validator( 'Validate::notEmpty', array(
                "message" => "Campo requerido") ),
        Field::inst( 'BL.fechaRecepcion' )
          ->validator( 'Validate::dateFormat', array(
              "format"  => "d/m/Y H:i:s",
              "message" => "Por favor ingrese una fecha dd/mm/yyyy h:m:s"
          ) )
          ->getFormatter( 'Format::datetime', array("from" => "Y-m-d H:i:s", "to" => "d/m/Y H:i:s") )
          ->setFormatter( function ( $val, $data, $opts ) {
              if ($val == "") return NULL;
              else {
                  return date( 'Y-m-d H:i:s', strtotime( str_replace('/', '-', $val) ) );
          } } ),
        Field::inst( 'BL.fechaEntrega' )
          ->validator( 'Validate::dateFormat', array(
              "format"  => "d/m/Y H:i:s",
              "message" => "Por favor ingrese una fecha dd/mm/yyyy h:m:s"
          ) )
          ->getFormatter( 'Format::datetime', array("from" => "Y-m-d H:i:s", "to" => "d/m/Y H:i:s") )
          ->setFormatter( function ( $val, $data, $opts ) {
              if ($val == "") return NULL;
              else {
                  return date( 'Y-m-d H:i:s', strtotime( str_replace('/', '-', $val) ) );
          } } ),
        Field::inst( 'BL.fechaArribo' )
          ->validator( 'Validate::dateFormat', array(
              "format"  => "d/m/Y H:i:s",
              "message" => "Por favor ingrese una fecha dd/mm/yyyy h:m:s"
          ) )
          ->getFormatter( 'Format::datetime', array("from" => "Y-m-d H:i:s", "to" => "d/m/Y H:i:s") )
          ->setFormatter( function ( $val, $data, $opts ) {
              if ($val == "") return NULL;
              else {
                  return date( 'Y-m-d H:i:s', strtotime( str_replace('/', '-', $val) ) );
          } } ),
        Field::inst( 'BL.fechaMora' )
          ->validator( 'Validate::dateFormat', array(
              "format"  => "d/m/Y H:i:s",
              "message" => "Por favor ingrese una fecha dd/mm/yyyy h:m:s"
          ) )
          ->getFormatter( 'Format::datetime', array("from" => "Y-m-d H:i:s", "to" => "d/m/Y H:i:s") )
          ->setFormatter( function ( $val, $data, $opts ) {
              if ($val == "") return NULL;
              else {
                  return date( 'Y-m-d H:i:s', strtotime( str_replace('/', '-', $val) ) );
          } } ),
        Field::inst( 'BL.fechaAlmacenaje' )
          ->validator( 'Validate::dateFormat', array(
              "format"  => "d/m/Y H:i:s",
              "message" => "Por favor ingrese una fecha dd/mm/yyyy h:m:s"
          ) )
          ->getFormatter( 'Format::datetime', array("from" => "Y-m-d H:i:s", "to" => "d/m/Y H:i:s") )
          ->setFormatter( function ( $val, $data, $opts ) {
              if ($val == "") return NULL;
              else {
                  return date( 'Y-m-d H:i:s', strtotime( str_replace('/', '-', $val) ) );
          } } )
          
    )
    ->leftJoin( 'Naviera',     'Naviera.idNaviera',          '=', 'BL.idNaviera' )
    ->leftJoin( 'Regimen',     'Regimen.idRegimen',          '=', 'BL.idRegimen' )
    ->leftJoin( 'Cliente',     'Cliente.idCliente',          '=', 'BL.idCliente' )
    ->leftJoin( 'Producto',     'Producto.idProducto',          '=', 'BL.idProducto' )
    ->leftJoin( 'Equipo',     'Equipo.idEquipo',          '=', 'BL.idEquipo' )
    ->leftJoin( 'EstadoBL',     'EstadoBL.idEstadoBL',          '=', 'BL.idEstadoBL' )
    ->process($_POST)
    ->data();

    // When there is no 'action' parameter we are getting data, and in this
    // case we want to send extra data back to the client, with the options
    if ( !isset($_POST['action']) ) {
        // Get a list of navieras for the `select` list
        $out['navieras'] = $db
            ->selectDistinct( 'Naviera', 'idNaviera as value, nombre as label' )
            ->fetchAll();
     
        // Get regimen details
        $out['regimen'] = $db
            ->select( 'Regimen', 'idRegimen as value, nombre as label' )
            ->fetchAll();

        // Get clientes details
        $out['clientes'] = $db
            ->select( 'Cliente', 'idCliente as value, nombre as label' )
            ->fetchAll();

        // Get productos details
        $out['productos'] = $db
            ->select( 'Producto', 'idProducto as value, nombre as label' )
            ->fetchAll();

        // Get equipos details
        $out['equipos'] = $db
            ->select( 'Equipo', 'idEquipo as value, nombre as label' )
            ->fetchAll();

        // Get estados BL details
        $out['estadosBL'] = $db
            ->select( 'EstadoBL', 'idEstadoBL as value, nombre as label' )
            ->fetchAll();

        //$out['estadosBL'] = $app['db']->fetchAll("select bl.idBL from BL bl join Factura f on bl.idBL = f.idBL left join FacturaItem fi on f.idFactura = fi.idFactura where bl.fechaEntrega > 0 group by bl.idBL having SUM(fi.valor) > 0");
    }
     
    // Send it back to the client
    // echo json_encode( $out );
    return $app->json($out, 201);
})
->bind('getBLs');

$app->get('/tipogastosoperativos', function () use ($app,$db) {

    $typeOfExpenses = $app['db']->fetchAll("select * from TipoGastoOperativo");
    return $app->json($typeOfExpenses, 201);

})
->bind('tipogastosoperativos');

$app->get('/gastosoperativos/{idTipoGasto}', function ($idTipoGasto) use ($app) {

    $sql = "SELECT * FROM GastoOperativo where idTipoGastoOperativo = $idTipoGasto";
    $gastosOperativos = $app['db']->fetchAll($sql, array());

    return $app->json($gastosOperativos);
})
->bind('gastosoperativos');

// get all expenses made by all BLs
$app->get('/gastosoperativosbls', function () use ($app) {

    $sql = "SELECT blgo.*, go.nombre FROM BLGastoOperativo blgo join GastoOperativo go on blgo.idGastoOperativo = go.idGastoOperativo where pagado = 'N'";
    $gastosOperativos = $app['db']->fetchAll($sql, array());

    return $app->json($gastosOperativos);
});

// get all banks
$app->get('/bancos', function () use ($app) {

    $sql = "SELECT * from Banco";
    $bancos = $app['db']->fetchAll($sql, array());

    return $app->json($bancos);
});

$app->post('/getgastosoperativos', function () use ($app,$db) {

    $facturas = $app['db']->fetchAll("select bl.idBL, bl.numeroBL, bl.numeroContenedor, bl.fechaRecepcion, SUM(blgo.valor) total, c.nombre nombreCliente, r.nombre nombreRegimen, p.nombre nombreProducto from BL bl left join BLGastoOperativo blgo on bl.idBL = blgo.idBL join Cliente c on bl.idCliente = c.idCliente join Regimen r on bl.idRegimen = r.idRegimen join Producto p on bl.idProducto = p.idProducto group by bl.idBL");

    $BLsReestructurados = array();

    foreach ($facturas as $value) {
        $BLActual = array();

        $BLActual["DT_RowId"] = "row_" . $value['idBL'];
        $BLActual["BL"] = $value;
        array_push($BLsReestructurados, $BLActual);
    }

    $out = array(
        "data" => $BLsReestructurados
    );

    return $app->json($out, 201);
})
->bind('getgastosoperativos');

$app->get('/getfacturaspendientesdepago/{idCliente}', function ($idCliente) use ($app,$db) {

    $facturas = $app['db']->fetchAll("select f.idFactura, bl.fechaEntrega, f.valor total, SUM(a.valor) pendiente from Factura f join Asociacion a on f.idFactura = a.idFactura join BL bl on f.idBL = bl.idBL group by f.idFactura having pendiente > 0");

    //echo $app->json($out, 201);exit;
    return $app->json($facturas, 201);
})
->bind('getfacturaspendientesdepago');

$app->post('/getestadosdecuenta', function () use ($app,$db) {

    $facturas = $app['db']->fetchAll("select bl.idBL, bl.numeroContenedor, bl.fechaRecepcion, c.nombre nombreCliente, p.nombre nombreProducto, f.idFactura, SUM(fi.valor) totalFactura, (select SUM(cp.valor) from Pago cp where f.idCliente = cp.idCliente) totalPagos from BL bl join Factura f on bl.idBL = f.idBL join FacturaItem fi on f.idFactura = fi.idFactura join Cliente c on bl.idCliente = c.idCliente join Producto p on bl.idProducto = p.idProducto group by f.idFactura");

    $facturasReestructuradas = array();

    $debe = 0;

    foreach ($facturas as $value) {
        $facturaActual = array();

        $facturaActual["DT_RowId"] = "row_" . $value['idFactura'];
        $facturaActual["BL"] = $value;

        // add calculates values
        var_dump($value);
        $totalPagos = (int) $value["totalPagos"];
        $debe += (int) $value["totalFactura"];

        if ( $totalPagos >= $debe) {
            $value["estado"] = "Pagado";
        } else {
            $value["estado"] = "Pendiente";
        }

        array_push($facturasReestructuradas, $facturaActual);
    }

    $out = array(
        "data" => $facturasReestructuradas
    );

    //echo $app->json($out, 201);exit;
    return $app->json($out, 201);
})
->bind('getestadosdecuenta');

$app->post('/getnavieras', function () use ($app,$db) {

    // Datatables
    $out = Editor::inst( $db, 'Naviera' )->pkey( 'idNaviera' )
    ->field(
        Field::inst( 'Naviera.nombre' )
    )
    ->process($_POST)
    ->data();
     
    // Send it back to the client
    return $app->json($out, 201);
})
->bind('getnavieras');

$app->post('/getregimen', function () use ($app,$db) {

    // Datatables
    $out = Editor::inst( $db, 'Regimen' )->pkey( 'idRegimen' )
    ->field(
        Field::inst( 'Regimen.nombre' )
    )
    ->process($_POST)
    ->data();
     
    // Send it back to the client
    return $app->json($out, 201);
})
->bind('getregimen');

$app->post('/getaduanas', function () use ($app,$db) {

    // Datatables
    $out = Editor::inst( $db, 'Aduana' )->pkey( 'idAduana' )
    ->field(
        Field::inst( 'Aduana.nombre' )
    )
    ->process($_POST)
    ->data();
     
    // Send it back to the client
    return $app->json($out, 201);
})
->bind('getaduanas');

$app->post('/getclientes', function () use ($app,$db) {

    // Datatables
    $out = Editor::inst( $db, 'Cliente' )->pkey( 'idCliente' )
    ->field(
        Field::inst( 'Cliente.nombre' ),
        Field::inst( 'Cliente.email' ),
        Field::inst( 'Cliente.celular' ),
        Field::inst( 'Cliente.telefono' ),
        Field::inst( 'Cliente.direccion' )
    )
    ->process($_POST)
    ->data();
     
    // Send it back to the client
    return $app->json($out, 201);
})
->bind('getclientes');

$app->post('/getproductos', function () use ($app,$db) {

    // Datatables
    $out = Editor::inst( $db, 'Producto' )->pkey( 'idProducto' )
    ->field(
        Field::inst( 'Producto.nombre' )
    )
    ->process($_POST)
    ->data();
     
    // Send it back to the client
    return $app->json($out, 201);
})
->bind('getproductos');

$app->post('/getitems', function () use ($app,$db) {

    // Datatables
    $out = Editor::inst( $db, 'Item' )->pkey( 'idItem' )
    ->field(
        Field::inst( 'Item.nombre' )
    )
    ->process($_POST)
    ->data();
     
    // Send it back to the client
    return $app->json($out, 201);
})
->bind('getitems');

$app->post('/getequipos', function () use ($app,$db) {

    // Datatables
    $out = Editor::inst( $db, 'Equipo' )->pkey( 'idEquipo' )
    ->field(
        Field::inst( 'Equipo.nombre' )
    )
    ->process($_POST)
    ->data();
     
    // Send it back to the client
    return $app->json($out, 201);
})
->bind('getequipos');

$app->post('/getgastosadmon', function () use ($app,$db) {

    // Datatables
    $out = Editor::inst( $db, 'GastoAdmon' )->pkey( 'idGastoAdmon' )
    ->field(
        Field::inst( 'GastoAdmon.nombre' )
    )
    ->process($_POST)
    ->data();
     
    // Send it back to the client
    return $app->json($out, 201);
})
->bind('getgastosadmon');

$app->post('/getdetallegastosadmon', function () use ($app,$db) {

    // Datatables
    $out = Editor::inst( $db, 'DetalleGastoAdmon' )->pkey( 'idDetalleGastoAdmon' )
    ->field(
        Field::inst( 'DetalleGastoAdmon.valor' ),
        Field::inst( 'DetalleGastoAdmon.fecha' )
            ->validator( 'Validate::dateFormat', array(
                "format"  => "d/m/Y",
                "message" => "Por favor ingrese una fecha dd/mm/yyyy"
            ) )
            ->getFormatter( 'Format::date_sql_to_format', "d/m/Y" )
            ->setFormatter( 'Format::date_format_to_sql', "d/m/Y" ),
        Field::inst( 'DetalleGastoAdmon.idGastoAdmon' ),
        Field::inst( 'GastoAdmon.nombre' ))
    ->leftJoin( 'GastoAdmon',     'GastoAdmon.idGastoAdmon',          '=', 'DetalleGastoAdmon.idGastoAdmon' )
    ->process($_POST)
    ->data();
    
    // When there is no 'action' parameter we are getting data, and in this
    // case we want to send extra data back to the client, with the options
    if ( !isset($_POST['action']) ) {
        
        // Get gastos admon
        $out['gastosadmon'] = $db
            ->select( 'GastoAdmon', 'idGastoAdmon as value, nombre as label' )
            ->fetchAll();
    }

     
    // Send it back to the client
    return $app->json($out, 201);
})
->bind('getdetallegastosadmon');

$app->post('/getdetallegastosoperativos', function () use ($app,$db) {

    // Datatables
    $out = Editor::inst( $db, 'BLGastoOperativo' )->pkey( 'idBLGastoOperativo' )
    ->field(
        Field::inst( 'BLGastoOperativo.valor' ),
        Field::inst( 'BLGastoOperativo.fecha' )
            ->validator( 'Validate::dateFormat', array(
                "format"  => "d/m/Y",
                "message" => "Por favor ingrese una fecha dd/mm/yyyy"
            ) )
            ->getFormatter( 'Format::date_sql_to_format', "d/m/Y" )
            ->setFormatter( 'Format::date_format_to_sql', "d/m/Y" ),
        Field::inst( 'BLGastoOperativo.idBL' ),
        Field::inst( 'BLGastoOperativo.idGastoOperativo' ),
        Field::inst( 'BL.numeroBL' ),
        Field::inst( 'GastoOperativo.nombre' ))
    ->leftJoin( 'BL',     'BL.idBL',          '=', 'BLGastoOperativo.idBL' )
    ->leftJoin( 'GastoOperativo',     'GastoOperativo.idGastoOperativo',          '=', 'BLGastoOperativo.idGastoOperativo' )
    ->process($_POST)
    ->data();
    
    // When there is no 'action' parameter we are getting data, and in this
    // case we want to send extra data back to the client, with the options
    if ( !isset($_POST['action']) ) {
        
        // Get gastos operativos
        $out['BLs'] = $db
            ->select( 'BL', 'idBL as value, numeroBL as label' )
            ->fetchAll();

        $out['gastosoperativos'] = $db
            ->select( 'GastoOperativo', 'idGastoOperativo as value, nombre as label' )
            ->fetchAll();
    }

     
    // Send it back to the client
    return $app->json($out, 201);
})
->bind('getdetallegastosoperativos');



$app->post('/ingresarpago/{idCliente}', function ($idCliente) use ($app) {

    $facturasAPagar = json_decode(stripslashes($_POST['facturasAPagar']));

    $pagoSaldoInicial = $_POST['pagoSaldoInicial'];

    if (sizeof($facturasAPagar) > 0 || $pagoSaldoInicial > 0) {
    
      // insertar pago
      $app['db']->insert('Pago', array('idCliente' => $idCliente, 'idMetodoPago' => $_POST['idMetodoPago'], 'valor' => $_POST['valor'], 'fecha' => $_POST['fecha'], 'referencia' => $_POST['referencia'], 'cambio' => $_POST['cambio'] ));
      $idPago = $app['db']->lastInsertId();

      // si hay pago de saldo inicial
      if ($pagoSaldoInicial > 0) {
        $app['db']->insert('Asociacion', array('idPago' => $idPago, 'idCliente' => $idCliente, 'valor' => $pagoSaldoInicial * -1, 'tipo' => 'pago saldo inicial', 'fecha' => $_POST['fecha'] ));
      }

      // ingresar asociacion(es)
      foreach($facturasAPagar as $d) {
          $factura = get_object_vars($d);
          $valor = $factura[key($factura)];
          $valor = $valor * -1;

          $app['db']->insert('Asociacion', array('idPago' => $idPago, 'idFactura' => key($factura), 'idCliente' => $idCliente, 'valor' => $valor, 'tipo' => 'pago', 'fecha' => $_POST['fecha'] ));
      }

      return $idPago;
    } else {
      return $pagoSaldoInicial;
    }
})
->bind('ingresarpago');

$app->post('/getusuarios', function () use ($app,$db) {

    // Datatables
    $out = Editor::inst( $db, 'users' )->pkey( 'id' )
    ->field(
        Field::inst( 'users.username' ),
                Field::inst( 'users.password' )->setFormatter( function ( $val, $data, $opts ) use ($app,$db) {
            $userProvider = new App\User\UserProvider($app['db']);
            $user= $userProvider->getUser("usuariodefault", $val);
            $encoder = $app['security.encoder_factory']->getEncoder($user);
            $password = $encoder->encodePassword($val, $user->getSalt());
            return $password;
        } ),
        Field::inst( 'users.roles' )
    )
    ->process($_POST)
    ->data();
     
    // Send it back to the client
    return $app->json($out, 201);
})
->bind('getusuarios');

$app->get('/verfacturas', function () use ($app) {

    $sql = "SELECT * FROM Cliente";
    $clientes = $app['db']->fetchAll($sql, array());

    return $app['twig']->render('verfacturas.html', array('clientes' => $clientes));
})
->bind('verfacturas');

$app->get('/cuentasporcobrar', function () use ($app) {

    $sql = "SELECT * FROM Cliente";
    $clientes = $app['db']->fetchAll($sql, array());

    return $app['twig']->render('cuentasporcobrar.html', array('clientes' => $clientes));
})
->bind('cuentasporcobrar');

$app->get('/estadosdecuenta', function () use ($app) {

    $sql = "SELECT * FROM Cliente";
    $clientes = $app['db']->fetchAll($sql, array());

    return $app['twig']->render('estadosdecuenta.html', array('clientes' => $clientes));
})
->bind('verestadosdecuenta');




// obtener requisicion con sus gastos
$app->get('/requisicioncongastos/{id}', function ($id) use ($app) {

    $result = array();

    $sql = "SELECT * FROM RequisicionPago where idRequisicionPago = $id";
    $requisicion = $app['db']->fetchAssoc($sql, array());

    $sql = "select drp.idBLGastoOperativo, drp.idDetalleRequisicionPago, go.nombre, blgo.valor, blgo.pagado from DetalleRequisicionPago drp join RequisicionPago rp on drp.idRequisicionPago = rp.idRequisicionPago join BLGastoOperativo blgo on drp.idBLGastoOperativo = blgo.idBLGastoOperativo join GastoOperativo go on blgo.idGastoOperativo = go.idGastoOperativo where drp.idRequisicionPago = $id;";
    $gastosPorRequisicion = $app['db']->fetchAll($sql, array());

    $result['requisicion'] = $requisicion;
    $result['gastosPorRequisicion'] = $gastosPorRequisicion;

    return $app->json($result);
});

// agregar requisicion
$app->post('/requisicion/agregar', function () use ($app) {

    $postdata = file_get_contents("php://input");
    $data = json_decode($postdata);

    $requisicion = $data->requisition;
    $gastos = $data->expenses;

    // insertar requisicion
    $app['db']->insert('RequisicionPago', array('fecha' => date("Y-m-d H:i:s"), 'idBanco' => $requisicion->bank, 'numeroCuenta' => $requisicion->accountNumber,
      'transferencia' => $requisicion->transfer, 'numeroCheque' => $requisicion->checkNumber, 'pagado' => $requisicion->paid, 'monto' => $requisicion->amount));
    
    $idRequisicionPago = $app['db']->lastInsertId();

     // insertar detalle requisicion
    foreach($gastos as $gasto) {
      // actualizar estado pagado del gasto
      $app['db']->update('BLGastoOperativo', array('pagado' => 'S'), array('idBLGastoOperativo' => $gasto));
      // insertar un detalle en la requisicion
      $app['db']->insert('DetalleRequisicionPago', array('idRequisicionPago' => $idRequisicionPago, 'idBLGastoOperativo' => $gasto));
    }

    $result = array("status" => "ok", "id" => $idRequisicionPago);

    return $app->json($result);
});

// editar requisicion
$app->post('/requisicion/{id}/editar', function ($id) use ($app) {

    $postdata = file_get_contents("php://input");
    $data = json_decode($postdata);

    $requisicion = $data->requisition;
    $gastos = $data->expenses;
    $gastosPorRequisicion = $data->expensesBLsByRequisition;

    // actualizar requisicion
    $app['db']->update('RequisicionPago', array('fecha' => date("Y-m-d H:i:s"), 'idBanco' => $requisicion->bank, 'numeroCuenta' => $requisicion->accountNumber,
      'transferencia' => $requisicion->transfer, 'numeroCheque' => $requisicion->checkNumber, 'pagado' => $requisicion->paid, 'monto' => $requisicion->amount), array('idRequisicionPago' => $requisicion->idRequisition));

    // verificar los gastos anteriores
    foreach($gastosPorRequisicion as $gasto) {
      if ($gasto->pagado != "S") {
        // actualizar estado pagado del gasto
        $app['db']->update('BLGastoOperativo', array('pagado' => 'N'), array('idBLGastoOperativo' => $gasto->idBLGastoOperativo));
        // eliminar el detalle en la requisicion
        $app['db']->delete('DetalleRequisicionPago', array('idDetalleRequisicionPago' => $gasto->idDetalleRequisicionPago));
      }
    }

    // insertar detalle requisicion
    foreach($gastos as $gasto) {
        // actualizar estado pagado del gasto
        $app['db']->update('BLGastoOperativo', array('pagado' => 'S'), array('idBLGastoOperativo' => $gasto));
        // insertar un detalle en la requisicion
        $app['db']->insert('DetalleRequisicionPago', array('idRequisicionPago' => $id, 'idBLGastoOperativo' => $gasto));
    }

    return $app->json($data);
});


$app->get('/listarequisiciones', function () use ($app) {
    return $app['twig']->render('requisiciones.html', array());
});

$app->get('/requisiciones', function () use ($app) {
    $sql = "SELECT * FROM RequisicionPago order by idRequisicionPago desc";
    $result = $app['db']->fetchAll($sql, array());

    return $app->json($result);
});

$app->get('/requisicion/{id}', function ($id) use ($app) {

    $sql = "SELECT * FROM RequisicionPago where idRequisicionPago = $id";
    $result = $app['db']->fetchAssoc($sql, array());

    return $app->json($result);
});

$app->get('/requisicion/{id}/gastos', function ($id) use ($app) {

    $sql = "select drp.idBLGastoOperativo, go.nombre, blgo.valor, blgo.pagado from DetalleRequisicionPago drp join RequisicionPago rp on drp.idRequisicionPago = rp.idRequisicionPago join BLGastoOperativo blgo on drp.idBLGastoOperativo = blgo.idBLGastoOperativo join GastoOperativo go on blgo.idGastoOperativo = go.idGastoOperativo where drp.idRequisicionPago = $id;";
    $result = $app['db']->fetchAll($sql, array());

    return $app->json($result);
});



$app->get('/vergastosoperativos', function () use ($app) {

    $sql = "SELECT * FROM Cliente";
    $clientes = $app['db']->fetchAll($sql, array());

    return $app['twig']->render('vergastosoperativos.html', array('clientes' => $clientes));
})
->bind('vergastosoperativos');

$app->get('/verpagos', function () use ($app) {
    $sql = "SELECT * FROM Cliente";
    $clientes = $app['db']->fetchAll($sql, array());

    $sql = "SELECT * FROM MetodoPago";
    $metodosPago = $app['db']->fetchAll($sql, array());

    return $app['twig']->render('verpagos.html', array('clientes' => $clientes, 'metodosPago' => $metodosPago));
})
->bind('verpagos');

$app->get('/navieras', function () use ($app) {
    return $app['twig']->render('navieras.html', array());
})
->bind('navieras');

$app->get('/regimen', function () use ($app) {
    return $app['twig']->render('regimen.html', array());
})
->bind('regimen');

$app->get('/aduanas', function () use ($app) {
    return $app['twig']->render('aduanas.html', array());
})
->bind('aduanas');

$app->get('/clientes', function () use ($app) {
    return $app['twig']->render('clientes.html', array());
})
->bind('clientes');

$app->get('/productos', function () use ($app) {
    return $app['twig']->render('productos.html', array());
})
->bind('productos');

$app->get('/items', function () use ($app) {
    return $app['twig']->render('items.html', array());
})
->bind('items');

$app->get('/equipos', function () use ($app) {
    return $app['twig']->render('equipos.html', array());
})
->bind('equipos');


$app->get('/detallegastosadmon', function () use ($app) {
    return $app['twig']->render('detallegastosadmon.html', array());
})
->bind('detallegastosadmon');

$app->get('/detallegastosoperativos', function () use ($app) {
    return $app['twig']->render('detallegastosoperativos.html', array());
})
->bind('detallegastosoperativos');


$app->get('/gastosadmon', function () use ($app) {
    return $app['twig']->render('gastosadmon.html', array());
})
->bind('gastosadmon');

$app->get('/reportes', function () use ($app) {
    return $app['twig']->render('reportes.html', array());
})
->bind('reportes')
->secure('ROLE_ADMIN');

$app->get('/usuarios', function () use ($app) {
    return $app['twig']->render('usuarios.html', array());
})
->bind('usuarios')
->secure('ROLE_ADMIN');

$app->post('/ingresarfactura', function () use ($app) {
    
    $app['db']->insert('Factura', array('idCliente' => $_POST['idCliente'], 'idBL' => $_POST['idBL'], 'fecha' => $_POST['fechaFactura'] == "" ? NULL : $_POST['fechaFactura'] ));
    $idFactura = $app['db']->lastInsertId();

    // insertar una asignacion
    $fecha = date("Y-m-d H:i:s");
    $app['db']->insert('Asociacion', array('idFactura' => $idFactura, 'idCliente' => $_POST['idCliente'], 'tipo' => 'Por cobrar', 'fecha' => $fecha));

    return $idFactura;
})
->bind('ingresarfactura');

$app->get('/editarfactura/{idFactura}', function ($idFactura) use ($app) {
    $sql = "SELECT * FROM Cliente";
    $clientes = $app['db']->fetchAll($sql, array());

    $sql = "SELECT * FROM Item";
    $items = $app['db']->fetchAll($sql, array());

    $sql = "SELECT fi.*, i.nombre FROM FacturaItem as fi inner join Item as i on fi.idItem = i.idItem where fi.idFactura = $idFactura";
    $facturaItem = $app['db']->fetchAll($sql, array());

    $sql = "select f.*, bl.*, c.nombre nombreCliente, n.nombre nombreNaviera, r.nombre nombreRegimen, p.nombre nombreProducto,
    e.nombre nombreEquipo from Factura f join BL bl on f.idBL = bl.idBL join Cliente c on bl.idCliente = c.idCliente
    join Naviera n on bl.idNaviera = n.idNaviera join Regimen r on bl.idRegimen = r.idRegimen join Producto p on bl.idProducto = p.idProducto
    join Equipo e on bl.idEquipo = e.idEquipo where f.idFactura = $idFactura";
    $facturaBL = $app['db']->fetchAssoc($sql, array());

    return $app['twig']->render('editarfactura.html', array(
        "clientes" => $clientes, "items" => $items,
        "facturaBL" => $facturaBL, "facturaItem" => $facturaItem, "idFactura" => $idFactura));
})
->bind('editarfactura');

$app->post('/ingresaritemsfactura/{idFactura}', function ($idFactura) use ($app) {
    //var_dump($_POST);
    $items = json_decode(stripslashes($_POST['items']));

    // delete all items from current invoice
    $app['db']->delete('FacturaItem', array('idFactura' => $idFactura));

    $totalFactura = 0;
    foreach($items as $d){
        $item = get_object_vars($d);
        $valor = $item[key($item)];
        $totalFactura += $valor;
        $app['db']->insert('FacturaItem', array('idFactura' => $idFactura, 'idItem' => key($item), 'valor' => $valor));
    }

    if ($_POST['fechaEntrega'] != "") {
      $idEstadoBL = 4; // entregado
      $app['db']->update('Factura', array('valor' => $totalFactura, 'fecha' => $_POST['fechaEntrega']), array('idFactura' => $idFactura));
      $app['db']->update('BL', array('fechaEntrega' => $_POST['fechaEntrega'], 'idEstadoBL' => $idEstadoBL), array('idBL' => $_POST['idBL']));
    } else {
      $app['db']->update('Factura', array('valor' => $totalFactura), array('idFactura' => $idFactura));
    }
    
    $app['db']->executeUpdate('update Asociacion set valor = ? where idFactura = ? and ISNULL(idPago)', array($totalFactura, $idFactura));

    return $idFactura;
})
->bind('ingresaritemsfactura');


$app->get('/editargastosoperativos/{idBL}', function ($idBL) use ($app) {

    $sql = "SELECT * from TipoGastoOperativo";
    $tipoGastosOperativos = $app['db']->fetchAll($sql, array());

    $sql = "SELECT blgo.*, go.nombre nombreGasto, tgo.nombre nombreTipoGasto FROM BLGastoOperativo blgo join GastoOperativo go on blgo.idGastoOperativo = go.idGastoOperativo join TipoGastoOperativo tgo on go.idTipoGastoOperativo = tgo.idTipoGastoOperativo where blgo.idBL = $idBL";
    $gastosOperativos = $app['db']->fetchAll($sql, array());

    // var_dump($gastosOperativos);exit;

    return $app['twig']->render('editargastosoperativos.html', array( "tipoGastosOperativos" => $tipoGastosOperativos, "gastosOperativos" => $gastosOperativos, "idBL" => $idBL ));
})
->bind('editargastosoperativos');

$app->post('/ingresargastooperativo/{idBL}', function ($idBL) use ($app) {

    $idGastoOperativo = $_POST['gasto'];

    // si el id del gasto es texto entonces hay que insertarlo en el BD
    if ( !is_numeric( $idGastoOperativo ) ) {
      $app['db']->insert('GastoOperativo', array('idTipoGastoOperativo' => $_POST['tipoGasto'], 'nombre' => $_POST['gasto']));
      $idGastoOperativo = $app['db']->lastInsertId();
    }
    
    $app['db']->insert('BLGastoOperativo', array('idBL' => $idBL, 'idGastoOperativo' => $idGastoOperativo, 'valor' => $_POST['valor'], 'fecha' => date('Y-m-d H:i:s')));

    return $app['db']->lastInsertId();
})
->bind('ingresargastooperativo');


$app->post('/eliminargastooperativo/{idBLGastoOperativo}', function ($idBLGastoOperativo) use ($app) {

    // eliminar todos los detalles de este gasto en la tabla de requisicion
    $app['db']->delete('DetalleRequisicionPago', array('idBLGastoOperativo' => $idBLGastoOperativo));

    // eliminar el gasto
    $app['db']->delete('BLGastoOperativo', array('idBLGastoOperativo' => $idBLGastoOperativo));

    return 1;
})
->bind('eliminargastooperativo');


// API
$app->get('/api/clientes', function () use ($app,$db) {

  $sql = "SELECT c.*, a.valor saldo FROM Cliente c join Asociacion a on c.idCliente = a.idCliente and a.tipo = 'saldo inicial'";
  $result = $app['db']->fetchAll($sql, array());

  return $app->json($result);
});

$app->get('/api/clientes/{id}', function ($id) use ($app,$db) {

  $sql = "SELECT c.*, a.valor saldo FROM Cliente c join Asociacion a on c.idCliente = a.idCliente and a.tipo = 'saldo inicial' where c.idCliente = $id";
  $result = $app['db']->fetchAssoc($sql, array());
  $result['saldo'] = (Integer) $result['saldo'];

  return $app->json($result);
});

$app->post('/api/clientes/agregar', function () use ($app,$db) {
  $postdata = file_get_contents("php://input");
  $data = json_decode($postdata);

  $cliente = $data;

  // insertar cliente
  $app['db']->insert('Cliente', array('nombre' => $cliente->nombre, 'email' => $cliente->email, 'celular' => $cliente->celular,
    'telefono' => $cliente->telefono, 'direccion' => $cliente->direccion));
  
  $idCliente = $app['db']->lastInsertId();

  // insertar balance
  $app['db']->insert('Asociacion', array('idCliente' => $idCliente, 'valor' => $cliente->saldo, 'tipo' => 'saldo inicial', 'fecha' => date("Y-m-d H:i:s")));
  
  $result = array("status" => "ok", "id" => $idCliente);

  return $app->json($result);
});

$app->post('/api/clientes/{id}/editar', function ($id) use ($app,$db) {
  $postdata = file_get_contents("php://input");
  $data = json_decode($postdata);

  $cliente = $data;

  // actualizar cliente
  $app['db']->update('Cliente', array('nombre' => $cliente->nombre, 'email' => $cliente->email, 'celular' => $cliente->celular,
    'telefono' => $cliente->telefono, 'direccion' => $cliente->direccion), array('idCliente' => $id));

  // actualizar balance
  $app['db']->update('Asociacion', array('valor' => $cliente->saldo, 'fecha' => date("Y-m-d H:i:s")), array('idCliente' => $id, 'tipo' => 'saldo inicial'));
  
  $result = array("status" => "ok", "id" => $id);

  return $app->json($result);
});

$app->get('/api/clientes/{id}/saldoinicial', function ($id) use ($app,$db) {
  
  $sql = "select SUM(a.valor) saldo from Cliente c join Asociacion a on c.idCliente = a.idCliente where c.idCliente = $id and (a.tipo = 'saldo inicial' or a.tipo = 'pago saldo inicial')";
  $result = $app['db']->fetchAssoc($sql, array());

  return $app->json($result);

});

$app->get('/api/catalogoscontenedor', function () use ($app,$db) {
  
  $sql = "select * from Naviera";
  $navieras = $app['db']->fetchAll($sql, array());

  $sql = "select * from Regimen";
  $regimenes = $app['db']->fetchAll($sql, array());

  $sql = "select * from Cliente";
  $clientes = $app['db']->fetchAll($sql, array());

  $sql = "select * from Producto";
  $productos = $app['db']->fetchAll($sql, array());

  $sql = "select * from Equipo";
  $equipos = $app['db']->fetchAll($sql, array());

  $sql = "select * from Aduana";
  $aduanas = $app['db']->fetchAll($sql, array());

  $sql = "select * from EstadoBL";
  $estadosBL = $app['db']->fetchAll($sql, array());

  $result = array( "navieras" => $navieras, "regimenes" => $regimenes, "clientes" => $clientes, "productos" => $productos, "equipos" => $equipos, "aduanas" => $aduanas, "estadosBL" => $estadosBL );

  return $app->json($result);

});

$app->get('/api/contenedores', function () use ($app,$db) {

  $sqlConditions = '';
  if (isset($_GET['month']) && $_GET['month'] != '') {
    if ( $_GET['month'] != 'all' ) { // if select all months
      $startDate = $_GET['year'] . "-" . $_GET['month'] . "-01";
      $endDate = date("Y-m-t", strtotime($startDate));
    } else { // is select a particular month
      $startDate =  $_GET['year'] . "-01-01";
      $endDate = date($_GET['year']."-12-t", strtotime($startDate));
    }
    // if is the current date or is month = all
    if ( ( date('m') == $_GET['month'] && date('Y') == $_GET['year'] ) || $_GET['month'] == 'all' ) {
      $sqlConditions = 'or (bl.fechaEntrega is null)';
    }

  } else { // no month and no year
    $startDate = date("Y-m-01");
    $endDate = date("Y-m-t", strtotime($startDate));
    $sqlConditions = 'or (bl.fechaEntrega is null)';
  }

  $sql = "select bl.*, c.nombre as nombreCliente, n.nombre nombreNaviera, r.nombre nombreRegimen, p.nombre nombreProducto, e.nombre nombreEquipo, ebl.nombre nombreEstadoBL, a.nombre nombreAduana from BL bl join Cliente c on bl.idCliente = c.idCliente join Naviera n on bl.idNaviera = n.idNaviera      join Regimen r on bl.idRegimen = r.idRegimen join Producto p on bl.idProducto = p.idProducto join Equipo e on bl.idEquipo = e.idEquipo join EstadoBL ebl on bl.idEstadoBL = ebl.idEstadoBL left join Aduana a on bl.idAduana = a.idAduana where (bl.fechaEntrega >= '$startDate' and bl.fechaEntrega <= '$endDate') $sqlConditions order by bl.idEstadoBL desc, bl.fechaEntrega asc";

  //echo $sql;

  $result = $app['db']->fetchAll($sql, array());

  foreach ($result as $key => $value) {
    $result[$key]['numeroFila'] = $key + 1;
  }
  
  return $app->json($result);

});

$app->get('/api/contenedores/{id}', function ($id) use ($app,$db) {
  
  $sql = "select @rn:=@rn+1 as numeroFila, bl.*, c.nombre as nombreCliente, n.nombre nombreNaviera, r.nombre nombreRegimen, p.nombre nombreProducto, e.nombre nombreEquipo, ebl.nombre nombreEstadoBL, a.nombre nombreAduana from (SELECT @rn := 0) r, BL bl join Cliente c on bl.idCliente = c.idCliente join Naviera n on bl.idNaviera = n.idNaviera      join Regimen r on bl.idRegimen = r.idRegimen join Producto p on bl.idProducto = p.idProducto join Equipo e on bl.idEquipo = e.idEquipo join EstadoBL ebl on bl.idEstadoBL = ebl.idEstadoBL left join Aduana a on bl.idAduana = a.idAduana where idBL = $id";
  $result = $app['db']->fetchAssoc($sql, array());

  return $app->json($result);

});

$app->post('/api/contenedores/agregar', function () use ($app,$db) {
  $postdata = file_get_contents("php://input");
  $data = json_decode($postdata);

  // insertar BK
  $app['db']->insert('BL', array('numeroBL' => $data->numeroBL, 'numeroContenedor' => $data->numeroContenedor, 'idCliente' => $data->idCliente,
    'idNaviera' => $data->idNaviera, 'numeroCartones' => $data->numeroCartones, 'fechaRecepcion' => $data->fechaRecepcion == '' ? null : $data->fechaRecepcion,
    'fechaArribo' => $data->fechaArribo == '' ? null : $data->fechaArribo, 'fechaMora' => $data->fechaMora == '' ? null : $data->fechaMora,
    'fechaAlmacenaje' => $data->fechaAlmacenaje == '' ? null : $data->fechaAlmacenaje, 'idRegimen' => $data->idRegimen, 'idProducto' => $data->idProducto,
    'idEquipo' => $data->idEquipo, 'idAduana' => $data->idAduana, 'idEstadoBL' => $data->idEstadoBL,
    'detalleEstado' => ''));
  
  $idBL = $app['db']->lastInsertId();


  // insertar una factura
  $fecha = date("Y-m-d H:i:s");
  $app['db']->insert('Factura', array('idCliente' => $data->idCliente, 'idBL' => $idBL, 'fecha' => $fecha ));
  $idFactura = $app['db']->lastInsertId();

  // insertar una asociacion
  $app['db']->insert('Asociacion', array('idFactura' => $idFactura, 'idCliente' => $data->idCliente, 'tipo' => 'Por cobrar', 'fecha' => $fecha));
    
  $result = array("status" => "ok", "id" => $idBL);

  return $app->json($result);
});

$app->post('/api/contenedores/{id}/editar', function ($id) use ($app,$db) {
  $postdata = file_get_contents("php://input");
  $data = json_decode($postdata);

  // actualizar cliente
  // insertar BK
  $app['db']->update('BL', array('numeroBL' => $data->numeroBL, 'numeroContenedor' => $data->numeroContenedor, 'idCliente' => $data->idCliente,
    'idNaviera' => $data->idNaviera, 'numeroCartones' => $data->numeroCartones, 'fechaRecepcion' => $data->fechaRecepcion == '' ? null : $data->fechaRecepcion,
    'fechaArribo' => $data->fechaArribo == '' ? null : $data->fechaArribo, 'fechaMora' => $data->fechaMora == '' ? null : $data->fechaMora,
    'fechaAlmacenaje' => $data->fechaAlmacenaje == '' ? null : $data->fechaAlmacenaje, 'idRegimen' => $data->idRegimen, 'idProducto' => $data->idProducto,
    'idEquipo' => $data->idEquipo, 'idAduana' => $data->idAduana, 'idEstadoBL' => $data->idEstadoBL,
    'detalleEstado' => ''), array('idBL' => $id));
 
  $result = array("status" => "ok", "id" => $id);

  return $app->json($result);
});

function getInvoices( $idCliente, $month, $year ) {
  $sqlConditions = '';
  if ($month != '') {
    if ( $month != 'all' ) { // if select all months
      $startDate = $year . "-" . $month . "-01";
      $endDate = date("Y-m-t", strtotime($startDate));
    } else { // is select a particular month
      $startDate =  $year  . "-01-01";
      $endDate = date($year."-12-t", strtotime($startDate));
    }

  } else { // no month and no year
    $startDate = date("Y-m-01");
    $endDate = date("Y-m-t", strtotime($startDate));
  }

  if ($idCliente != '') {
    $sqlConditions = 'and a.idCliente = ' . $idCliente;
  }

  $sql = "select f.idFactura, bl.fechaEntrega, bl.numeroContenedor, c.nombre nombreCliente, c.email, p.nombre nombreProducto, f.valor, sum(a.valor) pendiente from Factura f join Asociacion a on f.idFactura = a.idFactura join BL bl on f.idBL = bl.idBL join Cliente c on bl.idCliente = c.idCliente join Producto p on bl.idProducto = p.idProducto where a.tipo != 'saldo inicial' and a.tipo != 'pago saldo inicial' and ( f.fecha >= '$startDate' and f.fecha <= '$endDate' ) $sqlConditions group by f.idFactura order by case when bl.fechaEntrega is null then 1 else 0 end, bl.fechaEntrega asc";

  return $app['db']->fetchAll($sql, array());
}


$app['getInvoices'] = $app->protect(function ( $idCliente, $month, $year ) use ($app) {
  $sqlConditions = '';
  if ($month != '') {
    if ( $month != 'all' ) { // if select all months
      $startDate = $year . "-" . $month . "-01";
      $endDate = date("Y-m-t", strtotime($startDate));
    } else { // is select a particular month
      $startDate =  $year  . "-01-01";
      $endDate = date($year."-12-t", strtotime($startDate));
    }

  } else { // no month and no year
    $startDate = date("Y-m-01");
    $endDate = date("Y-m-t", strtotime($startDate));
  }

  if ($idCliente != '') {
    $sqlConditions = 'and a.idCliente = ' . $idCliente;
  }

  $sql = "select f.idFactura, bl.fechaEntrega, bl.numeroContenedor, c.nombre nombreCliente, c.email, p.nombre nombreProducto, f.valor, sum(a.valor) pendiente from Factura f join Asociacion a on f.idFactura = a.idFactura join BL bl on f.idBL = bl.idBL join Cliente c on bl.idCliente = c.idCliente join Producto p on bl.idProducto = p.idProducto where a.tipo != 'saldo inicial' and a.tipo != 'pago saldo inicial' and ( f.fecha >= '$startDate' and f.fecha <= '$endDate' ) $sqlConditions group by f.idFactura order by case when bl.fechaEntrega is null then 1 else 0 end, bl.fechaEntrega asc";

  return $app['db']->fetchAll($sql, array());
});

$app->get('/api/facturas', function () use ($app,$db) {

  $getInvoices = $app['getInvoices'];
  $result = $getInvoices($_GET['idCliente'],$_GET['month'],$_GET['year']);

  $total = 0;
  $pendiente = 0;
  foreach ($result as $key => $value) {
    $result[$key]['numeroFila'] = $key + 1;
    $total += $value['valor'];
    $pendiente += $value['pendiente'];
  }

  $result[] = array('fechaEntrega' => 'TOTAL', 'valor' => $total, 'pendiente' => $pendiente);
  
  return $app->json($result);

});

$app['getPayments'] = $app->protect(function ( $idCliente, $month, $year ) use ($app) {
  $sqlConditions = '';
  if (isset($_GET['month']) && $_GET['month'] != '') {
    if ( $_GET['month'] != 'all' ) { // if select all months
      $startDate = $_GET['year'] . "-" . $_GET['month'] . "-01";
      $endDate = date("Y-m-t", strtotime($startDate));
    } else { // is select a particular month
      $startDate =  $_GET['year'] . "-01-01";
      $endDate = date($_GET['year']."-12-t", strtotime($startDate));
    }

  } else { // no month and no year
    $startDate = date("Y-m-01");
    $endDate = date("Y-m-t", strtotime($startDate));
  }

  if (isset($_GET['idCliente']) && $_GET['idCliente'] != '') {
    $sqlConditions = 'and p.idCliente = ' . $_GET['idCliente'];
  }

  $sql = "select p.*, IF(p.cambio > 0, p.valor * p.cambio, p.valor) importe, c.nombre nombreCliente, c.email from Pago p join Cliente c on p.idCliente = c.idCliente where p.fecha >= '$startDate' and p.fecha <= '$endDate' $sqlConditions order by case when p.fecha is null then 1 else 0 end, p.fecha asc";
  return $app['db']->fetchAll($sql, array());
});

$app->get('/api/pagos', function () use ($app,$db) {

  $getPayments = $app['getPayments'];
  $result = $getPayments($_GET['idCliente'],$_GET['month'],$_GET['year']);

  $total = 0;
  foreach ($result as $key => $value) {
    $result[$key]['numeroFila'] = $key + 1;
    $total += $value['valor'];
  }

  $result[] = array('fecha' => 'TOTAL', 'importe' => $total);
  
  return $app->json($result);

});

$app->get('/api/pagos/{id}', function ($id) use ($app,$db) {

  $sql = "select p.*, IF(p.cambio > 0, p.valor * p.cambio, p.valor) importe, c.nombre nombreCliente, c.email from Pago p join Cliente c on p.idCliente = c.idCliente where p.idPago = $id";
  $result = $app['db']->fetchAssoc($sql, array());
  
  return $app->json($result);

});

$app->post('/api/pagos/agregar', function () use ($app,$db) {

  $postdata = file_get_contents("php://input");
  $data = json_decode($postdata);

  $data->cambio = ( $data->cambio == null || $data->cambio < 1 || $data->cambio == '' ) ? 0 : $data->cambio;

  // insertar pago
  $app['db']->insert('Pago', array('idCliente' => $data->idCliente, 'idMetodoPago' => $data->idMetodoPago, 'valor' => $data->valor, 'fecha' => $data->fecha, 'referencia' => $data->referencia, 'cambio' => $data->cambio ));
  $idPago = $app['db']->lastInsertId();

  // saldo inicial
  $sql = "select SUM(a.valor) saldo from Cliente c join Asociacion a on c.idCliente = a.idCliente where c.idCliente = " . $data->idCliente . " and (a.tipo = 'saldo inicial' or a.tipo = 'pago saldo inicial')";
  $saldoInicial = $app['db']->fetchAssoc($sql, array());
  $saldoInicial = (Double) $saldoInicial['saldo'];

  // conversion
  if ( $data->cambio > 0 ) {
    $data->valor = $data->valor * $data->cambio;
  }

  if ($saldoInicial != null && $saldoInicial != 0 && $saldoInicial != '') {
    if ( $data->valor > 0 ) {
      if ( $data->valor >= $saldoInicial ) {
        $app['db']->insert('Asociacion', array('idPago' => $idPago, 'idCliente' => $data->idCliente, 'valor' => $saldoInicial * -1, 'tipo' => 'pago saldo inicial', 'fecha' => $data->fecha ));
        $data->valor -= $saldoInicial;
      } else {
        $app['db']->insert('Asociacion', array('idPago' => $idPago, 'idCliente' => $data->idCliente, 'valor' => $data->valor * -1, 'tipo' => 'pago saldo inicial', 'fecha' => $data->fecha ));
        $data->valor = 0;
      }
    }
  }

  // facturas pendientes de pago
  $sql = "select f.idFactura, bl.fechaEntrega, f.valor total, SUM(a.valor) pendiente from Factura f join Asociacion a on f.idFactura = a.idFactura join BL bl on f.idBL = bl.idBL where f.idCliente = " . $data->idCliente . " group by f.idFactura having pendiente > 0";
  $facturasPendientesPago = $app['db']->fetchAll($sql, array());

  foreach ($facturasPendientesPago as $factura) {
    if ($data->valor > 0) {
      $pendiente = (Double) $factura['pendiente'];
      if ( $data->valor >= $pendiente ) {
        $app['db']->insert('Asociacion', array('idPago' => $idPago, 'idFactura' => $factura['idFactura'], 'idCliente' => $data->idCliente, 'valor' => $pendiente * -1, 'tipo' => 'pago', 'fecha' => $data->fecha ));
        $data->valor -= $pendiente;
      } else {
        $app['db']->insert('Asociacion', array('idPago' => $idPago, 'idFactura' => $factura['idFactura'], 'idCliente' => $data->idCliente, 'valor' => $data->valor * -1, 'tipo' => 'pago', 'fecha' => $data->fecha ));
        $data->valor = 0;
      }
    }
  }

  return $app->json($data);

});

$app->post('/api/pagos/{id}/editar', function ($id) use ($app,$db) {

  $postdata = file_get_contents("php://input");
  $data = json_decode($postdata);

  $data->cambio = ( $data->cambio == null || $data->cambio < 1 || $data->cambio == '' ) ? 0 : $data->cambio;

  // actualizar pago
  $app['db']->update('Pago', array('idCliente' => $data->idCliente, 'idMetodoPago' => $data->idMetodoPago, 'valor' => $data->valor, 'fecha' => $data->fecha, 'referencia' => $data->referencia, 'cambio' => $data->cambio ), array('idPago' => $id));

  // eliminar las asociaciones correspondientes a ese pago y las asociaciones posteriores a ese pago es decir las que se hicieron de pagos despues
  $app['db']->executeUpdate('delete from Asociacion where idPago >= ? and idCliente = ?', array($id, $data->idCliente));

  // saldo inicial
  $sql = "select SUM(a.valor) saldo from Cliente c join Asociacion a on c.idCliente = a.idCliente where c.idCliente = " . $data->idCliente . " and (a.tipo = 'saldo inicial' or a.tipo = 'pago saldo inicial')";
  $saldoInicial = $app['db']->fetchAssoc($sql, array());
  $saldoInicial = (Double) $saldoInicial['saldo'];

  // facturas pendientes de pago
  $sql = "select f.idFactura, bl.fechaEntrega, f.valor total, SUM(a.valor) pendiente from Factura f join Asociacion a on f.idFactura = a.idFactura join BL bl on f.idBL = bl.idBL where f.idCliente = " . $data->idCliente . " group by f.idFactura having pendiente > 0";
  $facturasPendientesPago = $app['db']->fetchAll($sql, array());
  
  // obtener el pago actual y los posteriores de este cliente
  $sql = "select * from Pago where idPago >= $id order by idPago asc";
  $pagos = $app['db']->fetchAll($sql, array());

  // recorrer todos los pagos e insertarle las asociaciones correspondientes
  foreach ($pagos as $pago) {
    // conversion
    if ( ( (Double) $pago['cambio'] ) != 0) {
      $pago['valor'] = (Double) $pago['valor'] * (Double) $pago['cambio'];
    }

    
    if ( $pago['valor'] > 0 && $saldoInicial > 0 ) {
      if ( $pago['valor'] >= $saldoInicial ) {
        $app['db']->insert('Asociacion', array('idPago' => $pago['idPago'], 'idCliente' => $pago['idCliente'], 'valor' => $saldoInicial * -1, 'tipo' => 'pago saldo inicial', 'fecha' => $pago['fecha'] ));
        $pago['valor'] -= $saldoInicial;
        $saldoInicial = 0;
      } else {
        $app['db']->insert('Asociacion', array('idPago' => $pago['idPago'], 'idCliente' => $pago['idCliente'], 'valor' => $pago['valor'] * -1, 'tipo' => 'pago saldo inicial', 'fecha' => $pago['fecha'] ));
        $saldoInicial -= $pago['valor'];
        $pago['valor'] = 0;
      }
    }
  
    foreach ($facturasPendientesPago as $key => $factura) {
      $pendiente = (Double) $factura['pendiente'];
      if ($pago['valor'] > 0 && $pendiente > 0) {
        if ( $pago['valor'] >= $pendiente ) {
          $app['db']->insert('Asociacion', array('idPago' => $pago['idPago'], 'idFactura' => $factura['idFactura'], 'idCliente' => $pago['idCliente'], 'valor' => $pendiente * -1, 'tipo' => 'pago', 'fecha' => $pago['fecha'] ));
          $pago['valor'] -= $pendiente;
          $facturasPendientesPago[$key]['pendiente'] = 0;
        } else {
          $app['db']->insert('Asociacion', array('idPago' => $pago['idPago'], 'idFactura' => $factura['idFactura'], 'idCliente' => $pago['idCliente'], 'valor' => $pago['valor'] * -1, 'tipo' => 'pago', 'fecha' => $pago['fecha'] ));
          $facturasPendientesPago[$key]['pendiente'] -= $pago['valor'];
          $pago['valor'] = 0;
        }
      }
    }
    
    
  }

  return $app->json($facturasPendientesPago);

});

$app->post('/api/pagos/{id}/eliminar', function ($id) use ($app,$db) {

  $sql = "select idCliente from Pago where idPago = $id";
  $result = $app['db']->fetchAssoc($sql, array());

  $data = json_encode(array('idCliente' => $result['idCliente']));
  $data = json_decode($data);

  // eliminar pago
  $app['db']->delete('Pago', array('idPago' => $id));

  // eliminar las asociaciones correspondientes a ese pago y las asociaciones posteriores a ese pago es decir las que se hicieron de pagos despues
  $app['db']->executeUpdate('delete from Asociacion where idPago >= ? and idCliente = ?', array($id, $data->idCliente));

  // saldo inicial
  $sql = "select SUM(a.valor) saldo from Cliente c join Asociacion a on c.idCliente = a.idCliente where c.idCliente = " . $data->idCliente . " and (a.tipo = 'saldo inicial' or a.tipo = 'pago saldo inicial')";
  $saldoInicial = $app['db']->fetchAssoc($sql, array());
  $saldoInicial = (Double) $saldoInicial['saldo'];

  // facturas pendientes de pago
  $sql = "select f.idFactura, bl.fechaEntrega, f.valor total, SUM(a.valor) pendiente from Factura f join Asociacion a on f.idFactura = a.idFactura join BL bl on f.idBL = bl.idBL where f.idCliente = " . $data->idCliente . " group by f.idFactura having pendiente > 0";
  $facturasPendientesPago = $app['db']->fetchAll($sql, array());
  
  // obtener el pago actual y los posteriores de este cliente
  $sql = "select * from Pago where idPago >= $id order by idPago asc";
  $pagos = $app['db']->fetchAll($sql, array());

  // recorrer todos los pagos e insertarle las asociaciones correspondientes
  foreach ($pagos as $pago) {
    // conversion
    if ( ( (Double) $pago['cambio'] ) != 0) {
      $pago['valor'] = (Double) $pago['valor'] * (Double) $pago['cambio'];
    }

    
    if ( $pago['valor'] > 0 && $saldoInicial > 0 ) {
      if ( $pago['valor'] >= $saldoInicial ) {
        $app['db']->insert('Asociacion', array('idPago' => $pago['idPago'], 'idCliente' => $pago['idCliente'], 'valor' => $saldoInicial * -1, 'tipo' => 'pago saldo inicial', 'fecha' => $pago['fecha'] ));
        $pago['valor'] -= $saldoInicial;
        $saldoInicial = 0;
      } else {
        $app['db']->insert('Asociacion', array('idPago' => $pago['idPago'], 'idCliente' => $pago['idCliente'], 'valor' => $pago['valor'] * -1, 'tipo' => 'pago saldo inicial', 'fecha' => $pago['fecha'] ));
        $saldoInicial -= $pago['valor'];
        $pago['valor'] = 0;
      }
    }
  
    foreach ($facturasPendientesPago as $key => $factura) {
      $pendiente = (Double) $factura['pendiente'];
      if ($pago['valor'] > 0 && $pendiente > 0) {
        if ( $pago['valor'] >= $pendiente ) {
          $app['db']->insert('Asociacion', array('idPago' => $pago['idPago'], 'idFactura' => $factura['idFactura'], 'idCliente' => $pago['idCliente'], 'valor' => $pendiente * -1, 'tipo' => 'pago', 'fecha' => $pago['fecha'] ));
          $pago['valor'] -= $pendiente;
          $facturasPendientesPago[$key]['pendiente'] = 0;
        } else {
          $app['db']->insert('Asociacion', array('idPago' => $pago['idPago'], 'idFactura' => $factura['idFactura'], 'idCliente' => $pago['idCliente'], 'valor' => $pago['valor'] * -1, 'tipo' => 'pago', 'fecha' => $pago['fecha'] ));
          $facturasPendientesPago[$key]['pendiente'] -= $pago['valor'];
          $pago['valor'] = 0;
        }
      }
    }
    
    
  }

  return $app->json($facturasPendientesPago);

});

$app->get('/api/metodospago', function () use ($app,$db) {

  $sql = "SELECT * FROM MetodoPago";
  $result = $app['db']->fetchAll($sql, array());

  return $app->json($result);
});

$app->get('/api/cuentasporcobrar', function () use ($app,$db) {

  //$facturas = $app['db']->fetchAll("select f.idFactura, c.nombre nombreCliente, sum(a.valor) pendiente from Factura f join Asociacion a on f.idFactura = a.idFactura join Cliente c on f.idCliente = c.idCliente group by f.idCliente having pendiente > 0;");
  $sql = "select c.idCliente, c.nombre nombreCliente, sum(a.valor) pendiente from Cliente c join Asociacion a on c.idCliente = a.idCliente group by c.idCliente having pendiente > 0";

  $result = $app['db']->fetchAll($sql, array());

  $total = 0;
  foreach ($result as $key => $value) {
    $result[$key]['numeroFila'] = $key + 1;
    $total += $value['pendiente'];
  }

  $result[] = array('idCliente' => 0, 'nombreCliente' => 'TOTAL', 'pendiente' => $total);
  
  return $app->json($result);
});


$app->error(function (\Exception $e, $code) use ($app) {

    if ($app['debug'] == false) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.html',
        'errors/'.substr($code, 0, 2).'x.html',
        'errors/'.substr($code, 0, 1).'xx.html',
        'errors/default.html',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});
