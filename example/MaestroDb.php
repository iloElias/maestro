<?php

namespace Maestro\Example;

use Ilias\Maestro\Abstract\Database;
use Ilias\Maestro\Types\Postgres;

final class MaestroDb extends Database
{
    public Hr $hr;
    public Social $social;

    public DocumentTypes $documentTypes;

    public function __construct()
    {
        self::declareFunction(
            'generate_four_digit_auth_code',
            Postgres::TEXT,
            'CREATE OR REPLACE FUNCTION generate_four_digit_auth_code() RETURNS TEXT AS $$ BEGIN RETURN CAST(FLOOR(1000 + RANDOM() * 9000) AS TEXT); END; $$ LANGUAGE plpgsql;'
        );
    }
}
