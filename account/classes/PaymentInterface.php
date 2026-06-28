<?php

interface PaymentInterface
{
    public function processPayment();

    public function completePayment();
}