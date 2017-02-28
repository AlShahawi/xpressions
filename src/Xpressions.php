<?php

class Xpressions
{
    /**
     * Regex Expression.
     *
     * @var string
     */
    protected $expression;

    /**
     * Start matching by make a new instance.
     *
     * @return Xpressions
     */
    public static function match()
    {
        return new static;
    }

    /**
     * Match a line start.
     *
     * @return $this
     */
    public function begin()
    {
        $this->append('^');

        return $this;
    }

    /**
     * Match a line end.
     *
     * @return $this
     */
    public function end()
    {
        $this->append('$');

        return $this;
    }

    /**
     * Match a given string.
     *
     * @param  string $value
     * @return $this
     */
    public function find($value)
    {
        return $this->exact($value);
    }

    /**
     * Match a given string.
     *
     * @param  string $value
     * @return $this
     */
    public function exact($value)
    {
        $value = $this->escape($value);

        $this->append($value);

        return $this;
    }

    /**
     * Match an optional given string.
     *
     * @param  string|Callable $value
     * @return $this
     */
    public function maybe($value)
    {
        if (is_callable($value)) {
            $this->append($this->groupCallbackExpressions($value , '(?:', ')?'));
            return $this;
        }

        $value = $this->escape($value);
        $this->append('(?:' . $value . ')?');

        return $this;
    }

    /**
     * Match a non given string.
     *
     * @param  string $value
     * @return $this
     */
    public function non($value)
    {
        $value = $this->escape($value);

        $this->append('(?!' . $value . ')?');

        return $this;
    }

    /**
     * Match a word.
     *
     * @return $this
     */
    public function word()
    {
        $this->append('\w');

        return $this;
    }

    /**
     * Match a non word.
     *
     * @return $this
     */
    public function nonWord()
    {
        $this->append('\W');

        return $this;
    }

    /**
     * Match a digit.
     *
     * @return $this
     */
    public function digit()
    {
        $this->append('\d');

        return $this;
    }

    /**
     * Match a non digit.
     *
     * @return $this
     */
    public function nonDigit()
    {
        $this->append('\D');

        return $this;
    }

    /**
     * Match a space.
     *
     * @return $this
     */
    public function space()
    {
        $this->append('\s');

        return $this;
    }

    /**
     * Match a non space.
     *
     * @return $this
     */
    public function nonSpace()
    {
        $this->append('\S');

        return $this;
    }

    /**
     * Matches any of the given values.
     *
     * @param  string $values
     * @return $this
     */
    public function any(...$values)
    {
        if ( ! count($values))
            return $this;

        $this->exact(array_shift($values));

        foreach ($values as $value) {
            $this->or($value);
        }

        return $this;
    }

    /**
     * Matches the expression before of after this expression.
     *
     * @param  string|Callback $value an optional value/group after the or expression.
     * @return $this
     */
    public function or($value = null)
    {
        $this->append('|');

        if (is_callable($value)) {

            $this->append($this->groupCallbackExpressions($value));

            return $this;
        }

        if($value) {
            $this->exact($value);
        }

        return $this;
    }

    /**
     * Match the last OR group of expression(s) one or more times.
     *
     * @param  \Callable $callback
     * @return $this
     */
    public function oneOrMore(Callable $callback = null)
    {
        if ($callback) {
            return $this->group($callback)->append('+');
        }

        $this->append('+');

        return $this;
    }

    /**
     * Match the last OR group of expression(s) zero or more times.
     *
     * @param  \Callable $callback
     * @return $this
     */
    public function zeroOrMore(Callable $callback = null)
    {
        if ($callback) {
            return $this->group($callback)->append('*');
        }

        $this->append('*');

        return $this;
    }

    /**
     * Repeat the last OR group of expression(s) n of times.
     *
     * @param  integer $n
     * @param  \Callable $callback
     * @return $this
     */
    public function repeat($n, Callable $callback = null)
    {
        if ($callback) {
            $this->group($callback)->append("{$n}");

            return $this;
        }

        $this->append("{$n}");

        return $this;
    }

    /**
     * Create a group of expressions, or append an open parenthesis.
     *
     * @param  \Callable|null $callback
     * @return $this
     */
    public function group(Callable $callback = null)
    {
        if ($callback) {
            $this->append($this->groupCallbackExpressions($callback));
            return $this;
        }

        $this->append('(');

        return $this;
    }

    /**
     * Wrap a callback expressions.
     *
     * @param  Callable $callback
     * @param  string   $open
     * @param  string   $close
     * @return string
     */
    protected function groupCallbackExpressions(Callable $callback, $open = '(', $close = ')')
    {
        $regex = $this->match();
        $callback($regex);

        return $open . $regex->withoutDelimiters() . $close;
    }

    /**
     * Pppend an close parenthesis.
     *
     * @return $this
     */
    public function groupEnd()
    {
        $this->append(')');

        return $this;
    }

    /**
     * Append a regex to the current regular expressions.
     * @param  string $regex
     * @return $this
     */
    public function append($regex)
    {
        $this->expression .= $regex;

        return $this;
    }

    /**
     * Quote regular expression characters.
     *
     * @param  string  $value
     * @return string
     */
    public function escape($value)
    {
        return preg_quote($value, '/');
    }

    /**
     * Test a given string against current regex.
     *
     * @param  string $value
     * @return boolean
     */
    public function test($value)
    {
        return (bool) preg_match($this->getRegex(), $value);
    }

    /**
     * Get regular expression with delimiters.
     *
     * @return string
     */
    public function getRegex()
    {
        return '/' . $this->withoutDelimiters() . '/';
    }

    /**
     * Get regular expression without delimiters.
     *
     * @return string
     */
    public function withoutDelimiters()
    {
        return $this->expression;
    }

    /**
     * Convert current instance to a string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getRegex();
    }
}
