<?php
/**
 * Custom payment method in Magento 2
 * @category    Base
 * @package     Apexx_Base
 */
namespace Apexx\Base\Model\Adminhtml\Source;

/**
 * Class ApiType
 * @package Apexx\Base\Model\Adminhtml\Source
 */
class ApiType
{
    /**
     * Different api type.
     */
    const ACTION_AX2 = 'AX2';
    const ACTION_ATOMIC = 'Atomic';

    public function toOptionArray()
    {
        return [
                    [
                        'value' => self::ACTION_ATOMIC,
                        'label' => __('Atomic')
                    ],
                     [
                         'value' => self::ACTION_AX2,
                         'label' => __('AX2')
                     ],
        ];
    }
}
