INSERT INTO cscart_payment_processors (processor, processor_script, processor_template, admin_template, callback,
type) VALUES ('Checkout.com(credit/debit card)', 'checkoutapipayment.php', 'views/orders/components/payments/cc.tpl',
'checkoutapipayment.tpl',
'N', 'P');
INSERT INTO cscart_payment_processors (processor, processor_script, processor_template, admin_template, callback,
type) VALUES ('Checkout.com(credit/debit card)  js solution', 'checkoutapipayment.php',
'views/orders/components/payments/checkoutapipaymentjs.tpl', 'checkoutapipayment.tpl', 'N', 'P');