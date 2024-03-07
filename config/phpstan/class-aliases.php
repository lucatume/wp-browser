<?php

if (!class_exists(Ifsnop\Mysqldump\Mysqldump::class) && class_exists(Druidfi\Mysqldump\Mysqldump::class)) {
    class_alias(Druidfi\Mysqldump\Mysqldump::class, Ifsnop\Mysqldump\Mysqldump::class);
}
