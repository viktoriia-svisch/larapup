<?php
namespace Illuminate\Validation\Concerns;
use DateTime;
use Countable;
use Exception;
use Throwable;
use DateTimeZone;
use DateTimeInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Validation\ValidationData;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
trait ValidatesAttributes
{
    public function validateAccepted($attribute, $value)
    {
        $acceptable = ['yes', 'on', '1', 1, true, 'true'];
        return $this->validateRequired($attribute, $value) && in_array($value, $acceptable, true);
    }
    public function validateActiveUrl($attribute, $value)
    {
        if (! is_string($value)) {
            return false;
        }
        if ($url = parse_url($value, PHP_URL_HOST)) {
            try {
                return count(dns_get_record($url, DNS_A | DNS_AAAA)) > 0;
            } catch (Exception $e) {
                return false;
            }
        }
        return false;
    }
    public function validateBail()
    {
        return true;
    }
    public function validateBefore($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'before');
        return $this->compareDates($attribute, $value, $parameters, '<');
    }
    public function validateBeforeOrEqual($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'before_or_equal');
        return $this->compareDates($attribute, $value, $parameters, '<=');
    }
    public function validateAfter($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'after');
        return $this->compareDates($attribute, $value, $parameters, '>');
    }
    public function validateAfterOrEqual($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'after_or_equal');
        return $this->compareDates($attribute, $value, $parameters, '>=');
    }
    protected function compareDates($attribute, $value, $parameters, $operator)
    {
        if (! is_string($value) && ! is_numeric($value) && ! $value instanceof DateTimeInterface) {
            return false;
        }
        if ($format = $this->getDateFormat($attribute)) {
            return $this->checkDateTimeOrder($format, $value, $parameters[0], $operator);
        }
        if (! $date = $this->getDateTimestamp($parameters[0])) {
            $date = $this->getDateTimestamp($this->getValue($parameters[0]));
        }
        return $this->compare($this->getDateTimestamp($value), $date, $operator);
    }
    protected function getDateFormat($attribute)
    {
        if ($result = $this->getRule($attribute, 'DateFormat')) {
            return $result[1][0];
        }
    }
    protected function getDateTimestamp($value)
    {
        if ($value instanceof DateTimeInterface) {
            return $value->getTimestamp();
        }
        if ($this->isTestingRelativeDateTime($value)) {
            $date = $this->getDateTime($value);
            if (! is_null($date)) {
                return $date->getTimestamp();
            }
        }
        return strtotime($value);
    }
    protected function checkDateTimeOrder($format, $first, $second, $operator)
    {
        $firstDate = $this->getDateTimeWithOptionalFormat($format, $first);
        if (! $secondDate = $this->getDateTimeWithOptionalFormat($format, $second)) {
            $secondDate = $this->getDateTimeWithOptionalFormat($format, $this->getValue($second));
        }
        return ($firstDate && $secondDate) && ($this->compare($firstDate, $secondDate, $operator));
    }
    protected function getDateTimeWithOptionalFormat($format, $value)
    {
        if ($date = DateTime::createFromFormat('!'.$format, $value)) {
            return $date;
        }
        return $this->getDateTime($value);
    }
    protected function getDateTime($value)
    {
        try {
            if ($this->isTestingRelativeDateTime($value)) {
                return new Carbon($value);
            }
            return new DateTime($value);
        } catch (Exception $e) {
        }
    }
    protected function isTestingRelativeDateTime($value)
    {
        return Carbon::hasTestNow() && is_string($value) && (
            $value === 'now' || Carbon::hasRelativeKeywords($value)
        );
    }
    public function validateAlpha($attribute, $value)
    {
        return is_string($value) && preg_match('/^[\pL\pM]+$/u', $value);
    }
    public function validateAlphaDash($attribute, $value)
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }
        return preg_match('/^[\pL\pM\pN_-]+$/u', $value) > 0;
    }
    public function validateAlphaNum($attribute, $value)
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }
        return preg_match('/^[\pL\pM\pN]+$/u', $value) > 0;
    }
    public function validateArray($attribute, $value)
    {
        return is_array($value);
    }
    public function validateBetween($attribute, $value, $parameters)
    {
        $this->requireParameterCount(2, $parameters, 'between');
        $size = $this->getSize($attribute, $value);
        return $size >= $parameters[0] && $size <= $parameters[1];
    }
    public function validateBoolean($attribute, $value)
    {
        $acceptable = [true, false, 0, 1, '0', '1'];
        return in_array($value, $acceptable, true);
    }
    public function validateConfirmed($attribute, $value)
    {
        return $this->validateSame($attribute, $value, [$attribute.'_confirmation']);
    }
    public function validateDate($attribute, $value)
    {
        if ($value instanceof DateTimeInterface) {
            return true;
        }
        if ((! is_string($value) && ! is_numeric($value)) || strtotime($value) === false) {
            return false;
        }
        $date = date_parse($value);
        return checkdate($date['month'], $date['day'], $date['year']);
    }
    public function validateDateFormat($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'date_format');
        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }
        $format = $parameters[0];
        $date = DateTime::createFromFormat('!'.$format, $value);
        return $date && $date->format($format) == $value;
    }
    public function validateDateEquals($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'date_equals');
        return $this->compareDates($attribute, $value, $parameters, '=');
    }
    public function validateDifferent($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'different');
        foreach ($parameters as $parameter) {
            $other = Arr::get($this->data, $parameter);
            if (is_null($other) || $value === $other) {
                return false;
            }
        }
        return true;
    }
    public function validateDigits($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'digits');
        return ! preg_match('/[^0-9]/', $value)
                    && strlen((string) $value) == $parameters[0];
    }
    public function validateDigitsBetween($attribute, $value, $parameters)
    {
        $this->requireParameterCount(2, $parameters, 'digits_between');
        $length = strlen((string) $value);
        return ! preg_match('/[^0-9]/', $value)
                    && $length >= $parameters[0] && $length <= $parameters[1];
    }
    public function validateDimensions($attribute, $value, $parameters)
    {
        if ($this->isValidFileInstance($value) && $value->getClientMimeType() === 'image/svg+xml') {
            return true;
        }
        if (! $this->isValidFileInstance($value) || ! $sizeDetails = @getimagesize($value->getRealPath())) {
            return false;
        }
        $this->requireParameterCount(1, $parameters, 'dimensions');
        [$width, $height] = $sizeDetails;
        $parameters = $this->parseNamedParameters($parameters);
        if ($this->failsBasicDimensionChecks($parameters, $width, $height) ||
            $this->failsRatioCheck($parameters, $width, $height)) {
            return false;
        }
        return true;
    }
    protected function failsBasicDimensionChecks($parameters, $width, $height)
    {
        return (isset($parameters['width']) && $parameters['width'] != $width) ||
               (isset($parameters['min_width']) && $parameters['min_width'] > $width) ||
               (isset($parameters['max_width']) && $parameters['max_width'] < $width) ||
               (isset($parameters['height']) && $parameters['height'] != $height) ||
               (isset($parameters['min_height']) && $parameters['min_height'] > $height) ||
               (isset($parameters['max_height']) && $parameters['max_height'] < $height);
    }
    protected function failsRatioCheck($parameters, $width, $height)
    {
        if (! isset($parameters['ratio'])) {
            return false;
        }
        [$numerator, $denominator] = array_replace(
            [1, 1], array_filter(sscanf($parameters['ratio'], '%f/%d'))
        );
        $precision = 1 / max($width, $height);
        return abs($numerator / $denominator - $width / $height) > $precision;
    }
    public function validateDistinct($attribute, $value, $parameters)
    {
        $data = Arr::except($this->getDistinctValues($attribute), $attribute);
        if (in_array('ignore_case', $parameters)) {
            return empty(preg_grep('/^'.preg_quote($value, '/').'$/iu', $data));
        }
        return ! in_array($value, array_values($data));
    }
    protected function getDistinctValues($attribute)
    {
        $attributeName = $this->getPrimaryAttribute($attribute);
        if (! property_exists($this, 'distinctValues')) {
            return $this->extractDistinctValues($attributeName);
        }
        if (! array_key_exists($attributeName, $this->distinctValues)) {
            $this->distinctValues[$attributeName] = $this->extractDistinctValues($attributeName);
        }
        return $this->distinctValues[$attributeName];
    }
    protected function extractDistinctValues($attribute)
    {
        $attributeData = ValidationData::extractDataFromPath(
            ValidationData::getLeadingExplicitAttributePath($attribute), $this->data
        );
        $pattern = str_replace('\*', '[^.]+', preg_quote($attribute, '#'));
        return Arr::where(Arr::dot($attributeData), function ($value, $key) use ($pattern) {
            return (bool) preg_match('#^'.$pattern.'\z#u', $key);
        });
    }
    public function validateEmail($attribute, $value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
    public function validateExists($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'exists');
        [$connection, $table] = $this->parseTable($parameters[0]);
        $column = $this->getQueryColumn($parameters, $attribute);
        $expected = is_array($value) ? count(array_unique($value)) : 1;
        return $this->getExistCount(
            $connection, $table, $column, $value, $parameters
        ) >= $expected;
    }
    protected function getExistCount($connection, $table, $column, $value, $parameters)
    {
        $verifier = $this->getPresenceVerifierFor($connection);
        $extra = $this->getExtraConditions(
            array_values(array_slice($parameters, 2))
        );
        if ($this->currentRule instanceof Exists) {
            $extra = array_merge($extra, $this->currentRule->queryCallbacks());
        }
        return is_array($value)
                ? $verifier->getMultiCount($table, $column, $value, $extra)
                : $verifier->getCount($table, $column, $value, null, null, $extra);
    }
    public function validateUnique($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'unique');
        [$connection, $table] = $this->parseTable($parameters[0]);
        $column = $this->getQueryColumn($parameters, $attribute);
        [$idColumn, $id] = [null, null];
        if (isset($parameters[2])) {
            [$idColumn, $id] = $this->getUniqueIds($parameters);
        }
        $verifier = $this->getPresenceVerifierFor($connection);
        $extra = $this->getUniqueExtra($parameters);
        if ($this->currentRule instanceof Unique) {
            $extra = array_merge($extra, $this->currentRule->queryCallbacks());
        }
        return $verifier->getCount(
            $table, $column, $value, $id, $idColumn, $extra
        ) == 0;
    }
    protected function getUniqueIds($parameters)
    {
        $idColumn = $parameters[3] ?? 'id';
        return [$idColumn, $this->prepareUniqueId($parameters[2])];
    }
    protected function prepareUniqueId($id)
    {
        if (preg_match('/\[(.*)\]/', $id, $matches)) {
            $id = $this->getValue($matches[1]);
        }
        if (strtolower($id) === 'null') {
            $id = null;
        }
        if (filter_var($id, FILTER_VALIDATE_INT) !== false) {
            $id = (int) $id;
        }
        return $id;
    }
    protected function getUniqueExtra($parameters)
    {
        if (isset($parameters[4])) {
            return $this->getExtraConditions(array_slice($parameters, 4));
        }
        return [];
    }
    protected function parseTable($table)
    {
        return Str::contains($table, '.') ? explode('.', $table, 2) : [null, $table];
    }
    protected function getQueryColumn($parameters, $attribute)
    {
        return isset($parameters[1]) && $parameters[1] !== 'NULL'
                    ? $parameters[1] : $this->guessColumnForQuery($attribute);
    }
    public function guessColumnForQuery($attribute)
    {
        if (in_array($attribute, Arr::collapse($this->implicitAttributes))
                && ! is_numeric($last = last(explode('.', $attribute)))) {
            return $last;
        }
        return $attribute;
    }
    protected function getExtraConditions(array $segments)
    {
        $extra = [];
        $count = count($segments);
        for ($i = 0; $i < $count; $i += 2) {
            $extra[$segments[$i]] = $segments[$i + 1];
        }
        return $extra;
    }
    public function validateFile($attribute, $value)
    {
        return $this->isValidFileInstance($value);
    }
    public function validateFilled($attribute, $value)
    {
        if (Arr::has($this->data, $attribute)) {
            return $this->validateRequired($attribute, $value);
        }
        return true;
    }
    public function validateGt($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'gt');
        $comparedToValue = $this->getValue($parameters[0]);
        $this->shouldBeNumeric($attribute, 'Gt');
        if (is_null($comparedToValue) && (is_numeric($value) && is_numeric($parameters[0]))) {
            return $this->getSize($attribute, $value) > $parameters[0];
        }
        $this->requireSameType($value, $comparedToValue);
        return $this->getSize($attribute, $value) > $this->getSize($attribute, $comparedToValue);
    }
    public function validateLt($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'lt');
        $comparedToValue = $this->getValue($parameters[0]);
        $this->shouldBeNumeric($attribute, 'Lt');
        if (is_null($comparedToValue) && (is_numeric($value) && is_numeric($parameters[0]))) {
            return $this->getSize($attribute, $value) < $parameters[0];
        }
        $this->requireSameType($value, $comparedToValue);
        return $this->getSize($attribute, $value) < $this->getSize($attribute, $comparedToValue);
    }
    public function validateGte($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'gte');
        $comparedToValue = $this->getValue($parameters[0]);
        $this->shouldBeNumeric($attribute, 'Gte');
        if (is_null($comparedToValue) && (is_numeric($value) && is_numeric($parameters[0]))) {
            return $this->getSize($attribute, $value) >= $parameters[0];
        }
        $this->requireSameType($value, $comparedToValue);
        return $this->getSize($attribute, $value) >= $this->getSize($attribute, $comparedToValue);
    }
    public function validateLte($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'lte');
        $comparedToValue = $this->getValue($parameters[0]);
        $this->shouldBeNumeric($attribute, 'Lte');
        if (is_null($comparedToValue) && (is_numeric($value) && is_numeric($parameters[0]))) {
            return $this->getSize($attribute, $value) <= $parameters[0];
        }
        $this->requireSameType($value, $comparedToValue);
        return $this->getSize($attribute, $value) <= $this->getSize($attribute, $comparedToValue);
    }
    public function validateImage($attribute, $value)
    {
        return $this->validateMimes($attribute, $value, ['jpeg', 'png', 'gif', 'bmp', 'svg']);
    }
    public function validateIn($attribute, $value, $parameters)
    {
        if (is_array($value) && $this->hasRule($attribute, 'Array')) {
            foreach ($value as $element) {
                if (is_array($element)) {
                    return false;
                }
            }
            return count(array_diff($value, $parameters)) === 0;
        }
        return ! is_array($value) && in_array((string) $value, $parameters);
    }
    public function validateInArray($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'in_array');
        $explicitPath = ValidationData::getLeadingExplicitAttributePath($parameters[0]);
        $attributeData = ValidationData::extractDataFromPath($explicitPath, $this->data);
        $otherValues = Arr::where(Arr::dot($attributeData), function ($value, $key) use ($parameters) {
            return Str::is($parameters[0], $key);
        });
        return in_array($value, $otherValues);
    }
    public function validateInteger($attribute, $value)
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
    public function validateIp($attribute, $value)
    {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }
    public function validateIpv4($attribute, $value)
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }
    public function validateIpv6($attribute, $value)
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }
    public function validateJson($attribute, $value)
    {
        if (! is_scalar($value) && ! method_exists($value, '__toString')) {
            return false;
        }
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }
    public function validateMax($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'max');
        if ($value instanceof UploadedFile && ! $value->isValid()) {
            return false;
        }
        return $this->getSize($attribute, $value) <= $parameters[0];
    }
    public function validateMimes($attribute, $value, $parameters)
    {
        if (! $this->isValidFileInstance($value)) {
            return false;
        }
        if ($this->shouldBlockPhpUpload($value, $parameters)) {
            return false;
        }
        return $value->getPath() !== '' && in_array($value->guessExtension(), $parameters);
    }
    public function validateMimetypes($attribute, $value, $parameters)
    {
        if (! $this->isValidFileInstance($value)) {
            return false;
        }
        if ($this->shouldBlockPhpUpload($value, $parameters)) {
            return false;
        }
        return $value->getPath() !== '' &&
                (in_array($value->getMimeType(), $parameters) ||
                 in_array(explode('/', $value->getMimeType())[0].'
    protected function shouldBlockPhpUpload($value, $parameters)
    {
        if (in_array('php', $parameters)) {
            return false;
        }
        $phpExtensions = [
            'php', 'php3', 'php4', 'php5', 'phtml',
        ];
        return ($value instanceof UploadedFile)
           ? in_array(trim(strtolower($value->getClientOriginalExtension())), $phpExtensions)
           : in_array(trim(strtolower($value->getExtension())), $phpExtensions);
    }
    public function validateMin($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'min');
        return $this->getSize($attribute, $value) >= $parameters[0];
    }
    public function validateNullable()
    {
        return true;
    }
    public function validateNotIn($attribute, $value, $parameters)
    {
        return ! $this->validateIn($attribute, $value, $parameters);
    }
    public function validateNumeric($attribute, $value)
    {
        return is_numeric($value);
    }
    public function validatePresent($attribute, $value)
    {
        return Arr::has($this->data, $attribute);
    }
    public function validateRegex($attribute, $value, $parameters)
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }
        $this->requireParameterCount(1, $parameters, 'regex');
        return preg_match($parameters[0], $value) > 0;
    }
    public function validateNotRegex($attribute, $value, $parameters)
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }
        $this->requireParameterCount(1, $parameters, 'not_regex');
        return preg_match($parameters[0], $value) < 1;
    }
    public function validateRequired($attribute, $value)
    {
        if (is_null($value)) {
            return false;
        } elseif (is_string($value) && trim($value) === '') {
            return false;
        } elseif ((is_array($value) || $value instanceof Countable) && count($value) < 1) {
            return false;
        } elseif ($value instanceof File) {
            return (string) $value->getPath() !== '';
        }
        return true;
    }
    public function validateRequiredIf($attribute, $value, $parameters)
    {
        $this->requireParameterCount(2, $parameters, 'required_if');
        $other = Arr::get($this->data, $parameters[0]);
        $values = array_slice($parameters, 1);
        if (is_bool($other)) {
            $values = $this->convertValuesToBoolean($values);
        }
        if (in_array($other, $values)) {
            return $this->validateRequired($attribute, $value);
        }
        return true;
    }
    protected function convertValuesToBoolean($values)
    {
        return array_map(function ($value) {
            if ($value === 'true') {
                return true;
            } elseif ($value === 'false') {
                return false;
            }
            return $value;
        }, $values);
    }
    public function validateRequiredUnless($attribute, $value, $parameters)
    {
        $this->requireParameterCount(2, $parameters, 'required_unless');
        $data = Arr::get($this->data, $parameters[0]);
        $values = array_slice($parameters, 1);
        if (! in_array($data, $values)) {
            return $this->validateRequired($attribute, $value);
        }
        return true;
    }
    public function validateRequiredWith($attribute, $value, $parameters)
    {
        if (! $this->allFailingRequired($parameters)) {
            return $this->validateRequired($attribute, $value);
        }
        return true;
    }
    public function validateRequiredWithAll($attribute, $value, $parameters)
    {
        if (! $this->anyFailingRequired($parameters)) {
            return $this->validateRequired($attribute, $value);
        }
        return true;
    }
    public function validateRequiredWithout($attribute, $value, $parameters)
    {
        if ($this->anyFailingRequired($parameters)) {
            return $this->validateRequired($attribute, $value);
        }
        return true;
    }
    public function validateRequiredWithoutAll($attribute, $value, $parameters)
    {
        if ($this->allFailingRequired($parameters)) {
            return $this->validateRequired($attribute, $value);
        }
        return true;
    }
    protected function anyFailingRequired(array $attributes)
    {
        foreach ($attributes as $key) {
            if (! $this->validateRequired($key, $this->getValue($key))) {
                return true;
            }
        }
        return false;
    }
    protected function allFailingRequired(array $attributes)
    {
        foreach ($attributes as $key) {
            if ($this->validateRequired($key, $this->getValue($key))) {
                return false;
            }
        }
        return true;
    }
    public function validateSame($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'same');
        $other = Arr::get($this->data, $parameters[0]);
        return $value === $other;
    }
    public function validateSize($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'size');
        return $this->getSize($attribute, $value) == $parameters[0];
    }
    public function validateSometimes()
    {
        return true;
    }
    public function validateStartsWith($attribute, $value, $parameters)
    {
        return Str::startsWith($value, $parameters);
    }
    public function validateString($attribute, $value)
    {
        return is_string($value);
    }
    public function validateTimezone($attribute, $value)
    {
        try {
            new DateTimeZone($value);
        } catch (Exception $e) {
            return false;
        } catch (Throwable $e) {
            return false;
        }
        return true;
    }
    public function validateUrl($attribute, $value)
    {
        if (! is_string($value)) {
            return false;
        }
        $pattern = '~^
            ((aaa|aaas|about|acap|acct|acr|adiumxtra|afp|afs|aim|apt|attachment|aw|barion|beshare|bitcoin|blob|bolo|callto|cap|chrome|chrome-extension|cid|coap|coaps|com-eventbrite-attendee|content|crid|cvs|data|dav|dict|dlna-playcontainer|dlna-playsingle|dns|dntp|dtn|dvb|ed2k|example|facetime|fax|feed|feedready|file|filesystem|finger|fish|ftp|geo|gg|git|gizmoproject|go|gopher|gtalk|h323|ham|hcp|http|https|iax|icap|icon|im|imap|info|iotdisco|ipn|ipp|ipps|irc|irc6|ircs|iris|iris.beep|iris.lwz|iris.xpc|iris.xpcs|itms|jabber|jar|jms|keyparc|lastfm|ldap|ldaps|magnet|mailserver|mailto|maps|market|message|mid|mms|modem|ms-help|ms-settings|ms-settings-airplanemode|ms-settings-bluetooth|ms-settings-camera|ms-settings-cellular|ms-settings-cloudstorage|ms-settings-emailandaccounts|ms-settings-language|ms-settings-location|ms-settings-lock|ms-settings-nfctransactions|ms-settings-notifications|ms-settings-power|ms-settings-privacy|ms-settings-proximity|ms-settings-screenrotation|ms-settings-wifi|ms-settings-workplace|msnim|msrp|msrps|mtqp|mumble|mupdate|mvn|news|nfs|ni|nih|nntp|notes|oid|opaquelocktoken|pack|palm|paparazzi|pkcs11|platform|pop|pres|prospero|proxy|psyc|query|redis|rediss|reload|res|resource|rmi|rsync|rtmfp|rtmp|rtsp|rtsps|rtspu|secondlife|s3|service|session|sftp|sgn|shttp|sieve|sip|sips|skype|smb|sms|smtp|snews|snmp|soap.beep|soap.beeps|soldat|spotify|ssh|steam|stun|stuns|submit|svn|tag|teamspeak|tel|teliaeid|telnet|tftp|things|thismessage|tip|tn3270|turn|turns|tv|udp|unreal|urn|ut2004|vemmi|ventrilo|videotex|view-source|wais|webcal|ws|wss|wtai|wyciwyg|xcon|xcon-userid|xfire|xmlrpc\.beep|xmlrpc.beeps|xmpp|xri|ymsgr|z39\.50|z39\.50r|z39\.50s)):
            (([\pL\pN-]+:)?([\pL\pN-]+)@)?          # basic auth
            (
                ([\pL\pN\pS\-\.])+(\.?([\pL]|xn\-\-[\pL\pN-]+)+\.?) # a domain name
                    |                                              # or
                \d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}                 # an IP address
                    |                                              # or
                \[
                    (?:(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){6})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:::(?:(?:(?:[0-9a-f]{1,4})):){5})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){4})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,1}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){3})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,2}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){2})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,3}(?:(?:[0-9a-f]{1,4})))?::(?:(?:[0-9a-f]{1,4})):)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,4}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,5}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,6}(?:(?:[0-9a-f]{1,4})))?::))))
                \]  # an IPv6 address
            )
            (:[0-9]+)?                              # a port (optional)
            (/?|/\S+|\?\S*|\#\S*)                   # a /, nothing, a / with something, a query or a fragment
        $~ixu';
        return preg_match($pattern, $value) > 0;
    }
    public function validateUuid($attribute, $value)
    {
        if (! is_string($value)) {
            return false;
        }
        return preg_match('/^[\da-f]{8}-[\da-f]{4}-[\da-f]{4}-[\da-f]{4}-[\da-f]{12}$/iD', $value) > 0;
    }
    protected function getSize($attribute, $value)
    {
        $hasNumeric = $this->hasRule($attribute, $this->numericRules);
        if (is_numeric($value) && $hasNumeric) {
            return $value;
        } elseif (is_array($value)) {
            return count($value);
        } elseif ($value instanceof File) {
            return $value->getSize() / 1024;
        }
        return mb_strlen($value);
    }
    public function isValidFileInstance($value)
    {
        if ($value instanceof UploadedFile && ! $value->isValid()) {
            return false;
        }
        return $value instanceof File;
    }
    protected function compare($first, $second, $operator)
    {
        switch ($operator) {
            case '<':
                return $first < $second;
            case '>':
                return $first > $second;
            case '<=':
                return $first <= $second;
            case '>=':
                return $first >= $second;
            case '=':
                return $first == $second;
            default:
                throw new InvalidArgumentException;
        }
    }
    protected function parseNamedParameters($parameters)
    {
        return array_reduce($parameters, function ($result, $item) {
            [$key, $value] = array_pad(explode('=', $item, 2), 2, null);
            $result[$key] = $value;
            return $result;
        });
    }
    protected function requireParameterCount($count, $parameters, $rule)
    {
        if (count($parameters) < $count) {
            throw new InvalidArgumentException("Validation rule $rule requires at least $count parameters.");
        }
    }
    protected function requireSameType($first, $second)
    {
        if (gettype($first) != gettype($second)) {
            throw new InvalidArgumentException('The values under comparison must be of the same type');
        }
    }
    protected function shouldBeNumeric($attribute, $rule)
    {
        if (is_numeric($this->getValue($attribute))) {
            $this->numericRules[] = $rule;
        }
    }
}
