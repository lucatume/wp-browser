<?php
/* ===========================================================================
 * Copyright (c) 2018-2021 Zindex Software
 *
 * Licensed under the MIT License
 * =========================================================================== */

namespace lucatume\WPBrowser\Opis\Closure;

interface ISecurityProvider
{
    /**
     * Sign serialized closure
     * @param string $closure
     * @return array
     */
    public function sign($closure): array;

    /**
     * Verify signature
     * @param array $data
     * @return bool
     */
    public function verify(array $data): bool;
}
