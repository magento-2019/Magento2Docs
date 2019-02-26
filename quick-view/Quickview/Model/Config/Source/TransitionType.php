<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace MageArray\Quickview\Model\Config\Source;

class TransitionType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'elastic', 'label' => __('Elastic')],
            ['value' => 'fade', 'label' => __('Fade')],
            ['value' => 'none', 'label' => __('None')]
        ];
    }
}
