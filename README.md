<div align="center">

  <h1>XGATE</h1>

<img src="https://img.shields.io/badge/license-Apache%202-blue" />

</div>

## O que a XGate?

Uma solução moderna para pagamentos e conversões financeiras. Uma plataforma robusta para transações PIX e Cripto, perfeita para otimizar operações financeiras.

- Processamento instantâneo via PIX.
- Conversão automática de moeda FIAT para cripto.
- Depósitos e saques em Bitcoin, Ethereum, SHIBA INU, USDT, USDC, BNB e MATIC.
- Monitoramento em tempo real via dashboard avançado.

## Instalação

```
composer require xgate/xgate-integration dev-production
```

## Manual

### Iniciar integração:

Crie um instância da classe `Xgate` para ter acesso aos métodos.

```php
require 'vendor/xgate/xgate-integration/src/index.php';

try {
  $xgate = new XGate(
    new Account(
      'nameemail@domain.com',
      '**************'
    )
  );

  ...
} catch (err) {
  return err;
}
```

### Errors:

```js
// catch
{
  message: "...",
  name: "XGateError",
  status: 400, // exemplo
  originalError: {...}
}
```

### 👉 DEPÓSITO usando moeda fiduciária:

```js
try {
  return $xgate->depositFiat(
    10,
    new Customer("Nome do cliente", "00000000000") ?? "1a0********", // {...} or ID
    MethodCurrency::PIX
  );
} catch (err) {
  return err;
}
```

- **PARÂMETRO 1:** Valor de depósito
- **PARÂMETRO 2:** Informações do cliente ou ID do cliente que já existe
  | parâmetro | Obrigatório | Tipo | Descrição
  |------------|-------|--------|------|
  | name | SIM | `String` | Nome do cliente |
  | phone | NÃO | `String` | Telefone do cliente |
  | email | NÃO | `String` | E-mail do cliente |
  | document | SIM | `String` | Algum documento do cliete, ex: CPF |

- **PARÂMETRO 3:** Tipo de transação

Resposta:

```js
{
  message: "****";
  data: {
    status: "PENDING";
    code: "0002012684001****";
    id: "12s1****";
    customerId: "01asd1****";
  }
}
```

<br />

### 👉 DEPÓSITO convertendo moeda fiduciária para cripto moeda:

```js
try {
  return $xgate->depositConversionFiatToCrypto(
    10,
    new Customer("Nome do cliente", "00000000000") ?? "1a0********", // {...} or ID
    MethodCurrency::PIX,
    MethodCryptocurrency::USDT
  );
} catch (err) {
  return err;
}
```

- **PARÂMETRO 1:** Valor de depósito
- **PARÂMETRO 2:** Informações do cliente ou ID do cliente que já existe
  | parâmetro | Obrigatório | Tipo | Descrição
  |------------|-------|--------|------|
  | name | SIM | `String` | Nome do cliente |
  | phone | NÃO | `String` | Telefone do cliente |
  | email | NÃO | `String` | E-mail do cliente |
  | document | SIM | `String` | Algum documento do cliete, ex: CPF |

- **PARÂMETRO 3:** Tipo de transação
- **PARÂMETRO 3:** Para qual cripto moeda quer converter

Resposta:

```js
{
  message: "****";
  data: {
    status: "PENDING";
    code: "0002012684001****";
    id: "12s1****";
    customerId: "01asd1****";
  }
}
```

<br />

### 👉 DEPÓSITO de cripto moeda (Gerar uma carteira)

```js
try {
  return $xgate->depositGenerateCryptoWallet(
    new Customer("Nome do cliente", "00000000000") ?? "1a0********" // {...} or ID
  );
} catch (err) {
  return err;
}
```

- **PARÂMETRO 1:** Informações do cliente ou ID do cliente que já existe
  | parâmetro | Obrigatório | Tipo | Descrição
  |------------|-------|--------|------|
  | name | SIM | `String` | Nome do cliente |
  | phone | NÃO | `String` | Telefone do cliente |
  | email | NÃO | `String` | E-mail do cliente |
  | document | SIM | `String` | Algum documento do cliete, ex: CPF |

Resposta:

```js
[
  {
    blockchainNetworks: ["Ethereum", "ERC-20", "BEP-20"];
    publicKey: "0xE5****";
  },
  ...
]
```

**OBSERVAÇÃO:** Não é gerado uma solicitação de depósito para cripto moeda, apenas a carteira onde o cliente pode transferir o valor que ele desejar.

<br />

### 👉 DEPÓSITO de cripto moeda convertendo para moeda fiduciária

❌ Não temos essa opção disponível no momento.

### 👉 SAQUE usando moeda fiduciária:

```js
try {
  return $xgate->withdrawFiat(
    10,
    new Customer("Nome do cliente", "00000000000") ?? "1a0********", // {...} or ID
    MethodCurrency::PIX,
    new PixKeyParam("00000000000", PixKeyParamType::CPF)
  );
} catch (err) {
  return err;
}
```

- **PARÂMETRO 1:** Valor de saque
- **PARÂMETRO 2:** Informações do cliente ou ID do cliente que já existe
  | parâmetro | Obrigatório | Tipo | Descrição
  |------------|-------|--------|------|
  | name | SIM | `String` | Nome do cliente |
  | phone | NÃO | `String` | Telefone do cliente |
  | email | NÃO | `String` | E-mail do cliente |
  | document | SIM | `String` | Algum documento do cliete, ex: CPF |

- **PARÂMETRO 3:** Tipo de transação
- **PARÂMETRO 4:** Informações da chave pix
  | parâmetro | Obrigatório | Tipo | Descrição
  |------------|-------|--------|------|
  | key | SIM | `String` | Chave pix |
  | type | SIM | `String` | Tipo da chave, opções: `PHONE`, `CPF`, `CNPJ`, `EMAIL`, `RANDOM` |

Resposta:

```js
{
  message: "******";
  status: "********";
  _id: "as1*****";
}
```

<br />

### 👉 SAQUE convertendo cripto moeda para moeda fiduciária:

```js
try {
  return $xgate->withdrawConversionCryptoToFiat(
    10,
    new Customer("Nome do cliente", "00000000000") ?? "1a0********", // {...} or ID
    MethodCryptocurrency::USDT,
    MethodCurrency::PIX,
    new PixKeyParam("00000000000", PixKeyParamType::CPF)
  );
} catch (err) {
  return err;
}
```

- **PARÂMETRO 1:** Valor de saque
- **PARÂMETRO 2:** Informações do cliente ou ID do cliente que já existe
  | parâmetro | Obrigatório | Tipo | Descrição
  |------------|-------|--------|------|
  | name | SIM | `String` | Nome do cliente |
  | phone | NÃO | `String` | Telefone do cliente |
  | email | NÃO | `String` | E-mail do cliente |
  | document | SIM | `String` | Algum documento do cliete, ex: CPF |
- **PARÂMETRO 3:** Cripto moeda
- **PARÂMETRO 4:** Para qual forma de pagamento você quer receber (Converter)
- **PARÂMETRO 5:** Informações da chave pix
  | parâmetro | Obrigatório | Tipo | Descrição
  |------------|-------|--------|------|
  | key | SIM | `String` | Chave pix |
  | type | SIM | `String` | Tipo da chave, opções: `PHONE`, `CPF`, `CNPJ`, `EMAIL`, `RANDOM` |

Resposta:

```js
{
  message: "******";
  status: "********";
  _id: "as1*****";
}
```

<br />

### 👉 SAQUE para carteira externa:

```js
try {
  return $xgate->withdrawExternalWallet(
    10,
    new Customer("Nome do cliente", "00000000000") ?? "1a0********", // {...} or ID
    MethodBlockchain::BEP20,
    MethodCryptocurrency::USDT,
    "0xff*****"
  );
} catch (err) {
  return err;
}
```

- **PARÂMETRO 1:** Valor de saque
- **PARÂMETRO 2:** Informações do cliente ou ID do cliente que já existe
  | parâmetro | Obrigatório | Tipo | Descrição
  |------------|-------|--------|------|
  | name | SIM | `String` | Nome do cliente |
  | phone | NÃO | `String` | Telefone do cliente |
  | email | NÃO | `String` | E-mail do cliente |
  | document | SIM | `String` | Algum documento do cliete, ex: CPF |
- **PARÂMETRO 3:** Rede blockchain
- **PARÂMETRO 4:** Cripto moeda
- **PARÂMETRO 5:** Chave pública da carteira que vai receber a transferência

Resposta:

```js
{
  message: "******";
  status: "********";
  _id: "as1*****";
}
```

<br />

### 👉 SAQUE convertendo moeda fiduciária para cripto moeda

❌ Não temos essa opção disponível no momento.

### 👉 BUSCAR lista de moedas fiduciária disponíveis para depósito

```js
try {
  return $xgate->getCurrenciesDeposit();
} catch (err) {
  return err;
}
```

Resposta:

```js
[
  {
    _id: "sw2****";
    name: "********";
    type: "********";
    createdDate: "********";
    updatedDate: "********";
    __v: 0;
    symbol: "********";
  },
  ...
]
```

<br />

### 👉 BUSCAR lista de moedas fiduciária disponíveis para saques

```js
try {
  return $xgate->getCurrenciesWithdraw();
} catch (err) {
  return err;
}
```

Resposta:

```js
[
  {
    _id: "sw2****";
    name: "********";
    type: "********";
    createdDate: "********";
    updatedDate: "********";
    __v: 0;
    symbol: "********";
  },
  ...
]
```

<br />

### 👉 BUSCAR lista de cripto moedas disponíveis para depósito

```js
try {
  return $xgate->getCryptocurrenciesDeposit();
} catch (err) {
  return err;
}
```

Resposta:

```js
[
  {
    _id: "sw2****";
    name: "********";
    symbol: "******";
    coinGecko: "******";
    createdDate: "********";
    updatedDate: "********";
    __v: 0;
  },
  ...
]
```

<br />

### 👉 BUSCAR lista de cripto moedas disponíveis para saque

```js
try {
  return $xgate->getCryptocurrenciesWithdraw();
} catch (err) {
  return err;
}
```

Resposta:

```js
[
  {
    _id: "sw2****";
    name: "********";
    symbol: "******";
    coinGecko: "******";
    createdDate: "********";
    updatedDate: "********";
    __v: 0;
  },
  ...
]
```

<br />

### 👉 BUSCAR redes blockchain disponíveis para depósito e suas cripto moedas suportadas

```js
try {
  return $xgate->getBlockchainDeposit();
} catch (err) {
  return err;
}
```

Resposta:

```js
[
  {
    _id: "1Q******";
    name: "********";
    symbol: "********";
    coinGecko: "********";
    updatedDate: "********";
    createdDate: "********";
    __v: 0;
  },
  ...
]
```

<br />

### 👉 BUSCAR redes blockchain disponíveis para saque e suas cripto moedas suportadas

```js
try {
  return $xgate->getBlockchainWithdraw();
} catch (err) {
  return err;
}
```

Resposta:

```js
[
  {
    _id: "1Q******";
    name: "********";
    symbol: "********";
    coinGecko: "********";
    updatedDate: "********";
    createdDate: "********";
    __v: 0;
  },
  ...
]
```

<br />

### 👉 COTAÇÃO de depósito convertendo moeda fiduciária para cripto moeda

```js
try {
  return $xgate->getQuotationDepositFiatToCrypto(
    10,
    MethodCurrency::PIX,
    MethodCryptocurrency::USDT
  );
} catch (err) {
  return err;
}
```

- **PARÂMETRO 1:** Valor de depósito
- **PARÂMETRO 3:** Tipo de transação
- **PARÂMETRO 3:** Para qual cripto moeda quer converter

Resposta:

```js
{
  amount: 10;
  crypto: "******";
}
```

<br />

### 👉 COTAÇÃO de depósito convertendo cripto moeda para moeda fiduciária

❌ Não temos essa opção disponível no momento.

<br />

### 👉 COTAÇÃO de saque convertendo cripto moeda para moeda fiduciária

```js
try {
  return $xgate->getQuotationWithdrawCryptoToFiat(
    10,
    MethodCryptocurrency::USDT,
    MethodCurrency::PIX
  );
} catch (err) {
  return err;
}
```

- **PARÂMETRO 1:** Valor de saque
- **PARÂMETRO 3:** Cripto moeda
- **PARÂMETRO 3:** Para qual forma de pagamento você quer receber (Converter)

Resposta:

```js
{
  amount: 10;
  crypto: "******";
}
```

<br />

### 👉 COTAÇÃO de saque convertendo moeda fiduciária para cripto moeda

❌ Não temos essa opção disponível no momento.

<br />

### 👉 COTAÇÃO de saque para um carteira de cripto moeda externa

```js
try {
  return $xgate->getQuotationWithdrawExternalWallet(
    10,
    MethodBlockchain::BEP20,
    MethodCryptocurrency::USDT
  );
} catch (err) {
  return err;
}
```

- **PARÂMETRO 1:** Valor de saque
- **PARÂMETRO 3:** Rede blockchain
- **PARÂMETRO 3:** Cripto moeda (A rede blockchain precisa suportar essa cripto moeda)

Resposta:

```js
{
  amount: 10;
}
```

<br />

### 👉 CLIENTE: Criar um cliente

```js
try {
  return $xgate->customerCreate(
    new Customer(
      "Nome do cliente",
      "00000000000"
    )
  );
} catch (err) {
  return err;
}
```

- **PARÂMETRO 1:** Informações do cliente
  | parâmetro | Obrigatório | Tipo | Descrição
  |------------|-------|--------|------|
  | name | SIM | `String` | Nome do cliente |
  | phone | NÃO | `String` | Telefone do cliente |
  | email | NÃO | `String` | E-mail do cliente |
  | document | SIM | `String` | Algum documento do cliete, ex: CPF |

Resposta:

```js
{
  message: "********";
  customer: {
    _id: "********";
  }
}
```

<br />

### 👉 CLIENTE: Atualizar informações do cliente

```js
try {
  return $xgate->customerUpdate(
    "********",
    new Customer("Nome do cliente", "00000000000")
  );
} catch (err) {
  return err;
}
```

- **PARÂMETRO 1:** ID do cliente existente
- **PARÂMETRO 2:** Informações do cliente
  | parâmetro | Obrigatório | Tipo | Descrição
  |------------|-------|--------|------|
  | name | SIM | `String` | Nome do cliente |
  | phone | NÃO | `String` | Telefone do cliente |
  | email | NÃO | `String` | E-mail do cliente |
  | document | SIM | `String` | Algum documento do cliete, ex: CPF |

Resposta:

```js
{
  message: "********";
  customer: {
    _id: "********";
  }
}
```

<br />

### 👉 PIX: Criar uma chave pix para um clientes

```js
try {
  return $xgate->pixKeyCreate(
    new Customer("Nome do cliente", "00000000000") ?? "1a0********", // {...} or ID
    new PixKeyParam(
      "00000000000",
      PixKeyParamType::CPF
    )
  );
} catch (err) {
  return err;
}
```

- **PARÂMETRO 1:** Informações do cliente ou ID do cliente existente
  | parâmetro | Obrigatório | Tipo | Descrição
  |------------|-------|--------|------|
  | name | SIM | `String` | Nome do cliente |
  | phone | NÃO | `String` | Telefone do cliente |
  | email | NÃO | `String` | E-mail do cliente |
  | document | SIM | `String` | Algum documento do cliete, ex: CPF |

- **PARÂMETRO 2:** Informações da chave pix do cliente
  | parâmetro | Obrigatório | Tipo | Descrição
  |------------|-------|--------|------|
  | key | SIM | `String` | Nome do cliente |
  | type | SIM | `String` | Tipo da chave, opções: `PHONE`, `CPF`, `CNPJ`, `EMAIL`, `RANDOM` |

Resposta:

```js
{
  message: "********";
  key: "********";
}
```

<br />

### 👉 PIX: Buscar todas as chaves pix de um cliente

```js
try {
  return $xgate->pixKeyGetAll(
    new Customer("Nome do cliente", "00000000000") ?? "1a0********", // {...} or ID
  );
} catch (err) {
  return err;
}
```

- **PARÂMETRO 1:** Informações do cliente ou ID do cliente existente
  | parâmetro | Obrigatório | Tipo | Descrição
  |------------|-------|--------|------|
  | name | SIM | `String` | Nome do cliente |
  | phone | NÃO | `String` | Telefone do cliente |
  | email | NÃO | `String` | E-mail do cliente |
  | document | SIM | `String` | Algum documento do cliete, ex: CPF |

Resposta:

```js
[
  {
    key: "00000000000";
    type: "CPF";
    _id: "********";
  }
]
```

<br />

### 👉 PIX: Deletar uma chave pix do cliente

```js
try {
  return $xgate->pixKeyDelete("*******", "*******");
} catch (err) {
  return err;
}
```

- **PARÂMETRO 1:** ID do cliente
- **PARÂMETRO 2:** ID da chave pix do cliente

Resposta:

```js
{
  message: "********";
}
```

<br />

### 👉 EMPRESA: Buscar saldo da empresa

#### # Busca todas as moedas fiduciárias e cripto moedas

```js
try {
  return $xgate->getBalance();
} catch (err) {
  return err;
}
```

Resposta:

```js
[
  {
    currency: {
      name: "******";
      type: "******";
    };
    totalAmount: 10;
    totalHeld: 10;
  },
  {
    cryptocurrency: {
      name: "******";
      symbol: "******";
    };
    totalAmount: 10;
  },
  ...
]
```

#### # Busca uma moeda fiduciária específica

```js
try {
  return $xgate->getBalance(new CurrencyBalance("********"));
} catch (err) {
  return err;
}
```

- **PARÂMETRO 1:** ID da moeda fiduciária

Resposta:

```js
[
  {
    currency: {
      name: "******";
      type: "******";
    };
    totalAmount: 10;
    totalHeld: 10;
  },
  ...
]
```

OBSERVAÇÃO: Pode ser o ID tanto da moeda fiduciária de **saque** como a de **depósito**

#### # Busca uma cripto moeda específica

```js
try {
  return $xgate->getBalance(new CryptoBalance("********"));
} catch (err) {
  return err;
}
```

- **PARÂMETRO 1:** ID da cripto moeda

Resposta:

```js
[
  {
    cryptocurrency: {
      name: "******";
      symbol: "******";
    };
    totalAmount: 10;
  },
  ...
]
```

OBSERVAÇÃO: Pode ser o ID tanto da cripto moeda de **saque** como a de **depósito**

<br />

### 👉 SUB EMPRESA: Criar sub empresa

```js
try {
  $currencies = $xgate->getCurrenciesDeposit();

  $xgate->createSubCompany(
    new SubCompanyCreate(
      new User(
        "Meu primeiro usuário",
        "email@domain.com",
        "********",
        new Phone(
          PhoneType::mobile,
          "900000000",
          "11",
          "55"
        )
      ),
      new SubCompanyOption(
        [
          new SubCompanyOptionCurrency(
            $currencies[0],
            new Fee(
              FeeType::PERCENTAGE,
              10
            )
          )
        ],
        new Fee(
          FeeType::PERCENTAGE,
          10
        ),
        new Fee(
          FeeType::PERCENTAGE,
          10
        )
      ),
      new SubCompanyOption(
        new Fee(
          FeeType::PERCENTAGE,
          10
        ),
        new Fee(
          FeeType::PERCENTAGE,
          10
        ),
        new Fee(
          FeeType::PERCENTAGE,
          10
        )
      )
    )
  );
} catch (err) {
  return err;
}
```

**OBSERVAÇÃO:** Um objecto único com os seguites parâmetros:

- **PARÂMETRO 1:** `user`: Informações do primeiro usuário (ADMIN) da sub conta

  - `name`:
    | OBRIGATÓRIO | `String` | Nome do usuário |
    |-------------|----------|-----------------|
  - `email`:
    | OBRIGATÓRIO | `String` | E-mail do usuário |
    |-------------|----------|-----------------|
  - `password`:
    | OBRIGATÓRIO | `String` | Senha do usuário |
    |-------------|----------|-----------------|
  - `phone`:
    | OBRIGATÓRIO | `String` | Object com inforções do telefone do usuário |
    |-------------|----------|-----------------|
    - `type`:
      | OBRIGATÓRIO | `String` | Tipo do número. Opções: `mobile` |
      |-------------|----------|-----------------|
    - `number`:
      | OBRIGATÓRIO | `String` | Número de telefon |
      |-------------|----------|-----------------|
    - `areaCode`:
      | OBRIGATÓRIO | `String` | Código do estado |
      |-------------|----------|-----------------|
    - `countryCode`:
      | OBRIGATÓRIO | `String` | Código do país |
      |-------------|----------|-----------------|

- **PARÂMETRO 2 e 3:** `deposit` e `withdraw`: Informações de taxas de depósito e saque
  Tanto no `deposit` como no `withdraw` tem os parâmetros `currencies`, `cryptocurrencies` e `blockchainNetworks`.

  - `currencies`: Moedas fiduciárias
  - `cryptocurrencies`: Cripto moedas
  - `blockchainNetworks`: Redes blockchains

  E cada uma delas tem a opção de mandar um object `{ type: "PERCENTAGE", value: 1, }`, isso significa que toda a categoria terão o mesmo valor de taxa. Exemplo:

  ```js
    withdraw: {
      currencies: {
        type: "PERCENTAGE",
        value: 1,
      },
      ...
    },
    ...
  ```

  **OBSERVAÇÃO:** A palavra 'categoria' pode-se se entender que é `currencies`, `cryptocurrencies` e `blockchainNetworks`

  Isso significa que todas as moedas fiduciárias de saque terão uma taxa de 1%, isso server para todos. Agora se você quiser definir uma porcentagem para cada moeda fiduciárias diferente, você precisa buscar a listagem de todas as moedas (Isso serve tembém para as outras categorias). Exemplo:

  Cenário hipotético: Adicionando um valor de dinâmico para cada cripto moeda de depósito

  ```js
  const cryptocurrencies =
    await xgate.cryptocurrencies.getCryptocurrenciesDeposit();
  return await xgate.subCompany.createSubCompany({
    deposit: {
      cryptocurrencies: cryptocurrencies.map((item, index) => ({
        cryptocurrency: item._id || item,
        fee: {
          type: "PERCENTAGE",
          value: 1 + index,
        },
      })),
    },
  });
  ```

  Sempre que usar essa opção, adicione um object com dois parâmetros, o 1°: o nome da categoria no singular e o 2°: `fee`, onde será adicionado um object com os dois parâmetros, `type` = Tipo de taxa e `value` = Valor da taxa. No primeiro parâmetro você pode adicionar tanto o **ID da moeda** como o **object completo**.

  Na "vida real", você pode criar um painel visual para adicionar essas taxas diferentes ou apenas criar um object fixo definindo a taxa de cada categoria e moedas.

Resposta:

```js
{
  message: "********";
}
```

<br />

### 👉 SUB EMPRESA: Adicionar o primeiro IP da sub empresa

⚠️ _Essa rota adiciona o primeiro IP da sub empresa, caso tente adicionar um segundo IP, retornará um error._

```js
try {
  $xgateSubCompany = new XGate(
    new Account(
      'nameemail@domain.com',
      '**************'
    )
  );
  return await $xgate->subCompanyAddFirstIP(
    "0000:0000:0000:0000:0000:0000:0000:0000"
  );
} catch (err) {
  return err;
}
```

Primeiro é criado um nova instância da classe `Xgate` e é passada as informações de acesso da sub conta.
**OBSERVAÇÃO**: Todos os métodos chamados nessa intância, afetará a sub empresa e não a empresa principal.

- **PARÂMETRO 1:** `IP`, Aceitas IPV4 e IPV6

Resposta:

```js
{
  message: "********";
}
```

<br />

### 👉 SUB EMPRESA: Adicionar o primeiro Webhook da sub empresa

⚠️ _Essa rota adiciona a primeira rota de Webhook da sub empresa, caso tente adicionar um segundo Webhook, retornará um error._

```js
try {
  $xgateSubCompany = new XGate(
    new Account(
      'nameemail@domain.com',
      '**************'
    )
  );
  return $xgate->subCompanyAddFirstWebhook(
    new Webhook(
      "https://www.mydomain.com/webhook",
      "Primeiro Webhook Test"
    )
  );
} catch (err) {
  return err;
}
```

Primeiro é criado um nova instância da classe `Xgate` e é passada as informações de acesso da sub conta.
**OBSERVAÇÃO**: Todos os métodos chamados nessa intância, afetará a sub empresa e não a empresa principal.

Um object com os seguintes parâmetros:

- **PARÂMETRO 1:** `externalWebhookUrl`: URL do webhook
- **PARÂMETRO 2:** `name`: Nome para identificação do webhook

Resposta:

```js
{
  message: "********";
}
```

<br />
