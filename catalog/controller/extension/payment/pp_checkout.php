<?php

use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;

class ControllerExtensionPaymentPPCheckout extends Controller {
    public function index($order_data) {
        $this->order_data = $order_data;
        $this->load->language('extension/payment/pp_checkout');

        $data['text_testmode'] = $this->language->get('text_testmode');
        $data['api_is_error'] = $this->language->get('api_is_error');
        $data['order_is_not_isset'] = $this->language->get('order_is_not_isset');

        $data['testmode'] = $this->config->get('payment_pp_checkout_test');
        $data['transaction'] = $this->config->get('payment_pp_checkout_transaction');
        $this->client_id = $this->config->get('payment_pp_checkout_client_id');
        $this->client_secret = $this->config->get('payment_pp_checkout_client_secret');

        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        try {
            if ($order_info) {
                $this->body = $this->buildRequestBody($order_info, $data);
                $client = $this->client();
                $response = $this->create($client);

                if ($data['testmode']) {
                    $Log = new Log("pp_checkout.log");
                    $Log->write($response);
                }

                $response = json_decode($response, true);

                if ($response['statusCode'] == '201') {
                    if (isset($response['result']['links'])) {
                        foreach ($response['result']['links'] as $link) {
                            if ($link['rel'] == 'capture') {
                                $data['href'] = $link['href'];
                            }
                        }
                    }
                }

                return $this->load->view('extension/payment/pp_checkout', $data);
            } else {
                throw new \Exception($data['order_is_not_isset']);
            }
        } catch (Exception $e){

        }
    }

    public function buildRequestBody($order_info,$data) {

        $bulidData ['intent'] = $data['transaction'];
        $bulidData['purchase_units'] = [[
            "reference_id" => $order_info['order_id'],
            "amount" => [
                "value" => $order_info['total'],
                "currency_code" => $order_info["currency_code"]
            ],
            'custom_id' => $order_info['customer_id']
        ]];
        $bulidData['redirect_urls'] = [
            "cancel_url" => "https://www.cambodia-home.cn",
            "return_url" => "hhttp://www.opencart.cn/index.php?route=extension/payment/pp_checkout/returnback"
        ];

        $breakdown = [];

        foreach($this->order_data['totals'] as $total) {
            if($total['code'] == 'sub_total') {
                $breakdown['item_total'] = [
                    "currency_code" => $order_info["currency_code"],
                    "value" => $total['value']
                ];
            }

            if($total['code'] == 'shipping') {
                $breakdown['shipping '] = [
                    "currency_code" => $order_info["currency_code"],
                    "value" => $total['value']
                ];
            }

            if($total['code'] == 'tax') {
                $breakdown['tax_total '] = [
                    "currency_code" => $order_info["currency_code"],
                    "value" => $total['value']
                ];
            }
        }

        $bulidData['purchase_units'][0]['breakdown'] = $breakdown;

        $item = [];

        foreach($this->order_data['products'] as $product) {
            $item[] = [
              'name'=> $product['name'],
              'unit_amount' => $product['price'],
              'tax' => isset($product['tax'])?$product['tax']:0,
              'quantity' => $product['quantity']
            ];
        }

        $bulidData['purchase_units'][0]['item'] = $item;

        $shipping =  [];
        $shipping['name']['fullname'] = $this->order_data['payment_firstname'].' '.$this->order_data['payment_lastname'];
        $shipping['address']['address_line_1'] = $this->order_data['payment_address_1'];
        $shipping['address']['address_line_2 '] = $this->order_data['payment_address_2'];
        $shipping['address']['admin_area_1 '] =  $this->order_data['payment_country'];
        $shipping['address']['postal_code '] = $this->order_data['payment_postcode'];

        $bulidData['purchase_units'][0]['shipping'] = $shipping;
        return $bulidData;
    }
    public function returnBack() {
        echo "return back";
    }
    public static function client()
    {
        return new PayPalHttpClient(self::environment());
    }
    public  function environment()
    {
        $clientId = getenv("CLIENT_ID") ?: $this->client_id;
        $clientSecret = getenv("CLIENT_SECRET") ?: $this->client_secret;
        return new SandboxEnvironment($clientId, $clientSecret);
    }
    public  function create($client) {
        $request = new OrdersCreateRequest();
        $request->prefer("return=representation");
        $request->body = $this->body;
        return $client->execute($request);
    }
}