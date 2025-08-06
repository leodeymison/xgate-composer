<?php

class XGateError extends Error
{
    public int $status;
    public object $originalError;

    public string $name;

    public function __construct(object $error, string $messageDefault, int $defaultStatus)
    {
        // Tenta pegar a mensagem do erro aninhado
        $message = $messageDefault;

        $messageExternal = json_decode($error->getResponse()->getBody()->getContents())->message;

        if($messageExternal){
            $message = $messageExternal;
        }

        // Chama o construtor pai com a mensagem encontrada
        parent::__construct($message);

        // Define o nome da exceção
        $this->name = "XGateError";

        // Define o status, se existir no erro, senão usa o padrão
        $statusExternal = $error->getResponse()->getStatusCode();
        if ($statusExternal) {
            $this->status = $statusExternal;
        }

        // Armazena o erro original
        $this->originalError = $error;
    }
}