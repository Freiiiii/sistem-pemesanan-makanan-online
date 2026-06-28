<?php

require_once 'PaymentInterface.php';

class Payment implements PaymentInterface
{
    private $orderId;
    private $amount;
    private $paymentMethod;

    public function __construct($orderId, $amount, $paymentMethod)
    {
        $this->orderId = $orderId;
        $this->amount = $amount;
        $this->paymentMethod = $paymentMethod;
    }

    public function processPayment()
    {
        return createPayment(
            $this->orderId,
            $this->amount,
            $this->paymentMethod
        );
    }

    public function completePayment()
    {
        return updatePaymentStatus($this->orderId, 'paid');
    }

    public function getOrderId()
    {
        return $this->orderId;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }
}