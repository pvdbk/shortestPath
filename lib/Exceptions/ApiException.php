<?php

namespace Exceptions;

class ApiException extends \Exception {
    use \Dependencies\Injection;
    private string $statusHeader;
    private mixed $details;

    public function __construct(string $message, mixed $details = null, string $statusHeader = 'badRequest')
    {
        parent::__construct($message);
        $this->details = $details;
        $this->statusHeader = $statusHeader;
    }

    public function getDetails() {
        return $this->details;
    }

    public function send()
    {
        $this
            ->getDepInstance('headersHandler')
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
