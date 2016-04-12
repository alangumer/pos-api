<?php

$invoices = $app['controllers_factory'];

$invoices->get('/', function () use ($app) {
  return $app->json( $app['db']->fetchAll( "SELECT * FROM Invoice order by id desc", array() ) );
});

$invoices->get('/{id}', function ($id) use ($app) {
  return $app->json( $app['db']->fetchAssoc( "SELECT * FROM Invoice where id = ?", array($id) ) );
});

// http://dba.stackexchange.com/questions/13071/generating-invoices-and-tracking
$invoices->post('/add', function () use ($app) {
  $data = json_decode( file_get_contents("php://input"), true );
  
  // insert invoice
  $app['db']->insert('Invoice', array());
  $invoiceId = $app['db']->lastInsertId();
  
  // insert invoice details
  $grandTotal = 0;
  for( $i = 0; $i < sizeof($data); $i++ ) {
    $app['db']->insert('InvoiceDetail', array(
      'invoice_id' => $invoiceId,
      'product_id' => $data[$i]['id'],
      'quantity' => $data[$i]['quantity'],
      'price' => $data[$i]['price'],
      'discount' => isset( $data[$i]['discount'] ) && is_numeric( $data[$i]['discount'] ) ? $data[$i]['discount'] : 'NULL',
      'total' => $data[$i]['total']
    ) );
    $grandTotal += (double) $data[$i]['total'];
  }
  
  // insert allocation - invoice
  $app['db']->insert('Allocation', array(
    'invoice_id' => $invoiceId,
    'amount' => $grandTotal,
    'type' => 'receivable'
  ));
  
  // insert payment
  $app['db']->insert('Payment', array(
    'payment_type_id' => 1, // efectivo
    'amount' => $grandTotal,
  ));
  $paymentId = $app['db']->lastInsertId();
  
  // insert allocation - payment
  $app['db']->insert('Allocation', array(
    'payment_id' => $paymentId,
    'invoice_id' => $invoiceId,
    'amount' => -$grandTotal,
    'type' => 'paid'
  ));
  
  return $app->json( formatResponse( $invoiceId ) );
});

/*
truncate InvoiceDetail;
truncate Invoice;
truncate Allocation;
truncate Payment;
*/

return $invoices;