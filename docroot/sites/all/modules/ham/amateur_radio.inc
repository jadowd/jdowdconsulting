<?php

// function to calculate latitude from maidenhead grid locator
function loc2lat($locator) {
  
  $ascii = array("0" => 48, "1" => 49, "2" => 50, "3" => 51, "4" => 52, "5" => 53, "6" => 54, "7" => 55, "8" => 56, "9" => 57, ":" => 58, ";" => 59, "<" => 60, "=" => 61, ">" => 62, "?" => 63, "@" => 64, "A" => 65, "B" => 66, "C" => 67, "D" => 68, "E" => 69, "F" => 70, "G" => 71, "H" => 72, "I" => 73, "J" => 74, "K" => 75, "L" => 76, "M" => 77, "N" => 78, "O" => 79, "P" => 80, "Q" => 81, "R" => 82, "S" => 83, "T" => 84, "U" => 85, "V" => 86, "W" => 87, "X" => 88, "Y" => 89, "Z" => 90);
  
  $locator = strtoupper($locator);
  
  $lat = ($ascii[$locator[1]] - 65) * 10 + ($ascii[$locator[3]] - 48) + ($ascii[$locator[5]] - 65 + 0.5) / 24 - 90;

  return $lat;
}

// function to calculate longitude from maidenhead grid locator
function loc2lon($locator)
{
  $ascii = array("0" => 48, "1" => 49, "2" => 50, "3" => 51, "4" => 52, "5" => 53, "6" => 54, "7" => 55, "8" => 56, "9" => 57, ":" => 58, ";" => 59, "<" => 60, "=" => 61, ">" => 62, "?" => 63, "@" => 64, "A" => 65, "B" => 66, "C" => 67, "D" => 68, "E" => 69, "F" => 70, "G" => 71, "H" => 72, "I" => 73, "J" => 74, "K" => 75, "L" => 76, "M" => 77, "N" => 78, "O" => 79, "P" => 80, "Q" => 81, "R" => 82, "S" => 83, "T" => 84, "U" => 85, "V" => 86, "W" => 87, "X" => 88, "Y" => 89, "Z" => 90);;
  
  $locator = strtoupper($locator);
  
  $lon = ($ascii[$locator[0]] - 65) * 20 + ($ascii[$locator[2]] - 48) * 2 + ($ascii[$locator[4]] - 65 + 0.5) / 12 - 180;

  return $lon;
}

// fucntion that parses a single line from an ADIF logbook file
// high priority to properly comment this code
function parseADIF_Line($strInput) {
  $retVal = null;
  
  $valid_record = 0;
  
  $n = 0;
  
  while ($n < strlen($strInput)) {
    $tag = "";
    $length = "";
    $datatype = "";
    $value = "";
    while ($n < strlen($strInput) && $strInput[$n] != "<") {
      $n++;
    }
    $n++;
    while ($n < strlen($strInput) && $strInput[$n] != ">" && $strInput[$n] != ":" && $strInput[$n] != "<") {
      $tag .= $strInput[$n];
      $n++;
    }
    if ($n < strlen($strInput) && $strInput[$n] == ":") {
      $n++;
      while ($n < strlen($strInput) && $strInput[$n] != ">" && $strInput[$n] != ":" && $strInput[$n] != "<") {
        $length .= $strInput[$n];
        $n++;
      }
      $length = intval($length);
    }
    if ($n < strlen($strInput) && $strInput[$n] == ":") {
      $n++;
      while ($n < strlen($strInput) && $strInput[$n] != ">" && $strInput[$n] != ":" && $strInput[$n] != "<") {
        $datatype .= $strInput[$n];
        $n++;
      }
    }
    if ($n < strlen($strInput) && $strInput[$n] == ">") {
      $n++;
      for($y = 0; $y < $length; $y++) {
        $value .= $strInput[$n];
        $n++;
      }
    }
    
    $tmp = null;
    
    if (strlen($tag)) {
      if (strtolower($tag) == "eor") {
        $valid_record = true;
      }
      if (strlen($value)) {
        $tmp = $value;
      } 
      
      $retVal[$tag] = $tmp;
    }
    
    $n++;
    
  }
  
  if ($valid_record) {
    return $retVal;
  }
  return null;
}


// function that opens an ADIF logbook file and imports it in to a database table
// high priority to properly comment this code
// also high priority to make this efficiently import ADIF files without line breaks or with extra line breaks
function adif_import($filename) {
  
  $myHam = new ham();
  $handle = @fopen($filename, "r");
  if ($handle) {
    while (!feof($handle) || $x < 22) {
        $buffer = fgets($handle, 4096);
        $line = parseADIF_Line($buffer);
        if ($line)
        {
          if (isset($line['mode'])) {
            @$mode[$line['mode']]++;
          }
          
          $date = null;
          
          if (isset($line['qso_date']) && isset($line['time_on'])) {
            $date = date("Y-m-d H:i:s",strtotime($line['qso_date'] . " " . $line['time_on']));
          }
          
          if (isset($line['gridsquare'])) {
            $grid_sql =  "'" . $line['gridsquare'] . "'";
          }
          else 
          {
            $grid_sql = "NULL";  
          }
          
          if (isset($line['my_gridsquare'])) {
            $mygrid_sql =  "'" . $line['my_gridsquare'] . "'";
          }
          else 
          {
            $mygrid_sql = "NULL";  
          }
          
          if (isset($line['dxcc'])) {
            $dxcc_sql =  "'" . $line['dxcc'] . "'";
          }
          else 
          {
            $dxcc_sql = "NULL";  
          }
          
          if (isset($line['freq'])) {
            $freq_sql =  "'" . $line['freq'] . "'";
            $band_sql = "'" . $myHam->freq_band($line['freq']) . "'";
          }
          else 
          {
            $freq_sql = "NULL";  
            $band_sql = "NULL";
          }
          
          if (isset($line['mode'])) {
            $mode_sql =  "'" . $line['mode'] . "'";
          }
          else 
          {
            $mode_sql = "NULL";  
          }
                    
          $sql = "INSERT INTO `ham_qso` (`qso_time`, `qso_call`, `qso_remote_grid`, `qso_local_grid`, `qso_dxcc`, `qso_mode`, `qso_band`, `qso_freq`) VALUES ('" .
           $date . "', '" . $line['call'] . "', " . $grid_sql . ", " . $mygrid_sql . ", " . $dxcc_sql . ", " . $mode_sql . ", " . $band_sql . ", " . $freq_sql . ");";
          
          $result = @db_query($sql);
        }
        $x++;
    }
    fclose($handle);
  }
  return 0;
}

?>
