<?php

use PHPUnit\Framework\TestCase;

class XpressionsTest extends TestCase
{

    /** @test */
    public function it_creates_instance()
    {
        $matcher = Xpressions::match();
        $this->assertTrue($matcher instanceof Xpressions);
    }

    /** @test */
    public function it_match_a_string()
    {
        $matcher = Xpressions::match()->exact('foo');

        $this->assertTrue($matcher->test('foo'));
        $this->assertFalse($matcher->test('bar'));
    }

    /** @test */
    public function it_match_a_single_line_string()
    {
        $matcher = Xpressions::match()->begin()->exact('baz')->end();

        $this->assertTrue($matcher->test("baz"));
        $this->assertFalse($matcher->test("\nbaz"));
    }

    /** @test */
    public function it_match_an_optional_string()
    {
        $matcher = Xpressions::match()->exact('foo')->maybe('bar')->exact('baz');

        $this->assertFalse($matcher->test('foo'));
        $this->assertFalse($matcher->test('foobar'));
        $this->assertTrue($matcher->test('foobarbaz'));
        $this->assertTrue($matcher->test('foobaz'));
    }

    /** @test */
    public function it_match_an_optional_group()
    {
        $matcher = Xpressions::match()
            ->exact('my optional email is:')
            ->maybe(function($xpr) {
                $xpr->space()
                    ->word()
                    ->oneOrMore()
                    ->exact('@')
                    ->oneOrMore(function($xpr) {
                        $xpr->exact('.')
                            ->word()
                            ->oneOrMore();
                    });
            });

        $this->assertTrue($matcher->test('my optional email is: john@example.com'));
        $this->assertTrue($matcher->test('my optional email is:'));
        $this->assertFalse($matcher->test(''));
    }

    /** @test */
    public function it_non_match_a_string()
    {
        $matcher = Xpressions::match()->exact('foo')->non('bar')->exact('baz');

        $this->assertTrue($matcher->test('foobaz'));
        $this->assertFalse($matcher->test('foobarbaz'));
    }

    /** @test */
    public function it_match_a_word()
    {
        $matcher = Xpressions::match()->word();

        $this->assertTrue($matcher->test('foo'));
        $this->assertTrue($matcher->test('123456'));
        $this->assertFalse($matcher->test('!@#$%'));
    }

    /** @test */
    public function it_non_match_a_word()
    {
        $matcher = Xpressions::match()->nonWord();

        $this->assertTrue($matcher->test('!@#$%'));
        $this->assertFalse($matcher->test('foo'));
    }

    /** @test */
    public function it_match_a_digit()
    {
        $matcher = Xpressions::match()->digit();

        $this->assertTrue($matcher->test('123456'));
        $this->assertFalse($matcher->test('foo'));
    }

    /** @test */
    public function it_non_match_a_digit()
    {
        $matcher = Xpressions::match()->nonDigit();

        $this->assertTrue($matcher->test('foo'));
        $this->assertFalse($matcher->test('123456'));
    }

    /** @test */
    public function it_match_a_space()
    {
        $matcher = Xpressions::match()->space();

        $this->assertTrue($matcher->test('foo bar'));
        $this->assertFalse($matcher->test('foo'));
    }

    /** @test */
    public function it_non_match_a_space()
    {
        $matcher = Xpressions::match()->nonSpace();

        $this->assertTrue($matcher->test('foo'));
        $this->assertFalse($matcher->test(' '));
    }

    /** @test */
    public function it_chooses_between_expressions_using_or()
    {
        $matcher = Xpressions::match()->exact('foo')->or()->exact('bar');

        $this->assertTrue($matcher->test('foo'));
        $this->assertTrue($matcher->test('bar'));
        $this->assertTrue($matcher->test('foobar'));
        $this->assertFalse($matcher->test('baz'));
    }

    /** @test */
    public function it_chooses_between_expressions_using_or_and_accepts_callback()
    {
        $matcher = Xpressions::match()
            // match an email address or a domain.
            ->exact('contact me via: ')
                ->group(function($xpr) {
                    $xpr->oneOrMore(function($xpr) { $xpr->word(); })
                        ->exact('@')
                        ->oneOrMore(function($xpr) {
                            $xpr->maybe('.')
                                ->word()
                                ->oneOrMore();
                        })->word();
                })
                ->or(function($xpr) {
                    $xpr->word()
                        ->oneOrMore()
                        ->group(function($xpr) {
                            $xpr->exact('.')
                                ->word()
                                ->oneOrMore();
                        })->oneOrMore();
                });

        $this->assertTrue($matcher->test('contact me via: example.com'));
        $this->assertTrue($matcher->test('contact me via: john@example.com'));
        $this->assertFalse($matcher->test('contact me via: other platform'));
    }

    /** @test */
    public function it_make_a_group_of_matchers()
    {
        $matcher = Xpressions::match()
            ->exact('my name is: ')
            ->group(function($expression) {
                $expression->exact('foo')->or()->exact('bar');
            });

        $this->assertTrue($matcher->test('my name is: foo'));
        $this->assertTrue($matcher->test('my name is: bar'));

        $this->assertFalse($matcher->test('my name is: anonymous'));
    }

    /** @test */
    public function it_make_a_group_of_matchers_without_callback()
    {
        $matcher = Xpressions::match()->exact('my name is: ')
            ->group()
            ->exact('foo')
            ->or()
            ->exact('bar')
            ->groupEnd();

        $this->assertTrue($matcher->test('my name is: foo'));
        $this->assertTrue($matcher->test('my name is: bar'));

        $this->assertFalse($matcher->test('my name is: anonymous'));
    }

    /** @test */
    public function it_match_an_email_address()
    {
        $matcher = Xpressions::match()
            ->begin() // match a line start
            ->oneOrMore(function($xpr) {
                $xpr->word()->or('.');
            })
            ->exact('@')
            ->word()
            ->oneOrMore()
            ->oneOrMore(function($xpr) {
                $xpr->maybe('.')
                    ->word();
            })
            ->end(); // match a line end

        $this->assertTrue($matcher->test('foo@bar.baz'));
        $this->assertTrue($matcher->test('foo@bar.baz.co'));

        $this->assertFalse($matcher->test('fooxbar.baz.co'));
        $this->assertFalse($matcher->test('fooxbar.baz.co'));
    }

    /** @test */
    public function it_match_any_of_words()
    {
        $matcher = Xpressions::match()
            ->any('foo', 'bar', 'baz');

        $this->assertTrue($matcher->test('foo'));
        $this->assertTrue($matcher->test('bar'));
        $this->assertTrue($matcher->test('baz'));
        $this->assertTrue($matcher->test('my name is baz'));
        $this->assertFalse($matcher->test('john'));

        $matcher = Xpressions::match()
            ->exact('my name is: ')
            ->any('foo', 'bar', 'baz');

        $this->assertTrue($matcher->test('my name is: foo'));
        $this->assertTrue($matcher->test('my name is: bar'));
        $this->assertTrue($matcher->test('my name is: baz'));
        $this->assertFalse($matcher->test('foo'));
        $this->assertFalse($matcher->test('my name is: john'));
    }

    /** @test */
    public function it_match_any_of_groups()
    {
        // match an email address or domain
        $matcher = Xpressions::match()
            ->any(function($xpr) {
                $xpr->oneOrMore(function($xpr) { $xpr->word(); })
                    ->exact('@')
                    ->oneOrMore(function($xpr) {
                        $xpr->maybe('.')
                            ->word()
                            ->oneOrMore();
                    })->word();
            }, function($xpr) {
                $xpr->words()
                    ->group(function($xpr) {
                        $xpr->exact('.')
                            ->word()
                            ->oneOrMore();
                    })->oneOrMore();
            }, 'foo');

        $this->assertTrue($matcher->test('example.com'));
        $this->assertTrue($matcher->test('me@example.com'));
        $this->assertTrue($matcher->test('foo'));
        $this->assertFalse($matcher->test('bar'));
    }

    /** @test */
    public function it_gets_expression_without_delimiters()
    {
        $matcher = Xpressions::match()
            ->exact('foo');

        $this->assertEquals($matcher->withoutDelimiters(), 'foo');
    }
}
