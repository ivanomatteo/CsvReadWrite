<?php

declare(strict_types=1);

namespace IvanoMatteo\CsvReadWrite;

use Closure;
use Iterator;
use OutOfBoundsException;

class CsvReader
{
    private string $sep = ',';
    private string $quot = '"';
    private string $esc = "\\";
    private int $maxLineLength = 0;

    private ?array $columns = null;

    private bool $trim = false;
    private bool $emptyStringToNull = false;

    private ?Closure $mapColumns = null;
    private ?Closure $mapValues = null;

    public function __construct(
        private string $file
    )
    {
    }

    public function format(string $sep, string $quot = '"', string $esc = "\\"): static
    {
        $this->sep = $sep;
        $this->quot = $quot;
        $this->esc = $esc;

        return $this;
    }

    public function maxLineLength(int $l): static
    {
        $this->maxLineLength = $l;

        return $this;
    }

    public function mapColumns(callable $c): static
    {
        $this->mapColumns = $c;

        return $this;
    }

    public function mapValues(Closure $v): static
    {
        $this->mapValues = $v;

        return $this;
    }

    public function trim(bool $b = true): static
    {
        $this->trim = $b;

        return $this;
    }

    public function emptyStringToNull(bool $b = true): static
    {
        $this->emptyStringToNull = $b;

        return $this;
    }

    public function iterator(): Iterator
    {
        if (($handle = fopen($this->file, "r")) !== false) {
            try {
                $line = 0;
                $row = null;
                while (($row = fgetcsv(
                        $handle,
                        $this->maxLineLength,
                        $this->sep,
                        $this->quot,
                        $this->esc
                    )) !== false) {
                    if ($line === 0) {
                        $this->loadColumns($row);
                    } else {
                        yield $this->processRow($row, $line);
                    }
                    $line++;
                }
            } finally {
                fclose($handle);
            }
        }
    }


    private function loadColumns(array $row)
    {
        $this->columns = array_values(array_map(function ($c) {
            return trim($c);
        }, $row));

        if ($this->mapColumns) {
            $this->columns = ($this->mapColumns)($this->columns);
        }
    }

    private function processRow(array $row, int $lineNumber): array
    {
        foreach ($row as $i => $v) {
            if ($this->trim) {
                $v = trim($v);
            }
            if ($this->emptyStringToNull) {
                $v = $v === '' ? null : $v;
            }
            $row[$i] = $v;
        }

        if (count($this->columns) !== count($row)) {
            throw new OutOfBoundsException("row {$lineNumber} column count " . count($row) . " do not match with columns " . count($this->columns));
        }

        $assoc = array_combine($this->columns, $row);

        if (isset($this->mapValues)) {
            $assoc = ($this->mapValues)($assoc);
        }

        return $assoc;
    }
}
