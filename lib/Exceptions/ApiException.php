<?php

namespace Exceptions;

class ApiException extends \Exception {
    use \Dependencies\Injection;
    private string $statusHeader;
    private mixed $details;
    private object $headersHdl;

    public function __construct(string $message, mixed $details = null, string $statusHeader = 'badRequest')
    {
        parent::__construct($message);
        $this->details = $details;
        $this->statusHeader = $statusHeader;
        $this->headersHdl = self::getDepInstance('headersHandler');
    }

    public function getDetails(): mixed {
        return $this->details;
    }

    public function send()
    {
        $this
            ->headersHdl
            ->{$this->statusHeader}()
            ->jsonContent()
            ->send();
        echo json_encode([
            'error' => $this->message,
            'details' => $this->details
        ]);
        die();
    }
}
