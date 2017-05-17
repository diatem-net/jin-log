<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\Log;

use Jin2\Assets\AssetsInterface;
use Jin2\FileSystem\File;

/**
 * Outils de debuggage/dump
 */
class Debug implements AssetsInterface
{

  /**
   *  @var boolean  Etat d'activation de l'affichage des instructions de debug
   */
  protected static $activated = true;

  /**
   *  @var boolean  Premier trace/dump envoyé dans la sortie navigateur. (Pour affichage css nécessaire)
   */
  protected static $firstDump = false;

  /**
   * Implements getAssetUrl function
   *
   * @param string $key
   * @return string
   */
  public static function getAssetUrl($key)
  {
    $root =  __DIR__
      . DIRECTORY_SEPARATOR .'..'
      . DIRECTORY_SEPARATOR .'..'
      . DIRECTORY_SEPARATOR .'assets/';
    switch ($key) {
      case 'js':
        return $root . 'js/debug.js';
      case 'css':
        return $root . 'css/debug.css';
    }
    return null;
  }

  /**
   * Implements getAssetContent function
   *
   * @param string $key
   * @return string
   */
  public static function getAssetContent($key)
  {
    if ($url = static::getAssetUrl($key)) {
      return file_get_contents($url, FILE_USE_INCLUDE_PATH);
    }
    return null;
  }

  /**
   * Affiche le dump de la structure d'une variable
   *
   * @param  mixed   $var    Variable à tracer
   */
  public static function dump($var)
  {
    if (self::$activated) {
      print static::getDump($var);
    }
  }

  /**
   * Retourne le dump de la structure d'une variable
   *
   * @param  mixed   $var  Variable à tracer
   * @return string        Dump formaté HTML de la structure
   * @todo Tests sur les classes SGBD à refaire
   */
  public static function getDump($var)
  {
    // Trace
    $trace = debug_backtrace();

    // Initialise la sortie
    $dump = '';

    // Header
    $dump .= static::getHeader('Dump de variable');

    // Contexte
    $dump .= '<div class="dump_segment">Contexte</div>';
    if (count($trace) > 2) {
      $dump .= static::getContext($trace[1], $trace[2]);
    } else {
      $dump .= static::getContext($trace[1]);
    }

    // Requête SQL
    if (is_object($var) && get_class($var) == 'sylab\common\sgbd\Query') {
      $dump .= '<div class="dump_segment">Requête SQL</div>';
      $dump .= '<div class="dump_segment_content"><pre>' . $var->getSql() . '</pre></div>';
    }

    // Exploration
    $dump .= '<div class="dump_segment">Exploration de la variable</div>';
    $dump .= '<div class="dump_segment_content"><pre>';
    if (is_object($var) && get_class($var) == 'sylab\framework\query\Query') {
      $dump .= static::getDumpQueryResult($var->getQueryResults());
    } elseif (is_object($var) && get_class($var) == 'sylab\framework\query\QueryResult') {
      $dump .= static::getDumpQueryResult($var);
    } else {
      $dump .= static::getDumpContent($var);
    }
    $dump .= '</pre></div>';

    // Footer
    $dump .= static::getFooter();
    return $dump;
  }

  /**
   * Retourne le dump d'une query
   *
   * @param  Query $var  Instance de Jin2\query\Query
   * @return string      Code HTML du dump de la Query
   */
  public static function getDumpQueryResult($var)
  {
    $header = true;
    $dump = '<table cellpadding=5 cellspacing=0>';
    $i = 1;
    foreach ($var as $ligne) {
      // Affichage du header
      if ($header) {
        $dump .= '<tr>';
        foreach ($ligne as $key => $value) {
          if (!is_numeric($key)) {
            $dump .= '<th>' . $key . '</th>';
          }
        }
        $dump .= '</tr>';
        $header = false;
      }
      // Affichage des données
      $class = '';
      if (!($i % 2)) {
        $class = 'highlight';
      }
      $dump .= '<tr class="' . $class . '">';
      foreach ($ligne as $key => $value) {
        if (!is_numeric($key)) {
          if ($value != '') {
            $dump .= '<td>' . $value . '</td>';
          } else {
            $dump .= '<td>&nbsp;</td>';
          }
        }
      }
      $dump .= '</tr>';
      $i++;
    }
    $dump .= '</table>';
    return $dump;
  }

  /**
   * Retourne le dump d'une variable standard
   *
   * @param  type $var  Variable à dumper
   * @return string     Dump formatté HTML
   */
  protected static function getDumpContent($var)
  {
    $dump = '<div class="dump_segment_content_main">';
      $dump .= '<div class="dump_variable">';
        $dump .= sprintf('<ul>%s</ul>', static::dumpElement($var));
      $dump .= '</div>';
    $dump .= '</div>';
    return $dump;
  }

  /**
   * Dump d'un élément (fonction recursive)
   *
   * @param  mixed   $var   Variable à tracer
   * @param  string  $name  Nom de l'élément ('' par défaut)
   * @return string         Dump de la variable
   */
  protected static function dumpElement($var, $name = '')
  {
    $dump = '';

    // On récupère le type de variable
    $type = gettype($var);

    // Is parcourable
    $iterable = $type == 'object' || $type == 'array';

    // Affichage des informations sur l'élément courant
    $dump .= '<li>';
    $dump .= '<div class="dump_item">';

    if ($name !== "") {
      $dump .= '<div class="dump_name">' . $name . '</div><div class="dump_pleinseparator">&nbsp;</div>';
    }
    $dump .= '<div class="dump_type">' . $type . '</div>';
    if ($type == 'string') {
      $dump .= '<div class="dump_separator">&nbsp;</div><div class="dump_size">(' . strlen($var) . ' caractères)</div>';
    } else if ($type == 'object') {
      $dump .= '<div class="dump_separator">&nbsp;</div><div class="dump_size">' . get_class($var) . '</div>';
    } else if ($type == 'array') {
      $dump .= '<div class="dump_separator">&nbsp;</div><div class="dump_size">(' . count($var) . ' éléments)</div>';
    }
    $dump .= '<div class="dump_clear"></div>';
    $dump .= '</div>';

    // Affichage du contenu
    if ($type == 'string') {
      $dump .= '<div class="dump_item_content"><pre>' . htmlspecialchars($var) . '</pre></div>';
    } else if ($type == 'boolean') {
      $dump .= '<div class="dump_item_content"><pre>' . ($var ? 'TRUE' : 'FALSE') . '</pre></div>';
    } else if ($type == 'object') {
      $dump .= '';
    } else if ($type == 'array') {
      $dump .= '<div class="dump_item_content"></div>';
    } else {
      $dump .= '<div class="dump_item_content"><pre>' . $var . '</pre></div>';
    }

    // On parcourt les éléments iterables
    if ($iterable) {
      $dump .= '<ul>';
      foreach ($var as $key => $e) {
        $dump .= static::dumpElement($e, $key);
      }
      $dump .= '</ul>';
    }

    // Fermeture balise élément
    $dump .= '</li>';
    return $dump;
  }

  /**
   * Trace la pile d'execution
   */
  public static function trace()
  {
    if (self::$activated) {
      print static::getTrace();
    }
  }

  /**
   * Retourne le contenu d'un trace de la pile d'execution
   *
   * @return string  Contenu formaté de la pile d'execution
   */
  public static function getTrace()
  {
    // Trace
    $trace = debug_backtrace();

    // Initialise la sortie
    $dump = '';

    // Header
    $dump .= static::getHeader('Trace du contexte');

    // Contexte
    $dump .= '<div class="dump_segment">Contexte</div>';

    $nb = count($trace);
    for ($i = 1; $i < $nb; $i++) {
      if ($i < $nb - 1) {
        $dump .= static::getContext($trace[$i], $trace[$i + 1]);
      } else {
        $dump .= static::getContext($trace[$i]);
      }
    }

    // Footer
    $dump .= static::getFooter();
    return $dump;
  }

  /**
   * Permet de rendre un contenu sous la forme d'un trace personnalisé
   *
   * @param  string $titre    Titre de la fenêtre
   * @param  string $content  Contenu à afficher
   * @return string           Contenu formatté HTML
   */
  public static function getCustomTrace($titre, $content)
  {
    // Initialise la sortie
    $dump = '';

    // Header
    $dump .= static::getHeader($titre);

    // Onglets
    foreach ($content as $onglet) {
      $dump .= sprintf('<div class="dump_segment">%s</div>', $onglet['name']);
      $dump .= sprintf('<div class="dump_segment_content">%s</div>', $onglet['content']);
    }

    // Footer
    $dump .= static::getFooter();
    return $dump;
  }

  /**
   * Retourne la balise style pour l'affichage des dump/trace
   *
   * @return string  Contenu style
   */
  protected static function getStyle()
  {
    $style = static::getAssetContent('css');
    return sprintf('<style>%s</style>', $style);
  }

  /**
   * Retourne la balise script pour l'affichage des dump/trace
   *
   * @return string  Contenu script
   */
  protected static function getScript()
  {
    $script = static::getAssetContent('js');
    return sprintf('<script language="javascript">%s</script>', $script);
  }

  /**
   * Retourne la trace d'un contexte
   *
   * @param  array    $contexte   Contexte à analyser
   * @param  array    $rcontexte  Contexte servant à analyser la méthode courante (contexte à n+1)
   * @return string               Contexte formaté
   */
  public static function getContext($contexte, $rcontexte = NULL)
  {
    $dump = '';

    $segmentId = uniqid();
    $dump .= '<div class="dump_segment_content">';
    $dump .= '<div onclick="javascript:debugOpenClose(\'' . $segmentId . '\');" class="dump_segment_content_header" id="dump_segment_' . $segmentId . '">';
    $dump .= '<b>' . $contexte['file'] . '</b> ligne ' . $contexte['line'] . '</div>';
    $dump .= '<div class="dump_segment_content_main" style="display:none;" id="dump_segment_content_' . $segmentId . '">';

    if (!is_null($rcontexte) && isset($rcontexte['function']) && $rcontexte['function'] != '') {
      $dump .= '<div class="dump_segment_content_context">';
      if (isset($rcontexte['class'])) {
        $dump .= '<div class="dump_segment_content_context_line"><b>classe :</b> ' . $rcontexte['class'] . '</div>';
      }
      $dump .= '<div class="dump_segment_content_context_line"><b>méthode :</b> ' . $rcontexte['function'] . "(";

      if (isset($rcontexte['class'])) {
        // Cas d'une méthode
        $rm = new \ReflectionMethod($rcontexte['class'], $rcontexte['function']);
        $argsname = $rm->getParameters();
        $args = $rcontexte['args'];

        $nb = count($argsname);
        for ($i = 0; $i < $nb; $i++) {
          $ap = $argsname[$i];
          if ($i > 0) {
            $dump .= ', ';
          }
          $dump .= $argsname[$i]->name;
        }
      } else {
        // Cas d'une fonction
        $args = $rcontexte['args'];

        $nb = count($args);
        for ($i = 0; $i < $nb; $i++) {
          if ($i > 0) {
            $dump .= ', ';
          }
          $dump .= $i . '=\'' . $args[$i] . '\'';
        }
      }
      $dump .= ')</div>';
      $dump .= '</div>';
    }

    $file = new File($contexte['file']);
    $index = $contexte['line'] - 5;
    if ($index < 0) {
      $index = 0;
    }
    $fileLines = $file->getLines($index, 11, false);

    $nb = count($fileLines);
    for ($i = 0; $i < $nb; $i++) {
      if ($index == $contexte['line'] - 1) {
        $dump .= '<div class="dump_file_line dump_file_line_selected">';
      } else {
        $dump .= '<div class="dump_file_line">';
      }

      $dump .= '<div class="dump_file_line_number">' . ($index + 1) . '</div><div class="dump_file_line_content">' . htmlspecialchars($fileLines[$i]) . '</div>';
      $index++;
      $dump .= '</div>';
    }
    $dump .= '<div class="clear"></div>';
    $dump .= '</div>';
    $dump .= '</div>';

    return $dump;
  }

  /**
   * Retourne un header formaté d'une opération de debug/trace
   *
   * @param  string   $titre  (optional) Titre de la fenêtre (Vide par défaut)
   * @return string           Contenu formaté
   */
  protected static function getHeader($titre = NULL)
  {
    $dump = '';
    if (!self::$firstDump) {
      $dump .= static::getStyle();
      $dump .= static::getScript();
    }
    $dump .= '<div class="dump_container">';
    if ($titre) {
      $dump .= '<div class="dump_title">' . $titre . '</div>';
    }
    return $dump;
  }

  /**
   * Retourne un footer formaté d'une opération de debug/trace
   *
   * @return string  Contenu formaté
   */
  protected static function getFooter()
  {
    return '</div>';
  }

  /**
   * Active ou désactive les instructions de debug
   *
   * @param  boolean $etat   Etat d'activation
   */
  public static function setEnabled($etat)
  {
    self::$activated = $etat;
  }

  /**
   * Retourne le statut d'activation du debug
   *
   * @return boolean Etat d'activation
   */
  public static function getEnabled()
  {
    return self::$activated;
  }

}
