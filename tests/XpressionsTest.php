<?php

use PHPUnit\Framework\TestCase;

class XpressionsTest extends TestCase
{

    /** @test */
    public function it_creates_instance()
    {
        $regex = Xpressions::match();
        $this->assertTrue($regex instanceof Xpressions);
    }

    /** @test */
    public function it_match_a_string()
    {
        $regex = Xpressions::match()->exact('foo');

        $this->assertTrue($regex->test('foo'));
        $this->assertFalse($regex->test('bar'));
    }

    /** @test */
    public function it_match_a_single_line_string()
    {
        $regex = Xpressions::match()->begin()->exact('baz')->end();

        $this->assertTrue($regex->test("baz"));
        $this->assertFalse($regex->test("\nbaz"));
    }

    /** @test */
    public function it_match_a_optional_string()
    {
        $regex = Xpressions::match()->exact('foo')->maybe('bar');

        $this->assertTrue($regex->test('foo'));
        $this->assertTrue($regex->test('foobar'));
    }

    /** @test */
    public function it_not_match_a_string()
    {
        $regex = Xpressions::match()->exact('foo')->not('bar')->exact('baz');

        $this->assertTrue($regex->test('foobaz'));
        $this->assertFalse($regex->test('foobarbaz'));
    }

    /** @test */
    public function it_match_a_word()
    {
        $regex = Xpressions::match()->word();

        $this->assertTrue($regex->test('foo'));
        $this->assertTrue($regex->test('123456'));
        $this->assertFalse($regex->test('!@#$%'));
    }

    /** @test */
    public function it_not_match_a_word()
    {
        $regex = Xpressions::match()->notWord();

        $this->assertTrue($regex->test('!@#$%'));
        $this->assertFalse($regex->test('foo'));
    }

    /** @test */
    public function it_match_a_digit()
    {
        $regex = Xpressions::match()->digit();

        $this->assertTrue($regex->test('123456'));
        $this->assertFalse($regex->test('foo'));
    }

    /** @test */
    public function it_not_match_a_digit()
    {
        $regex = Xpressions::match()->notDigit();

        $this->assertTrue($regex->test('foo'));
        $this->assertFalse($regex->test('123456'));
    }

    /** @test */
    public function it_match_a_space()
    {
        $regex = Xpressions::match()->space();

        $this->assertTrue($regex->test('foo bar'));
        $this->assertFalse($regex->test('foo'));
    }

    /** @test */
    public function it_not_match_a_space()
    {
        $regex = Xpressions::match()->notSpace();

        $this->assertTrue($regex->test('foo'));
        $this->assertFalse($regex->test(' '));
    }

    /** @test */
    public function it_chooses_between_expressions_using_or()
    {
        $regex = Xpressions::match()->exact('foo')->or()->exact('bar');

        $this->assertTrue($regex->test('foo'));
        $this->assertTrue($regex->test('bar'));
        $this->assertTrue($regex->test('foobar'));
        $this->assertFalse($regex->test('baz'));
    }

    /** @test */
    public function it_make_a_group_of_matchers()
    {
        $regex = Xpressions::match()->exact('my name is: ')->group(function($expression) {
            $expression->exact('foo')->or()->exact('bar');
        });

        $this->assertTrue($regex->test('my name is: foo'));
        $this->assertTrue($regex->test('my name is: bar'));

        $this->assertFalse($regex->test('my name is: anonymous'));
    }

    /** @test */
    public function it_make_a_group_of_matchers_without_callback()
    {
        $regex = Xpressions::match()->exact('my name is: ')
            ->group()
            ->exact('foo')
            ->or()
            ->exact('bar')
            ->groupEnd();

        $this->assertTrue($regex->test('my name is: foo'));
        $this->assertTrue($regex->test('my name is: bar'));

        $this->assertFalse($regex->test('my name is: anonymous'));
    }

    /** @test */
    public function it_match_an_email_address()
    {
        $regex = Xpressions::match()
            ->oneOrMore(function($xpr) { $xpr->word(); })
            ->exact('@')
            ->oneOrMore(function($xpr) {
                $xpr->maybe('.')
                ->word();
            })->word();

        $this->assertTrue($regex->test('foo@bar.baz'));
        $this->assertTrue($regex->test('foo@bar.baz.co'));

        $this->assertFalse($regex->test('fooxbar.baz.co'));
        $this->assertFalse($regex->test('fooxbar.baz.co'));
    }

    /** @test */
    public function it_match_an_email_address_without_callback()
    {
        $regex = Xpressions::match()
            ->word()
            ->oneOrMore()
            ->exact('@')
            ->oneOrMore(function($xpr) {
                $xpr->maybe('.')
                ->word();
            })->word();

        $this->assertTrue($regex->test('foo@bar.baz'));
        $this->assertTrue($regex->test('foo@bar.baz.co'));

        $this->assertFalse($regex->test('fooxbar.baz.co'));
        $this->assertFalse($regex->test('fooxbar.baz.co'));
    }

    /** @test */
    public function it_gets_expression_without_delimiters()
    {
        $regex = Xpressions::match()
            ->exact('foo');

        $this->assertEquals($regex->withoutDelimiters(), 'foo');
    }
}
