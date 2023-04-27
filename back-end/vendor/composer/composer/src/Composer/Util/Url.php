<?php
namespace Composer\Util;
use Composer\Config;
class Url
{
    public static function updateDistReference(Config $config, $url, $ref)
    {
        $host = parse_url($url, PHP_URL_HOST);
        if ($host === 'api.github.com' || $host === 'github.com' || $host === 'www.github.com') {
            if (preg_match('{^https?:
                $url = 'https:
            } elseif (preg_match('{^https?:
                $url = 'https:
            } elseif (preg_match('{^https?:
                $url = 'https:
            }
        } elseif ($host === 'bitbucket.org' || $host === 'www.bitbucket.org') {
            if (preg_match('{^https?:
                $url = 'https:
            }
        } elseif ($host === 'gitlab.com' || $host === 'www.gitlab.com') {
            if (preg_match('{^https?:
                $url = 'https:
            }
        } elseif (in_array($host, $config->get('github-domains'), true)) {
            $url = preg_replace('{(/repos/[^/]+/[^/]+/(zip|tar)ball)(?:/.+)?$}i', '$1/'.$ref, $url);
        } elseif (in_array($host, $config->get('gitlab-domains'), true)) {
            $url = preg_replace('{(/api/v[34]/projects/[^/]+/repository/archive\.(?:zip|tar\.gz|tar\.bz2|tar)\?sha=).+$}i', '${1}'.$ref, $url);
        }
        return $url;
    }
}
