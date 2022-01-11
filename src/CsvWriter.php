<?php

namespace IvanoMatteo\CsvReadWrite;

use Exception;
use InvalidArgumentException;
use ReflectionClass;
use stdClass;

/**
 *
 * @property string $file
 *
 * @property string $sep
 * @property string $quot
 * @property string $esc
 *
 * @property int $maxLineLength
 * @property int $line
 *
 * @property array $columns
 * @property int $columnsCount
 *
 * @property bool $trim
 * @property bool $emptyStringToNull
 *
 * @property callable|null $mapColumns
 * @property callable|null $mapValues
 *
 */
class CsvWriter
{
    private $file;

    private $sep = ',';
    private $quot = '"';
    private $esc = "\\";

    private $counter = 0;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function format($sep, $quot = '"', $esc = "\\")
    {
        $this->sep = $sep;
        $this->quot = $quot;
        $this->esc = $esc;

        return $this;
    }

    public function write($collection, $headers = null)
    {
        try {
            if (($handle = fopen($this->file, "w")) !== false) {
                $this->counter = 0;

                $headers_count = 0;

                if ($headers) {
                    fputcsv(
                        $handle,
                        $headers,
                        $this->sep,
                        $this->quot,
                        $this->esc
                    );
                    $headers_count = count($headers);
                }


                foreach ($collection as $row) {
                    if (is_object($row)) {
                        if ($this->isArrayable($row)) {
                            $row = $row->toArray();
                        } elseif ($row instanceof stdClass) {
                            $row = (array) $row;
                        } else {
                            throw new InvalidArgumentException("Class '".get_class($row)."' do not have toArray() method.");
                        }
                    }

                    if ($headers && count($row) !== $headers_count) {
                        $record = $this->counter + 1;

                        throw new Exception("record $record column count " . count($row) . " do not match with headers $headers_count");
                    }

                    fputcsv(
                        $handle,
                        $row,
                        $this->sep,
                        $this->quot,
                        $this->esc
                    );
                    $this->counter++;
                }
            }
        } finally {
            if (! empty($handle)) {
                fclose($handle);
            }
        }
    }

    private function isArrayable($obj)
    {
        $class = new ReflectionClass($obj);
        if (! $class->hasMethod('toArray')) {
            return false;
        }
        if ($class->getMethod('toArray')->getNumberOfRequiredParameters() > 0) {
            return false;
        }

        return true;
    }
}
