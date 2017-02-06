<?php

/**
 * Stringy test package.
 *
 * @codingStandardsIgnoreFile
 */
namespace spec\Moccalotto\Stringy;

use Prophecy\Argument;
use PhpSpec\ObjectBehavior;
use Moccalotto\Stringy\Stringy;
use Moccalotto\Stringy\StringyException;

class StringySpec extends ObjectBehavior
{
    function it_is_initializable_with_an_empty_string()
    {
        $this->beConstructedWith('');
        $this->shouldHaveType(Stringy::class);
    }

    function it_contains_a_string()
    {
        $this->beConstructedWith('foo');
        $this->string()->shouldBe('foo');
    }

    function it_has_sane_default_parameters_in_constructor()
    {
        $this->beConstructedWith();
        $this->string()->shouldBe('');
    }

    function it_can_convert_encoding()
    {
        $this->beConstructedWith('some string');

        $this->string('UTF-32')->shouldBe(
            mb_convert_encoding('some string', 'UTF-32')
        );
    }

    function it_has_a_static_constructor()
    {
        $this->beConstructedThrough('create', ['foo']);
        $this->string()->shouldBe('foo');
    }

    function it_can_make_a_string_shorter_via_the_limit_method()
    {
        $this->beConstructedWith('test string');
        $this->limit(0)->shouldHaveType(Stringy::class);
        $this->limit(4)->string()->shouldBe('test');
    }

    function it_can_detect_the_precense_of_substrings()
    {
        $this->beConstructedWith('Foo 1/Foo 2/Foo 3-Foo 4\Foo 5\Foo 6');

        $this->contains('Foo 1/Foo 2/Foo 3-Foo 4\Foo 5\Foo 6')->shouldBe(true);
        $this->contains('Foo')->shouldBe(true);
        $this->contains(str('Foo'))->shouldBe(true);
        $this->contains('Bar')->shouldBe(false);
        $this->contains('')->shouldBe(true);
    }

    function it_can_locate_the_position_of_substrings()
    {
        $this->beConstructedWith('Foo 1/Foo 2/Foo 3-Foo 4\Foo 5\Foo 6');

        $this->positionOf('Foo')->shouldBe(0);
        $this->positionOf('Foo', 0)->shouldBe(0);
        $this->positionOf('Foo', 1)->shouldBe(6);
        $this->positionOf('Foo', 2)->shouldBe(12);
        $this->positionOf('Foo', 3)->shouldBe(18);
        $this->positionOf('Foo', 4)->shouldBe(24);
        $this->positionOf('Foo', 5)->shouldBe(30);
        $this->positionOf('Foo', -1)->shouldBe(30);
        $this->positionOf('Foo', -2)->shouldBe(24);
        $this->positionOf('Foo', -3)->shouldBe(18);
        $this->positionOf('Foo', -4)->shouldBe(12);
        $this->positionOf('Foo', -5)->shouldBe(6);
        $this->positionOf('Foo', -6)->shouldBe(0);

        $this->positionOf(str('Foo'))->shouldBe(0);

        $this->positionOf('Foo', 6)->shouldBe(null);
        $this->positionOf('Foo', -7)->shouldBe(null);
        $this->positionOf('Bar', 6)->shouldBe(null);
    }

    public function it_can_left_trim()
    {
        $this->beConstructedWith('/some regex/');
        $this->leftTrim('/')->string()->shouldBe('some regex/');
        $this->leftTrim('/some')->string()->shouldBe(' regex/');

        $this->leftTrim('some')->string()->shouldBe('/some regex/');
        $this->leftTrim('regex/')->string()->shouldBe('/some regex/');

        $this->leftTrimAll(['/', 'some', ' '])->string()->shouldBe('regex/');
        $this->leftTrimAll(['/', 'some', ' ', 'foo', 'bar', 'baz'])->string()->shouldBe('regex/');
    }

    public function it_can_right_trim()
    {
        $this->beConstructedWith('/some regex/');
        $this->rightTrimAll(['/'])->string()->shouldBe('/some regex');
        $this->rightTrim('regex/')->string()->shouldBe('/some ');

        $this->rightTrim('regex')->string()->shouldBe('/some regex/');
        $this->rightTrim('some')->string()->shouldBe('/some regex/');

        $this->rightTrimAll(['/', 'regex', ' '])->string()->shouldBe('/some');
        $this->rightTrimAll(['/', 'regex', ' ', 'foo', 'bar', 'baz'])->string()->shouldBe('/some');
    }

    public function it_can_repeat_a_string()
    {
        $this->beConstructedWith('foo');

        $this->repeat(3)->string()->shouldBe('foofoofoo');
        $this->repeat(0)->string()->shouldBe('');
    }

    public function it_can_detect_if_a_string_starts_with()
    {
        $this->beConstructedWith('foo bar baz');

        $this->startsWith('foo')->shouldBe(true);
        $this->startsWith('foo bar baz')->shouldBe(true);

        $this->startsWith('bar')->shouldBe(false);
        $this->startsWith('foo baz')->shouldBe(false);
    }

    public function it_can_detect_if_a_string_ends_with()
    {
        $this->beConstructedWith('foo bar baz');

        $this->endsWith('baz')->shouldBe(true);
        $this->endsWith('foo bar baz')->shouldBe(true);

        $this->endsWith('bar')->shouldBe(false);
        $this->endsWith('foo baz')->shouldBe(false);
    }

    public function it_can_be_a_sprintf_template()
    {
        $this->beConstructedWith('foo %s baz');

        $this->format(['bar'])->string()->shouldBe('foo bar baz');
        $this->format(['bing'])->string()->shouldBe('foo bing baz');
    }

    public function it_throws_exceptions_if_format_is_used_incorrectly()
    {
        $this->beConstructedWith('%s %s');

        $this->shouldThrow(StringyException::class)->during('format', [['one one arg']]);
    }
}
