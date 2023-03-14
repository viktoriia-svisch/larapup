#!/usr/bin/php
<?php
define('APACHE_MIME_TYPES_URL', 'https:
define('FREEDESKTOP_XML_URL', 'https:
function generateUpToDateMimeArray()
{
    $preamble = "<?php\n\n";
    $preamble .= "\n\n";
    $preamble .= "\n\n";
    $preamble .= "
    $mime_types = @file_get_contents(APACHE_MIME_TYPES_URL);
    $mime_xml = @file_get_contents(FREEDESKTOP_XML_URL);
    $valid_mime_types = [];
    if (false !== preg_match_all('/^#?([a-z0-9\-\+\/\.]+)[\t]+(.*)$/miu', $mime_types, $matches)) {
        $valid_mime_types_preset = [
            'php' => 'application/x-php',
            'php3' => 'application/x-php',
            'php4' => 'application/x-php',
            'php5' => 'application/x-php',
            'zip' => 'application/zip',
            'gif' => 'image/gif',
            'png' => 'image/png',
            'css' => 'text/css',
            'js' => 'text/javascript',
            'txt' => 'text/plain',
            'aif' => 'audio/x-aiff',
            'aiff' => 'audio/x-aiff',
            'avi' => 'video/avi',
            'bmp' => 'image/bmp',
            'bz2' => 'application/x-bz2',
            'csv' => 'text/csv',
            'dmg' => 'application/x-apple-diskimage',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'eml' => 'message/rfc822',
            'aps' => 'application/postscript',
            'exe' => 'application/x-ms-dos-executable',
            'flv' => 'video/x-flv',
            'gz' => 'application/x-gzip',
            'hqx' => 'application/stuffit',
            'htm' => 'text/html',
            'html' => 'text/html',
            'jar' => 'application/x-java-archive',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'm3u' => 'audio/x-mpegurl',
            'm4a' => 'audio/mp4',
            'mdb' => 'application/x-msaccess',
            'mid' => 'audio/midi',
            'midi' => 'audio/midi',
            'mov' => 'video/quicktime',
            'mp3' => 'audio/mpeg',
            'mp4' => 'video/mp4',
            'mpeg' => 'video/mpeg',
            'mpg' => 'video/mpeg',
            'odg' => 'vnd.oasis.opendocument.graphics',
            'odp' => 'vnd.oasis.opendocument.presentation',
            'odt' => 'vnd.oasis.opendocument.text',
            'ods' => 'vnd.oasis.opendocument.spreadsheet',
            'ogg' => 'audio/ogg',
            'pdf' => 'application/pdf',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'ps' => 'application/postscript',
            'rar' => 'application/x-rar-compressed',
            'rtf' => 'application/rtf',
            'tar' => 'application/x-tar',
            'sit' => 'application/x-stuffit',
            'svg' => 'image/svg+xml',
            'tif' => 'image/tiff',
            'tiff' => 'image/tiff',
            'ttf' => 'application/x-font-truetype',
            'vcf' => 'text/x-vcard',
            'wav' => 'audio/wav',
            'wma' => 'audio/x-ms-wma',
            'wmv' => 'audio/x-ms-wmv',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xml' => 'application/xml',
        ];
        foreach ($valid_mime_types_preset as $extension => $mime_type) {
            $valid_mime_types[$extension] = "'{$extension}' => '{$mime_type}'";
        }
        foreach ($matches[2] as $i => $extensions) {
            $extensions = explode(' ', strtolower($extensions));
            if (!is_array($extensions)) {
                $extensions = [$extensions];
            }
            foreach ($extensions as $extension) {
                $mime_type = $matches[1][$i];
                if (strlen($extension) < 10) {
                    if (!isset($valid_mime_types[$mime_type])) {
                        $valid_mime_types[$extension] = "'{$extension}' => '{$mime_type}'";
                    }
                }
            }
        }
    }
    $xml = simplexml_load_string($mime_xml);
    foreach ($xml as $node) {
        if (!isset($node->glob['pattern'])) {
            continue;
        }
        foreach ((array) $node->glob['pattern'] as $extension) {
            if (false === strpos($extension, '.')) {
                continue;
            }
            $extension = explode('.', strtolower($extension));
            $extension = end($extension);
        }
        if (isset($node->glob['pattern'][0])) {
            $mime_type = strtolower((string) $node['type']);
            $extension = strtolower(trim($node->glob['ddpattern'][0], '*.'));
            if (false !== strpos($extension, '.') || strlen($extension) < 1 || strlen($extension) > 9) {
                continue;
            }
            if (!isset($valid_mime_types[$mime_type])) {
                $valid_mime_types[$extension] = "'{$extension}' => '{$mime_type}'";
            }
        }
    }
    $valid_mime_types = array_unique($valid_mime_types);
    ksort($valid_mime_types);
    $output = "$preamble\$swift_mime_types = array(\n    ".implode($valid_mime_types, ",\n    ")."\n);";
    @file_put_contents('./mime_types.php', $output);
}
generateUpToDateMimeArray();
