<?php

namespace Apexx\Base\Model\Payment\MappingPayment;

use Magento\Sales\Model\Order;
use Signifyd\Connect\Model\Payment\Base\BinMapper as BaseBinMapper;

/**
 * Class BinMapper
 * @package Mido\Apexx\Model\Payment\HostedPayment
 */
class BinMapper extends BaseBinMapper
{
    /**
     * @param Order $order
     * @return string
     */
    public function getPaymentData(Order $order)
    {
        $binResponse = $order->getPayment()->getBin();
        $bin = null;

        if ($this->isBin($binResponse)) {
            $bin = substr($binResponse, 0, 6);
        }

        if (empty($bin)) {
            $bin = parent::getPaymentData($order);
        }

        return $bin;
    }

     /**
     * @param array $additionalInfo
     * @return bool
     */
    protected function isBin($binResponse)
    {
        if (empty($binResponse)) {
            return false;
        }
        return true;
    }
}