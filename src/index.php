<?php

require 'vendor/autoload.php';

// Types
require_once __DIR__ . '/types/account.php';
require_once __DIR__ . '/types/response.php';
require_once __DIR__ . '/types/token.php';

// Modules
require_once __DIR__ . '/modules/Currencies.php';
require_once __DIR__ . '/modules/Errors.php';
require_once __DIR__ . '/modules/Http.php';

use GuzzleHttp\Exception\RequestException;

class XGate {
    protected string $url;
    protected Account $account;
    protected Http $api;
    protected ?Access $access = null;

    public function __construct(Account $account) {
        $this->url = 'https://api.xgateglobal.com';
        $this->account = $account;
        $this->api = new Http($this->url);
    }

    private function login(): Login {
        try {
            $response = $this->api->post('/auth/token', $this->account);
            $data = $response->data;

            $this->access = new Access(
                time() * 1000 + 1000 * 60 * 60 * 24 * 2,
                $data->token
            );

            return $data;
        } catch (RequestException $error) {
            throw new XGateError($error, "Erro ao acessar conta", 500);
        }
    }

    protected function getHeader(): array {
        return [
            'Authorization' => 'Bearer ' . ($this->access?->token ?? ''),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];
    }

    protected function verifyLogged(): mixed {
        if (!$this->access) {
            $this->login();
            return true;
        }
    
        if ($this->access->expirate < time() * 1000) {
            $this->login();
            return true;
        }
    
        return true;
    }
}