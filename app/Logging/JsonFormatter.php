<?php

namespace App\Logging;

class JsonFormatter extends \Monolog\Formatter\JsonFormatter
{
    public const SIMPLE_DATE = 'Y-m-d\TH:i:s.vO';

    public function __construct(
        int $batchMode = \Monolog\Formatter\JsonFormatter::BATCH_MODE_JSON,
        bool $appendNewline = true,
        bool $ignoreEmptyContextAndExtra = false,
        bool $includeStacktraces = false
    ) {
        parent::__construct($batchMode, $appendNewline, $ignoreEmptyContextAndExtra, $includeStacktraces);
    }
}
