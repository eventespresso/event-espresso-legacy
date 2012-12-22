<?php

class Espresso_Stripe_InvalidRequestError extends Espresso_Stripe_Error
{
  public function __construct($message, $param)
  {
    parent::__construct($message);
    $this->param = $param;
  }
}
