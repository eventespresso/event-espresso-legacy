<?php
/**
 * Copyright (C) 2007 Google Inc.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *      http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
 
  // Log levels
  define("Espresso_L_OFF", 0); // No log
  define("Espresso_L_ERR", 1); // Log Errors
  define("Espresso_L_RQST", 2); // Log Request from GC
  define("Espresso_L_RESP", 4); // Log Resoponse To Google
  define("Espresso_L_ERR_RQST", Espresso_L_ERR | Espresso_L_RQST);
  define("Espresso_L_ALL", Espresso_L_ERR | Espresso_L_RQST | Espresso_L_RESP);
 
class Espresso_GoogleLog {
    
    var $errorLogFile;
    var $messageLogFile;
 // L_ALL (err+requests+responses), L_ERR, L_RQST, L_RESP, L_OFF    
    var $logLevel = Espresso_L_ERR_RQST;

  /**
   * SetLogFiles
   */
  function Espresso_GoogleLog($errorLogFile, $messageLogFile, $logLevel=Espresso_L_ERR_RQST, $die=true){
    $this->logLevel = $logLevel;
    if($logLevel == Espresso_L_OFF) {
      $this->logLevel = Espresso_L_OFF;
    } else {
      if (!$this->errorLogFile = @fopen($errorLogFile, "a")) {
        header('HTTP/1.0 500 Internal Server Error');
        $log = "Cannot open " . $errorLogFile . " file.\n" .
                    "Logs are not writable, set them to 777";        
        error_log($log, 0);
        if($die) {
          die($log);
        }else {
          echo $log;
          $this->logLevel = Espresso_L_OFF;
        }
      }
      if (!$this->messageLogFile = @fopen($messageLogFile, "a")) {
        fclose($this->errorLogFile);
        header('HTTP/1.0 500 Internal Server Error');
        $log = "Cannot open " . $messageLogFile . " file.\n" .
                    "Logs are not writable, set them to 777";        
        error_log($log, 0);
        if($die) {
          die($log);
        }else {
          echo $log;
          $this->logLevel = Espresso_L_OFF;
        }
      }
    }
    $this->logLevel = $logLevel;;
  }
  
  function LogError($log){
    if($this->logLevel & Espresso_L_ERR){
      fwrite($this->errorLogFile,
      sprintf("\n%s:- %s\n",date("D M j G:i:s T Y"),$log));
      return true;
    }
    return false;
  }
  
  function LogRequest($log){
    if($this->logLevel & Espresso_L_RQST){
      fwrite($this->messageLogFile,
       sprintf("\n%s:- %s\n",date("D M j G:i:s T Y"),$log));
       return true;
    }
    return false;
  }
  
  function LogResponse($log) {
    if($this->logLevel & Espresso_L_RESP){
      $this->LogRequest($log);
      return true;
    }
    return false;
  }
}
?>
