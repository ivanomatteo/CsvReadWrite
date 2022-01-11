<?php

declare(strict_types=1);

namespace IvanoMatteo\CsvReadWrite;

use InvalidArgumentException;
use OutOfBoundsException;
use ReflectionClass;
use ReflectionException;
use stdClass;

class CsvWriter
{
    private string $sep = ',';
    private string $quot = '"';
    private string $esc = "\\";

    public function __construct(
        private string $file
    ) {
    }

    public function format(string $sep, string $quot = '"', string $esc = "\\"): static
    {
        $this->sep = $sep;
        $this->quot = $quot;
        $this->esc = $esc;

        return $this;
    }

    public function write(iterable $collection, ?array $headers = null): void
    {
        try {
            if (($handle = fopen($this->file, "w")) !== false) {
                $counter = 0;

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
                            $row = (array)$row;
                        } else {
                            throw new InvalidArgumentException("Class '" . get_class($row) . "' do not have toArray() method.");
                        }
                    }

                    if ($headers && count($row) !== $headers_count) {
                        $record = $counter + 1;

                        throw new OutOfBoundsException("record $record column count " . count($row) . " do not match with headers $headers_count");
                    }

                    fputcsv(
                        $handle,
                        $row,
                        $this->sep,
                        $this->quot,
                        $this->esc
                    );

                    $counter++;
                }
            }
        } finally {
            if (! empty($handle)) {
                fclose($handle);
            }
        }
    }

    private function isArrayable($obj): bool
    {
        try {
            $class = new ReflectionClass($obj);

            if (! $class->hasMethod('toArray')) {
                return false;
            }
            if ($class->getMethod('toArray')->getNumberOfRequiredParameters() > 0) {
                return false;
            }

            return true;
        } catch (ReflectionException $e) {
            return false;
        }
    }
}
