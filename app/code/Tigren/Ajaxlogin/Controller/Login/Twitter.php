<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxlogin\Controller\Login;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Tigren\Ajaxlogin\Helper\TwitterOAuth\TwitterOAuth;

class Twitter extends Action
{
    protected $_ajaxLoginHelper;
    protected $jsonHelper;

    public function __construct(Context $context, \Tigren\Ajaxlogin\Helper\Data $ajaxLoginHelper, JsonHelper $jsonHelper)
    {
        $this->jsonHelper = $jsonHelper;
        $this->_ajaxLoginHelper = $ajaxLoginHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = [];
        $consumerKey = $this->_ajaxLoginHelper->getTwitterConsumerKey();
        $consumerSecret = $this->_ajaxLoginHelper->getTwitterConsumerSecret();
        $callbackUrl = $this->_ajaxLoginHelper->getTwitterCallbackUrl();

        // create TwitterOAuth object
        $twitteroauth = new TwitterOAuth($consumerKey, $consumerSecret);

        // request token of application
        $request_token = $twitteroauth->oauth(
            'oauth/request_token', [
                'oauth_callback' => $callbackUrl
            ]
        );

        // throw exception if something gone wrong
        if ($twitteroauth->getLastHttpCode() != 200) {
            $result['error'] = __('There was a problem performing this request');
        }

        // save token of application to session
        $_SESSION['oauth_token'] = $request_token['oauth_token'];
        $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

        // generate the URL to make request to authorize our application
        $url = $twitteroauth->url(
            'oauth/authorize', [
                'oauth_token' => $request_token['oauth_token']
            ]
        );

        $result['success'] = true;
        $result['url'] = $url;

        return $this->getResponse()->representJson($this->jsonHelper->jsonEncode($result));
    }
}