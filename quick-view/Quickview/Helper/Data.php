<?php


namespace MageArray\Quickview\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;


class Data extends AbstractHelper
{
    protected $_context;

    protected $_storeManager;

    protected $_appConfig;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ReinitableConfigInterface $config
    ) {
        $this->_context = $context;
        $this->_storeManager = $storeManager;
        $this->_appConfig = $config;

        parent::__construct($context);
    }

    public function getConfigValue($key = null, $data = [])
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();

        $manager = $om->get('\Magento\Framework\App\ScopeResolverInterface');
        $scopeCode = $manager->getScope()->getCode();

        $currentStoreCode = $this->_storeManager->getStore()->getCode();
        $currentWebsiteCode = $this->_storeManager->getWebsite()->getCode();

        if ($scopeCode == $currentStoreCode) {
            $scope = ScopeInterface::SCOPE_STORES;
        } elseif ($scopeCode == $currentWebsiteCode) {
            $scope = ScopeInterface::SCOPE_WEBSITES;
        } else {
            $scope = 'default';
            //$scopeId = 0;
            $scopeCode = '';
        }

        $sections = ['quickview'];
        $value = null;
        if (isset($data[$key])) {
            $value = $data[$key];
        } else {
            foreach ($sections as $section) {
                $groups = $this->_appConfig
                    ->getValue($section, $scope, $scopeCode);

                if ($groups) {
                    foreach ($groups as $configs) {
                        if (isset($configs[$key])) {
                            $value = $configs[$key];
                            break;
                        }
                    }
                }
                if ($value) {
                    break;
                }
            }
        }

        return $value;
    }

    /**
     * @return mixed
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * @param $productId
     * @return string
     */
    public function getQuickViewUrl($productId)
    {
        return $this->getBaseUrl() . 'quickview/index/view/id/' . $productId;
    }

}
