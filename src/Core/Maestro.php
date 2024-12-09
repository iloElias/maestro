<?php

namespace Ilias\Maestro\Core;

final class Maestro
{
    public const SQL_STRICT     = 'STRICT';
    public const SQL_PREDICT    = 'PREDICT';
    public const SQL_NO_PREDICT = 'NO_PREDICT';
    public const DOC_PRIMARY    = '@primary';
    public const DOC_FOREIGN    = '@foreign';
    public const DOC_UNIQUE     = '@unique';
    public const DOC_NUABLE     = '@nuable';
    public const DOC_NOT_NUABLE = '@not_nuable';
    public const DOC_COMMENT    = '@comment';

    public const MAETRO_DOC_CLAUSES = [
      self::DOC_UNIQUE,
      self::DOC_NUABLE,
      self::DOC_NOT_NUABLE,
      self::DOC_PRIMARY,
      self::DOC_FOREIGN,
      self::DOC_COMMENT,
    ];

    public const PREFER_DEFAULT = 0;
    public const PREFER_DOC     = 1;

    private static bool $isInitialized = false;

    public static array $databases = [];

    /**
     * Summary of __construct
     *
     * @param array[string] $databases
     */
    public function __construct(array $databases = [])
    {
        self::handle();
    }

    public static function handle()
    {
        if (!self::$isInitialized) {
            $phpMaestroFilePath = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', 'maestro.config.php']);
            if (file_exists($phpMaestroFilePath)) {
                require_once $phpMaestroFilePath;
            }
            self::$isInitialized = true;
        }
    }
}
