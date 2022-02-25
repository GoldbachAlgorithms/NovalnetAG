<?php

namespace GoldbachAlgorithms\Novalnet;

class Novalnet
{
    public const FAIL = 'FAILURE';

    private $signature;

    private $tariff;

    private $headers;

    private $payment_url = 'https://payport.novalnet.de/v2/seamless/payment';

    private $refund_url = 'https://payport.novalnet.de/v2/transaction/refund';

    private $transaction;

    private $billing;

    private $customer;

    private $logo;

    private $css_url;

    private $skip_pages = [];

    private $hide_blocks = [];

    public function setSignature(string $signature): self
    {
        $this->signature = $signature;

        return $this;
    }

    public function setTariff(string $tariff): self
    {
        $this->tariff = $tariff;

        return $this;
    }

    public function setLogo(string $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    public function setCssUrl(string $css_url): self
    {
        $this->css_url = $css_url;

        return $this;
    }

    public function setHideBlocks(array $hide_blocks = []): self
    {
        $this->hide_blocks = $hide_blocks;

        return $this;
    }

    public function setSkipPages(array $skip_pages = []): self
    {
        $this->skip_pages = $skip_pages;

        return $this;
    }

    public function setCustomer(
        string $first_name,
        string $last_name,
        string $email,
        string $mobile,
        string $birth_date,
        string $gender,
        string $tel = '',
        string $fax = ''
    ): self {
        $this->customer = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'tel' => $tel,
            'mobile' => $mobile,
            'birth_date' => $birth_date,
            'gender' => $gender,
            'fax' => $fax,
        ];

        return $this;
    }

    public function refund(
        string $tid,
        string $reason,
        string $lang,
        ?string $amount = null
    ) {
        $data = [];

        $data['transaction'] = [
            'tid' => $tid,
            'reason' => $reason,
        ];

        if (!empty($amount)) {
            $data['transaction']['amount'] = $amount;
        }

        $data['custom'] = [
            'lang' => $lang,
        ];

        return $this->getRefund($data);
    }

    public function setBilling(
        string $house_no,
        string $street,
        string $city,
        string $zip,
        string $country_code,
        string $company
    ): self {
        $this->billing = [
            'house_no' => $house_no,
            'street' => $street,
            'city' => $city,
            'zip' => $zip,
            'country_code' => $country_code,
            'company' => $company,
        ];

        return $this;
    }

    public function setPaymentKey(string $paymentKey): self
    {
        $encoded_data = base64_encode($paymentKey);

        $this->headers = [
            'Content-Type:application/json',
            'Charset:utf-8',
            'Accept:application/json',
            'X-NN-Access-Key:'.$encoded_data,
        ];

        return $this;
    }

    public function setTransaction(
        string $payment_type,
        string $amount,
        string $currency,
        int $test_mode = 1,
        string $return_url = '',
        string $error_return_url = ''
    ): self {
        $this->transaction = [
            'payment_type' => $payment_type,
            'amount' => $amount,
            'currency' => $currency,
            'test_mode' => $test_mode,
            'return_url' => $return_url,
            'error_return_url' => $error_return_url,
        ];

        return $this;
    }

    public function get(string $attribute)
    {
        try {
            $attribute = $this->$attribute;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $attribute;
    }

    public function getLink(): object
    {
        $data = $this->generate();
        $json_data = json_encode($data);
        $resultset = $this->execute($json_data, $this->payment_url);

        if (gettype($resultset) == 'object') {
            $resultset = [
                'status' => 'success',
                'link' => $resultset->result->redirect_url,
            ];
        }

        return (object) $resultset;
    }

    public function getRefund(array $data): object
    {
        $json_data = json_encode($data);
        $resultset = $this->execute($json_data, $this->refund_url);

        if (gettype($resultset) == 'object') {
            $resultset = [
                'status' => 'success',
                'tid' => $resultset->transaction->tid,
                'refunded_amount' => $resultset->transaction->refunded_amount,
            ];
        }

        return (object) $resultset;
    }

    private function generate(): array
    {
        $data = [];
        $data['merchant'] = [
            'signature' => $this->signature,
            'tariff' => $this->tariff,
        ];
        $data['customer'] = $this->customer;
        $data['customer']['billing'] = $this->billing;
        $data['transaction'] = $this->transaction;

        $data['hosted_page'] = [
            'logo' => $this->logo,
            'css_url' => $this->css_url,
            'hide_blocks' => $this->hide_blocks,
            'skip_pages' => $this->skip_pages,
        ];

        return $data;
    }

    private function execute(string $data, string $url)
    {
        $headers = $this->headers;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($curl);
        if (curl_errno($curl)) {
            throw new \Exception('Request Error:'.curl_error($curl));
        }
        curl_close($curl);
        $result = json_decode($result);

        if ($result->result->status == self::FAIL) {
            $resultset = [
                'status' => 'error',
                'message' => $result->result->status_text,
            ];

            return $resultset;
        } else {
            return $result;
        }
    }
}
