<?php

namespace Apexx\Base\Model\Payment\MappingPayment;

use Magento\Sales\Model\Order;
use Signifyd\Connect\Model\Payment\Base\AvsEmsCodeMapper as Base_AvsEmsCodeMapper;

class AvsEmsCodeMapper extends Base_AvsEmsCodeMapper
{
    /**
     * @param Order $order
     * @return string
     */
    public function getPaymentData(Order $order)
    {
        $avsResponse = $order->getPayment()->getAvsResponse();
        $avsStatus = null;

        if ($this->isAvsStatus($avsResponse)) {
            $avsStatus = $avsResponse;
        }

        if (empty($avsStatus)) {
            $avsStatus = parent::getPaymentData($order);
        }

        return $avsStatus;
    }

     /**
     * @param array $additionalInfo
     * @return bool
     */
    protected function isAvsStatus($avsResponse)
    {
        if (empty($avsResponse)) {
            return false;
        }
        return true;
    }
}
