<?php

namespace App\Helpers;

final class AppCodes
{
    /** Hold the class instance. **/
    private static ?AppCodes $instance = null;

    /**
     * @var array contains the app codes
     */
    private array $appCodes;

    /**
     * The object is created from within the class itself
     * only if the class has no instance.
     **/
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new AppCodes();
        }
        return self::$instance;
    }

    /**
     * Private ctor so nobody else can instantiate it
     */
    private function __construct()
    {
        try {
            $this->appCodes = parse_ini_file(realpath(dirname($_SERVER['PHP_SELF']) . '/parser_test/appCodes.ini'));
        } catch(\Exception $e) {
            throw new \Exception("Unable to parse appCodes.ini", 0, $e);
        }
    }

    public function getCodeByName($code)
    {
        return array_flip($this->appCodes)[$code] ?? "N/A";
    }
}