<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */

namespace MageArray\Quickview\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class View extends \Magento\Catalog\Controller\Product
{
    /**
     * @var \Magento\Catalog\Helper\Product\View
     */
    protected $_viewHelper;
    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $_resultForwardFactory;
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

        public function __construct(
        Context $context,
        \Magento\Catalog\Helper\Product\View $_viewHelper,
        \Magento\Framework\Controller\Result\ForwardFactory $_resultForwardFactory,
        PageFactory $_resultPageFactory
    ) {
        $this->_viewHelper = $_viewHelper;
        $this->_resultForwardFactory = $_resultForwardFactory;
        $this->_resultPageFactory = $_resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page|void
     */
    public function execute()
    {
        $pId = (int)$this->getRequest()->getParam('id', false);
        $catId = (int)$this->getRequest()->getParam('category', false);
        $options = $this->getRequest()->getParam('options', false);

        //prepare params
        $params = new \Magento\Framework\DataObject();
        $params->setCategoryId($catId);
        $params->setSpecifyOptions($options);

        //render result
        try {

            $page = $this->_resultPageFactory->create(false, ['template' => 'MageArray_Quickview::default.phtml']);
            $this->_viewHelper->prepareAndRender($page, $pId, $this, $params);
            return $page;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->_returnJSON(['message' => $e->getMessage()]);
            return;
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $this->_returnJSON(['message' => $e->getMessage()]);
            return;
        }
        /* } else {
            $this->_returnJSON(['message' => __('Bad request.')]);
            return;
        } */
    }

    /**
     * @param $result
     */
    private function _returnJSON($result)
    {
        $this->getResponse()->representJson(
            $this->_objectManager->
            get('Magento\Framework\Json\Helper\Data')->jsonEncode([
                'backUrl' => $this->_redirect->getRedirectUrl(),
                'message' => $result['message']
            ])
        );
        return;
    }

}
