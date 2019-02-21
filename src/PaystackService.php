<?php
namespace Wisomanthoni\Cashier;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
class PaystackService {
    /**
     * Issue Secret Key from your Paystack Dashboard
     * @var string
     */
    protected $secretKey;
    /**
     * Instance of Client
     * @var Client
     */
    protected $client;
    /**
     *  Response from requests made to Paystack
     * @var mixed
     */
    protected $response;
    /**
     * Paystack API base Url
     * @var string
     */

    public function __construct()
    {
        $this->setKey();
        $this->setBaseUrl();
        $this->setRequestOptions();
    }
    /**
     * Get Base Url from Paystack config file
     */
    public function setBaseUrl()
    {
        $this->baseUrl = Config::get('paystack.paymentUrl');
    }
    /**
     * Get secret key from Paystack config file
     */
    public function setKey()
    {
        $this->secretKey = Config::get('paystack.secretKey');
    }
    /**
     * Set options for making the Client request
     */
    private function setRequestOptions()
    {
        $authBearer = 'Bearer '. $this->secretKey;
        $this->client = new Client(
            [
                'base_uri' => $this->baseUrl,
                'headers' => [
                    'Authorization' => $authBearer,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json'
                ]
            ]
        );
    }

    /**
     * @param string $relativeUrl
     * @param string $method
     * @param array $body
     * @return Paystack
     * @throws IsNullException
     */
    private function setHttpResponse($relativeUrl, $method, $body = [])
    {
        if (is_null($method)) {
            throw new IsNullException("Empty method not allowed");
        }
        $this->response = $this->client->{strtolower($method)}(
            $this->baseUrl . $relativeUrl,
            ["body" => json_encode($body)]
        );
        return $this;
    }

    /**
     * Get the whole response from a get operation
     * @return array
     */
    private function getResponse()
    {
        return json_decode($this->response->getBody(), true);
    }

    /**
     * Get the data response from a get operation
     * @return array
     */
    private function getData()
    {
        return $this->getResponse()['data'];
    }

    // private function setRequestPayload($data)
    // {
    //     $request = new Request;
    //     $request->replace($data);
    // }

    public static function chargeAuthorization($data)
    {
        return self::setHttpResponse('/charge_authorization', 'POST', $data)->getResponse();
    }

    public static function refund($data)
    {
        return self::setHttpResponse('/refund', 'POST', $data)->getResponse();
    }

    public static function createSubscription($data)
    {
        return self::setHttpResponse('/subscription', 'POST', $data)->getResponse();
    }

    public static function createCustomer($data)
    {
        return self::setHttpResponse('/customer', 'POST', $data)->getResponse();
    }

    /**
     * Enable a subscription using the subscription code and token
     * @return array
     */
    public static function enableSubscription($data)
    {
        return self::setHttpResponse('/subscription/enable', 'POST', $data)->getResponse();
    }
    /**
     * Disable a subscription using the subscription code and token
     * @return array
     */
    public static function disableSubscription($data)
    {
        return self::setHttpResponse('/subscription/disable', 'POST', $data)->getResponse();
    }

    public static function createInvoice($data)
    {
        return self::setHttpResponse('/paymentrequest', 'POST', $data)->getResponse();
    }

    public static function findInvoice($invoice_id)
    {
        return self::setHttpResponse('/paymentrequest'. $invoice_id, 'GET', [])->getData();
    }

    public static function fetchInvoices($data)
    {
        return self::setHttpResponse('/paymentrequest', 'GET', $data)->getData();
    }

}