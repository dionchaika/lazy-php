<?php

namespace Lazy\Http;

use Throwable;
use RuntimeException;
use InvalidArgumentException;

/**
 * The HTTP cookie model class.
 *
 * @see https://tools.ietf.org/html/rfc6265
 */
class Cookie
{
    /**
     * The date format.
     */
    const DATE_FORMAT = 'D, d M Y H:i:s T';

    /**
     * The cookie name.
     *
     * @var string
     */
    protected $name;

    /**
     * The cookie value.
     *
     * @var string|null
     */
    protected $value;

    /**
     * The cookie Expires attribute.
     *
     * @var string|null
     */
    protected $expires;

    /**
     * The cookie Max-Age attribute.
     *
     * @var int|null
     */
    protected $maxAge;

    /**
     * The cookie Domain attribute.
     *
     * @var string|null
     */
    protected $domain;

    /**
     * The cookie Path attribute.
     *
     * @var string|null
     */
    protected $path;

    /**
     * The cookie Secure attribute.
     *
     * @var bool
     */
    protected $secure = false;

    /**
     * The cookie HttpOnly attribute.
     *
     * @var bool
     */
    protected $httpOnly = false;

    /**
     * The cookie SameSite attribute.
     *
     * @var string|null
     */
    protected $sameSite;

    /**
     * Has the cookie a __Host- prefix.
     *
     * @var bool
     */
    protected $hasHostPrefix = false;

    /**
     * Has the cookie a __Secure- prefix.
     *
     * @var bool
     */
    protected $hasSecurePrefix = false;

    /**
     * The cookie constructor.
     *
     * @param  string  $name
     * @param  string|null  $value
     * @param  string|null  $expires
     * @param  int|null  $maxAge
     * @param  string|null  $domain
     * @param  string|null  $path
     * @param  bool  $secure
     * @param  bool  $httpOnly
     * @param  string|null  $sameSite
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($name,
                                $value = null,
                                $expires = null,
                                $maxAge = null,
                                $domain = null,
                                $path = null,
                                $secure = false,
                                $httpOnly = false,
                                $sameSite = null)
    {
        if (0 === strpos($name, '__Host-')) {
            $name = substr($name, 7);
            $this->hasHostPrefix = true;
        } else if (0 === strpos($name, '__Secure-')) {
            $name = substr($name, 9);
            $this->hasSecurePrefix = true;
        }

        $this->name = $this->filterName($name);
        $this->value = $this->filterValue($value);
        $this->expires = $this->filterExpires($expires);
        $this->maxAge = $maxAge;
        $this->domain = $this->filterDomain($domain);
        $this->path = $this->filterPath($path);
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
        $this->sameSite = $this->filterSameSite($sameSite);
    }

    /**
     * Create a new cookie.
     *
     * @param  string  $name
     * @param  string|null  $value
     * @param  int|null  $expiryTime
     * @param  string|null  $domain
     * @param  string|null  $path
     * @param  bool  $secure
     * @param  bool  $httpOnly
     * @param  string|null  $sameSite
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public static function create($name,
                                  $value = null,
                                  $expiryTime = null,
                                  $domain = null,
                                  $path = null,
                                  $secure = false,
                                  $httpOnly = false,
                                  $sameSite = null)
    {
        if (null === $expiryTime) {
            $maxAge = $expires = null;
        } else {
            $maxAge = $expiryTime - time();
            $expires = gmdate(static::DATE_FORMAT, $expiryTime);
        }

        return new static($name, $value, $expires, $maxAge, $domain, $path, $secure, $httpOnly, $sameSite);
    }

    /**
     * Create a new cookie from string.
     *
     * @param  string  $cookie
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public static function fromString($cookie)
    {
        $parts = explode(';', $cookie);

        if (false === strpos($parts[0], '=')) {
            throw new InvalidArgumentException(
                "Invalid cookie: {$cookie}! "
                ."Cookie must be compliant with the \"RFC 6265\" standart."
            );
        }

        $pairParts = explode('=', array_shift($parts), 2);

        $name = trim($pairParts[0]);

        $value = ('' !== $value = trim($pairParts[1])) ? $value : null;

        $attributes = [

            'Expires'  => null,
            'Max-Age'  => null,
            'Domain'   => null,
            'Path'     => null,
            'Secure'   => false,
            'HttpOnly' => false,
            'SameSite' => null

        ];

        foreach ($parts as $part) {
            $attributeParts = explode('=', $part, 2);

            $attributeName = trim($attributeParts[0]);

            $attributeValue = isset($attributeParts[1]) ? trim($attributeParts[1]) : true;

            foreach (array_keys($attributes) as $attribute) {
                if (0 === strcasecmp($attribute, $attributeName)) {
                    $attributes[$attribute] = $attributeValue;
                    continue 2;
                }
            }
        }

        if (null !== $attributes['Expires']) {
            $time = $day = $month = $year = null;

            $expiresParts = preg_split('/[\x09\x20-\x2f\x3b-\x40\x5b-\x60\x7b-\x7e]+/', $attributes['Expires']);

            foreach ($expiresParts as $expiresPart) {
                if (null === $time && preg_match('/^(\d{1,2})\:(\d{1,2})\:(\d{1,2})$/', $expiresPart, $matches)) {
                    $time = [

                        'hours'   => (int) $matches[1],
                        'minutes' => (int) $matches[2],
                        'seconds' => (int) $matches[3]

                    ];

                    continue;
                }

                if (null === $day && preg_match('/^(\d{1,2})$/', $expiresPart, $matches)) {
                    $day = (int) $matches[1];
                    continue;
                }

                $monthRegEx = '/^(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)$/i';

                if (null === $month && preg_match($monthRegEx, $expiresPart, $matches)) {
                    switch (strtolower($matches[1])) {
                        case 'jan': $month = 1;  break;
                        case 'feb': $month = 2;  break;
                        case 'mar': $month = 3;  break;
                        case 'apr': $month = 4;  break;
                        case 'may': $month = 5;  break;
                        case 'jun': $month = 6;  break;
                        case 'jul': $month = 7;  break;
                        case 'aug': $month = 8;  break;
                        case 'sep': $month = 9;  break;
                        case 'oct': $month = 10; break;
                        case 'nov': $month = 11; break;
                        case 'dec': $month = 12; break;
                    }

                    continue;
                }

                if (null === $year && preg_match('/^(\d{2,4})$/', $expiresPart, $matches)) {
                    $year = (int) $matches[1];
                    continue;
                }
            }

            if (0 <= $year && 69 >= $year) {
                $year += 2000;
            } else if (70 <= $year && 99 >= $year) {
                $year += 1900;
            }

            if (
                null === $time ||
                null === $day ||
                null === $month ||
                null === $year ||
                1 > $day ||
                31 < $day ||
                1601 > $year ||
                23 < $time['hours'] ||
                59 < $time['minutes'] ||
                59 < $time['seconds'] ||
                false === $expires = gmmktime($time['hours'], $time['minutes'], $time['seconds'], $month, $day, $year)
            ) {
                $attributes['Expires'] = null;
            } else {
                $attributes['Expires'] = $expires;
            }
        }

        if (null !== $attributes['Max-Age']) {
            if (preg_match('/^\-?\d+$/', $attributes['Max-Age'])) {
                $attributes['Max-Age'] = (int) $attributes['Max-Age'];
            } else {
                $attributes['Max-Age'] = null;
            }
        }

        if (null !== $attributes['Max-Age']) {
            $expiryTime = time() + $attributes['Max-Age'];
        } else if (null !== $attributes['Expires']) {
            $expiryTime = $attributes['Expires'];
        } else {
            $expiryTime = null;
        }

        return static::create($name, $value, $expiryTime, $attributes['Domain'], $attributes['Path'], $attributes['Secure'], $attributes['HttpOnly'], $attributes['SameSite']);
    }

    /**
     * Get the cookie pair.
     *
     * @return string
     */
    public function getPair()
    {
        $pair = $this->name.'=';

        if (null !== $this->value) {
            $pair .= $this->value;
        }

        return $pair;
    }

    /**
     * Get the cookie name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the cookie value.
     *
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the cookie Expires attribute.
     *
     * @return string|null
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * Get the cookie Max-Age attribute.
     *
     * @return int|null
     */
    public function getMaxAge()
    {
        return $this->maxAge;
    }

    /**
     * Get the cookie Domain attribute.
     *
     * @return string|null
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Get the cookie Path attribute.
     *
     * @return string|null
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get the cookie Secure attribute.
     *
     * @return bool
     */
    public function getSecure()
    {
        return $this->secure;
    }

    /**
     * Get the cookie HttpOnly attribute.
     *
     * @return bool
     */
    public function getHttpOnly()
    {
        return $this->httpOnly;
    }

    /**
     * Get the cookie SameSite attribute.
     *
     * @return string|null
     */
    public function getSameSite()
    {
        return $this->sameSite;
    }

    /**
     * Return an instance
     * with the specified cookie value.
     *
     * @param  string|null  $value
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withValue($value)
    {
        $new = clone $this;

        $new->value = $new->filterValue($value);

        return $new;
    }

    /**
     * Return an instance
     * with the specified cookie Expires attribute.
     *
     * @param  string|null  $expires
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withExpires($expires)
    {
        $new = clone $this;

        $new->expires = $new->filterExpires($expires);

        return $new;
    }

    /**
     * Return an instance
     * with the specified cookie Max-Age attribute.
     *
     * @param  int|null  $maxAge
     * @return static
     */
    public function withMaxAge($maxAge)
    {
        $new = clone $this;

        $new->maxAge = $maxAge;

        return $new;
    }

    /**
     * Return an instance
     * with the specified cookie Domain attribute.
     *
     * @param  string|null  $domain
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withDomain($domain)
    {
        $new = clone $this;

        $new->domain = $new->filterDomain($domain);

        return $new;
    }

    /**
     * Return an instance
     * with the specified cookie Path attribute.
     *
     * @param  string|null  $path
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withPath($path)
    {
        $new = clone $this;

        $new->path = $new->filterPath($path);

        return $new;
    }

    /**
     * Return an instance
     * with the specified cookie Secure attribute.
     *
     * @param  bool  $secure
     * @return static
     */
    public function withSecure($secure)
    {
        $new = clone $this;

        $new->secure = $secure;

        return $new;
    }

    /**
     * Return an instance
     * with the specified cookie HttpOnly attribute.
     *
     * @param  bool  $httpOnly
     * @return static
     */
    public function withHttpOnly($httpOnly)
    {
        $new = clone $this;

        $new->httpOnly = $httpOnly;

        return $new;
    }

    /**
     * Return an instance
     * with the specified cookie SameSite attribute.
     *
     * @param  string|null  $sameSite
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withSameSite($sameSite)
    {
        $new = clone $this;

        $new->sameSite = $new->filterSameSite($sameSite);

        return $new;
    }

    /**
     * Expire the cookie.
     *
     * @return $this
     */
    public function expire()
    {
        $this->maxAge = -2147483648;
        $this->expires = gmdate('D, d M Y H:i:s T', -2147483648);

        return $this;
    }

    /**
     * Sign the cookie.
     *
     * @param  string  $key
     * @return $this
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function sign($key)
    {
        if (null !== $this->value && '' !== $this->value) {
            if (32 > mb_strlen($key)) {
                throw new InvalidArgumentException(
                    "Invalid key: {$key}! "
                    ."Key must be at least 32 characters long."
                );
            }

            $hash = hash_hmac('sha256', $this->name.$this->value, $key);

            if (false === $hash) {
                throw new RuntimeException('Unable to sign the cookie! "SHA-256" algorithm is not supported!');
            }

            $this->value = $hash.$this->value;
        }

        return $this;
    }

    /**
     * Verify the cookie.
     *
     * @param  string  $key
     * @return $this
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function verify($key)
    {
        if (null !== $this->value && '' !== $this->value) {
            if (32 > mb_strlen($key)) {
                throw new InvalidArgumentException(
                    "Invalid key: {$key}! "
                    ."Key must be at least 32 characters long."
                );
            }

            $originalHash = mb_substr($this->value, 0, 64);
            $originalValue = mb_substr($this->value, 64);

            $hash = hash_hmac('sha256', $this->name.$originalValue, $key);

            if (false === $hash) {
                throw new RuntimeException('Unable to verify the cookie! "SHA-256" algorithm is not supported!');
            }

            if (! hash_equals($hash, $originalHash)) {
                throw new RuntimeException('The cookie is modified!');
            }

            $this->value = $originalValue;
        }

        return $this;
    }

    /**
     * Check has the cookie a __Host- prefix.
     *
     * @return bool
     */
    public function hasHostPrefix()
    {
        return $this->hasHostPrefix;
    }

    /**
     * Check has the cookie a __Secure- prefix.
     *
     * @return bool
     */
    public function hasSecurePrefix()
    {
        return $this->hasSecurePrefix;
    }

    /**
     * Stringify the cookie.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $cookie = $this->getPair();

            if ($this->hasHostPrefix) {
                $cookie = '__Host-'.$cookie;
            } else if ($this->hasSecurePrefix) {
                $cookie = '__Secure-'.$cookie;
            }

            if (null !== $this->expires) {
                $cookie .= '; Expires='.$this->expires;
            }

            if (null !== $this->maxAge) {
                $cookie .= '; Max-Age='.$this->maxAge;
            }

            if (null !== $this->domain) {
                $cookie .= '; Domain='.$this->domain;
            }

            if (null !== $this->path) {
                $cookie .= '; Path='.$this->path;
            }

            if ($this->secure) {
                $cookie .= '; Secure';
            }

            if ($this->httpOnly) {
                $cookie .= '; HttpOnly';
            }

            if (null !== $this->sameSite) {
                $cookie .= '; SameSite='.$this->sameSite;
            }

            return $cookie;
        } catch (Throwable $e) {
            trigger_error($e->getMessage(), \E_USER_ERROR);
        }
    }

    /**
     * Filter a cookie name.
     *
     * @param  string  $name
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function filterName($name)
    {
        if (! preg_match('/^[^\x00-\x1f\x7f\x20()<>@,;:\\"\/\[\]?={}]+$/', $name)) {
            throw new InvalidArgumentException(
                "Invalid cookie name: {$name}! "
                ."Cookie name must be compliant with the \"RFC 6265\" standart."
            );
        }

        return $name;
    }

    /**
     * Filter a cookie value.
     *
     * @param  string|null  $value
     * @return string|null
     *
     * @throws \InvalidArgumentException
     */
    protected function filterValue($value)
    {
        if (null !== $value) {
            $unquotedValue = preg_match('/^\".*\"$/', $value) ? trim($value, '"') : $value;

            if (! preg_match('/^[^\x00-\x1f\x7f\x20,;\\"]*$/', $unquotedValue)) {
                throw new InvalidArgumentException(
                    "Invalid cookie value: {$value}! "
                    ."Cookie value must be compliant with the \"RFC 6265\" standart."
                );
            }
        }

        return $value;
    }

    /**
     * Filter a cookie Expires attribute.
     *
     * @param  string|null  $expires
     * @return string|null
     *
     * @throws \InvalidArgumentException
     */
    protected function filterExpires($expires)
    {
        if (null !== $expires) {
            $day = '(Mon|Tue|Wed|Thu|Fri|Sat|Sun)';
            $month = '(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)';

            $expiresRegEx = "/^{$day}\, \d{2} {$month} \d{4} \d{2}\:\d{2}\:\d{2} GMT$/";

            if (! preg_match($expiresRegEx, $expires)) {
                throw new InvalidArgumentException(
                    "Invalid cookie \"Expires\" attribute: {$expires}! "
                    ."Cookie \"Expires\" attribute must be compliant with the \"RFC 6265\" standart."
                );
            }
        }

        return $expires;
    }

    /**
     * Filter a cookie Domain attribute.
     *
     * @param  string|null  $domain
     * @return string|null
     *
     * @throws \InvalidArgumentException
     */
    protected function filterDomain($domain)
    {
        if (null !== $domain) {
            if ('' === $domain || '.' === $domain) {
                return null;
            }

            if (! preg_match('/^([a-zA-Z0-9\-._~]|%[a-fA-F0-9]{2}|[!$&\'()*+,;=])+$/', $domain)) {
                throw new InvalidArgumentException(
                    "Invalid cookie \"Domain\" attribute: {$domain}! "
                    ."Cookie \"Domain\" attribute must be compliant with the \"RFC 6265\" standart."
                );
            }

            return strtolower(ltrim($domain, '.'));
        }

        return $domain;
    }

    /**
     * Filter a cookie Path attribute.
     *
     * @param  string|null  $path
     * @return string|null
     *
     * @throws \InvalidArgumentException
     */
    protected function filterPath($path)
    {
        if (null !== $path) {
            if ('' === $path || 0 !== strpos($path, '/')) {
                return null;
            }

            if (! preg_match('/^[^\x00-\x1f\x7f;]+$/', $path)) {
                throw new InvalidArgumentException(
                    "Invalid cookie \"Path\" attribute: {$path}! "
                    ."Cookie \"Path\" attribute must be compliant with the \"RFC 6265\" standart."
                );
            }
        }

        return $path;
    }

    /**
     * Filter a cookie SameSite attribute.
     *
     * @param  string|null  $sameSite
     * @return string|null
     *
     * @throws \InvalidArgumentException
     */
    protected function filterSameSite($sameSite)
    {
        if (null !== $sameSite) {
            if ($sameSite !== SameSite::LAX && $sameSite !== SameSite::STRICT) {
                throw new InvalidArgumentException(
                    "Invalid cookie \"SameSite\" attribute: {$sameSite}! "
                    ."The cookie \"SameSite\" attribute must be \"{SameSite::LAX}\" or \"{SameSite::STRICT}\"."
                );
            }
        }

        return $sameSite;
    }
}
