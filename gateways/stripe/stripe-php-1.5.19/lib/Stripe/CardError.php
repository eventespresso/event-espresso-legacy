<?php

class Espresso_Stripe_CardError extends Espresso_Stripe_Error
{
  public function __construct($message, $param, $code)
  {
    parent::__construct($message);
    $this->param = $param;
    $this->code = $code;
  }
}
