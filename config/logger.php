<?php
class Logger {
    private static $logFile = '../logs/app.log';
    
    public static function init() {
        $logDir = dirname(self::$logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
    }

    public static function log($component, $message, $level = 'INFO') {
        self::init();
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp][$level][$component] $message\n";
        error_log($logMessage, 3, self::$logFile);
        if (getenv('APP_DEBUG') === 'true') {
            error_log($logMessage);
        }
    }

    public static function error($component, $message) {
        self::log($component, $message, 'ERROR');
    }

    public static function info($component, $message) {
        self::log($component, $message, 'INFO');
    }

    public static function debug($component, $message) {
        self::log($component, $message, 'DEBUG');
    }
}
?> 