<?php
 
namespace App\Services\Midtrans;

use Midtrans\Transaction;

class TransactionStatus extends Midtrans
{
    protected $orderId;
    public function __construct($orderId)
    {
        parent::__construct();
        $this->orderId = $orderId;
    }

    public function status ()
    {
        $status = Transaction::status($this->orderId);
        return $status;
    }

    public function approve ()
    {
        $approve = Transaction::approve($this->orderId);
        return $approve;
    }

    public function cancel ()
    {
        $cancel = Transaction::cancel($this->orderId);
        return $cancel;
    }

    public function expire ()
    {
        $expire = Transaction::expire($this->orderId);
        return $expire;
    }
}