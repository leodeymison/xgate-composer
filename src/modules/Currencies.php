<?php

use GuzzleHttp\Exception\RequestException;

class Currencies extends XGate {
    public function getCurrenciesDeposit() {
        call_user_func($this->verifyLogged());

        try {
            $headers = $this->getHeader();

            $response = $this->api->get('/currencies/deposit', $headers);
            return $response->data;
        } catch (RequestException $error) {
            throw new Exception("Erro ao buscar moedas de depósito: " . $error->getMessage());
        }
    }
}