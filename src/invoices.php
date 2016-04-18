<?php

$invoices = $app['controllers_factory'];

$invoices->get('/debe', function () use ($app) {
  $sql = "
  select i.ref, SUM(a.amount) as Owing
    from Invoice i
    join Allocation a
      on a.invoice_id = i.id and i.customer_id = 17
    group by i.ref
  having SUM(a.amount) > 0";

  // return $app->json( $app['db']->fetchAssoc( $sql, array() ) );
  return $app->json( array( 'id' => 'algo' ) );
});

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
    group by i.ref
  having SUM(a.amount) > 0";
  
  $owinCustomer = $app['db']->fetchAssoc( $sql, array() );
  
  // get credit_limit customer
  $creditLimit = $app['db']->fetchAssoc( "select ct.credit_limit from Customer c join CustomerType ct on c.customer_type_id = ct.id where c.id = 17", array() );
  
  // insert invoice details
  $items = $data['items'];
  $grandTotal = 0;
  for( $i = 0; $i < sizeof($items); $i++ ) {
    $grandTotal += (double) $items[$i]['total'];
  }
  
  if ( ( $owinCustomer + $grandTotal ) <= $creditLimit['credit_limit'] ) {
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
    
    return $app->json( formatResponse( $invoiceId ) );
  } else {
    return $app->json( array( 'error' => 'El cliente ha excedio su limite de credito' ) );
  }
});

/*
truncate InvoiceDetail;
truncate Invoice;
truncate Allocation;
truncate Payment;
*/

return $invoices;