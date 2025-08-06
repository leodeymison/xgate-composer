<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

require_once __DIR__ . '/types/account.php';
require_once __DIR__ . '/types/token.php';
require_once __DIR__ . '/modules/Errors.php';

class XGate
{
    private Account $account;
    private Access $access;
    private Client $api;

    public function __construct(Account $account)
    {
        $this->account = $account;
        $this->api = new Client([
            'base_uri' => 'https://api.xgateglobal.com',
        ]);
        $this->login();
    }

    private function login()
    {
        try {
            $response = $this->api->post('/auth/token', [
                'json' => $this->account,
            ]);
            $data = json_decode($response->getBody(), true);
            $this->access = new Access(
                time() + 60 * 60 * 24 * 2, // 48hrs
                $data['token'],
            );
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao acessar conta: ", 500);
        }
    }

    private function getHeader()
    {
        return [
            'Authorization' => 'Bearer ' . $this->access->token,
        ];
    }

    private function verifyLogged()
    {
        if (!$this->access || $this->access->expirate < time()) {
            $this->login();
        }
    }

    /**
     * Método usado para buscar todas as moedas fiduciárias disponível para depósitos na sua conta XGate
     */
    public function getCurrenciesDeposit()
    {
        $this->verifyLogged();
        try {
            $response = $this->api->get('/deposit/company/currencies', [
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao buscar moedas de depósito: ", 500);
        }
    }

    /**
     * Método usado para buscar todas as moedas fiduciárias disponível para saques na sua conta XGate
     */
    public function getCurrenciesWithdraw()
    {
        $this->verifyLogged();
        try {
            $response = $this->api->get('/withdraw/company/currencies', [
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao buscar moedas de saque: ", 500);
        }
    }

    /**
     * Método usado para buscar todas as cripto moedas disponível para depósitos na sua conta XGate
     */
    public function getCryptocurrenciesDeposit()
    {
        $this->verifyLogged();
        try {
            $response = $this->api->get('/deposit/company/cryptocurrencies', [
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao buscar criptomoedas de depósito: ", 500);
        }
    }

    /**
     * Método usado para buscar todas as cripto moedas disponível para saques na sua conta XGate
     */
    public function getCryptocurrenciesWithdraw()
    {
        $this->verifyLogged();
        try {
            $response = $this->api->get('/withdraw/company/cryptocurrencies', [
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao buscar criptomoedas de saque: ", 500);
        }
    }

    public function getBalance($filter = null)
    {
        $this->verifyLogged();
        try {
            $body = [];
            if ($filter) {
                if (isset($filter['coinGecko'])) {
                    $body['cryptocurrency'] = $filter;
                } else {
                    $body['currency'] = $filter;
                }
            }
            $response = $this->api->post('/balance/company', [
                'json' => $body,
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao buscar saldo em conta: ", 500);
        }
    }

    public function customerCreate($customer)
    {
        $this->verifyLogged();
        try {
            $response = $this->api->post('/customer', [
                'json' => $customer,
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao criar cliente: ", 500);
        }
    }

    public function customerUpdate($customerId, $customer)
    {
        $this->verifyLogged();
        try {
            $response = $this->api->put("/customer/{$customerId}", [
                'json' => $customer,
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao atualizar cliente: ", 500);
        }
    }

    public function getQuotationDepositFiatToCrypto($amount, $currencyType, $cryptoName)
    {
        $this->verifyLogged();

        $currencies = $this->getCurrenciesDeposit();
        $currency = array_filter($currencies, fn($c) => $c['type'] === $currencyType);
        $currency = array_values($currency)[0] ?? null;

        $cryptos = $this->getCryptocurrenciesDeposit();
        $crypto = array_filter($cryptos, fn($c) => $c['name'] === $cryptoName);
        $crypto = array_values($crypto)[0] ?? null;

        if (!$currency || !$crypto) {
            throw new XGateError(new Error(), "Moeda ou Cripto não habilitada na conta", 400);
        }

        try {
            $response = $this->api->post("/deposit/conversion/{$crypto['coinGecko']}", [
                'json' => [
                    'amount' => $amount,
                    'currency' => $currency
                ],
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao obter cotação: ", 500);
        }
    }

    public function depositFiat($amount, $customerId, $methodCurrency)
    {
        $this->verifyLogged();

        $currencies = $this->getCurrenciesDeposit();
        $currency = array_filter($currencies, fn($c) => $c['type'] === $methodCurrency);
        $currency = array_values($currency)[0] ?? null;

        if (!$currency) {
            throw new XGateError(new Error(), "Moeda $methodCurrency não habilitada na conta", 400);
        }

        try {
            $response = $this->api->post('/deposit', [
                'json' => [
                    'amount' => $amount,
                    'customerId' => $customerId,
                    'currency' => $currency
                ],
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao solicitar depósito: ", 500);
        }
    }

    public function withdrawFiat($amount, $customerId, $methodCurrency, $pixKey)
    {
        $this->verifyLogged();

        $currencies = $this->getCurrenciesWithdraw();
        $currency = array_filter($currencies, fn($c) => $c['type'] === $methodCurrency);
        $currency = array_values($currency)[0] ?? null;

        if (!$currency) {
            throw new XGateError(new Error(), "Moeda $methodCurrency não habilitada na conta", 400);
        }

        try {
            $response = $this->api->post('/withdraw', [
                'json' => [
                    'amount' => $amount,
                    'customerId' => $customerId,
                    'currency' => $currency,
                    'pixKey' => $pixKey
                ],
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao solicitar saque: ", 500);
        }
    }

    public function depositConversionFiatToCrypto($amount, $customerId, $methodCurrency, $methodCryptocurrency)
    {
        $this->verifyLogged();

        $currencies = $this->getCurrenciesDeposit();
        $currency = array_values(array_filter($currencies, fn($c) => $c['type'] === $methodCurrency))[0] ?? null;

        $cryptos = $this->getCryptocurrenciesDeposit();
        $crypto = array_values(array_filter($cryptos, fn($c) => $c['name'] === $methodCryptocurrency))[0] ?? null;

        if (!$currency || !$crypto) {
            throw new XGateError(new Error(), "Moeda ou Cripto não habilitada na conta", 400);
        }

        try {
            $response = $this->api->post('/deposit', [
                'json' => [
                    'amount' => $amount,
                    'customerId' => $customerId,
                    'currency' => $currency,
                    'cryptocurrency' => $crypto
                ],
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao solicitar depósito com conversão: ", 500);
        }
    }

    public function withdrawConversionCryptoToFiat($amount, $customerId, $methodCryptocurrency, $methodCurrency, $pixKey)
    {
        $this->verifyLogged();

        $currencies = $this->getCurrenciesWithdraw();
        $currency = array_values(array_filter($currencies, fn($c) => $c['type'] === $methodCurrency))[0] ?? null;

        $cryptos = $this->getCryptocurrenciesDeposit();
        $crypto = array_values(array_filter($cryptos, fn($c) => $c['name'] === $methodCryptocurrency))[0] ?? null;

        if (!$currency || !$crypto) {
            throw new XGateError(new Error(), "Moeda ou Cripto não habilitada na conta", 400);
        }

        try {
            $response = $this->api->post('/withdraw', [
                'json' => [
                    'amount' => $amount,
                    'customerId' => $customerId,
                    'currency' => $currency,
                    'cryptocurrency' => $crypto,
                    'pixKey' => $pixKey
                ],
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao solicitar saque com conversão: ", 500);
        }
    }

    public function withdrawExternalWallet($amount, $customerId, $blockchainName, $cryptoName, $walletKey)
    {
        $this->verifyLogged();

        $response = $this->api->get('/withdraw/company/blockchain-networks', [
            'headers' => $this->getHeader()
        ]);
        $blockchains = json_decode($response->getBody(), true);

        $blockchain = array_values(array_filter($blockchains, fn($b) => $b['name'] === $blockchainName))[0] ?? null;
        if (!$blockchain) throw new XGateError(new Error(), "Blockchain $blockchainName não encontrada", 400);

        $cryptoData = array_values(array_filter($blockchain['cryptocurrencies'], fn($c) => $c['cryptocurrency']['name'] === $cryptoName))[0] ?? null;
        if (!$cryptoData) throw new XGateError(new Error(), "Cripto $cryptoName não encontrada nesta rede", 400);

        if ($cryptoData['minWithdraw'] > $amount) {
            throw new XGateError(new Error(), "Valor mínimo para saque é {$cryptoData['minWithdraw']}", 400);
        }

        try {
            $response = $this->api->post('/withdraw', [
                'json' => [
                    'amount' => $amount,
                    'customerId' => $customerId,
                    'blockchainNetwork' => $blockchain,
                    'cryptocurrency' => $cryptoData['cryptocurrency'],
                    'wallet' => $walletKey
                ],
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao solicitar saque para carteira externa: ", 500);
        }
    }

    public function pixKeyCreate($customerId, $body)
    {
        $this->verifyLogged();

        try {
            $response = $this->api->post("/pix/customer/{$customerId}/key", [
                'json' => $body,
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao criar chave PIX: ", 500);
        }
    }

    public function pixKeyGetAll($customerId)
    {
        $this->verifyLogged();

        try {
            $response = $this->api->get("/pix/customer/{$customerId}/key", [
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao buscar chaves PIX: ", 500);
        }
    }

    public function pixKeyDelete($customerId, $pixKeyId)
    {
        $this->verifyLogged();

        try {
            $response = $this->api->delete("/pix/customer/{$customerId}/key/remove/{$pixKeyId}", [
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao deletar chave PIX: ", 500);
        }
    }

    public function depositGenerateCryptoWallet($customerId)
    {
        $this->verifyLogged();

        try {
            $response = $this->api->get("/crypto/customer/{$customerId}/wallet", [
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao gerar carteira cripto: ", 500);
        }
    }
}
