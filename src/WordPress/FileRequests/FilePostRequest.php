<?php

namespace lucatume\WPBrowser\WordPress\FileRequests;

class FilePostRequest extends FileRequest
{

    protected function getMethod(): string
    {
        return 'POST';
    }
}
