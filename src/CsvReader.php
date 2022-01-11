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

    private int $line = 0;

    private ?array $columns = null;
    private int $columnsCount = 0;

    private bool $trim = false;
    private bool $emptyStringToNull = false;

    private ?Closure $mapColumns = null;
    private ?Closure $mapValues = null;

    private ?array $row = null;

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
            $this->line = 0;
            while (($this->row = fgetcsv(
                $handle,
                $this->maxLineLength,
                $this->sep,
                $this->quot,
                $this->esc
            )) !== false) {
                if ($this->line === 0) {
                    $this->loadColumns();
                } else {
                    yield $this->processRow();
                }
                $this->line++;
            }
            fclose($handle);
        }
    }

    public function getColumnCount(): int
    {
        return $this->columnsCount;
    }

    private function loadColumns()
    {
        $this->columns = array_values(array_map(function ($c) {
            return trim($c);
        }, $this->row));
        if ($this->mapColumns) {
            $this->columns = ($this->mapColumns)($this->columns);
        }
        $this->columnsCount = count($this->columns);
    }

    private function processRow(): array
    {
        for ($i = 0; $i < $this->columnsCount; $i++) {
            $v = $this->row[$i];

            if ($this->trim) {
                $v = trim($v);
            }
            if ($this->emptyStringToNull) {
                $v = $v === '' ? null : $v;
            }
            $this->row[$i] = $v;
        }
        $assoc = array_combine($this->columns, $this->row);
        if ($assoc === false) {
            throw new OutOfBoundsException("row {$this->line} column count " . count($this->row) . " do not match with columns {$this->columnsCount}");
        }
        if (isset($this->mapValues)) {
            $assoc = ($this->mapValues)($assoc);
        }

        return $assoc;
    }
}
