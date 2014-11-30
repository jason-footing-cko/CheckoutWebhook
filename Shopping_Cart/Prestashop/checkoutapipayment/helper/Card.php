<?php
 class helper_Card extends PaymentModule
{
    public static  function getCardType($self)
    {
        $arrayCardType = array('visa','mastercard','amex','discover','diners','unionpay');
        $arrayCard = array();
        foreach($arrayCardType as $card) {
            $arrayCard[] = array( 'id'        =>    'CHECKOUTAPI_CARD_TYPE_'.strtoupper($card),
                'label'     =>    $self->l($card),
                'path'      =>    "{$self->_path}skin/img/card/$card.gif",
                'selected'  =>    Configuration::get('CHECKOUTAPI_CARD_TYPE_'.strtoupper($card))
            );
        }

        return $arrayCard;
    }

     public static  function getExMonth()
     {
         $monthArray = array();
         for ($month=1; $month<=12; $month++) {
             $monthArray[$month] = date('F', mktime(0,0,0,$month, 1, date('Y')));

         }
         return $monthArray;
     }

     public static  function getExYear()
     {
         $yearArray = array();
         $yearNow = date('Y',time());

         for ($year = $yearNow,$nextRange =$yearNow+10; $year<=$nextRange; $year++) {
             $yearArray[] = $year;

         }
         return $yearArray;
     }
}