<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\Log;

/**
 * Boite d'analyse des performances
 */
class PerfAnalyser
{

  /**
   * @var array ableau des points de contrôle enregistrés
   */
  private $points = array();

  /**
   * Constructeur (instancie un premier point de contrôle, débute l'analyse)
   */
  public function __construct()
  {
    $this->_addPoint('[START]');
  }

  /**
   * Ajoute un point de contrôle sur lequel analyser le temps écoulé depuis l'instanciation de la classe
   */
  public function addPoint()
  {
    $this->_addPoint();
  }

  /**
   * Ajoute techniquement le point de contrôle
   *
   * @param  string  $entete (optional) En-tête affiché ('[POINT]' par défaut)
   */
  private function _addPoint($entete = '[POINT]')
  {
    $trace = debug_backtrace();
    $time = round(microtime(true) * 1000);
    if (count($this->points) == 0) {
      $elapsed = 0;
    } else {
      $elapsed = $time - $this->points[0]['time'];
    }
    $point = array(
      'time' => $time,
      'entete' => $entete,
      'contexte' => $trace[1]['file'] . ' ligne ' . $trace[1]['line'],
      'elapsed' => $elapsed
    );
    $this->points[] = $point;
  }

  /**
   * Génère et affiche un rapport de performances. (Ajoute un dernier point de contrôle qui clôt l'analyse)
   *
   * @param string $titre  (optional) Titre du rapport ('Rapport de performance' par défaut)
   */
  public function renderTimeReport($titre = 'Rapport de performance')
  {
    if (Debug::getEnabled() || Config::get('reportTimeEnabled')) {
      $this->_addPoint('[STOP]');
      $diff = $this->points[count($this->points) - 1]['time'] - $this->points[0]['time'];

      $dump = '';

      $c = count($this->points);
      for ($i = 0; $i < $c; $i++) {
        $dump .= sprintf(
          '<div class="dump_segment_content_point"><b>%s</b> %s :: <b>%s ms</b></div>',
          $this->points[$i]['entete'],
          $this->points[$i]['contexte'],
          $this->points[$i]['elapsed']
        );
      }

      $dump .= sprintf(
        '<div class="dump_segment_content_pointend"><b>TOTAL : %s ms</b></div>',
        $diff
      );

      print Debug::getCustomTrace($titre, array(array('name' => 'Points d\'analyse', 'content' => $dump)));
    }
  }

  /**
   * Retourne le temps total d'execution en milisecondes
   *
   * @return integer
   */
  public function getTotalTimeInMS()
  {
    return $this->points[count($this->points) - 1]['time'] - $this->points[0]['time'];
  }

}
