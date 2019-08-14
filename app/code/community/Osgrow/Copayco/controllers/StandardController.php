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

class Osgrow_Copayco_StandardController extends Mage_Core_Controller_Front_Action
{
    /*
     * copayco object
     */
    public $oApi;

    /**
     * refirect
     * first step
     */
    public function redirectAction()
    {
        $aPayData = Mage::getModel('copayco/standard')->getPayData();
        $oSes = Mage::getSingleton('checkout/session');

        $this->oApi = Mage::helper('copayco/api');
        $this->oApi->prepare($aPayData);

        $oSes = Mage::getSingleton('checkout/session');
        $oSes->setCopaycoQuoteId($oSes->getQuoteId());
        $this->getResponse()->setBody($this->getLayout()->createBlock('copayco/redirect')->toHtml());
    }//function redirectAction

    /*
     * confirmAction
     * main copayco api methods
     * Check
     * Payment Result
     */
    public function confirmAction()
    {
        $this->oApi = Mage::helper('copayco/api');
        $this->oApi->confirm();
    }//function confirmAction

    /*
     * successfulAction
     */
    public function successfulAction()
    {
        $nTaId = Mage::app()->getRequest()->getParam('ta_id');
        $oSes = Mage::getSingleton('checkout/session');
        $oOrder = Mage::getModel('sales/order')->loadByIncrementId($nTaId);
        //$oOrder = Mage::getModel('sales/order')->loadByIncrementId($oSes->getLastRealOrderId());
        $sComment = 'Payment Completed';

        if ($oOrder->getState() == 'processing' || $oOrder->getStatus() == 'processing'){
            $sTmpState = Mage_Sales_Model_Order::STATE_COMPLETE;
            $oOrder->setData('state', $sTmpState);
            $oOrder->setStatus($oOrder->getConfig()->getStateDefaultStatus($sTmpState));
            $sHistory = $oOrder->addStatusHistoryComment($sComment, false);
            $sHistory->setIsCustomerNotified(true);
            $oOrder->sendOrderUpdateEmail(true, $sComment);
            $oOrder->save();
            $oSes->setQuoteId($oSes->getCopaycoQuoteId());
            Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
            $this->_redirect('checkout/onepage/success');
            return;
        } else {
            $this->errorAction();
            return;
        }

    }//function successfulAction

    /*
     * errorAction
     * When customer canceled
     * or payment was not proceeded
     */
    public function errorAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getCopaycoQuoteId());
        if ($session->getLastRealOrderId()) {
            $oOrder = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
            if ($oOrder->getId()) {
                $oOrder->cancel();
                $oOrder->addStatusHistoryComment('payment Error', false);
                $oOrder->save();
            }
        }
        $quote = Mage::getModel('sales/quote')->load($session->getCopaycoQuoteId());
        if ($quote->getId()) {
            $quote->setActive(true);
            $quote->save();
        }
        $session->addError(Mage::helper('copayco')->__('Payment canceled! Pleas try again later.'));
        $session->unsQuoteId();
        $session->unsRedirectUrl();
        $this->_redirect('checkout/cart');


    }//function errorAction


}