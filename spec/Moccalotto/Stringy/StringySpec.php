<?php

/**
 * Stringy test package.
 *
 * @codingStandardsIgnoreFile
 */
namespace spec\Moccalotto\Stringy;

use Moccalotto\Stringy\Stringy;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class StringySpec extends ObjectBehavior
{
    function it_is_initializable_as_an_empty_utf8_string()
    {
        $this->shouldHaveType(Stringy::class);
    }

    function it_has_sane_defaults_with_empty_constructor()
    {
        $this->string()->shouldBe('');
        $this->encoding()->shouldBe('UTF-8');
    }

    function it_contains_a_string()
    {
        $this->beConstructedWith('foo');
        $this->string()->shouldBe('foo');
    }

    function it_contains_an_encoding()
    {
        $this->beConstructedWith('foo', 'ASCII');
        $this->encoding()->shouldBe('ASCII');
    }

    function it_has_a_static_constructor()
    {
        $this->beConstructedThrough('create', ['foo', 'Windows-1252']);
        $this->encoding()->shouldBe('Windows-1252');
        $this->string()->shouldBe('foo');
    }

    function it_can_make_a_string_shorter_via_the_limit_method()
    {
        $this->beConstructedWith('test string');
        $this->limit(0)->shouldHaveType(Stringy::class);
        $this->limit(4)->string()->shouldBe('test');
    }
}
