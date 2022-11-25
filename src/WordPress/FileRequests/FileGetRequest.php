<?php

namespace lucatume\WPBrowser\WordPress\FileRequests;


class FileGetRequest extends FileRequest
{
    protected function getMethod(): string
    {
        return 'GET';
    }
}
