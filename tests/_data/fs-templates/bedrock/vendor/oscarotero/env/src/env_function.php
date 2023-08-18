<?php

namespace Env;

/**
 * Gets the value of an environment variable.
 *
 * @return mixed
 */
function env(string $name)
{
    return Env::get($name);
}
