<?php

class BetterDate {
  
  private $original = '';
  private $type = '';
  private $format = '';
  private $timeZone = '';
  private $dateTime;

  public function __construct($date, $timeZone = 'UTC') {
    $this->original = $date;
    $this->timeZone = $timeZone;
    $this->setDateTime();
  }

  public function result($format = FALSE) {
    $format = $format ? $format : $this->format;
    return $this->dateTime->format($format);
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
      default:
        return 'ERROR, invalid month result format: ' . $result;
    }
  }

  private function setDateTime() {
    foreach (require('config/types.php') as $type => $format)
      if (DateTime::createFromFormat($format, $this->original) !== FALSE) {
        $this->dateTime = DateTime::createFromFormat($format, $this->original, new DateTimeZone($this->timeZone));
        $this->type = $type;
        $this->format = $format;
        return TRUE;
      }
  }

}
