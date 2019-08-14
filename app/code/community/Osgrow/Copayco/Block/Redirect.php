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
class Osgrow_Copayco_Block_Redirect extends Mage_Core_Block_Abstract{

    /*
     * _toHtml
     * @return string
     */
    protected function _toHtml()
    {
        $oApi       = Mage::helper('copayco/api');
        $sFields    = str_replace("text", "hidden", $oApi->getFields());
        $sSubmitUrl = $oApi->getSubmitUrl();


        ## Html string
        $html = '<html><body>';
        $html.= $this->__('Copayco Payment.');
        $html.="<form action=$sSubmitUrl id='copayco_pay_form' method='POST'>";
        $html.= $sFields;
        $html.=  '<input type="submit" value="' . $this->__("If not redirected, click here.") . '">';
        $html.= '</form>';
        $html.= '<script type="text/javascript">document.getElementById("copayco_pay_form").submit();</script>';
        $html.='</body></html>';
        return $html;
    } //function _toHtml

}