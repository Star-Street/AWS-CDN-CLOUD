<?php

class LogService
{
    const LOG_PATH = "/../logs/";

    public function __construct() {}

    public function log(string $folder, string $content): void {
        //? You can find all logs with id image, use this commands: 
        //? with time, id image and date:  sudo grep -n --color=auto "[H:i].*[id_image]" */[ymd].log
        //? with id image and date:        sudo grep -n --color=auto "[id_image]" */[ymd].log
        //? with id image:                 sudo grep -n --color=auto "[id_image]" */*.log
        $logDir = dirname(__FILE__) . self::LOG_PATH . $folder;
        $logFile = $logDir . "/" . date("Ymd") . ".log";

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        file_put_contents($logFile, $content . "\n", FILE_APPEND);
    }
}
