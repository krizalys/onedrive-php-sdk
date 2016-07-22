<?php

namespace Test\Mock;

use Mockery as m;

class GlobalNamespace
{
    /**
     * @var \Mockery\Mock
     */
    public static $functions;

    /**
     * Stubs global functions and force them to behave as if they were
     * successful & running within the realistic contexts.
     *
     * @param array $expectations
     */
    public static function reset(array $expectations = array())
    {
        $expectations = array_merge(array(
            'time' => function ($expectation) {
                $expectation->andReturn(strtotime('1970-01-01T00:00:00Z'));
            },

            'fopen' => function ($expectation) {
                $expectation->andReturn(true);
            },

            'fclose' => function ($expectation) {
                $expectation->andReturn(true);
            },

            'fwrite' => function ($expectation) {
                $expectation->andReturnUsing(function ($handle, $string, $length = null) {
                    return null == $length ? strlen($string) : $length;
                });
            },

            'rewind' => function ($expectation) {
                $expectation->andReturn(true);
            },

            'fstat' => function ($expectation) {
                $expectation->andReturn(array(
                    /* Size */ 7 => 0,
                ));
            },

            'curl_init' => function ($expectation) {
                $expectation->andReturn(true);
            },

            'curl_setopt' => function ($expectation) {
                $expectation->andReturn(true);
            },

            'curl_setopt_array' => function ($expectation) {
                $expectation->andReturn(true);
            },

            'curl_exec' => function ($expectation) {
                $expectation->andReturn(true);
            },

            'curl_getinfo' => function ($expectation) {
                $expectation->andReturn(array(
                    'http_code'    => 200,
                    'content_type' => 'application/json',
                ));
            },
        ), $expectations);

        $functions = m::mock();

        foreach ($expectations as $name => $callbacks) {
            if (!is_array($callbacks)) {
                $callbacks = array($callbacks);
            }

            foreach ($callbacks as $callback) {
                $expectation = $functions->shouldReceive($name);

                if (is_callable($callback)) {
                    $callback($expectation);
                }
            }
        }

        self::$functions = $functions;
    }
}
