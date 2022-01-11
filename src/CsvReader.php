<?php

namespace IvanoMatteo\CsvReadWrite;


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
class CsvReader
{
    private $file;

    private $sep = ',';
    private $quot = '"';
    private $esc = "\\";
    private $maxLineLength = 0;

    private $line;

    private $columns;
    private $columnsCount;

    private $trim = false;
    private $emptyStringToNull = false;

    private $mapColumns = null;
    private $mapValues = null;

    private $row = null;


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
    public function maxLineLength($l)
    {
        $this->maxLineLength = $l;
        return $this;
    }

    public function mapColumns(callable $c)
    {
        $this->mapColumns = $c;
        return $this;
    }
    public function mapValues(callable $v)
    {
        $this->mapValues = $v;
        return $this;
    }

    public function trim($b = true)
    {
        $this->trim = $b;
        return $this;
    }

    public function emptyStringToNull($b = true)
    {
        $this->emptyStringToNull = $b;
        return $this;
    }

    public function iterator()
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

    public function getColumnCount()
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

    private function processRow()
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
            throw new \Exception("row {$this->line} column count " . count($this->row) . " do not match with columns {$this->columnsCount}");
        }
        if (isset($this->mapValues)) { 
            $assoc = ($this->mapValues)($assoc);
        }
        return $assoc;
    }
}
