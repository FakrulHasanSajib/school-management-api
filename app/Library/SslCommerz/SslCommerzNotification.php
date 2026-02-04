<?php

namespace App\Library\SslCommerz;

use Illuminate\Support\Facades\Http;

class SslCommerzNotification
{
    protected $apiUrl;
    protected $storeId;
    protected $storePassword;

    public function __construct()
    {
        $this->storeId = env("SSLC_STORE_ID");
        $this->storePassword = env("SSLC_STORE_PASSWORD");

        // স্যান্ডবক্স মোড চেক
        if (env("SSLC_SANDBOX_MODE")) {
            $this->apiUrl = "https://sandbox.sslcommerz.com";
        } else {
            $this->apiUrl = "https://securepay.sslcommerz.com";
        }
    }

    // ১. পেমেন্ট লিংক তৈরি করা
    public function makePayment($requestData, $type = 'checkout', $pattern = 'json')
    {
        // ক্রেডেনশিয়াল যুক্ত করা
        $requestData['store_id'] = $this->storeId;
        $requestData['store_passwd'] = $this->storePassword;

        // API কল করা
        $response = Http::asForm()->post($this->apiUrl . "/gwprocess/v4/api.php", $requestData);
        $sslcz = json_decode($response->body(), true);

        if (isset($sslcz['GatewayPageURL']) && $sslcz['GatewayPageURL'] != "") {
            return [
                'status' => 'success',
                'data' => $sslcz['GatewayPageURL'],
                'GatewayPageURL' => $sslcz['GatewayPageURL']
            ];
        } else {
            return [
                'status' => 'fail',
                'data' => $sslcz['failedreason'] ?? 'Payment Initialization Failed'
            ];
        }
    }

    // ২. পেমেন্ট ভ্যালিডেট করা (সাকসেস হওয়ার পর)
    public function orderValidate($post_data, $trx_id = '', $amount = 0, $currency = "BDT")
    {
        $val_id = $post_data['val_id'] ?? null;

        if (!$val_id) {
            return false;
        }

        // ভ্যালিডেশন API কল
        $response = Http::get($this->apiUrl . "/validator/api/validationserverAPI.php", [
            'val_id' => $val_id,
            'store_id' => $this->storeId,
            'store_passwd' => $this->storePassword,
            'format' => 'json'
        ]);

        $result = json_decode($response->body(), true);

        if (isset($result['status']) && ($result['status'] == 'VALID' || $result['status'] == 'VALIDATED')) {
            return true;
        }

        return false;
    }
}
