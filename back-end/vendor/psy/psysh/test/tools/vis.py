from ctypes import CDLL, c_char_p, c_int
from ctypes.util import find_library
__all__ = [
    'vis', 'unvis',
    'VIS_OCTAL', 'VIS_CSTYLE',
    'VIS_SP', 'VIS_TAB', 'VIS_NL', 'VIS_WHITE', 'VIS_SAFE',
    'VIS_NOSLASH', 'VIS_HTTP1808', 'VIS_HTTPSTYLE', 'VIS_MIMESTYLE',
    'VIS_HTTP1866', 'VIS_NOESCAPE', 'VIS_GLOB'
]
VIS_OCTAL = 0x0001
VIS_CSTYLE = 0x0002
VIS_SP = 0x0004
VIS_TAB = 0x0008
VIS_NL = 0x0010
VIS_WHITE = VIS_SP | VIS_TAB | VIS_NL
VIS_SAFE = 0x0020
VIS_NOSLASH = 0x0040
VIS_HTTP1808 = 0x0080
VIS_HTTPSTYLE = 0x0080
VIS_MIMESTYLE = 0x0100
VIS_HTTP1866 = 0x0200
VIS_NOESCAPE = 0x0400
VIS_GLOB = 0x1000
_libbsd = CDLL(find_library('bsd'))
_strvis = _libbsd.strvis
_strvis.argtypes = [c_char_p, c_char_p, c_int]
_strvis.restype = c_int
_strunvis = _libbsd.strunvis
_strvis.argtypes = [c_char_p, c_char_p]
_strvis.restype = c_int
def vis(src, flags=VIS_WHITE):
    src = bytes(src, 'utf-8')
    dst_p = c_char_p(bytes(len(src) * 4))
    src_p = c_char_p(src)
    flags = c_int(flags)
    bytes_written = _strvis(dst_p, src_p, flags)
    if -1 == bytes_written:
        raise RuntimeError('vis failed to encode string "{}"'.format(src))
    return dst_p.value.decode('utf-8')
def unvis(src):
    src = bytes(src, 'utf-8')
    dst_p = c_char_p(bytes(len(src)))
    src_p = c_char_p(src)
    bytes_written = _strunvis(dst_p, src_p)
    if -1 == bytes_written:
        raise RuntimeError('unvis failed to decode string "{}"'.format(src))
    return dst_p.value.decode('utf-8')
