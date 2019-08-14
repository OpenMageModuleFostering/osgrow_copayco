<?php
/**
 * copayco config data
 * the same as in provided API
 */
$aPayData = Mage::getModel('copayco/standard')->getPayData();


return array (
    'test_mode'   => $aPayData['test_mode'], //At the end of the test change the value to false
    'shop_id'     => $aPayData['shop_id'],   // getting from copayco account
    'sign_key'    => $aPayData['sign_key'],  // getting from copayco account
    'notify_mode' => 'curl',                 // curl only
    //'use_rand'    => 2,
    //'charset'     => 'windows-1251', // 'windows-1251', 'koi8-r', 'koi8-u'

);
?>
