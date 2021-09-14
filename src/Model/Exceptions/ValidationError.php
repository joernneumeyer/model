<?php

  namespace Neu\Model\Exceptions;

  use Exception;
  use Throwable;

  class ValidationError extends Exception {
    public function __construct(public array $errors, $message = "", $code = 0, Throwable $previous = null) {
      parent::__construct($message, $code, $previous);
    }
  }
