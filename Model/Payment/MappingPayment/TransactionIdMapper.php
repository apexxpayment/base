<?php

namespace Apexx\Base\Model\Payment\MappingPayment;
use Magento\Sales\Model\Order;
use Signifyd\Connect\Model\Payment\Base\TransactionIdMapper as Base_TransactionIdMapper;

class TransactionIdMapper extends Base_TransactionIdMapper
{

    /**
     * @param Order $order
     * @return string
     */
    public function getPaymentData(Order $order)
    {
        $LastTransIdResponse = $order->getPayment()->getLastTransId();
        $LastTransId = null;

        if ($this->isLastTransId($LastTransIdResponse)) {
            $LastTransId = $LastTransIdResponse;
        }

        if (empty($LastTransId)) {
            $LastTransId = parent::getPaymentData($order);
        }

        return $LastTransId;
    }

     /**
     * @param array $additionalInfo
     * @return bool
     */
    protected function isLastTransId($LastTransIdResponse)
    {
        if (empty($LastTransIdResponse)) {
            return false;
        }
        return true;
    }
}
