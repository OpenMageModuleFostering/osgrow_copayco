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

$sApiUrl = Mage::getModuleDir('Helper', 'Osgrow_Copayco') . DS . 'Helper' .DS. 'copayco.php';
require_once $sApiUrl;

class Osgrow_Copayco_Helper_Api extends Mage_Core_Helper_Abstract{

    /*
     * Construct
     */
    public function __construct()
    {
        $this->oCop = copayco_api::instance();
    }// function __construct

    /*
     * prepare
     * setting main pay date
     * for transfering to copayco
     */
    public function prepare($aPayData)
    {
        $this->oCop->set_main_data($aPayData['ta_id'], $aPayData['amount'], $aPayData['currency'], $aPayData['shop_id']);
        $this->oCop->set_description($aPayData['description']);
        $this->oCop->set_purpose($aPayData['purpose']);
    }//function prepare

    /*
     * connfirm
     * curl connection to copayco server
     * 2 steps: check and getting real result
     * @return bool
     */
    public function confirm()
    {
        $oSes = Mage::getSingleton('core/session');

        try{
            $sTaId     = $this->oCop->get_ta_id();
            $oOrder    = Mage::getModel('sales/order')->loadByIncrementId($sTaId);
            $nAmount   = number_format($oOrder->getGrandTotal(), 2, '.', '');
            $nCurrency = $oOrder->getOrderCurrencyCode();
            //$nCurrency = 'UAH';


            ## Setting main pay data ##
            $this->oCop->set_main_data($sTaId, $nAmount, $nCurrency);
            $aReq         = $this->oCop->get_request_data();
            $sRequestType = $this->oCop->get_request_type();

            ## Getting result
            if ($sRequestType == 'check') {
                if ($this->oCop->check_data()) {
                    //in case of need may log result: check completed
                    $oOrder->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);
                    $oOrder->setStatus('pending_payment');
                    $oOrder->addStatusHistoryComment('Customer Started Payment', false);
                    $oOrder->save();
                } else {
                    //in case of need we log result: check failed
                }
            } else {
                ## payment is in RESERVED status ##
                if ($this->oCop->is_reserved()) {
                    //In next version will be the ability of reservation
                    $oOrder->setState(Mage_Sales_Model_Order::STATE_CANCELED);
                    $oOrder->setStatus('canceled');
                    $oOrder->addStatusHistoryComment("Payment is Reserved!!! Ask Copayco team for furher actions", false);
                    $oOrder->save();

                ## payment is in FINISHED status ##
                } elseif ($this->oCop->is_finished()) {
                    $oOrder->setState(Mage_Sales_Model_Order::STATE_PROCESSING);
                    $oOrder->setStatus('processing');
                    $oOrder->addStatusHistoryComment('Processing Payment', false);
                    $oOrder->save();
                ## payment is in CANCELED status ##
                } else {
                    $oOrder->setState(Mage_Sales_Model_Order::STATE_CANCELED);
                    $oOrder->setStatus('canceled');
                    $oOrder->addStatusHistoryComment('Payment Canceled', false);
                    $oOrder->save();

                }
            }
        } catch (copayco_exception $e) {
            ## error handling
            $nErrType  = $e->get_error_type_code();
            $nCode = $e->getCode();
            $this->oCop->set_error_message($e->getMessage() . ' ' . $nCode);
        }

        ## Showing result to copayco server
        if ($sRequestType == 'perform') {
            $this->oCop->output_perform_answer();
        } else {
            $this->oCop->output_check_answer();
        }
    }// function connfirm

    /*
     * getFields
     * Getting fields for post form
     * to preceed the payment
     * @return string
     */
    public function getFields()
    {
        $sFormFields = $this->oCop->get_form_fields(
        array(
            'ta_id' => array(
                'type'     => 'text',
                'readonly' => 'readonly',
            ),
            'currency' => array(
                'id' => 'currency',
            ),
        ),
        "\n"
        );
        return $sFormFields;
    }// function getFields


    /*
     * getSubmitUrl
     * getting url for payment form
     */
    public function getSubmitUrl()
    {
        return $this->oCop->get_submit_url();
    }//function getSubmitUrl

}