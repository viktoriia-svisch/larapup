#! /usr/bin/env python3
import sys
from os.path import abspath, expanduser, dirname, join
from itertools import chain
import json
import argparse
from vis import vis, unvis, VIS_WHITE
__dir__ = dirname(abspath(__file__))
OUTPUT_FILE = join(__dir__, '..', 'fixtures', 'unvis_fixtures.json')
CUSTOM_FIXTURES = [
    ''.join(chr(cp) for cp in range(1024)),
    'foo bar',
    'foo\nbar',
    "$bar = 'baz';",
    r'$foo = "\x20\\x20\\\x20\\\\x20"',
    '$foo = function($bar) use($baz) {\n\treturn $baz->getFoo()\n};'
]
RANGES = {
    'bmp': chain(range(0x0000, 0xD800), range(0xE000, 0xFFFF)),
    'small': chain(
        range(0x0000, 0x0250),
        range(0x0370, 0x0530),
        range(0x590, 0x0700),
        range(0x2E80, 0x2F00),
        range(0x3040, 0x3100)
    )
}
if __name__ == '__main__':
    argp = argparse.ArgumentParser(
        description='Generates test data for Psy\\Test\\Util\\StrTest')
    argp.add_argument('-f', '--format-output', action='store_true',
                      help='Indent JSON output to ease debugging')
    argp.add_argument('-a', '--all', action='store_true',
                      help=)
    argp.add_argument('-r', '--range',
                      help=,
                      choices=list(RANGES.keys()),
                      default='small')
    argp.add_argument('-o', '--output-file',
                      help=)
    args = argp.parse_args()
    cp_range = RANGES['bmp'] if args.all else RANGES[args.range]
    indent = 2 if args.format_output else None
    if args.output_file:
        OUTPUT_FILE = abspath(expanduser(args.output_file))
    fixtures = []
    for codepoint in cp_range:
        char = chr(codepoint)
        encoded = vis(char, VIS_WHITE)
        decoded = unvis(encoded)
        fixtures.append((encoded, decoded))
    for fixture in CUSTOM_FIXTURES:
        encoded = vis(fixture, VIS_WHITE)
        decoded = unvis(encoded)
        fixtures.append((encoded, decoded))
    with open(OUTPUT_FILE, 'w') as fp:
        json.dump(fixtures, fp, indent=indent)
    sys.exit(0)
