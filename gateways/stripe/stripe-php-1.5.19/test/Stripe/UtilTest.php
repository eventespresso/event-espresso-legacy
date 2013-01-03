<?php

class Stripe_UtilTest extends UnitTestCase
{
  public function testIsList()
  {
    $list = array(5, 'nstaoush', array());
    $this->assertTrue(Espresso_Stripe_Util::isList($list));

    $notlist = array(5, 'nstaoush', array(), 'bar' => 'baz');
    $this->assertFalse(Espresso_Stripe_Util::isList($notlist));
  }

  public function testArrayClone()
  {
    try {
      Espresso_Stripe_Util::arrayClone(1);
      $this->assertFalse(true);
    } catch (Espresso_Stripe_Error $e) {
      $this->assertTrue(true);
    }
  }
}
