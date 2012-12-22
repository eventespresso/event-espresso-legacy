<?php

class Stripe_TokenTest extends UnitTestCase
{
  public function testUrls()
  {
    $this->assertEqual(Espresso_Stripe_Token::classUrl('Stripe_Token'), '/tokens');
    $token = new Espresso_Stripe_Token('abcd/efgh');
    $this->assertEqual($token->instanceUrl(), '/tokens/abcd%2Fefgh');
  }
}