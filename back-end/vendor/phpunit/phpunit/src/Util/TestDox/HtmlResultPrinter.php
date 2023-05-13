<?php
namespace PHPUnit\Util\TestDox;
final class HtmlResultPrinter extends ResultPrinter
{
    private const PAGE_HEADER = <<<EOT
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <title>Test Documentation</title>
        <style>
            body {
                text-rendering: optimizeLegibility;
                font-variant-ligatures: common-ligatures;
                font-kerning: normal;
                margin-left: 2em;
            }
            body > ul > li {
                font-family: Source Serif Pro, PT Sans, Trebuchet MS, Helvetica, Arial;
                font-size: 2em;
            }
            h2 {
                font-family: Tahoma, Helvetica, Arial;
                font-size: 3em;
            }
            ul {
                list-style: none;
                margin-bottom: 1em;
            }
        </style>
    </head>
    <body>
EOT;
    private const CLASS_HEADER = <<<EOT
        <h2 id="%s">%s</h2>
        <ul>
EOT;
    private const CLASS_FOOTER = <<<EOT
        </ul>
EOT;
    private const PAGE_FOOTER = <<<EOT
    </body>
</html>
EOT;
    protected function startRun(): void
    {
        $this->write(self::PAGE_HEADER);
    }
    protected function startClass(string $name): void
    {
        $this->write(
            \sprintf(
                self::CLASS_HEADER,
                $name,
                $this->currentTestClassPrettified
            )
        );
    }
    protected function onTest($name, bool $success = true): void
    {
        $this->write(
            \sprintf(
                "            <li style=\"color: %s;\">%s %s</li>\n",
                $success ? '#555753' : '#ef2929',
                $success ? '✓' : '❌',
                $name
            )
        );
    }
    protected function endClass(string $name): void
    {
        $this->write(self::CLASS_FOOTER);
    }
    protected function endRun(): void
    {
        $this->write(self::PAGE_FOOTER);
    }
}
