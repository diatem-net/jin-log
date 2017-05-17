<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\Log;

use Jin2\FileSystem\File;

/**
 * Permet l'écriture de logs
 */
class Log
{

  /**
   * @var boolean  Service actif / inactif
   */
  protected static $enabled = false;

  /**
   * @var string Chemin d'accès relatif ou absolu au fichier de logs
   */
  protected static $logFilePath = '';

  /**
   * Active les logs
   *
   * @param  string  $writePath  Chemin relatif ou absolu du fichier dans lequel écrire les logs
   * @return boolean             Activation réussie ou non
   * @throws \Exception
   */
  public static function enableLogging($writePath)
  {
    if (is_file($writePath)) {
      self::$enabled = true;
      self::$logFilePath = $writePath;
      return true;
    }
    throw new \Exception('Impossible d\'activer les logs dans le fichier '.$writePath.' : celui ci n\'existe pas.');
    return false;
  }

  /**
   * Désactive les logs
   */
  public static function disableLogging()
  {
    self::$enabled = false;
  }

  /**
   * Ecrire dans les logs
   *
   * @param string $output  Ligne à écrire
   */
  public static function write($output)
  {
    if (self::$enabled) {
      $d = new \DateTime();
      $f = new File(self::$logFilePath, true);
      $f->write($d->format('d/m/Y H:i:s') . ' - ' . $output . "\n", true);
    }
  }

  /**
   * éinitialise le fichier de logs
   */
  public static function clear()
  {
    if (self::$enabled) {
      $f = new File(self::$logFilePath, true);
      $f->write('', false);
    }
  }

  /**
   * Modifie la destination des logs
   *
   * @param  string $writePath
   * @return boolean
   * @throws \Exception
   */
  protected static function setPath($writePath)
  {
    if (is_file($writePath)) {
      self::$logFilePath = $writePath;
      return true;
    }
    throw new \Exception('Impossible de modifier la destination des logs : le fichier '.$writePath.' n\'existe pas.');
    return false;
  }

}
