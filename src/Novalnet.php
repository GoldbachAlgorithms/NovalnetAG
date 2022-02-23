<?php

namespace GoldbachAlgorithms\Novalnet;

class Novalnet
{
    const FAIL = 'FAILURE';

    private $signature;

    private $tariff;

    private $headers;

    private $url = "https://payport.novalnet.de/v2/seamless/payment";

    private $transaction;

    private $billing;

    private $customer;

    private $logo;

    private $css_url;

    private $skip_pages;

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
        string $tel,
        string $mobile,
        string $birth_date,
        string $gender,
        string $fax
    ): self
    {
        $this->customer = [
            'first_name'  => $first_name,
            'last_name'   => $last_name, 
            'email'       => $email, 
            'tel'         => $tel,
            'mobile'      => $mobile,
            'birth_date'  => $birth_date,
            'gender'      => $gender,
            'fax'         => $fax
        ];

        return $this;
    }

    public function setBilling(
        string $house_no,
        string $street,
        string $city,
        string $zip,
        string $country_code,
        string $company
    ): self
    {
        $this->billing = [
            'house_no'     => $house_no,
            'street'       => $street,
            'city'         => $city,
            'zip'          => $zip,
            'country_code' => $country_code,
            'company'      => $company
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
            'X-NN-Access-Key:' . $encoded_data
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
    ): self
    {
        $this->transaction = [
            'payment_type'     => $payment_type,
            'amount'           => $amount,
            'currency'         => $currency,    
            'test_mode'        => $test_mode,    
            'return_url'       => $return_url,
            'error_return_url' => $error_return_url
        ];

        return $this;
    }

    public function get(string $attribute)
    {
        try{
            $attribute = $this->$attribute;
        }
        catch(\Exception $e){
            throw new \Exception($e->getMessage());
        }

        return $attribute;
    }

    public function getLink(): string
    {
        $data = $this->generate();
        $json_data = json_encode($data);
        $exec = $this->execute($json_data);
        return $exec->result->redirect_url;
    }

    

    private function generate(): array
    {
        $data = [];
        $data['merchant'] = [
            'signature' => $this->signature,
            'tariff'    => $this->tariff
        ];
        $data['customer'] = $this->customer;
        $data['customer']['billing'] = $this->billing;
        $data['transaction'] = $this->transaction;

        $data['hosted_page'] = [
            'logo'             => $this->logo,
            'css_url'          => $this->css_url,
            'hide_blocks' 	   => $this->hide_blocks,
            'skip_pages'	   => $this->skip_pages
        ];

        return $data;
    }

    private function execute(string $data)
    {
        $url = $this->url;
        $headers = $this->headers;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($curl);
        if (curl_errno($curl)) {
            echo 'Request Error:' . curl_error($curl);
            return $result;
        }
        curl_close($curl);  
        $result = json_decode($result);

        if($result->result->status == SELF::FAIL)
        {
            throw new \Exception($result->result->status_text);
        }
    
        return $result;    
    }
}