<?php
 // Reversal - "918" - "CR"
 $dc_918 = [
    'transaction_code',
    'result',
    'give_x_transaction_reference',
    'certificate_balance',
    'certificate_expiration_date',
    'receipt_message',
    'iso_serial',
    'program_specific_data',
    'comments,'
 ];

 // Forced Pre-Auth - "920"
 $dc_920 = [
   'transaction_code',
   'result',
   'givex_pre_auth_reference',
   'authorized_amount',
   'certificate_balance',
   'certificate_expiration_date',
   'receipt_message_optional',
   'iso_serial',
   'comments_optional',
 ];

 // Pre Auth Increment - "1018" - "IP"
 $dc_921 = [
   'transaction_code',
   'result',
   'givex_transaction',
   'amount_redeemed',
   'certificate_balance',
   'certificate_expiration_date',
   'receipt_message',
   'iso_serial',
   'comments',
 ];

 // Secure Balance - "994" - "SB"
  $dc_994 = [
    'transaction_code',
    'result',
    'certificate_balance_or_error_message',
    'points_balance', // Assuming this corresponds to 'Certificate Balance'
    'certificate_expiration_date',
    'currency_code',
    'member_name',
    'receipt_message',
    'loyalty_balance',
    'member_tier',
    'iso_serial',
    'balance_as_of_effective_date_or_psd',
    'effective_date_or_psd_indicator',
    'activating_currency_code',
    'operator_message_and_government_id',
    'locked_amount',
    'card_image_url',
    'pre_auth_balance',
    'order_type',
    'program_name',
  ];


  $dc_995 = [
    'transaction_code',
    'result',
    'cert_balance_or_error_message',
    'currency',
    'points_balance',
    'trans_hist',
    'total_rows',
    'iso_serial',
    'certificate_expiration_date',
    'operator_message',
  ];

 define('dc_918', $dc_918);
 define('dc_920', $dc_920);
 define('dc_921', $dc_921);
 define('dc_994', $dc_994);
 define('dc_995', $dc_995);


 function get_data($data_array, $method) {
    $attributes = constant($method);

    if($attributes) {
        $data_dict = [];

        for ($i = 0; $i < count($attributes); $i++) {
            $data_dict[$attributes[$i]] = $data_array[$i];
        }
    
        $json_object = json_encode($data_dict);
    
        return json_decode($json_object, true);
    }

    return null;
}

?>