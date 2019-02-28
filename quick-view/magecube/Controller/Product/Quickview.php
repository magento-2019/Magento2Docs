<?php

/**
 * MageCubeTeam
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageCubeTeam.com license that is
 * available through the world-wide-web at this URL:
 * https://www.magecube.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    MageCubeTeam
 * @package     MageCubeTeam_ProductQuickView
 * @copyright   Copyright (c) 2018 MageCubeTeam (http://www.magecube.com/)
 * @license     https://www.magecube.com/LICENSE.txt
 */

namespace MageCubeTeam\ProductQuickView\Controller\Product;

use MageCubeTeam\ProductQuickView\Controller\Product as ProductController;
use Magento\Framework\Controller\ResultFactory;

class Quickview extends ProductController {

    public function execute() {
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        $resultLayout->addHandle('catalog_product_view_type_' . $this->initProduct()->getTypeId());
        return $resultLayout;
    }

}
