<?php
class model_methods_creditcardpci extends model_methods_Abstract
{
    public function confirmation()
    {
        global $customer_id, $order, $currencies, $currency;

        $months_array = array();

        for ($i=1; $i<13; $i++) {
            $months_array[] = array('id' => tep_output_string(sprintf('%02d', $i)),
                'text' => tep_output_string_protected(sprintf('%02d', $i)));
        }

        $today = getdate();
        $years_array = array();

        for ($i=$today['year']; $i < $today['year']+10; $i++) {
            $years_array[] = array('id' => tep_output_string(strftime('%Y',mktime(0,0,0,1,1,$i))),
                'text' => tep_output_string_protected(strftime('%Y',mktime(0,0,0,1,1,$i))));
        }

        $months_string = '<select data-stripe="exp_month">';
        foreach ( $months_array as $m ) {
            $months_string .= '<option value="' . tep_output_string($m['id']) . '">' . tep_output_string($m['text']) . '</option>';
        }
        $months_string .= '</select>';

        $years_string = '<select data-stripe="exp_year">';
        foreach ( $years_array as $y ) {
            $years_string .= '<option value="' . tep_output_string($y['id']) . '">' . tep_output_string($y['text']) . '</option>';
        }
        $years_string .= '</select>';

        $content = '';



        $content .= '<div class="messageStackError payment-errors"></div>' .
            '<table id="stripe_table_new_card" border="0" width="100%" cellspacing="0" cellpadding="2">' .
            '<tr>' .
            '  <td width="30%">' . MODULE_PAYMENT_CHECKOUTAPIPAYMENT_CREDITCARD_OWNER . '</td>' .
            '  <td><input type="text" data-stripe="name" value="' . tep_output_string($order->billing['firstname'] . ' ' . $order->billing['lastname']) . '" /></td>' .
            '</tr>' .
            '<tr>' .
            '  <td width="30%">' . MODULE_PAYMENT_CHECKOUTAPIPAYMENT_CREDITCARD_NUMBER . '</td>' .
            '  <td><input type="text" maxlength="20" autocomplete="off" data-stripe="number" /></td>' .
            '</tr>' .
            '<tr>' .
            '  <td width="30%">' . MODULE_PAYMENT_CHECKOUTAPIPAYMENT_CREDITCARD_EXPIRY . '</td>' .
            '  <td>' . $months_string . ' / ' . $years_string . '</td>' .
            '</tr>';

            $content .= '<tr>' .
                '  <td width="30%">' . MODULE_PAYMENT_CHECKOUTAPIPAYMENT_CREDITCARD_CVC . '</td>' .
                '  <td><input type="text" size="5" maxlength="4" autocomplete="off" data-stripe="cvc" /></td>' .
                '</tr>';




        $content .= '</table>';





        $confirmation = array('title' => $content);

        return $confirmation;

    }

    public function pre_confirmation_check()
    {

    }
}