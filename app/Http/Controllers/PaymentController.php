<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Payment;
use Xendit\Configuration;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\Invoice;

class PaymentController extends Controller
{
    var $apiInstance = null;

    public function __construct() {
        Configuration::setXenditKey(env("XENDIT_DEV_APIKEY"));
        $this->apiInstance = new InvoiceApi();
    }

    public function create(Request $request) {
        $createInvoiceRequest = new CreateInvoiceRequest([
            'external_id' => (string) Str::uuid(),
            'pater_email' => $request->payer_email,
            'description' => $request->description,
            'amount' => $request->amount,
            'redirect_url' => 'testing-redirect-url.com'
        ]);

        $createInvoice = $this->apiInstance->createInvoice($createInvoiceRequest);

        // Save to database
        $payment = new Payment;
        $payment->status = 'pending';
        $payment->checkout_link = $createInvoice['invoice_url'];
        $payment->external_id = $createInvoiceRequest['external_id'];
        $payment->save();

        // return response()->json($payment);
        return response()->json(['data' => $createInvoice['invoice_url']]);
    }

    // Webhook
    public function notification(Request $request) {
        $result = $this->apiInstance->getInvoices(null, $request->external_id);

        // Get Data
        $payment = Payment::where('external_id', $request->external_id)->firstOrFail();

        if($payment->status == 'settled') {
            return response()->json('Payment anda telah diproses');
        }

        // Update status
        $payment->status = strtolower($result[0]['status']);
        $payment->save();

        return response()->json('Success');
    }

    public function webhook(Request $request) {
        $invoiceInstance = new InvoiceApi();
        $getInvoice = $invoiceInstance->getInvoiceById($request->id, null);

        // Get data
        $payment = Payment::where('external_id', $request->external_id)->firstOrFail();

        if ($payment->status == 'setted') {
            return response()->json(['data' => 'Payment has been already processed']);
        }

        // Update status payment
        $payment->status = strtolower($getInvoice('status'));
        $payment->save();

        return response()->json(['data' => 'Success']);
    }

    
}
