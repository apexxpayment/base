<?php

namespace Apexx\Base\Model\Payment\MappingPayment;
use Magento\Sales\Model\Order;
use Signifyd\Connect\Model\Payment\Base\ExpYearMapper as Base_ExpYearMapper;

class ExpYearMapper extends Base_ExpYearMapper
{
    /**
     * @param Order $order
     * @return string
     */
    public function getPaymentData(Order $order)
    {
        $yearResponse = $order->getPayment()->getCcExpYear();
        $expYear = null;

        if ($this->isExpYear($yearResponse)) {
            $expYear = $yearResponse;
        }

        if (empty($expYear)) {
            $expYear = parent::getPaymentData($order);
        }

        return $expYear;
    }

    /**
     * @param array $yearResponse
     * @return bool
     */
    protected function isExpYear($yearResponse)
    {
        if (empty($yearResponse)) {
            return false;
        }
        return true;
    }
}
