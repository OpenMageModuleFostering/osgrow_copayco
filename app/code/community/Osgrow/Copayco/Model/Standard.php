<?php
/**
 * Copayco Payment extension for Magento by Osgrow
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the Osgrow Copayco module to newer versions in the future.
 * If you wish to customize the Osgrow Copayco module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Osgrow
 * @package    Osgrow_Copayco
 * @copyright  Copyright (C) 2015 Osgrow (http://osgrow.net/)
 * @author     Dmytro Borodulin
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Osgrow_Copayco_Model_Standard extends Mage_Payment_Model_Method_Abstract {

    /*
     * internal payment method identifier
     */
    protected $_code = 'copayco_standard';

    /*
    * $_canUseForMultishipping
    */
    protected $_canUseForMultishipping = false;

    /*
     * $_canUseInternal
     */
    protected $_canUseInternal = false;

    /*
     * $_isInitializeNeeded
     */
    protected $_isInitializeNeeded = true;

    /*
     * Instantiate state and set it to state object
     */
    public function initialize($paymentAction, $stateObject)
    {
        $stateObject->setState(Mage_Sales_Model_Order::STATE_NEW);
        $stateObject->setStatus('new');
        $stateObject->setIsNotified(false);
        $stateObject->save();

    }//function initialize

    /*
     * getOrderPlaceRedirectUrl
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('copayco/standard/redirect', array('_secure' => true));
    }//function getOrderPlaceRedirectUrl

    /*
     * getPayData
     * for pay form
     * @return array
     */
    public function getPayData()
    {
        $oSes = Mage::getSingleton('checkout/session');
        $oOrder = Mage::getModel('sales/order')->loadByIncrementId($oSes->getLastRealOrderId());
        $sStore = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        $aForm = array(
            'test_mode'   => $this->getConfigData('test_mode'),
            'shop_id'     => $this->getConfigData('shop_id'),
            'sign_key'    => $this->getConfigData('sign_key'),
            'ta_id'        => $oOrder->getIncrementId(),
            'amount'      => number_format($oOrder->getGrandTotal(), 2, '.', ''),
            'currency'    => $oOrder->getOrderCurrencyCode(),
            //'currency'    => 'UAH',
            'description' => $sStore . ': ' .  $oOrder->getCustomerFirstname(). ' ' . $oOrder->getCustomerLastname() . ', ' . $oOrder->getIncrementId(),
            'purpose'     => $oOrder->getIncrementId(),
        );
        return $aForm;
    }//function getPayData


}

