<?php

namespace Apexx\Base\Model\Payment\MappingPayment;

use Magento\Sales\Model\Order;
use Signifyd\Connect\Model\Payment\Base\ExpMonthMapper as Base_ExpMonthMapper;

class ExpMonthMapper extends Base_ExpMonthMapper
{
    /**
     * @param Order $order
     * @return string
     */
    public function getPaymentData(Order $order)
    {
        $monthResponse = $order->getPayment()->getCcExpMonth();
        $expMonth = null;

        if ($this->isExpMonth($monthResponse)) {
            $expMonth = $monthResponse;
        }

        if (empty($expMonth)) {
            $expMonth = parent::getPaymentData($order);
        }

        return $expMonth;
    }

    /**
     * @param array $monthResponse
     * @return bool
     */
    protected function isExpMonth($monthResponse)
    {
        if (empty($monthResponse)) {
            return false;
        }
        return true;
    }
}