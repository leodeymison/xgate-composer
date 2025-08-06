<?php

class Login {
    public string $token;

    public function __construct(string $token) {
        $this->token = $token;
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