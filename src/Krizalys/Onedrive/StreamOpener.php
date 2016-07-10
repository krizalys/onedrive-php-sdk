<?php

namespace Krizalys\Onedrive;

class StreamOpener
{
    const URIS = array(
        StreamBackEnd::MEMORY => 'php://memory',
        StreamBackEnd::TEMP   => 'php://temp',
    );

    /**
     * Opens a stream given a stream back end.
     *
     * @param int $streamBackEnd The stream back end.
     *
     * @return bool|resource The open stream.
     *
     * @throws \Exception Thrown if the stream back end given is not supported.
     */
    public function open($streamBackEnd)
    {
        if (!array_key_exists($streamBackEnd, self::URIS)) {
            throw new \Exception("Unsupported stream back end: $streamBackEnd");
        }

        $uri = self::URIS[$streamBackEnd];
        return fopen($uri, 'rw+b');
    }
}
