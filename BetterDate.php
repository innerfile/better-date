<?php

class BetterDate {
  
  /**
   * @var String $original
   *  The date value passed into the constructor.
   */
  private $original = '';
  
  /**
   * @var String $formatName
   *  The name of the format used to convert the date passed in to the 
   *  constructor ($this->original) to a DateTime object ($this->dateTime).
   *
   * $type is the array key in config/types.php.
   */
  private $formatName = '';
  
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

  public function format($format = FALSE) {
    $format = $format ? $format : $this->format;
    return $this->dateTime->format($format);
  }
  
  public function original($format = FALSE) {
    if ($format) {
      $formattedOriginal = new self($this->original, $this->timeZone);
      return $formattedOriginal->format($format);
    }
    return $this->original;
  }
  
  public function dateTime() {
    return $this->dateTime;
  }

  public function isBefore($date) {
    return $this->dateTime() < self::convert($date)->dateTime();
  }
  
  public function isOnOrBefore($date) {
    return $this->dateTime() <= self::convert($date)->dateTime();
  }

  public function isAfter($date) {
    return $this->dateTime() > self::convert($date)->dateTime();
  }

  public function isOnOrAfter($date) {
    return $this->dateTime() >= self::convert($date)->dateTime();
  }
  
  public function isSameDateAs($date) {
    return $this->format('Y-m-d') === self::convert($date)->format('Y-m-d');
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
  
  public function day($formatName = 'leading zero') {
    return $this->formatOrError('day', $formatName, [
      'leading zero' => 'd',
      'no leading zero' => 'j',
      'name' => 'l',
    ]);
  }

  public function month($formatName = 'leading zero') {
    return $this->formatOrError('month', $formatName, [
      'leading zero' => 'm',
      'no leading zero' => 'n',
      'abbreviation' => 'M',
      'name' => 'F',
    ]);
  }

  public function year($formatName = '4 digits') {
    return $this->formatOrError('year', $formatName, [
      '4 digits' => 'Y',
      '2 digits' => 'y',
    ]);
  }
  
  private function formatOrError($name, $formatName, $formats) {
    if(!isset($formats[$formatName]))
      return 'ERROR, invalid ' . $type . ' format: ' . $formatName;
    return $this->format($formats[$formatName]);
  }

  private function setDateTime($format) {
    return $format ? $this->setDateTimeUsingFormat($format) : $this->setDateTimeUsingConfig();
  }

  private function setDateTimeUsingFormat($format) {
    return  $this->createDateTime($format, 'Format passed in via constructor.');
  }

  private function setDateTimeUsingConfig() {
    foreach (require('config/types.php') as $formatName => $format)
      if ($this->createDateTime($format, $formatName))
        return TRUE;
  }

  private function createDateTime($format, $formatName) {
    if (DateTime::createFromFormat($format, $this->original) !== FALSE) {
      $this->dateTime = DateTime::createFromFormat($format, $this->original, new DateTimeZone($this->timeZone));
      $this->formatName = $formatName;
      $this->format = $format;
      return TRUE;
    }
  }
  
  private static function convert($date) {
    return is_a($date, 'BetterDate') ? $date : new self($date);
  }

}
