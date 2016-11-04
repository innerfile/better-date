<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

class BetterDate {
  
  /**
   * @var String $original
   *  The date value passed into the constructor.
   */
  private $original = '';
  
  /**
   * @var String $type
   *  The name of the format used to convert the date passed in to the 
   *  constructor ($this->original) to a DateTime object ($this->dateTime).
   *
   * $type is the array key in config/types.php.
   */
  private $type = '';
  
  /**
   * @var String $format
   *  The format used to convert the date passed in to the constructor 
   *  ($this->original) to a DateTime object ($this->dateTime).
   *
   * $format is the array value in config/types.php.
   */
  private $format = '';
  
  /**
   * @var String $timeZone
   *  The timezone used to convert the date passed in to the constructor
   *  ($this->original) to a DateTime object ($this->dateTime).
   *
   * The default for $timeZone should be set in config/settings.php, not in
   *  this file, which should not be modified.
   */
  private $timeZone = '';
  
  /**
   * @var DateTime $dateTime
   *  The DateTime object used, and modified, throughout the life of this object.
   *
   * This value was created from $this->original, using $this->format and
   *  $this->timeZone.
   */
  private $dateTime;
  
  /**
   * @var Array $defaults
   *
   * [
   *   'TimeZone' => $timeZone,
   *   'Format' => 'Y-m-d',
   * ]
   */
  private $defaults = [];

  /**
   * @param String $date
   * @param String|Bool $timeZone
   * @param String|Bool $format
   */
  public function __construct($date, $timeZone = FALSE, $format = FALSE) {
    $this->defaults = require('config/defaults.php');
    $this->original = $date;
    $this->timeZone = $timeZone ? $timeZone : $this->defaults['TimeZone'];
    if (!$this->setDateTime($format))
      throw new Exception('Failure to create new BetterDate object. Please check config/types.php to ensure you have the format set up to match: ' . $date);
  }

  public function result($format = FALSE) {
    $format = $format ? $format : $this->format;
    return $this->dateTime->format($format);
  }
  
  public function original($format = FALSE) {
    if ($format) {
      $formattedOriginal = new self($this->original, $this->timeZone);
      return $formattedOriginal->result($format);
    }
    return $this->original;
  }
  
  public function dateTime() {
    return $this->dateTime;
  }

  public function isBefore($milestone) {
    return $this->dateTime() < self::convert($milestone)->dateTime();
  }

  public function isAfter($milestone) {
    return $this->dateTime() > self::convert($milestone)->dateTime();
  }

  public function addDays($days) {
    $this->dateTime = $this->dateTime->add(new DateInterval('P' . $days . 'D'));
    return $this;
  }
  
  public function subtractDays($days) {
    $this->dateTime = $this->dateTime->sub(new DateInterval('P' . $days . 'D'));
    return $this;
  }
  
  public function age() {
    return date('md', date('U', mktime(0, 0, 0, $this->month(), $this->day(), $this->year()))) > date('md')
      ? (date('Y') - $this->year()) - 1
      : date('Y') - $this->year();
  }
  
  public function year($result = '4 digits') {
    switch ($result) {
      case '4 digits':
        return $this->result('Y');
      case '2 digits':
        return $this->result('y');
    }
  }
  
  public function day($result = 'leading zero') {
    switch ($result) {
      case 'leading zero':
        return $this->result('d');
      case 'no leading zero':
        return $this->result('j');
      case 'name':
        return $this->result('l');
      default:
        return 'ERROR, invalid day result format: ' . $result;
    }
  }
  
  public function month($result = 'leading zero') {
    switch ($result) {
      case 'leading zero':
        return $this->result('m');
      case 'no leading zero':
        return $this->result('n');
      case 'abbreviation':
        return $this->result('M');
      case 'name':
        return $this->result('F');
      default:
        return 'ERROR, invalid month result format: ' . $result;
    }
  }

  private function setDateTime($format) {
    if ($format)
      return $this->setDateTimeUsingFormat($format);
    else
      return $this->setDateTimeUsingConfig();
  }

  private function setDateTimeUsingFormat($format) {
    return  $this->createDateTime($format, 'Format passed in via constructor.');
  }

  private function setDateTimeUsingConfig() {
    foreach (require('config/types.php') as $type => $format)
      if ($this->createDateTime($format, $type))
        return TRUE;
  }

  private function createDateTime($format, $type) {
    if (DateTime::createFromFormat($format, $this->original) !== FALSE) {
      $this->dateTime = DateTime::createFromFormat($format, $this->original, new DateTimeZone($this->timeZone));
      $this->type = $type;
      $this->format = $format;
      return TRUE;
    }
  }
  
  private static function convert($milestone) {
    return is_a($milestone, 'BetterDate') ? $milestone : new self($milestone);
  }

}
