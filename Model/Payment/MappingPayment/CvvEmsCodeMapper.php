<?php

namespace Apexx\Base\Model\Payment\MappingPayment;

use Magento\Sales\Model\Order;
use Signifyd\Connect\Model\Payment\Base\CvvEmsCodeMapper as Base_CvvEmsCodeMapper;

class CvvEmsCodeMapper extends Base_CvvEmsCodeMapper
{
    /**
     * @param Order $order
     * @return string
     */
    public function getPaymentData(Order $order)
    {
        $cvvResponse = $order->getPayment()->getCvvResponse();
        $cvvStatus = null;

        if ($this->isCvvStatus($cvvResponse)) {
            $cvvStatus = $cvvResponse;
        }

        if (empty($cvvStatus)) {
            $cvvStatus = parent::getPaymentData($order);
        }

        return $cvvStatus;
    }

     /**
     * @param array $additionalInfo
     * @return bool
     */
    protected function isCvvStatus($cvvResponse)
    {
        if (empty($cvvResponse)) {
            return false;
        }
        return true;
    }
}
