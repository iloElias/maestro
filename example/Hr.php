<?php

namespace Maestro\Example;

use Ilias\Maestro\Abstract\Schema;

final class Hr extends Schema
{
    public User $user;
    public AuthCode $authCode;
}
