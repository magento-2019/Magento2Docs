<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace MageArray\Quickview\Controller\Cart;

use Magento\Catalog\Api\ProductRepositoryInterface as ProductRepository;
use Magento\Checkout\Model\Cart;

class Add extends \Magento\Checkout\Controller\Cart
{
    /**
     * @var ProductRepository $productRepo
     */
    protected $productRepo;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $ckSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param Cart $cart
     * @param ProductRepository $productRepo
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $ckSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        Cart $cart,
        ProductRepository $productRepo
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $ckSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->productRepo = $productRepo;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        $result = [];
        $result['status'] = false;

        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $result['messages'] = __('Invalid form key.');
        }

        $params = $this->getRequest()->getParams();
        try {
            $product = $this->_initializeProduct();
            if (!$product) {
                $result['messages'] = __('This product was not found.');
            }

            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->_objectManager
                        ->get('Magento\Framework\Locale\ResolverInterface')
                        ->getLocale()]
                );
                $params['qty'] = $filter->filter($params['qty']);
            }
            $this->cart->addProduct($product, $params);

            $relatedIds = $this->getRequest()
                ->getParam('related_product', null);
            if ($relatedIds) {
                $this->cart->addProductsByIds(explode(',', $relatedIds));
            }
            $this->cart->save();

            /**
             * @todo remove product from wishlist observer \Magento\Wishlist\Observer\AddToCart
             */
            $this->_eventManager->dispatch(
                'checkout_cart_add_product_complete',
                ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
            );

            //return result
            if ($product && !$product->getIsSalable()) {
                $result['messages'] = __('This product was out of stock');
            } else {
                $result['status'] = true;
                $result['messages'] = __('You added %1 to your shopping cart.', $product->getName());
                $result['cartUrl'] = $this->_objectManager
                    ->get('Magento\Checkout\Helper\Cart')->getCartUrl();
            }

        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->_checkoutSession->getUseNotice(true)) {
                $result['messages'] = $this->_objectManager
                    ->get('Magento\Framework\Escaper')
                    ->escapeHtml($e->getMessage());
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                $msgText = [];
                foreach ($messages as $message) {
                    $msgText[] = $this->_objectManager->
                    get('Magento\Framework\Escaper')->escapeHtml($message);
                }
                $result['messages'] = implode('<br/>', $msgText);
            }

        } catch (\Exception $e) {
            //log
            $this->messageManager->addException($e, __('We can\'t add this item to your shopping cart right now.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $result['messages'] = $e->getMessage();
        }

        //return result
        return $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
        );
    }

    /**
     * @return bool|\Magento\Catalog\Api\Data\ProductInterface
     */
    private function _initializeProduct()
    {
        $pId = (int)$this->getRequest()->getParam('product', false);
        if ($pId) {
            $storeId = $this->_objectManager->
            get('Magento\Store\Model\StoreManagerInterface')
                ->getStore()->getId();
            try {
                return $this->productRepo->getById($pId, false, $storeId);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                return false;
            }
        }

        return false;
    }
}
