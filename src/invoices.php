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
  
  // get owing by customer
  $sql = "
  select i.ref, SUM(a.amount) as Owing
    from Invoice i
    join Allocation a
      on a.invoice_id = i.id and i.customer_id = " . $data['customerId'] . "
    group by i.ref";
  // having SUM(a.amount) > 0";
  
  $owingCustomer = $app['db']->fetchAssoc( $sql, array() );
  
  // get credit_limit customer
  $creditLimit = $app['db']->fetchAssoc( "select ct.credit_limit from Customer c join CustomerType ct on c.customer_type_id = ct.id where c.id = " . $data['customerId'], array() );
  
  // insert invoice details
  $items = $data['items'];
  $grandTotal = 0;
  for( $i = 0; $i < sizeof($items); $i++ ) {
    $grandTotal += ( double ) $items[$i]['total'];
  }

  $credit = $grandTotal - $data['payment'];
  
  if ( ( $credit + $owingCustomer['Owing'] ) <= $creditLimit['credit_limit'] ) {
    // insert invoice
    $app['db']->insert('Invoice', array(
      'customer_id' => $data['customerId']
    ));
    $invoiceId = $app['db']->lastInsertId();
    
    // insert invoice details
    for( $i = 0; $i < sizeof($items); $i++ ) {
      $app['db']->insert('InvoiceDetail', array(
        'invoice_id' => $invoiceId,
        'product_id' => $items[$i]['id'],
        'quantity' => $items[$i]['quantity'],
        'price' => $items[$i]['price'],
        'discount' => isset( $items[$i]['discount'] ) && is_numeric( $items[$i]['discount'] ) ? $items[$i]['discount'] : 'NULL',
        'total' => $items[$i]['total']
      ) );
    }
    
    // insert allocation - invoice
    $app['db']->insert('Allocation', array(
      'invoice_id' => $invoiceId,
      'amount' => $grandTotal,
      'type' => 'receivable'
    ));

    if ( $data['payment'] > 0 ) {
    
      // insert payment
      $app['db']->insert('Payment', array(
        'amount' => $data['payment'],
      ));
      $paymentId = $app['db']->lastInsertId();
      
      // insert allocation - payment
      $app['db']->insert('Allocation', array(
        'payment_id' => $paymentId,
        'invoice_id' => $invoiceId,
        'amount' => -$data['payment'],
        'type' => 'paid'
      ));
    }
    
    return $app->json( formatResponse( $invoiceId ) );
  } else {
    return $app->json( array( 'error' => 'No se puede ingresar la factura porque el cliente debe ' . $owingCustomer['Owing'] . ' y con este monto deberia ' . ( $credit + $owingCustomer['Owing'] ) . ' que excede el limite de credito de ' . $creditLimit['credit_limit'], 'sql' => $sql ) );
  }
});

/*
truncate InvoiceDetail;
truncate Invoice;
truncate Allocation;
truncate Payment;
*/

return $invoices;