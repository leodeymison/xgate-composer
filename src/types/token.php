<?php

class Access {
  public int $expirate;
  public string $token;

  public function __construct(int $expirate, string $token) {
    $this->expirate = $expirate;
    $this->token = $token;
  }
}