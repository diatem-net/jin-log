<?php

/**
* Jin Framework
* Diatem
*/

namespace Jin2\Log;

/**
 * Outils de notification à travers une session
*/
class Notifier
{

  /**
   * @var string Clé pour le stockage des notifications en session
   */
  const COOKIE_NAME = 'jin_notifications';

  /**
   * @var string Statut standard pour les notifications
   */
  const STATUS_NOTICE = 'notice';

  /**
   * @var string Statut d'erreur pour les notifications
   */
  const STATUS_ERROR = 'error';

  /**
   * @var string Statut d'avertissement pour les notifications
   */
  const STATUS_WARNING = 'warning';

  /**
   * @var string Statut de succès pour les notifications
   */
  const STATUS_SUCCESS = 'success';

  /**
   * Vérifie que le stockage en session existe
  */
  private static function checkStorage()
  {
    if( !isset($_COOKIE) || !isset($_COOKIE[self::COOKIE_NAME]) ) {
      static::clear();
    }
  }

  /**
   * Ajoute une(des) notification(s) dans la pile
   *
   * @param  mixed   $notif    Notification(s) à stocker
   * @param  string  $status   Statut de la notification (optionel, uniquement si on ajoute une seule notification)
  */
  public static function push($notif, $status = self::STATUS_NOTICE)
  {
    static::checkStorage();
    if( is_string($notif) ) {
      $notif = array(array(
        'status'  => $status,
        'message' => $notif
      ));
    }
    $notifs = unserialize($_COOKIE[self::COOKIE_NAME]);
    foreach ($notif as $object) {
      if( !isset($object['message']) || !is_string($object['message']) ) {
        continue;
      }
      if( !isset($object['status']) || !in_array($object['status'], array(self::STATUS_NOTICE, self::STATUS_ERROR, self::STATUS_WARNING, self::STATUS_SUCCESS)) ) {
        $object['status'] = self::STATUS_NOTICE;
      }
      array_push($notifs, (object)$object);
    }
    setcookie(self::COOKIE_NAME, serialize($notifs), time()+180, "/");
  }

  /**
   * Récupère la dernière notification de la pile
   *
   * @return object
  */
  public static function pull()
  {
    static::checkStorage();
    $notifs = unserialize($_COOKIE[self::COOKIE_NAME]);
    $firstnotif = array_shift($notifs);
    setcookie(self::COOKIE_NAME, serialize($notifs), time()+180, "/");
    return $firstnotif;
  }

  /**
   * Récupère toutes les notifications de la pile
   *
   * @return array
  */
  public static function pullAll()
  {
    static::checkStorage();
    $notifs = unserialize($_COOKIE[self::COOKIE_NAME]);
    static::clear();
    return $notifs;
  }

  /**
   * Supprime toutes les notifications de la pile
   *
   * @return array
  */
  public static function clear()
  {
    setcookie(self::COOKIE_NAME, serialize(array()), time()+180, "/");
  }

}
