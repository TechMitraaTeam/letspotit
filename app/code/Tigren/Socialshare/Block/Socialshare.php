<?php

/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */

namespace Tigren\Socialshare\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;

class Socialshare extends \Magento\Framework\View\Element\Template {

    public $_coreRegistry;
    public $_scopeConfig;
    protected $_urlInterface;
    public $_priceHelper;
    public $_product;
    public $_category;
    public $_cmsPageFactory;

    public function __construct(
        Context $context, 
        Registry $coreRegistry,
        \Magento\Framework\UrlInterface $urlInterface, 
        \Magento\Cms\Model\PageFactory $cmsPageFactory,
        PricingHelper $pricingHelper, 
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_coreRegistry = $coreRegistry;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_urlInterface = $urlInterface;
        $this->_priceHelper = $pricingHelper;
        $this->_product = $this->_coreRegistry->registry('current_product');
        $this->_category = $this->_coreRegistry->registry('current_category');
        $this->_cmsPageFactory = $cmsPageFactory;
    }
    
    public function getPageType() {
        if ($this->_product && $this->_product->getId()) {
            return 'product';
        } else if ( $this->_category && $this->_category->getId()) {
            return 'category';
        } else {
            return 'cms';
        }
    }
    
    public function getPageUrl() {
        $pageType = $this->getPageType();
        if ($pageType == 'product') {
            return $this->_product->getProductUrl();
        } else if ($pageType == 'category') {
            return $this->_category->getUrl();
        } else {
            return $this->_urlInterface->getCurrentUrl();
        }
    }
    
    public function getPageName() {
        $pageType = $this->getPageType();
        if ($pageType == 'product') {
            return $this->_product->getName();
        } else if ($pageType == 'category') {
            return $this->_category->getName();
        } else {
            $cmsPage = $this->getCmsPage();
            if ($cmsPage) {
                return $cmsPage->getTitle();
            }
            return 'Cms Page';
        }
    }
    
    public function getCmsPage() {
        $pageId = $this->getRequest()->getParam('page_id');
        if ($pageId) {
            return $this->_cmsPageFactory->create()->load($pageId);
        }
        return null;
    }

    public function getFacebookButton() {
        $facebookID = $this->getScopeConfig('socialshare/facebook/fb_id');
        $displayLike = $this->getScopeConfig('socialshare/facebook/display_onlylike');
        $displayFbCount = $this->getScopeConfig('socialshare/facebook/display_facebook_count');
        $facebookID = ($facebookID != "") ? $facebookID : '1082368948492595';
        $like_button = ($displayLike == 1) ? false : true;
        $count_button = ($displayFbCount == 1) ? 'button_count' : 'button';

        return '
            <div class="facebook_button social-button">
                <div id="fb-root"></div>
                <script>
                    (function(d, s, id) {
                        var js, fjs = d.getElementsByTagName(s)[0];
                        if (d.getElementById(id)) return;
                        js = d.createElement(s); js.id = id;
                        js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.5&appId=' . $facebookID . '";
                        fjs.parentNode.insertBefore(js, fjs);
                    }(document, \'script\', \'facebook-jssdk\'));
                </script>
                <div class="fb-like" data-layout="' . $count_button . '" data-width="400" data-show-faces="false"  data-href="' . $this->getPageUrl() . '"  data-send="' . $like_button . '"></div>
            </div>';
    }

    public function getTwitterButton() {
        return "
            <div class='twitter_button social-button'>
                <a href='https://twitter.com/share' class='twitter-share-button' data-url='" . $this->getPageUrl() . "' >Tweet</a>
                <script>
                    !function(d,s,id){
                        var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';
                        if(!d.getElementById(id)){
                            js=d.createElement(s);
                            js.id=id;
                            js.src=p+'://platform.twitter.com/widgets.js';
                            fjs.parentNode.insertBefore(js,fjs);
                        }
                    }(document, 'script', 'twitter-wjs');
                </script>
            </div>";
    }

    public function getPinItButton() {
        $count_button = $this->getScopeConfig('socialshare/pinitsharing/display_pinit_count');
        $count_button = ($count_button == 1) ? 'beside' : 'none';

        return '
            <div class="pinit_button social-button">
                <a href="//www.pinterest.com/pin/create/button/?url=' . urlencode($this->getPageUrl()) . '&description=' . urlencode($this->getPageName()) . '" data-pin-do="buttonPin" data-pin-color="red" data-pin-config="' . $count_button . '"  data-pin-height="20">pinit</a>
            </div>
            <script type="text/javascript" async defer src="//assets.pinterest.com/js/pinit.js"></script>';
    }

    public function getGooglePlusButton() {
        $count_button = $this->getScopeConfig('socialshare/googleplus/display_google_count');
        $count_button = ($count_button == 1) ? 'bubble' : 'none';
        return '
            <div class="google_button social-button">
                <div class="g-plusone" data-size="medium"  data-annotation="' . $count_button . '"></div>
            </div>
            <script src="https://apis.google.com/js/platform.js" async defer></script>';
    }

    public function getScopeConfig($path) {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

}