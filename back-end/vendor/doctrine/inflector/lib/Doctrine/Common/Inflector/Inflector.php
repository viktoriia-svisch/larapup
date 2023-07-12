<?php
namespace Doctrine\Common\Inflector;
class Inflector
{
    private static $plural = array(
        'rules' => array(
            '/(s)tatus$/i' => '\1\2tatuses',
            '/(quiz)$/i' => '\1zes',
            '/^(ox)$/i' => '\1\2en',
            '/([m|l])ouse$/i' => '\1ice',
            '/(matr|vert|ind)(ix|ex)$/i' => '\1ices',
            '/(x|ch|ss|sh)$/i' => '\1es',
            '/([^aeiouy]|qu)y$/i' => '\1ies',
            '/(hive|gulf)$/i' => '\1s',
            '/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
            '/sis$/i' => 'ses',
            '/([ti])um$/i' => '\1a',
            '/(c)riterion$/i' => '\1riteria',
            '/(p)erson$/i' => '\1eople',
            '/(m)an$/i' => '\1en',
            '/(c)hild$/i' => '\1hildren',
            '/(f)oot$/i' => '\1eet',
            '/(buffal|her|potat|tomat|volcan)o$/i' => '\1\2oes',
            '/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|vir)us$/i' => '\1i',
            '/us$/i' => 'uses',
            '/(alias)$/i' => '\1es',
            '/(analys|ax|cris|test|thes)is$/i' => '\1es',
            '/s$/' => 's',
            '/^$/' => '',
            '/$/' => 's',
        ),
        'uninflected' => array(
            '.*[nrlm]ese',
            '.*deer',
            '.*fish',
            '.*measles',
            '.*ois',
            '.*pox',
            '.*sheep',
            'people',
            'cookie',
            'police',
        ),
        'irregular' => array(
            'atlas' => 'atlases',
            'axe' => 'axes',
            'beef' => 'beefs',
            'brother' => 'brothers',
            'cafe' => 'cafes',
            'chateau' => 'chateaux',
            'niveau' => 'niveaux',
            'child' => 'children',
            'cookie' => 'cookies',
            'corpus' => 'corpuses',
            'cow' => 'cows',
            'criterion' => 'criteria',
            'curriculum' => 'curricula',
            'demo' => 'demos',
            'domino' => 'dominoes',
            'echo' => 'echoes',
            'foot' => 'feet',
            'fungus' => 'fungi',
            'ganglion' => 'ganglions',
            'genie' => 'genies',
            'genus' => 'genera',
            'goose' => 'geese',
            'graffito' => 'graffiti',
            'hippopotamus' => 'hippopotami',
            'hoof' => 'hoofs',
            'human' => 'humans',
            'iris' => 'irises',
            'larva' => 'larvae',
            'leaf' => 'leaves',
            'loaf' => 'loaves',
            'man' => 'men',
            'medium' => 'media',
            'memorandum' => 'memoranda',
            'money' => 'monies',
            'mongoose' => 'mongooses',
            'motto' => 'mottoes',
            'move' => 'moves',
            'mythos' => 'mythoi',
            'niche' => 'niches',
            'nucleus' => 'nuclei',
            'numen' => 'numina',
            'occiput' => 'occiputs',
            'octopus' => 'octopuses',
            'opus' => 'opuses',
            'ox' => 'oxen',
            'passerby' => 'passersby',
            'penis' => 'penises',
            'person' => 'people',
            'plateau' => 'plateaux',
            'runner-up' => 'runners-up',
            'sex' => 'sexes',
            'soliloquy' => 'soliloquies',
            'son-in-law' => 'sons-in-law',
            'syllabus' => 'syllabi',
            'testis' => 'testes',
            'thief' => 'thieves',
            'tooth' => 'teeth',
            'tornado' => 'tornadoes',
            'trilby' => 'trilbys',
            'turf' => 'turfs',
            'valve' => 'valves',
            'volcano' => 'volcanoes',
        )
    );
    private static $singular = array(
        'rules' => array(
            '/(s)tatuses$/i' => '\1\2tatus',
            '/^(.*)(menu)s$/i' => '\1\2',
            '/(quiz)zes$/i' => '\\1',
            '/(matr)ices$/i' => '\1ix',
            '/(vert|ind)ices$/i' => '\1ex',
            '/^(ox)en/i' => '\1',
            '/(alias)(es)*$/i' => '\1',
            '/(buffal|her|potat|tomat|volcan)oes$/i' => '\1o',
            '/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|viri?)i$/i' => '\1us',
            '/([ftw]ax)es/i' => '\1',
            '/(analys|ax|cris|test|thes)es$/i' => '\1is',
            '/(shoe|slave)s$/i' => '\1',
            '/(o)es$/i' => '\1',
            '/ouses$/' => 'ouse',
            '/([^a])uses$/' => '\1us',
            '/([m|l])ice$/i' => '\1ouse',
            '/(x|ch|ss|sh)es$/i' => '\1',
            '/(m)ovies$/i' => '\1\2ovie',
            '/(s)eries$/i' => '\1\2eries',
            '/([^aeiouy]|qu)ies$/i' => '\1y',
            '/([lr])ves$/i' => '\1f',
            '/(tive)s$/i' => '\1',
            '/(hive)s$/i' => '\1',
            '/(drive)s$/i' => '\1',
            '/(dive)s$/i' => '\1',
            '/(olive)s$/i' => '\1',
            '/([^fo])ves$/i' => '\1fe',
            '/(^analy)ses$/i' => '\1sis',
            '/(analy|diagno|^ba|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
            '/(c)riteria$/i' => '\1riterion',
            '/([ti])a$/i' => '\1um',
            '/(p)eople$/i' => '\1\2erson',
            '/(m)en$/i' => '\1an',
            '/(c)hildren$/i' => '\1\2hild',
            '/(f)eet$/i' => '\1oot',
            '/(n)ews$/i' => '\1\2ews',
            '/eaus$/' => 'eau',
            '/^(.*us)$/' => '\\1',
            '/s$/i' => '',
        ),
        'uninflected' => array(
            '.*[nrlm]ese',
            '.*deer',
            '.*fish',
            '.*measles',
            '.*ois',
            '.*pox',
            '.*sheep',
            '.*ss',
            'data',
            'police',
            'pants',
            'clothes',
        ),
        'irregular' => array(
            'abuses'     => 'abuse',
            'avalanches' => 'avalanche',
            'caches'     => 'cache',
            'criteria'   => 'criterion',
            'curves'     => 'curve',
            'emphases'   => 'emphasis',
            'foes'       => 'foe',
            'geese'      => 'goose',
            'graves'     => 'grave',
            'hoaxes'     => 'hoax',
            'media'      => 'medium',
            'neuroses'   => 'neurosis',
            'waves'      => 'wave',
            'oases'      => 'oasis',
            'valves'     => 'valve',
        )
    );
    private static $uninflected = array(
        '.*?media', 'Amoyese', 'audio', 'bison', 'Borghese', 'bream', 'breeches',
        'britches', 'buffalo', 'cantus', 'carp', 'chassis', 'clippers', 'cod', 'coitus', 'compensation', 'Congoese',
        'contretemps', 'coreopsis', 'corps', 'data', 'debris', 'deer', 'diabetes', 'djinn', 'education', 'eland',
        'elk', 'emoji', 'equipment', 'evidence', 'Faroese', 'feedback', 'fish', 'flounder', 'Foochowese',
        'Furniture', 'furniture', 'gallows', 'Genevese', 'Genoese', 'Gilbertese', 'gold', 
        'headquarters', 'herpes', 'hijinks', 'Hottentotese', 'information', 'innings', 'jackanapes', 'jedi',
        'Kiplingese', 'knowledge', 'Kongoese', 'love', 'Lucchese', 'Luggage', 'mackerel', 'Maltese', 'metadata',
        'mews', 'moose', 'mumps', 'Nankingese', 'news', 'nexus', 'Niasese', 'nutrition', 'offspring',
        'Pekingese', 'Piedmontese', 'pincers', 'Pistoiese', 'plankton', 'pliers', 'pokemon', 'police', 'Portuguese',
        'proceedings', 'rabies', 'rain', 'rhinoceros', 'rice', 'salmon', 'Sarawakese', 'scissors', 'sea[- ]bass',
        'series', 'Shavese', 'shears', 'sheep', 'siemens', 'species', 'staff', 'swine', 'traffic',
        'trousers', 'trout', 'tuna', 'us', 'Vermontese', 'Wenchowese', 'wheat', 'whiting', 'wildebeest', 'Yengeese'
    );
    private static $cache = array();
    private static $initialState = array();
    public static function tableize(string $word) : string
    {
        return strtolower(preg_replace('~(?<=\\w)([A-Z])~', '_$1', $word));
    }
    public static function classify(string $word) : string
    {
        return str_replace([' ', '_', '-'], '', ucwords($word, ' _-'));
    }
    public static function camelize(string $word) : string
    {
        return lcfirst(self::classify($word));
    }
    public static function ucwords(string $string, string $delimiters = " \n\t\r\0\x0B-") : string
    {
        return ucwords($string, $delimiters);
    }
    public static function reset() : void
    {
        if (empty(self::$initialState)) {
            self::$initialState = get_class_vars('Inflector');
            return;
        }
        foreach (self::$initialState as $key => $val) {
            if ($key !== 'initialState') {
                self::${$key} = $val;
            }
        }
    }
    public static function rules(string $type, iterable $rules, bool $reset = false) : void
    {
        foreach ($rules as $rule => $pattern) {
            if ( ! is_array($pattern)) {
                continue;
            }
            if ($reset) {
                self::${$type}[$rule] = $pattern;
            } else {
                self::${$type}[$rule] = ($rule === 'uninflected')
                    ? array_merge($pattern, self::${$type}[$rule])
                    : $pattern + self::${$type}[$rule];
            }
            unset($rules[$rule], self::${$type}['cache' . ucfirst($rule)]);
            if (isset(self::${$type}['merged'][$rule])) {
                unset(self::${$type}['merged'][$rule]);
            }
            if ($type === 'plural') {
                self::$cache['pluralize'] = self::$cache['tableize'] = array();
            } elseif ($type === 'singular') {
                self::$cache['singularize'] = array();
            }
        }
        self::${$type}['rules'] = $rules + self::${$type}['rules'];
    }
    public static function pluralize(string $word) : string
    {
        if (isset(self::$cache['pluralize'][$word])) {
            return self::$cache['pluralize'][$word];
        }
        if (!isset(self::$plural['merged']['irregular'])) {
            self::$plural['merged']['irregular'] = self::$plural['irregular'];
        }
        if (!isset(self::$plural['merged']['uninflected'])) {
            self::$plural['merged']['uninflected'] = array_merge(self::$plural['uninflected'], self::$uninflected);
        }
        if (!isset(self::$plural['cacheUninflected']) || !isset(self::$plural['cacheIrregular'])) {
            self::$plural['cacheUninflected'] = '(?:' . implode('|', self::$plural['merged']['uninflected']) . ')';
            self::$plural['cacheIrregular']   = '(?:' . implode('|', array_keys(self::$plural['merged']['irregular'])) . ')';
        }
        if (preg_match('/(.*)\\b(' . self::$plural['cacheIrregular'] . ')$/i', $word, $regs)) {
            self::$cache['pluralize'][$word] = $regs[1] . $word[0] . substr(self::$plural['merged']['irregular'][strtolower($regs[2])], 1);
            return self::$cache['pluralize'][$word];
        }
        if (preg_match('/^(' . self::$plural['cacheUninflected'] . ')$/i', $word, $regs)) {
            self::$cache['pluralize'][$word] = $word;
            return $word;
        }
        foreach (self::$plural['rules'] as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                self::$cache['pluralize'][$word] = preg_replace($rule, $replacement, $word);
                return self::$cache['pluralize'][$word];
            }
        }
    }
    public static function singularize(string $word) : string
    {
        if (isset(self::$cache['singularize'][$word])) {
            return self::$cache['singularize'][$word];
        }
        if (!isset(self::$singular['merged']['uninflected'])) {
            self::$singular['merged']['uninflected'] = array_merge(
                self::$singular['uninflected'],
                self::$uninflected
            );
        }
        if (!isset(self::$singular['merged']['irregular'])) {
            self::$singular['merged']['irregular'] = array_merge(
                self::$singular['irregular'],
                array_flip(self::$plural['irregular'])
            );
        }
        if (!isset(self::$singular['cacheUninflected']) || !isset(self::$singular['cacheIrregular'])) {
            self::$singular['cacheUninflected'] = '(?:' . implode('|', self::$singular['merged']['uninflected']) . ')';
            self::$singular['cacheIrregular'] = '(?:' . implode('|', array_keys(self::$singular['merged']['irregular'])) . ')';
        }
        if (preg_match('/(.*)\\b(' . self::$singular['cacheIrregular'] . ')$/i', $word, $regs)) {
            self::$cache['singularize'][$word] = $regs[1] . $word[0] . substr(self::$singular['merged']['irregular'][strtolower($regs[2])], 1);
            return self::$cache['singularize'][$word];
        }
        if (preg_match('/^(' . self::$singular['cacheUninflected'] . ')$/i', $word, $regs)) {
            self::$cache['singularize'][$word] = $word;
            return $word;
        }
        foreach (self::$singular['rules'] as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                self::$cache['singularize'][$word] = preg_replace($rule, $replacement, $word);
                return self::$cache['singularize'][$word];
            }
        }
        self::$cache['singularize'][$word] = $word;
        return $word;
    }
}
