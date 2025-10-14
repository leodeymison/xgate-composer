<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

require_once __DIR__ . '/types/account.php';
require_once __DIR__ . '/types/token.php';
require_once __DIR__ . './types/response.php';
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

    private function login(): Login
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

            return $data;
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao acessar conta", 500);
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

    private function verifyEpecificPixKeyInCustomerWithResultReverse(string $customerId, PixKeyParam $pixKey)
    {
        // Chama o serviço que retorna as chaves PIX do cliente
        $pixKeys = $this->pixKeyGetAll($customerId);

        // Filtra procurando a chave que corresponda
        $pixKeyFilter = array_filter($pixKeys, function ($item) use ($pixKey) {
            return isset($item['key']) && $item['key'] === $pixKey->key;
        });

        // Reorganiza os índices do array filtrado
        $pixKeyFilter = array_values($pixKeyFilter);

        if (count($pixKeyFilter) > 0) {
            return $pixKeyFilter[0]; // Retorna a primeira chave encontrada
        }

        return false; // Se não encontrou, retorna false
    }

    // ? CURRENCIES
    /**
     * Método usado para buscar todas as moedas fiduciárias disponível para depósitos na sua conta XGate
     * @return Currency[]
     */
    public function getCurrenciesDeposit(): array
    {
        $this->verifyLogged();
        try {
            $response = $this->api->get('/deposit/company/currencies', [
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao buscar moedas de depósito disponíveis na sua conta", 500);
        }
    }
    /**
     * Método usado para buscar todas as moedas fiduciárias disponível para saques na sua conta XGate
     * @return Currency[]
     */
    public function getCurrenciesWithdraw(): array
    {
        $this->verifyLogged();
        try {
            $response = $this->api->get('/withdraw/company/currencies', [
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao buscar moedas de saque disponíveis na sua conta", 500);
        }
    }

    // ? CRYPTOCURRENCIES
    /**
     * Método usado para buscar todas as cripto moedas disponível para depósitos na sua conta XGate
     * @return Cryptocurrency[]
     */
    public function getCryptocurrenciesDeposit(): array
    {
        $this->verifyLogged();
        try {
            $response = $this->api->get('/deposit/company/cryptocurrencies', [
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao buscar cripto moedas de depósito disponíveis na sua conta", 500);
        }
    }
    /**
     * Método usado para buscar todas as cripto moedas disponível para saques na sua conta XGate
     * @return Cryptocurrency[]
     */
    public function getCryptocurrenciesWithdraw(): array
    {
        $this->verifyLogged();
        try {
            $response = $this->api->get('/withdraw/company/cryptocurrencies', [
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao buscar cripto moedas de saque disponíveis na sua conta", 500);
        }
    }

    // ? BLOCKCHAIN NETWORK
    /**
     * Método usado para buscar todas as redes blockchains disponível para depósitos na sua conta XGate e as moedas suportadas por cada rede blockchain
     * @return BlockchainDeposit[]
     */
    public function getBlockchainDeposit(): array {
        $this->verifyLogged();
        try {
            $response = $this->api->get('/deposit/company/blockchain-networks', [
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao buscar redes blockchainscde de depósito disponíveis para depósito na sua conta", 500);
        }
    }
    /**
     * Método usado para buscar todas as redes blockchains disponível para saque na sua conta XGate e as moedas suportadas por cada rede blockchain
     * @return BlockchainWithdraw[]
     */
    public function getBlockchainWithdraw(): array {
        $this->verifyLogged();
        try {
            $response = $this->api->get('/deposit/company/blockchain-networks', [
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao buscar redes blockchainscde de saque disponíveis para depósito na sua conta", 500);
        }
    }

    // ? COMPANY
    /**
     * Método usado para buscar o saldo na sua conta XGate
     * @param CurrencyBalance|CryptoBalance|null $filter - Filtra por uma moeda ou cryptomoeda que você deseja saber o saldo, caso esse parâmetro seja ignorado, vai ser retornado todas as cryptomoedas e moedas disponível na sua conta, juntamente com o saldo de cada uma delas.
     * @return (BalanceCurrency|BalanceCryptocurrency)[]
     */
    public function getBalance(CurrencyBalance|CryptoBalance|null $filter = null): array
    {
        $this->verifyLogged();
        try {
            $body = [];

            if (!empty($filter)) {
                if (property_exists($filter, "currencyId")) {
                    $deposits = $this->getCurrenciesDeposit();
                    $depositsFilter = array_filter($deposits, function ($item) use ($filter) {
                        return $item->_id === $filter->currencyId;
                    });

                    if (!empty($depositsFilter)) {
                        $body = [
                            "currency" => array_values($depositsFilter)[0]
                        ];
                    } else {
                        $withdraw = $this->getCurrenciesWithdraw();
                        $withdrawFilter = array_filter($withdraw, function ($item) use ($filter) {
                            return $item->_id === $filter->currencyId;
                        });
                        $body = [
                            "currency" => array_values($withdrawFilter)[0]
                        ];
                    }
                } else {
                    $deposits = $this->getCryptocurrenciesDeposit();
                    $depositsFilter = array_filter($deposits, function ($item) use ($filter) {
                        return $item->_id === $filter->cryptocurrencyId;
                    });

                    if (!empty($depositsFilter)) {
                        $body = [
                            "cryptocurrency" => array_values($depositsFilter)[0]
                        ];
                    } else {
                        $withdraw = $this->getCryptocurrenciesWithdraw();
                        $withdrawFilter = array_filter($withdraw, function ($item) use ($filter) {
                            return $item->_id === $filter->cryptocurrencyId;
                        });
                        $body = [
                            "cryptocurrency" => array_values($withdrawFilter)[0]
                        ];
                    }
                }
            }

            $response = $this->api->post('/balance/company', [
                'json' => $body,
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao buscar saldo em conta", 500);
        }
    }

    // ? SUB COMPANY
    /**
     * Criar uma sub conta
     * @param SubCompanyCreate $dataParam - Object com as informações da sub conta. { user: {...}, deposit: {...}, withdraw: {...} }
     * @return SubCompanyCreate
     */
    public function createSubCompany(SubCompanyCreate $dataParam): SubCompanyCreate {
        $this->verifyLogged();
        $keys = [
            "currencies",
            "cryptocurrencies",
            "blockchainNetworks",
        ];
        try {
            $body = [
                'user' => $dataParam->user, // equivalente ao dataParam.user
                'deposit' => [
                    'blockchainNetworks' => [],
                    'cryptocurrencies' => [],
                    'currencies' => [],
                ],
                'withdraw' => [
                    'blockchainNetworks' => [],
                    'cryptocurrencies' => [],
                    'currencies' => [],
                ],
            ];
            $typeTransactions = [
                "deposit",
                "withdraw",
            ];
            foreach ($typeTransactions as $typeTransaction) {
                $currencies = $dataParam[$typeTransaction]['currencies'] ?? null;

                if (is_array($currencies)) {
                    foreach ($currencies as $coin) {
                        if (is_string($coin['currency'] ?? null)) {
                            $func = $typeTransaction === 'deposit'
                                ? $this->getCurrenciesDeposit()
                                : $this->getCurrenciesWithdraw();

                            $getCoin = array_filter($func, function ($item) use ($coin) {
                                return $item['_id'] === $coin['currency'];
                            });

                            if (empty($getCoin)) {
                                throw new Exception("Moeda {$coin['currency']} não disponível para a sua conta");
                            }

                            $body[$typeTransaction]['currencies'][] = [
                                'currency' => array_values($getCoin)[0],
                                'fee' => $coin['fee'] ?? null
                            ];
                        } else {
                            $body[$typeTransaction]['currencies'][] = [
                                'currency' => $coin['currency'],
                                'fee' => $coin['fee'] ?? null
                            ];
                        }
                    }
                } else {
                    $getCoin = $typeTransaction === 'deposit'
                        ? $this->getCurrenciesDeposit()
                        : $this->getCurrenciesWithdraw();

                    foreach ($getCoin as $coin) {
                        $body[$typeTransaction]['currencies'][] = [
                            'currency' => $coin,
                            'fee' => $currencies ?? null
                        ];
                    }
                }
            }

            // CRYPTOCURRENCIES
            foreach ($typeTransactions as $typeTransaction) {
                $cryptos = $dataParam[$typeTransaction]['cryptocurrencies'] ?? null;

                if (is_array($cryptos)) {
                    foreach ($cryptos as $coin) {
                        if (is_string($coin['cryptocurrency'] ?? null)) {
                            $func = $typeTransaction === 'deposit'
                                ? $this->getCryptocurrenciesDeposit()
                                : $this->getCryptocurrenciesWithdraw();

                            $getCoin = array_filter($func, function ($item) use ($coin) {
                                return $item['_id'] === $coin['cryptocurrency'];
                            });

                            if (empty($getCoin)) {
                                throw new Exception("Cripto moeda {$coin['cryptocurrency']} não disponível para a sua conta");
                            }

                            $body[$typeTransaction]['cryptocurrencies'][] = [
                                'cryptocurrency' => array_values($getCoin)[0],
                                'fee' => $coin['fee'] ?? null
                            ];
                        } else {
                            $body[$typeTransaction]['cryptocurrencies'][] = [
                                'cryptocurrency' => $coin['cryptocurrency'],
                                'fee' => $coin['fee'] ?? null
                            ];
                        }
                    }
                } else {
                    $getCoin = $typeTransaction === 'deposit'
                        ? $this->getCryptocurrenciesDeposit()
                        : $this->getCryptocurrenciesWithdraw();

                    foreach ($getCoin as $coin) {
                        $body[$typeTransaction]['cryptocurrencies'][] = [
                            'cryptocurrency' => $coin,
                            'fee' => $cryptos ?? null
                        ];
                    }
                }
            }

            // BLOCKCHAIN NETWORKS
            foreach ($typeTransactions as $typeTransaction) {
                $networks = $dataParam[$typeTransaction]['blockchainNetworks'] ?? null;

                if (is_array($networks)) {
                    foreach ($networks as $coin) {
                        if (is_string($coin['blockchainNetwork'] ?? null)) {
                            $func = $typeTransaction === 'deposit'
                                ? $this->getBlockchainDeposit()
                                : $this->getBlockchainWithdraw();

                            $funcWithoutCrypto = array_map(function ($item) {
                                return [
                                    '_id' => $item['_id'],
                                    'name' => $item['name'],
                                    'chainId' => $item['chainId'],
                                    'updatedDate' => $item['updatedDate'],
                                    'createdDate' => $item['createdDate'],
                                ];
                            }, $func);

                            $getCoin = array_filter($funcWithoutCrypto, function ($item) use ($coin) {
                                return $item['_id'] === $coin['blockchainNetwork'];
                            });

                            if (empty($getCoin)) {
                                throw new Exception("Blockchain {$coin['blockchainNetwork']} não disponível para a sua conta");
                            }

                            $body[$typeTransaction]['blockchainNetworks'][] = [
                                'blockchainNetwork' => array_values($getCoin)[0],
                                'fee' => $coin['fee'] ?? null
                            ];
                        } else {
                            $body[$typeTransaction]['blockchainNetworks'][] = [
                                'blockchainNetwork' => $coin['blockchainNetwork'],
                                'fee' => $coin['fee'] ?? null
                            ];
                        }
                    }
                } else {
                    $getCoin = $typeTransaction === 'deposit'
                        ? $this->getBlockchainDeposit()
                        : $this->getBlockchainWithdraw();

                    $funcWithoutCrypto = array_map(function ($item) {
                        return [
                            '_id' => $item['_id'],
                            'name' => $item['name'],
                            'chainId' => $item['chainId'],
                            'updatedDate' => $item['updatedDate'],
                            'createdDate' => $item['createdDate'],
                        ];
                    }, $getCoin);

                    foreach ($funcWithoutCrypto as $coin) {
                        $body[$typeTransaction]['blockchainNetworks'][] = [
                            'blockchainNetwork' => $coin,
                            'fee' => $networks ?? null
                        ];
                    }
                }
            }

            $response = $this->api->post('/company/subaccount', [
                'json' => $body,
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao criar sub conta", 500);
        }
    }
    /**
     * Adiciona o primeiro IP de uma sub conta
     * @param string $ip - Endereço IPV4 ou IPV6
     * @return SubCompanyCreate
     */
    public function addFirstIP(string $ip): Message {
        $this->verifyLogged();
        try {
            $response = $this->api->post('/withdraw/allowed-ip/subaccount', [
                'json' => [
                    "ip" => $ip
                ],
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao adicionar o primeira IP na sub conta", 500);
        }
    }
    /**
     * Adiciona o primeiro webhook de uma sub conta
     * @param Webhook $body - Um object{} com dois parâmetros: "externalWebhookUrl" = URL externa do WebHook e "name" = Para identificar o Webhook pelo nome
     * @return SubCompanyCreate
     */
    public function addFirstWebhook(Webhook $body): Message {
        $this->verifyLogged();
        try {
            $response = $this->api->post('/webhook/subaccount', [
                'json' => $body,
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao adicionar o primeira Webhook na sub conta", 500);
        }
    }

    // ? CUSTOMER
    /**
     *
     * Método usado para criar um cliente.
     * @param Customer $customer - Objeto com os dados do cliente. { name: "", ... }
     * @return CreateCustomer
     */
    public function customerCreate(Customer $customer): CreateCustomer
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
    /**
     *
     * Método usado para atualizar informações do cliente
     * @param string $customerId - ID do cliente que você deseja mudar as informações
     * @param Customer $customer - Objeto com as novas informações do cliente. { name: "", ... }
     * @return Message
     */
    public function customerUpdate(string $customerId, Customer $customer): Message
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

    // ? QUOTATION
    /**
     *
     * Método usado para buscar uma contação de depósito convertendo moeda fiduciária para cripto moeda.
     * @param float $amount - Valor de deseja depositar
     * @param MethodCurrency $methodCurrency - Moeda fiduciária
     * @param MethodCryptocurrency $methodCryptocurrency - Cripto moeda usada para conversão
     * @return QuotationCrypto
     */
    public function getQuotationDepositFiatToCrypto(float $amount, MethodCurrency $currencyType, MethodCryptocurrency $cryptoName): QuotationCrypto
    {
        $this->verifyLogged();

        $currencies = $this->getCurrenciesDeposit();
        $currency = array_filter($currencies, fn($c) => $c['type'] === $currencyType);
        $currency = array_values($currency)[0] ?? null;

        $cryptos = $this->getCryptocurrenciesDeposit();
        $crypto = array_filter($cryptos, fn($c) => $c['name'] === $cryptoName);
        $crypto = array_values($crypto)[0] ?? null;
        
        if (!$crypto) {
            throw new XGateError(new Error(), sprintf("Crypto moeda %s não está habilitada na sua conta", $crypto), 400);
        }
        if (!$currency) {
            throw new XGateError(new Error(), sprintf("Moeda %s não está habilitada na sua conta", $currency), 400);
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
    /**
     *
     * Método usado para buscar uma contação de saque convertendo cripto moeda para moeda fiduciária.
     * @param float $amount - Valor de deseja sacar
     * @param MethodCryptocurrency $methodCryptocurrency - Cripto moeda
     * @param MethodCurrency $methodCurrency - Moeda fiduciária usada para conversão
     * @return QuotationFiat
     */
    public function getQuotationWithdrawCryptoToFiat(float $amount, MethodCryptocurrency $methodCryptocurrency, MethodCurrency $methodCurrency): QuotationFiat {
        $this->verifyLogged();

        $currencies = $this->getCurrenciesWithdraw();
        $currency = array_filter($currencies, fn($c) => $c['type'] === $methodCurrency);
        $currency = array_values($currency)[0] ?? null;

        $cryptocurrencies = $this->getCryptocurrenciesWithdraw();
        $cryptocurrency = array_filter($cryptocurrencies, fn($c) => $c['name'] === $methodCryptocurrency);
        $cryptocurrency = array_values($cryptocurrency)[0] ?? null;

        if (!$cryptocurrency) {
            throw new XGateError(new Error(), sprintf("Crypto moeda %s não está habilitada na sua conta", $cryptocurrency), 400);
        }
        if (!$currency) {
            throw new XGateError(new Error(), sprintf("Moeda %s não está habilitada na sua conta", $methodCurrency), 400);
        }

        try {
            $response = $this->api->post(sprintf("/withdraw/conversion/%s/%s", mb_strtolower($currency[0]->name), mb_strtolower($currency[0]->type)), [
                'json' => [
                    'amount' => $amount,
                    'cryptocurrency' => $cryptocurrency[0],
                ],
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao buscar redes blockchainscde de depósito disponíveis para depósito na sua conta", 500);
        }
    }
    /**
     *
     * Método usado para buscar uma contação de saque de crypto moeda para uma carteira externa
     * @param float $amount - Valor de deseja sacar
     * @param MethodBlockchain $methodBlockchain - Rede Blockchain
     * @param MethodCryptocurrency $methodCryptocurrency - Cripto moeda
     * @return QuotationAmount
     */
    public function getQuotationWithdrawExternalWallet(float $amount, MethodBlockchain $methodBlockchain, MethodCryptocurrency $methodCryptocurrency): QuotationAmount {
        $this->verifyLogged();

        $blockchains = $this->getBlockchainWithdraw();
        $blockchain = array_filter($blockchains, fn($c) => $c['type'] === $methodBlockchain);
        $blockchain = array_values($blockchain)[0] ?? null;

        if (!$blockchain) {
            throw new XGateError(new Error(), sprintf("Rede Blockchain %s não está habilitada na sua conta", $methodBlockchain), 400);
        }

        $cryptocurrency = array_filter($blockchain[0]->cryptocurrencies, fn($c) => $c->cryptocurrency['name'] === $methodCryptocurrency);
        $cryptocurrency = array_values($cryptocurrency)[0] ?? null;

        if (!$cryptocurrency) {
            throw new XGateError(new Error(), sprintf("Crypto moeda %s não está habilitada na sua conta", $methodCryptocurrency), 400);
        }

        if (!$cryptocurrency[0]->minWithdraw > $amount) {
            throw new XGateError(new Error(), sprintf("Saque mínimo de %s na Rede %s é de %s %s", $methodCryptocurrency, $methodBlockchain, $cryptocurrency[0]->minWithdraw, $methodCryptocurrency), 400);
        }

        try {
            $response = $this->api->post(sprintf("/withdraw/transaction/crypto/amount"), [
                'json' => [
                    'amount' => $amount,
                    'cryptocurrency' => $cryptocurrency[0]->cryptocurrency,
                    'blockchainNetwork' => $blockchain[0],
                ],
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao buscar redes blockchains de depósito disponíveis para depósito na sua conta", 500);
        }
    }

    // ? DEPOSIT
    /**
     * Método usado para solicitar um depósito
     * @param float $amount - Valor de deseja depositar
     * @param string|Customer $customer - Dados do cliente ou ID do cliente já criado anteriormente
     * @param MethodCurrency $methodCurrency - Método de depósito, ex: PIX
     * @return Deposit
     */
    public function depositFiat(float $amount, string|Customer $customer, MethodCurrency $methodCurrency): Deposit
    {
        $this->verifyLogged();

        $currencies = $this->getCurrenciesDeposit();
        $currency = array_filter($currencies, fn($c) => $c['type'] === $methodCurrency);
        $currency = array_values($currency)[0] ?? null;

        if (!$currency) {
            throw new XGateError(
                new Error(), 
                sprintf("Moeda %s não habilitada na conta", $methodCurrency),
                400
            );
        }

        $customerId = "";

        if (!is_string($customer)) {
            $resCustomerCreate = $this->customerCreate($customer);
            $customerId = $resCustomerCreate->customer->_id ?? null;
        } else {
            $customerId = $customer;
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
    /**
     * Método usado para solicitar um depósito de moeda fiduciária com conversão para crypto moeda
     * @param float $amount - Valor de deseja depositar
     * @param string|Customer $customer - Dados do cliente ou ID do cliente já criado anteriormente
     * @param MethodCurrency $methodCurrency - Método de depósito, ex: PIX
     * @param MethodCryptocurrency $methodCryptocurrency - Método de conversão, ex: USDT
     * @return Deposit
     */
    public function depositConversionFiatToCrypto(float $amount, string|Customer $customer, MethodCurrency $methodCurrency, MethodCryptocurrency $methodCryptocurrency): Deposit
    {
        $this->verifyLogged();

        $currencies = $this->getCurrenciesDeposit();
        $currency = array_values(array_filter($currencies, fn($c) => $c['type'] === $methodCurrency))[0] ?? null;

        $cryptos = $this->getCryptocurrenciesDeposit();
        $crypto = array_values(array_filter($cryptos, fn($c) => $c['name'] === $methodCryptocurrency))[0] ?? null;

        if (!$currency) {
            throw new XGateError(
                new Error(), 
                sprintf("Moeda %s não habilitada na conta", $methodCurrency),
                400
            );
        }
        if (!$crypto) {
            throw new XGateError(
                new Error(), 
                sprintf("Cripto moeda %s não habilitada na conta", $methodCryptocurrency),
                400
            );
        }

        $customerId = "";

        if (!is_string($customer)) {
            $resCustomerCreate = $this->customerCreate($customer);
            $customerId = $resCustomerCreate->customer->_id ?? null;
        } else {
            $customerId = $customer;
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
    /**
     * Método usado para gerar uma carteira de cripto moeda para o cliente depositar
     * @param string|Customer $customer - Dados do cliente ou ID do cliente já criado anteriormente
     * @return Wallet[]
     */
    public function depositGenerateCryptoWallet(string|Customer $customer): array
    {
        $this->verifyLogged();

        try {
            $customerId = "";

            if (!is_string($customer)) {
                $resCustomerCreate = $this->customerCreate($customer);
                $customerId = $resCustomerCreate->customer->_id ?? null;
            } else {
                $customerId = $customer;
            }

            $response = $this->api->get(sprintf("/crypto/customer/%s/wallet", $customerId), [
                'headers' => $this->getHeader(),
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao gerar carteira cripto: ", 500);
        }
    }

    // ? WITHDRAW
    /**
     * Método usado para solicitar um saque
     * @param float $amount - Valor de deseja sacar
     * @param string|Customer $customer - Dados do cliente ou ID do cliente já criado anteriormente
     * @param MethodCurrency $methodCurrency - Método de saque, ex: PIX
     * @param PixKeyParam $pixKey - Chave Pix - { key: "...", type: "PHONE" | "CPF" | "CNPJ" | "EMAIL" | "RANDOM" }
     * @return Withdraw
     */
    public function withdrawFiat(float $amount, string|Customer $customer, MethodCurrency $methodCurrency, PixKeyParam $pixKey): Withdraw
    {
        $this->verifyLogged();

        $currencies = $this->getCurrenciesWithdraw();
        $currency = array_filter($currencies, fn($c) => $c['type'] === $methodCurrency);
        $currency = array_values($currency)[0] ?? null;

        if (!$currency) {
            throw new XGateError(new Error(), sprintf("Moeda %s não habilitada na sua conta", $methodCurrency), 400);
        }

        $customerId = "";

        if (!is_string($customer)) {
            $resCustomerCreate = $this->customerCreate($customer);
            $customerId = $resCustomerCreate->customer->_id ?? null;
        } else {
            $customerId = $customer;
        }

        $pixKetEnd = null;
        $pixKeyverify = $this->verifyEpecificPixKeyInCustomerWithResultReverse($customerId, $pixKey);

        if ($pixKeyverify) {
            $pixKetEnd = $pixKeyverify;
        } else {
            $pixKetEnd = ($this->pixKeyCreate($customerId, $pixKey))->key;
        }

        try {
            $response = $this->api->post('/withdraw', [
                'json' => [
                    'amount' => $amount,
                    'customerId' => $customerId,
                    'currency' => $currency,
                    'pixKey' => $pixKetEnd
                ],
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao solicitar saque: ", 500);
        }
    }
    /**
     * Método usado para solicitar um saque convertendo cripto moeda para moeda fiduciária.
     * @param float $amount - Valor de deseja sacar
     * @param string|Customer $customer - Dados do cliente ou ID do cliente já criado anteriormente
     * @param MethodCryptocurrency $methodCryptocurrency - Cripto moeda para conversão, ex: USDT
     * @param MethodCurrency $methodCurrency - Moeda base, ex: PIX
     * @param PixKeyParam $pixKey - Chave Pix - { key: "...", type: "PHONE" | "CPF" | "CNPJ" | "EMAIL" | "RANDOM" }
     * @return Withdraw
     */
    public function withdrawConversionCryptoToFiat(float $amount, string|Customer $customer, MethodCryptocurrency $methodCryptocurrency, MethodCurrency $methodCurrency, PixKeyParam $pixKey): Withdraw
    {
        $this->verifyLogged();

        $currencies = $this->getCurrenciesWithdraw();
        $currency = array_values(array_filter($currencies, fn($c) => $c['type'] === $methodCurrency))[0] ?? null;

        $cryptos = $this->getCryptocurrenciesDeposit();
        $crypto = array_values(array_filter($cryptos, fn($c) => $c['name'] === $methodCryptocurrency))[0] ?? null;

        if (!$currency) {
            throw new XGateError(
                new Error(), 
                sprintf("Moeda %s não habilitada na conta", $methodCurrency),
                400
            );
        }
        if (!$crypto) {
            throw new XGateError(
                new Error(), 
                sprintf("Cripto moeda %s não habilitada na conta", $methodCryptocurrency),
                400
            );
        }

        $customerId = "";

        if (!is_string($customer)) {
            $resCustomerCreate = $this->customerCreate($customer);
            $customerId = $resCustomerCreate->customer->_id ?? null;
        } else {
            $customerId = $customer;
        }

        $pixKetEnd = null;
        $pixKeyverify = $this->verifyEpecificPixKeyInCustomerWithResultReverse($customerId, $pixKey);

        if ($pixKeyverify) {
            $pixKetEnd = $pixKeyverify;
        } else {
            $pixKetEnd = ($this->pixKeyCreate($customerId, $pixKey))->key;
        }


        try {
            $response = $this->api->post('/withdraw', [
                'json' => [
                    'amount' => $amount,
                    'customerId' => $customerId,
                    'currency' => $currency,
                    'cryptocurrency' => $crypto,
                    'pixKey' => $pixKetEnd
                ],
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao solicitar saque com conversão: ", 500);
        }
    }
    /**
     * Método usado para solicitar um saque convertendo cripto moeda para moeda fiduciária.
     * @param float $amount - Valor de deseja sacar
     * @param string|Customer $customer - Dados do cliente ou ID do cliente já criado anteriormente
     * @param MethodBlockchain $methodBlockchain - Rede blockchain, ex: USDT
     * @param MethodCryptocurrency $methodCryptocurrency - Cripto moeda, ex: PIX
     * @param string $walletkey - Chave pública, ex: 0x12***********
     * @return Withdraw
     */
    public function withdrawExternalWallet(
        float $amount, 
        string|Customer $customer, 
        MethodBlockchain $methodBlockchainNetwork, 
        MethodCryptocurrency $methodCryptocurrency,
        string $walletKey
    ): Withdraw
    {
        $this->verifyLogged();

        $blockchains = $this->getBlockchainWithdraw();
        $blockchain = array_filter($blockchains, fn($c) => $c->name === $methodBlockchainNetwork);

        if (!count($blockchain, COUNT_RECURSIVE)) {
            throw new XGateError(
                new Error(), 
                sprintf("Rede blockchain %s não habilitada na sua conta", 
                $methodBlockchainNetwork
            ), 
            400
            );
        }

        $blockchain = array_values($blockchain)[0];

        // Filtra as criptomoedas dentro da blockchain
        $cryptocurrencies = array_filter($blockchain->cryptocurrencies, function ($item) use ($methodCryptocurrency) {
            return $item->cryptocurrency->name === $methodCryptocurrency;
        });

        if (!count($cryptocurrencies, COUNT_RECURSIVE)) {
            throw new XGateError(
                new Error(), 
                sprintf("Cripto moeda %s não habilitada na conta", $methodCryptocurrency),
                400
            );
        }

        $cryptocurrency = array_values($cryptocurrencies)[0];

        if ($cryptocurrency->minWithdraw > $amount) {
            throw new XGateError(
                new Error(), 
                sprintf("Saque mínimo de %s na Rede %s é de %s %s", $methodCryptocurrency, $methodBlockchainNetwork, $cryptocurrency->minWithdraw, $methodCryptocurrency),
                400
            );
        }

        $customerId = "";

        if (!is_string($customer)) {
            $resCustomerCreate = $this->customerCreate($customer);
            $customerId = $resCustomerCreate->customer->_id ?? null;
        } else {
            $customerId = $customer;
        }

        try {
            $response = $this->api->post('/withdraw', [
                'json' => [
                    'amount' => $amount,
                    'customerId' => $customerId,
                    'blockchainNetwork' => $blockchain,
                    'cryptocurrency' => $cryptocurrency->cryptocurrency,
                    'wallet' => $walletKey
                ],
                'headers' => $this->getHeader(),
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao solicitar saque para carteira externa: ", 500);
        }
    }

    // ? PIX
    /**
     * Método usado para criar uma chave pix para o cliente
     * @param string|Customer $customer - Dados do cliente ou ID do cliente já criado anteriormente
     * @param PixKeyParam $body - Chave Pix - { key: "...", type: "PHONE" | "CPF" | "CNPJ" | "EMAIL" | "RANDOM" }
     * @return PixKeyCreate
     */
    public function pixKeyCreate(string|Customer $customer, PixKeyParam $body): PixKeyCreate
    {
        $this->verifyLogged();

        try {
            $customerId = "";

            if (!is_string($customer)) {
                $resCustomerCreate = $this->customerCreate($customer);
                $customerId = $resCustomerCreate->customer->_id ?? null;
            } else {
                $customerId = $customer;
            }

            $response = $this->api->post("/pix/customer/{$customerId}/key", [
                'json' => $body,
                'headers' => $this->getHeader(),
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao criar chave pix para um cliente", 500);
        }
    }
    /**
     * Método usado para buscar todas as chaves pix do cliente
     * @param string|Customer $customer - Dados do cliente ou ID do cliente já criado anteriormente
     * @return PixKey[]
     */
    public function pixKeyGetAll(string|Customer $customer)
    {
        $this->verifyLogged();

        try {
            $customerId = "";

            if (!is_string($customer)) {
                $resCustomerCreate = $this->customerCreate($customer);
                $customerId = $resCustomerCreate->customer->_id ?? null;
            } else {
                $customerId = $customer;
            }

            $response = $this->api->get("/pix/customer/{$customerId}/key", [
                'headers' => $this->getHeader(),
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao buscar chaves pix de um cliente", 500);
        }
    }
    /**
     * Método usado para deletar uma chave pix do cliente
     * @param string $customerId - ID do cliente já criado anteriormente
     * @param string $pixKeyId - ID da chave pix do cliente
     * @return Message
     */
    public function pixKeyDelete(string $customer, string $pixKeyId): Message
    {
        $this->verifyLogged();

        try {
            $customerId = "";

            if (!is_string($customer)) {
                $resCustomerCreate = $this->customerCreate($customer);
                $customerId = $resCustomerCreate->customer->_id ?? null;
            } else {
                $customerId = $customer;
            }

            $response = $this->api->delete("/pix/customer/{$customerId}/key/remove/{$pixKeyId}", [
                'headers' => $this->getHeader(),
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new XGateError($e, "Erro ao deletar chave pix de um cliente", 500);
        }
    }
}
