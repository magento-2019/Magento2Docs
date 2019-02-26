<?php
/**
 * Copyright © 2015 Ubertheme.com All rights reserved.
 */

namespace MageArray\Quickview\Block;

class Init extends \Magento\Framework\View\Element\Template
{

    protected $_helper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
		\MageArray\Quickview\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->_helper = $dataHelper;

        $this->_storeManager = $context->getStoreManager();
        parent::__construct($context);
    }

    public function getQuickViewOptions()
    {
        $itemClass = $this->_helper->getConfigValue('item_class');
        $btnContainer = $this->_helper->getConfigValue('btn_container');
        $options = [
            "baseUrl" => $this->getBaseUrl(),
            "btnLabel" => $this->_helper->getConfigValue('btn_label'),
            "itemClass" => (!empty($itemClass)) ? $itemClass : "",
            "btnContainer" => (!empty($btnContainer)) ? $btnContainer : ".product-item-info",
            "showPopupTitle" => (bool)$this->_helper->getConfigValue('show_title'),
            "popupTitle" => $this->_helper->getConfigValue('popup_title'),
            "currentText" => ($this
                ->_helper->getConfigValue('show_current_status')) ? __('Product {current} of {total}') : null,
            "showButtonGoToProduct" => $this->_helper->getConfigValue('show_button_go_detail'),
            "transition" => $this->_helper->getConfigValue('transition'),
            "speed" => $this->_helper->getConfigValue('transition_speed'),
            "initialWidth" => $this->_helper->getConfigValue('initial_width'),
            "initialHeight" => $this->_helper->getConfigValue('initial_height'),
            "additionClass" => $this->_helper->getConfigValue('addition_css_class')
        ];

        $jsonHelper = \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magento\Framework\Json\Helper\Data');

        return $jsonHelper->jsonEncode($options);
    }

    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }
}
