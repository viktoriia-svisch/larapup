<?php
namespace Symfony\Component\Console\Helper;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Formatter\WrappableOutputFormatterInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;
class Table
{
    private const SEPARATOR_TOP = 0;
    private const SEPARATOR_TOP_BOTTOM = 1;
    private const SEPARATOR_MID = 2;
    private const SEPARATOR_BOTTOM = 3;
    private const BORDER_OUTSIDE = 0;
    private const BORDER_INSIDE = 1;
    private $headerTitle;
    private $footerTitle;
    private $headers = [];
    private $rows = [];
    private $effectiveColumnWidths = [];
    private $numberOfColumns;
    private $output;
    private $style;
    private $columnStyles = [];
    private $columnWidths = [];
    private $columnMaxWidths = [];
    private static $styles;
    private $rendered = false;
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
        if (!self::$styles) {
            self::$styles = self::initStyles();
        }
        $this->setStyle('default');
    }
    public static function setStyleDefinition($name, TableStyle $style)
    {
        if (!self::$styles) {
            self::$styles = self::initStyles();
        }
        self::$styles[$name] = $style;
    }
    public static function getStyleDefinition($name)
    {
        if (!self::$styles) {
            self::$styles = self::initStyles();
        }
        if (isset(self::$styles[$name])) {
            return self::$styles[$name];
        }
        throw new InvalidArgumentException(sprintf('Style "%s" is not defined.', $name));
    }
    public function setStyle($name)
    {
        $this->style = $this->resolveStyle($name);
        return $this;
    }
    public function getStyle()
    {
        return $this->style;
    }
    public function setColumnStyle($columnIndex, $name)
    {
        $columnIndex = (int) $columnIndex;
        $this->columnStyles[$columnIndex] = $this->resolveStyle($name);
        return $this;
    }
    public function getColumnStyle($columnIndex)
    {
        return $this->columnStyles[$columnIndex] ?? $this->getStyle();
    }
    public function setColumnWidth($columnIndex, $width)
    {
        $this->columnWidths[(int) $columnIndex] = (int) $width;
        return $this;
    }
    public function setColumnWidths(array $widths)
    {
        $this->columnWidths = [];
        foreach ($widths as $index => $width) {
            $this->setColumnWidth($index, $width);
        }
        return $this;
    }
    public function setColumnMaxWidth(int $columnIndex, int $width): self
    {
        if (!$this->output->getFormatter() instanceof WrappableOutputFormatterInterface) {
            throw new \LogicException(sprintf('Setting a maximum column width is only supported when using a "%s" formatter, got "%s".', WrappableOutputFormatterInterface::class, \get_class($this->output->getFormatter())));
        }
        $this->columnMaxWidths[$columnIndex] = $width;
        return $this;
    }
    public function setHeaders(array $headers)
    {
        $headers = array_values($headers);
        if (!empty($headers) && !\is_array($headers[0])) {
            $headers = [$headers];
        }
        $this->headers = $headers;
        return $this;
    }
    public function setRows(array $rows)
    {
        $this->rows = [];
        return $this->addRows($rows);
    }
    public function addRows(array $rows)
    {
        foreach ($rows as $row) {
            $this->addRow($row);
        }
        return $this;
    }
    public function addRow($row)
    {
        if ($row instanceof TableSeparator) {
            $this->rows[] = $row;
            return $this;
        }
        if (!\is_array($row)) {
            throw new InvalidArgumentException('A row must be an array or a TableSeparator instance.');
        }
        $this->rows[] = array_values($row);
        return $this;
    }
    public function appendRow($row): self
    {
        if (!$this->output instanceof ConsoleSectionOutput) {
            throw new RuntimeException(sprintf('Output should be an instance of "%s" when calling "%s".', ConsoleSectionOutput::class, __METHOD__));
        }
        if ($this->rendered) {
            $this->output->clear($this->calculateRowCount());
        }
        $this->addRow($row);
        $this->render();
        return $this;
    }
    public function setRow($column, array $row)
    {
        $this->rows[$column] = $row;
        return $this;
    }
    public function setHeaderTitle(?string $title): self
    {
        $this->headerTitle = $title;
        return $this;
    }
    public function setFooterTitle(?string $title): self
    {
        $this->footerTitle = $title;
        return $this;
    }
    public function render()
    {
        $rows = array_merge($this->headers, [$divider = new TableSeparator()], $this->rows);
        $this->calculateNumberOfColumns($rows);
        $rows = $this->buildTableRows($rows);
        $this->calculateColumnsWidth($rows);
        $isHeader = true;
        $isFirstRow = false;
        foreach ($rows as $row) {
            if ($divider === $row) {
                $isHeader = false;
                $isFirstRow = true;
                continue;
            }
            if ($row instanceof TableSeparator) {
                $this->renderRowSeparator();
                continue;
            }
            if (!$row) {
                continue;
            }
            if ($isHeader || $isFirstRow) {
                if ($isFirstRow) {
                    $this->renderRowSeparator(self::SEPARATOR_TOP_BOTTOM);
                    $isFirstRow = false;
                } else {
                    $this->renderRowSeparator(self::SEPARATOR_TOP, $this->headerTitle, $this->style->getHeaderTitleFormat());
                }
            }
            $this->renderRow($row, $isHeader ? $this->style->getCellHeaderFormat() : $this->style->getCellRowFormat());
        }
        $this->renderRowSeparator(self::SEPARATOR_BOTTOM, $this->footerTitle, $this->style->getFooterTitleFormat());
        $this->cleanup();
        $this->rendered = true;
    }
    private function renderRowSeparator(int $type = self::SEPARATOR_MID, string $title = null, string $titleFormat = null)
    {
        if (0 === $count = $this->numberOfColumns) {
            return;
        }
        $borders = $this->style->getBorderChars();
        if (!$borders[0] && !$borders[2] && !$this->style->getCrossingChar()) {
            return;
        }
        $crossings = $this->style->getCrossingChars();
        if (self::SEPARATOR_MID === $type) {
            list($horizontal, $leftChar, $midChar, $rightChar) = [$borders[2], $crossings[8], $crossings[0], $crossings[4]];
        } elseif (self::SEPARATOR_TOP === $type) {
            list($horizontal, $leftChar, $midChar, $rightChar) = [$borders[0], $crossings[1], $crossings[2], $crossings[3]];
        } elseif (self::SEPARATOR_TOP_BOTTOM === $type) {
            list($horizontal, $leftChar, $midChar, $rightChar) = [$borders[0], $crossings[9], $crossings[10], $crossings[11]];
        } else {
            list($horizontal, $leftChar, $midChar, $rightChar) = [$borders[0], $crossings[7], $crossings[6], $crossings[5]];
        }
        $markup = $leftChar;
        for ($column = 0; $column < $count; ++$column) {
            $markup .= str_repeat($horizontal, $this->effectiveColumnWidths[$column]);
            $markup .= $column === $count - 1 ? $rightChar : $midChar;
        }
        if (null !== $title) {
            $titleLength = Helper::strlenWithoutDecoration($formatter = $this->output->getFormatter(), $formattedTitle = sprintf($titleFormat, $title));
            $markupLength = Helper::strlen($markup);
            if ($titleLength > $limit = $markupLength - 4) {
                $titleLength = $limit;
                $formatLength = Helper::strlenWithoutDecoration($formatter, sprintf($titleFormat, ''));
                $formattedTitle = sprintf($titleFormat, Helper::substr($title, 0, $limit - $formatLength - 3).'...');
            }
            $titleStart = ($markupLength - $titleLength) / 2;
            if (false === mb_detect_encoding($markup, null, true)) {
                $markup = substr_replace($markup, $formattedTitle, $titleStart, $titleLength);
            } else {
                $markup = mb_substr($markup, 0, $titleStart).$formattedTitle.mb_substr($markup, $titleStart + $titleLength);
            }
        }
        $this->output->writeln(sprintf($this->style->getBorderFormat(), $markup));
    }
    private function renderColumnSeparator($type = self::BORDER_OUTSIDE)
    {
        $borders = $this->style->getBorderChars();
        return sprintf($this->style->getBorderFormat(), self::BORDER_OUTSIDE === $type ? $borders[1] : $borders[3]);
    }
    private function renderRow(array $row, string $cellFormat)
    {
        $rowContent = $this->renderColumnSeparator(self::BORDER_OUTSIDE);
        $columns = $this->getRowColumns($row);
        $last = \count($columns) - 1;
        foreach ($columns as $i => $column) {
            $rowContent .= $this->renderCell($row, $column, $cellFormat);
            $rowContent .= $this->renderColumnSeparator($last === $i ? self::BORDER_OUTSIDE : self::BORDER_INSIDE);
        }
        $this->output->writeln($rowContent);
    }
    private function renderCell(array $row, int $column, string $cellFormat)
    {
        $cell = isset($row[$column]) ? $row[$column] : '';
        $width = $this->effectiveColumnWidths[$column];
        if ($cell instanceof TableCell && $cell->getColspan() > 1) {
            foreach (range($column + 1, $column + $cell->getColspan() - 1) as $nextColumn) {
                $width += $this->getColumnSeparatorWidth() + $this->effectiveColumnWidths[$nextColumn];
            }
        }
        if (false !== $encoding = mb_detect_encoding($cell, null, true)) {
            $width += \strlen($cell) - mb_strwidth($cell, $encoding);
        }
        $style = $this->getColumnStyle($column);
        if ($cell instanceof TableSeparator) {
            return sprintf($style->getBorderFormat(), str_repeat($style->getBorderChars()[2], $width));
        }
        $width += Helper::strlen($cell) - Helper::strlenWithoutDecoration($this->output->getFormatter(), $cell);
        $content = sprintf($style->getCellRowContentFormat(), $cell);
        return sprintf($cellFormat, str_pad($content, $width, $style->getPaddingChar(), $style->getPadType()));
    }
    private function calculateNumberOfColumns($rows)
    {
        $columns = [0];
        foreach ($rows as $row) {
            if ($row instanceof TableSeparator) {
                continue;
            }
            $columns[] = $this->getNumberOfColumns($row);
        }
        $this->numberOfColumns = max($columns);
    }
    private function buildTableRows($rows)
    {
        $formatter = $this->output->getFormatter();
        $unmergedRows = [];
        for ($rowKey = 0; $rowKey < \count($rows); ++$rowKey) {
            $rows = $this->fillNextRows($rows, $rowKey);
            foreach ($rows[$rowKey] as $column => $cell) {
                if (isset($this->columnMaxWidths[$column]) && Helper::strlenWithoutDecoration($formatter, $cell) > $this->columnMaxWidths[$column]) {
                    $cell = $formatter->formatAndWrap($cell, $this->columnMaxWidths[$column]);
                }
                if (!strstr($cell, "\n")) {
                    continue;
                }
                $lines = explode("\n", str_replace("\n", "<fg=default;bg=default>\n</>", $cell));
                foreach ($lines as $lineKey => $line) {
                    if ($cell instanceof TableCell) {
                        $line = new TableCell($line, ['colspan' => $cell->getColspan()]);
                    }
                    if (0 === $lineKey) {
                        $rows[$rowKey][$column] = $line;
                    } else {
                        $unmergedRows[$rowKey][$lineKey][$column] = $line;
                    }
                }
            }
        }
        return new TableRows(function () use ($rows, $unmergedRows) {
            foreach ($rows as $rowKey => $row) {
                yield $this->fillCells($row);
                if (isset($unmergedRows[$rowKey])) {
                    foreach ($unmergedRows[$rowKey] as $row) {
                        yield $row;
                    }
                }
            }
        });
    }
    private function calculateRowCount(): int
    {
        $numberOfRows = \count(iterator_to_array($this->buildTableRows(array_merge($this->headers, [new TableSeparator()], $this->rows))));
        if ($this->headers) {
            ++$numberOfRows; 
        }
        ++$numberOfRows; 
        return $numberOfRows;
    }
    private function fillNextRows(array $rows, int $line): array
    {
        $unmergedRows = [];
        foreach ($rows[$line] as $column => $cell) {
            if (null !== $cell && !$cell instanceof TableCell && !is_scalar($cell) && !(\is_object($cell) && method_exists($cell, '__toString'))) {
                throw new InvalidArgumentException(sprintf('A cell must be a TableCell, a scalar or an object implementing __toString, %s given.', \gettype($cell)));
            }
            if ($cell instanceof TableCell && $cell->getRowspan() > 1) {
                $nbLines = $cell->getRowspan() - 1;
                $lines = [$cell];
                if (strstr($cell, "\n")) {
                    $lines = explode("\n", str_replace("\n", "<fg=default;bg=default>\n</>", $cell));
                    $nbLines = \count($lines) > $nbLines ? substr_count($cell, "\n") : $nbLines;
                    $rows[$line][$column] = new TableCell($lines[0], ['colspan' => $cell->getColspan()]);
                    unset($lines[0]);
                }
                $unmergedRows = array_replace_recursive(array_fill($line + 1, $nbLines, []), $unmergedRows);
                foreach ($unmergedRows as $unmergedRowKey => $unmergedRow) {
                    $value = isset($lines[$unmergedRowKey - $line]) ? $lines[$unmergedRowKey - $line] : '';
                    $unmergedRows[$unmergedRowKey][$column] = new TableCell($value, ['colspan' => $cell->getColspan()]);
                    if ($nbLines === $unmergedRowKey - $line) {
                        break;
                    }
                }
            }
        }
        foreach ($unmergedRows as $unmergedRowKey => $unmergedRow) {
            if (isset($rows[$unmergedRowKey]) && \is_array($rows[$unmergedRowKey]) && ($this->getNumberOfColumns($rows[$unmergedRowKey]) + $this->getNumberOfColumns($unmergedRows[$unmergedRowKey]) <= $this->numberOfColumns)) {
                foreach ($unmergedRow as $cellKey => $cell) {
                    array_splice($rows[$unmergedRowKey], $cellKey, 0, [$cell]);
                }
            } else {
                $row = $this->copyRow($rows, $unmergedRowKey - 1);
                foreach ($unmergedRow as $column => $cell) {
                    if (!empty($cell)) {
                        $row[$column] = $unmergedRow[$column];
                    }
                }
                array_splice($rows, $unmergedRowKey, 0, [$row]);
            }
        }
        return $rows;
    }
    private function fillCells($row)
    {
        $newRow = [];
        foreach ($row as $column => $cell) {
            $newRow[] = $cell;
            if ($cell instanceof TableCell && $cell->getColspan() > 1) {
                foreach (range($column + 1, $column + $cell->getColspan() - 1) as $position) {
                    $newRow[] = '';
                }
            }
        }
        return $newRow ?: $row;
    }
    private function copyRow(array $rows, int $line): array
    {
        $row = $rows[$line];
        foreach ($row as $cellKey => $cellValue) {
            $row[$cellKey] = '';
            if ($cellValue instanceof TableCell) {
                $row[$cellKey] = new TableCell('', ['colspan' => $cellValue->getColspan()]);
            }
        }
        return $row;
    }
    private function getNumberOfColumns(array $row): int
    {
        $columns = \count($row);
        foreach ($row as $column) {
            $columns += $column instanceof TableCell ? ($column->getColspan() - 1) : 0;
        }
        return $columns;
    }
    private function getRowColumns(array $row): array
    {
        $columns = range(0, $this->numberOfColumns - 1);
        foreach ($row as $cellKey => $cell) {
            if ($cell instanceof TableCell && $cell->getColspan() > 1) {
                $columns = array_diff($columns, range($cellKey + 1, $cellKey + $cell->getColspan() - 1));
            }
        }
        return $columns;
    }
    private function calculateColumnsWidth(iterable $rows)
    {
        for ($column = 0; $column < $this->numberOfColumns; ++$column) {
            $lengths = [];
            foreach ($rows as $row) {
                if ($row instanceof TableSeparator) {
                    continue;
                }
                foreach ($row as $i => $cell) {
                    if ($cell instanceof TableCell) {
                        $textContent = Helper::removeDecoration($this->output->getFormatter(), $cell);
                        $textLength = Helper::strlen($textContent);
                        if ($textLength > 0) {
                            $contentColumns = str_split($textContent, ceil($textLength / $cell->getColspan()));
                            foreach ($contentColumns as $position => $content) {
                                $row[$i + $position] = $content;
                            }
                        }
                    }
                }
                $lengths[] = $this->getCellWidth($row, $column);
            }
            $this->effectiveColumnWidths[$column] = max($lengths) + Helper::strlen($this->style->getCellRowContentFormat()) - 2;
        }
    }
    private function getColumnSeparatorWidth(): int
    {
        return Helper::strlen(sprintf($this->style->getBorderFormat(), $this->style->getBorderChars()[3]));
    }
    private function getCellWidth(array $row, int $column): int
    {
        $cellWidth = 0;
        if (isset($row[$column])) {
            $cell = $row[$column];
            $cellWidth = Helper::strlenWithoutDecoration($this->output->getFormatter(), $cell);
        }
        $columnWidth = isset($this->columnWidths[$column]) ? $this->columnWidths[$column] : 0;
        $cellWidth = max($cellWidth, $columnWidth);
        return isset($this->columnMaxWidths[$column]) ? min($this->columnMaxWidths[$column], $cellWidth) : $cellWidth;
    }
    private function cleanup()
    {
        $this->effectiveColumnWidths = [];
        $this->numberOfColumns = null;
    }
    private static function initStyles()
    {
        $borderless = new TableStyle();
        $borderless
            ->setHorizontalBorderChars('=')
            ->setVerticalBorderChars(' ')
            ->setDefaultCrossingChar(' ')
        ;
        $compact = new TableStyle();
        $compact
            ->setHorizontalBorderChars('')
            ->setVerticalBorderChars(' ')
            ->setDefaultCrossingChar('')
            ->setCellRowContentFormat('%s')
        ;
        $styleGuide = new TableStyle();
        $styleGuide
            ->setHorizontalBorderChars('-')
            ->setVerticalBorderChars(' ')
            ->setDefaultCrossingChar(' ')
            ->setCellHeaderFormat('%s')
        ;
        $box = (new TableStyle())
            ->setHorizontalBorderChars('─')
            ->setVerticalBorderChars('│')
            ->setCrossingChars('┼', '┌', '┬', '┐', '┤', '┘', '┴', '└', '├')
        ;
        $boxDouble = (new TableStyle())
            ->setHorizontalBorderChars('═', '─')
            ->setVerticalBorderChars('║', '│')
            ->setCrossingChars('┼', '╔', '╤', '╗', '╢', '╝', '╧', '╚', '╟', '╠', '╪', '╣')
        ;
        return [
            'default' => new TableStyle(),
            'borderless' => $borderless,
            'compact' => $compact,
            'symfony-style-guide' => $styleGuide,
            'box' => $box,
            'box-double' => $boxDouble,
        ];
    }
    private function resolveStyle($name)
    {
        if ($name instanceof TableStyle) {
            return $name;
        }
        if (isset(self::$styles[$name])) {
            return self::$styles[$name];
        }
        throw new InvalidArgumentException(sprintf('Style "%s" is not defined.', $name));
    }
}
