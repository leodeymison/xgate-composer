<?php

class Login {
    public string $token;

    public function __construct(string $token) {
        $this->token = $token;
    }
}

class Message {
    public string $message;

    public function __construct(string $message) {
        $this->message = $message;
    }
}

class CurrencyBalance {
    public string $currencyId;

    public function __construct(string $currencyId) {
        $this->currencyId = $currencyId;
    }
}

class CryptoBalance {
    public string $cryptocurrencyId;

    public function __construct(string $cryptocurrencyId) {
        $this->cryptocurrencyId = $cryptocurrencyId;
    }
}

class Currency {
    public string $_id;
    public string $name;
    public string $type;
    public string $createdDate;
    public string $updatedDate;
    public int $__v;
    public string $symbol;

    public function __construct(
        string $_id,
        string $name,
        string $type,
        string $createdDate,
        string $updatedDate,
        int $__v,
        string $symbol
    ){
        $this->_id = $_id;
        $this->name = $name;
        $this->type = $type;
        $this->createdDate = $createdDate;
        $this->updatedDate = $updatedDate;
        $this->__v = $__v;
        $this->symbol = $symbol;
    }
}

class Cryptocurrency {
    public string $_id;
    public string $name;
    public string $symbol;
    public string $coinGecko;
    public string $createdDate;
    public string $updatedDate;
    public int $__v;

    public function __construct(
        string $_id,
        string $name,
        string $symbol,
        string $coinGecko,
        string $createdDate,
        string $updatedDate,
        int $__v,
    ){
        $this->_id = $_id;
        $this->name = $name;
        $this->symbol = $symbol;
        $this->coinGecko = $coinGecko;
        $this->createdDate = $createdDate;
        $this->updatedDate = $updatedDate;
        $this->__v = $__v;
    }
}

class BlockchainDepositWithoutCryptocurrencies {
    public string $_id;
    public string $name;
    public string $chainId;
    public string $createdDate;
    public string $updatedDate;
    public int $__v;

    public function __construct(
        string $_id,
        string $name,
        string $chainId,
        string $createdDate,
        string $updatedDate,
        int $__v,
    ){
        $this->_id = $_id;
        $this->name = $name;
        $this->chainId = $chainId;
        $this->createdDate = $createdDate;
        $this->updatedDate = $updatedDate;
        $this->__v = $__v;
    }
}

class BlockchainDeposit {
    public string $_id;
    public string $name;
    public string $chainId;
    /** @var Cryptocurrency[] */
    public array $cryptocurrencies;
    public string $createdDate;
    public string $updatedDate;
    public int $__v;

    public function __construct(
        string $_id,
        string $name,
        string $chainId,
        /** @var Cryptocurrency[] */
        array $cryptocurrencies,
        string $createdDate,
        string $updatedDate,
        int $__v,
    ){
        $this->_id = $_id;
        $this->name = $name;
        $this->chainId = $chainId;
        $this->cryptocurrencies = $cryptocurrencies;
        $this->createdDate = $createdDate;
        $this->updatedDate = $updatedDate;
        $this->__v = $__v;
    }
}

class BlockchainWithdraw
{
    public string $_id;
    public string $name;
    public string $chainId;
    
    /** @var BlockchainWithdrawCryptocurrency[] */
    public array $cryptocurrencies = [];
    
    public string $updatedDate;
    public string $createdDate;
    public int $__v;

    public function __construct(
        string $_id,
        string $name,
        string $chainId,
        array $cryptocurrencies,
        string $updatedDate,
        string $createdDate,
        int $__v
    ) {
        $this->_id = $_id;
        $this->name = $name;
        $this->chainId = $chainId;
        $this->cryptocurrencies = $cryptocurrencies;
        $this->updatedDate = $updatedDate;
        $this->createdDate = $createdDate;
        $this->__v = $__v;
    }
}

class BlockchainWithdrawCryptocurrency
{
    public string $_id;
    public Cryptocurrency $cryptocurrency;
    public ?float $minWithdraw;

    public function __construct(
        string $_id,
        Cryptocurrency $cryptocurrency,
        ?float $minWithdraw = null
    ) {
        $this->_id = $_id;
        $this->cryptocurrency = $cryptocurrency;
        $this->minWithdraw = $minWithdraw;
    }
}

class BalanceCurrency {
    public BalanceCurrencyCurrency $currency;
    public Float $totalAmount;
    public Float $totalHeld;

    public function __construct(
        BalanceCurrencyCurrency $currency,
        Float $totalAmount,
        Float $totalHeld,
    ){
        $this->currency = $currency;
        $this->totalAmount = $totalAmount;
        $this->totalHeld = $totalHeld;
    }
}

class BalanceCurrencyCurrency {
    public string $name;
    public string $type;

    public function __construct(
        string $name,
        string $type,
    ){
        $this->name = $name;
        $this->type = $type;
    }
}

class BalanceCryptocurrency {
    public BalanceCryptocurrencyCryptocurrency $cryptocurrency;
    public float $totalAmount;
    public float $totalHeld;

    public function __construct(
        BalanceCryptocurrencyCryptocurrency $cryptocurrency,
        float $totalAmount,
        float $totalHeld,
    ){
        $this->cryptocurrency = $cryptocurrency;
        $this->totalAmount = $totalAmount;
        $this->totalHeld = $totalHeld;
    }
}

class BalanceCryptocurrencyCryptocurrency {
    public string $name;
    public string $type;

    public function __construct(
        string $name,
        string $type,
    ){
        $this->name = $name;
        $this->type = $type;
    }
}

class CreateCustomer {
    public CreateCustomerCustomer $customer;
    public string $message;

    public function __construct(
        CreateCustomerCustomer $customer,
        string $message,
    ){
        $this->customer = $customer;
        $this->message = $message;
    }
}

class CreateCustomerCustomer {
    public string $_id;

    public function __construct(
        string $_id,
    ){
        $this->_id = $_id;
    }
}

class Deposit {
    public DepositData $data;
    public string $message;

    public function __construct(
        DepositData $data,
        string $message,
    ){
        $this->data = $data;
        $this->message = $message;
    }
}

class DepositData {
    public string $status;
    public string $code;
    public string $id;
    public string $customerId;

    public function __construct(
        string $status,
        string $code,
        string $id,
        string $customerId,
    ){
        $this->status = $status;
        $this->code = $code;
        $this->id = $id;
        $this->customerId = $customerId;
    }
}

class Withdraw {
    public string $status;
    public string $message;
    public string $_id;

    public function __construct(
        string $status,
        string $message,
        string $_id,
    ){
        $this->status = $status;
        $this->message = $message;
        $this->_id = $_id;
    }
}

class QuotationCrypto {
    public float $amount;
    public string $crypto;

    public function __construct(
        float $amount,
        string $crypto,
    ){
        $this->amount = $amount;
        $this->crypto = $crypto;
    }
}

class QuotationFiat {
    public float $amount;
    public string $currency;

    public function __construct(
        float $amount,
        string $currency,
    ){
        $this->amount = $amount;
        $this->currency = $currency;
    }
}

class QuotationAmount {
    public float $amount;

    public function __construct(
        float $amount,
    ){
        $this->amount = $amount;
    }
}

class Wallet {
    public array $blockchainNetworks;
    public string $publicKey;

    public function __construct(
        array $blockchainNetworks,
        string $publicKey,
    ){
        $this->blockchainNetworks = $blockchainNetworks;
        $this->publicKey = $publicKey;
    }
}

class PixKey {
    public string $key;
    public string $type;
    public string $_id;

    public function __construct(
        string $key,
        string $type,
        string $_id,
    ){
        $this->key = $key;
        $this->type = $type;
        $this->_id = $_id;
    }
}

class PixKeyCreate {
    public PixKey $key;
    public string $message;

    public function __construct(
        PixKey $key,
        string $message,
    ){
        $this->key = $key;
        $this->message = $message;
    }
}

enum FeeType: string
{
    case PERCENTAGE = 'PERCENTAGE';
}

class Fee
{
    public string $type;
    public float $value;
    public function __construct(FeeType $type, float $value) {
        $this->type = $type->value;
        $this->value = $value;
    }
}

class SubCompanyOption
{
    /** @var SubCompanyOptionCurrency[]|Fee */
    public array|Fee $currencies;
    /** @var SubCompanyOptionBlockchainNetwork[]|Fee */
    public array|Fee $blockchainNetworks;
    /** @var SubCompanyOptionCryptocurrency[]|Fee */
    public array|Fee $cryptocurrencies;

    public function __construct(
        array|Fee $currencies,
        array|Fee $blockchainNetworks,
        array|Fee $cryptocurrencies,
    ) {
        $this->currencies = $currencies;
        $this->blockchainNetworks = $blockchainNetworks;
        $this->cryptocurrencies = $cryptocurrencies;
    }
}
class SubCompanyOptionCryptocurrency {
    public Cryptocurrency $cryptocurrency;
    public Fee $fee;
    public function __construct(
        Cryptocurrency $cryptocurrency,
        Fee $fee,
    ) {
        $this->cryptocurrency = $cryptocurrency;
        $this->fee = $fee;
    }
}

class SubCompanyOptionBlockchainNetwork {
    public BlockchainDepositWithoutCryptocurrencies $blockchainNetwork;
    public Fee $fee;
    public function __construct(
        BlockchainDepositWithoutCryptocurrencies $blockchainNetwork,
        Fee $fee,
    ) {
        $this->blockchainNetwork = $blockchainNetwork;
        $this->fee = $fee;
    }
}

class SubCompanyOptionCurrency {
    public Currency $currency;
    public Fee $fee;
    public function __construct(
        Currency $currency,
        Fee $fee,
    ) {
        $this->currency = $currency;
        $this->fee = $fee;
    }
}

enum PhoneType: string
{
    case mobile = 'mobile';
}

class Phone {
    public PhoneType $type;
    public string $number;
    public string $areaCode;
    public string $countryCode;

    public function __construct(
        PhoneType $type,
        string $number,
        string $areaCode,
        string $countryCode,
    ){
        $this->type = $type;
        $this->number = $number;
        $this->areaCode = $areaCode;
        $this->countryCode = $countryCode;
    }
}

class SubCompanyCreate {
    public User $user;
    public SubCompanyOption $deposit;
    public SubCompanyOption $withdraw;

    public function __construct(
        User $user,
        SubCompanyOption $deposit,
        SubCompanyOption $withdraw,
    ){
        $this->user = $user;
        $this->deposit = $deposit;
        $this->withdraw = $withdraw;
    }
}

class User {
    public string $name;
    public string $email;
    public string $password;
    public Phone $phone;

    public function __construct(
        string $name,
        string $email,
        string $password,
        Phone $phone,
    ){
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->phone = $phone;
    }
}

class Webhook {
    public string $externalWebhookUrl;
    public string $name;

    public function __construct(
        string $externalWebhookUrl,
        string $name,
    ){
        $this->externalWebhookUrl = $externalWebhookUrl;
        $this->name = $name;
    }
}

class Customer {
    public string $name;
    public string $document;
    public ?string $email;
    public ?Phone $phone;

    public function __construct(
        string $name,
        string $document,
        ?string $email = null,
        ?Phone $phone = null,
    ){
        $this->name = $name;
        $this->email = $email;
        $this->document = $document;
        $this->phone = $phone;
    }
}
  
enum PixKeyParamType: string
  {
    case PHONE = 'PHONE';
    case CPF = 'CPF';
    case CNPJ = 'CNPJ';
    case EMAIL = 'EMAIL';
    case RANDOM = 'RANDOM';
  }
class PixKeyParam {
    public string $key;
    public string $type;

    public function __construct(
        string $key,
        PixKeyParamType $type,
    ){
        $this->key = $key;
        $this->type = $type->value;
    }
}

enum MethodCurrency: string
{
    case PIX = 'PIX';
}

enum MethodCryptocurrency: string
{
    case USDT = 'USDT';
}
enum MethodBlockchain: string
{
    case ETHEREUM = 'Ethereum';
    case ERC20 = 'ERC-20';

    case BEP20 = 'BEP-20';
    case POLYGON = 'Polygon';
}
