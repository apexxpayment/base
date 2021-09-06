<?php

namespace Apexx\Base\Model\Payment\MappingPayment;
use Magento\Sales\Model\Order;
use Signifyd\Connect\Model\Payment\Base\Last4Mapper as Base_Last4Mapper;

class Last4Mapper extends Base_Last4Mapper
{
    /**
     * @param Order $order
     * @return string
     */
    public function getPaymentData(Order $order)
    {
        $ccLast4 = $order->getPayment()->getCcLast4();
        $ccLast = null;

        if ($this->isCCLast($ccLast4)) {
            $ccLast = $ccLast4;
        }

        if (empty($ccLast)) {
            $ccLast = parent::getPaymentData($order);
        }

        return $ccLast;
    }

     /**
     * @param array $additionalInfo
     * @return bool
     */
    protected function isCCLast($ccLast4)
    {
        if (empty($ccLast4)) {
            return false;
        }
        return true;
    }
}
