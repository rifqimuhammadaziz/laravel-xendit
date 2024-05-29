<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Xendit\Configuration;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\InvoiceApi;
use Xendit\XenditSdkException;

class HomeController extends Controller
{
    public function __construct()
    {
        Configuration::setXenditKey(env("XENDIT_DEV_APIKEY"));
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $products = Product::all();

        return view('welcome', array('products' => $products));
    }

    public function detail($id) {
        $product = Product::find($id);

        return view('detail-product', $product);
    }

    public function payment(Request $request) {
        // Check product
        $product = Product::find($request->id);

        // Generate UUID
        $uuid = (string) Str::uuid();

        // Call API Xendit
        $apiInstance = new InvoiceApi();
        $createInvoiceRequest = new CreateInvoiceRequest([
            'external_id' => $uuid,
            'description' => $product->description,
            'amount' => $product->price,
            'currency' => 'IDR',
            'customer' => array(
                'given_names' => 'John',
                'email' => 'johndoe@example.com'
            ),
            'success_redirect_url' => 'http://localhost:8000',
            'failure_redirect_url' => 'http://localhost:8000'
        ]);

        try {
            $result = $apiInstance->createInvoice($createInvoiceRequest);

            // Insert to table Order
            $order = new Order();
            $order->product_id = $product->id;
            $order->checkout_link = $result['invoice_url'];
            $order->external_id = $uuid;
            $order->status = "pending";
            $order->save();

            return redirect($result['invoice_url']);
        } catch (XenditSdkException $e) {

        }
    }

    public function notification($id) {
        $apiInstance = new InvoiceApi();

        $result = $apiInstance->getInvoices(null, $id);

        // Get Order
        $order = Order::where('external_id', $id)->firstOrFail();

        if ($order->status == 'SETTLED') {
            return response()->json('Payment Anda telah berhasil diproses');
        }

        // Update Status
        $order->status = $result[0]['status'];
        $order->save();

        return response()->json($order->status);
    }
}
