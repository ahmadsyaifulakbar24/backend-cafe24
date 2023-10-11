<?php
 
namespace App\Services\Midtrans;
 
use App\Models\Payment;
use App\Services\Midtrans\Midtrans;
use Midtrans\Notification;
 
class CallbackService extends Midtrans
{
    protected $notification;
    protected $order;
    protected $serverKey;
 
    public function __construct()
    {
        parent::__construct();
 
        $this->serverKey = config('midtrans.server_key');
        $this->_handleNotification();
    }
 
    public function isSignatureKeyVerified()
    {
        return ($this->_createLocalSignatureKey() == $this->notification->signature_key);
    }
 
    public function isSuccess()
    {
        $statusCode = $this->notification->status_code;
        $transactionStatus = $this->notification->transaction_status;
        $fraudStatus = !empty($this->notification->fraud_status) ? ($this->notification->fraud_status == 'accept') : true;
 
        return ($statusCode == 200 && $fraudStatus && ($transactionStatus == 'capture' || $transactionStatus == 'settlement'));
    }
 
    public function isExpire()
    {
        return ($this->notification->transaction_status == 'expire');
    }
 
    public function isCancelled()
    {
        return ($this->notification->transaction_status == 'cancel');
    }
 
    public function getNotification()
    {
        return $this->notification;
    }
 
    public function getOrder()
    {
        return $this->order;
    }
 
    protected function _createLocalSignatureKey()
    {
        $orderId = $this->order->id;
        $statusCode = $this->notification->status_code;
        $grossAmount = $this->order->total;
        $serverKey = $this->serverKey;
        $input = $orderId . $statusCode . $grossAmount . $serverKey;
        $signature = hash("sha512", $input);
 
        return $signature;
    }
 
    protected function _handleNotification()
    {
        $notification = new Notification();
 
        $order_id = $notification->order_id;
        $order = Payment::find($order_id);
 
        $this->notification = $notification;
        $this->order = $order;
    }
}