<?php

class Espresso_Stripe_Invoice extends Espresso_Stripe_ApiResource
{
  public static function constructFrom($values, $apiKey=null)
  {
    $class = get_class();
    return self::_scopedConstructFrom($class, $values, $apiKey);
  }

  public static function retrieve($id, $apiKey=null)
  {
    $class = get_class();
    return self::_scopedRetrieve($class, $id, $apiKey);
  }

  public static function all($params=null, $apiKey=null)
  {
    $class = get_class();
    return self::_scopedAll($class, $params, $apiKey);
  }

  public static function upcoming($params=null, $apiKey=null)
  {
    $requestor = new Espresso_Stripe_ApiRequestor($apiKey);
    $url = self::classUrl(get_class()) . '/upcoming';
    list($response, $apiKey) = $requestor->request('get', $url, $params);
    return Espresso_Stripe_Util::convertToStripeObject($response, $apiKey);
  }
}
