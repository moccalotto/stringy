<?php declare(strict_types=1);

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
use Moccalotto\Stringy\EncodingException;

class StringySpec extends ObjectBehavior
{
    const UTF_8_TEST_STRING = <<<EOT
         Arabic بارد وبارد مع دودة ضخمة كيم
        Braille ⠠⠅⠊⠍⠀⠻⠀⠎⠑⠚⠀⠕⠛⠀⠉⠕⠕⠇
          Greek Κιμ δροσερό και δροσερό
            Han 金正日的冷静和冷静
         Hangul 김위원장의시원하고멋진
         Hebrew סודות מן העברית הקלאסית
       Hiragana たたみさむらいてんぷら
       Katakana ペンビールワインスカートネクタイバナナ
          Latin Kæmpe tests ér bare über niße
         Number 13, 3.14 3,14 1.000.000,42 1,000,000.42
           Thai คิมเย็นและเย็นในวิธีที่เย็น
       Cyrillic Аз рӯи нуқтаи назари олимони
EOT;

    public function toggleEncoding($newEncoding)
    {
        static $oldEncoding = null;

        if ($oldEncoding === null) {
            $oldEncoding = mb_internal_encoding();
        }

        if ($newEncoding === null) {
            mb_internal_encoding($oldEncoding);
        } else {
            mb_internal_encoding($newEncoding);
        }
    }

    /**
     * Setup
     */
    public function let()
    {
        $this->toggleEncoding('UTF-8');
    }

    public function letGo()
    {
        $this->toggleEncoding(null);
    }


    function testString($encodedAs = 'UTF-8')
    {
        return mb_convert_encoding(static::UTF_8_TEST_STRING, $encodedAs, 'UTF-8');
    }

    function it_is_initializable_with_an_empty_string()
    {
        $this->beConstructedWith('');
        $this->shouldHaveType(Stringy::class);
    }

    function it_detects_bad_strings_during_instantiation()
    {
        $this->beConstructedWith(
            $this->testString('UTF-32')
        );

        $this->shouldThrow(EncodingException::class)->duringInstantiation();
    }

    function it_can_be_created_from_utf_32()
    {
        $this->beConstructedWith(
            $this->testString('UTF-32'),
            'UTF-32'
        );

        $this->string()->shouldBe(static::UTF_8_TEST_STRING);
    }

    function it_has_a_static_constructor()
    {
        $this->beConstructedThrough('create', ['foo']);
        $this->string()->shouldBe('foo');
    }


    function it_contains_a_string()
    {
        $this->beConstructedWith($this->testString());
        $this->string('UTF-8')->shouldBe($this->testString());
    }

    function it_has_sane_default_parameters_in_constructor()
    {
        $this->beConstructedWith();
        $this->string()->shouldBe('');
    }

    function it_can_be_constructed_with_strings_of_non_native_encoding()
    {
        $this->beConstructedWith($this->testString('UTF-32'), 'UTF-32');

        $this->string('UTF-8')->shouldBe($this->testString());
    }

    function it_can_convert_encoding()
    {
        $this->beConstructedWith($this->testString());

        $this->string('UTF-32')->shouldBe(
            $this->testString('UTF-32')
        );
    }

    function it_can_compare_similarity()
    {
        $this->beConstructedWith(static::UTF_8_TEST_STRING);
        $this->is(static::UTF_8_TEST_STRING)->shouldBe(true);

        $this->is(static::create($this->testString()))->shouldBe(true);
        $this->is(static::create($this->testString())->string())->shouldBe(true);

        $this->is('foo')->shouldBe(false);
        $this->is(Stringy::create('foo'))->shouldBe(false);

        $this->shouldThrow(EncodingException::class)->during(
            'is',
            [$this->testString('UTF-32')]
        );
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

        $this->shouldThrow(StringyException::class)->during('format', [['only one arg']]);
    }

    public function it_can_right_pad_a_string()
    {
        $this->beConstructedWith('foo');

        $this->rightPadded(5)->string()->shouldBe('foo  ');
        $this->rightPadded(5, ' ')->string()->shouldBe('foo  ');
        $this->rightPadded(5, '=')->string()->shouldBe('foo==');

        $this->shouldThrow('OutOfRangeException')->during('rightPadded', [5, '']);
    }

    public function it_can_left_pad_a_string()
    {
        $this->beConstructedWith('foo');

        $this->leftPadded(5)->string()->shouldBe('  foo');
        $this->leftPadded(5, ' ')->string()->shouldBe('  foo');
        $this->leftPadded(5, '=')->string()->shouldBe('==foo');

        $this->shouldThrow('OutOfRangeException')->during('leftPadded', [5, '']);
    }

    public function it_can_center_a_string()
    {
        $this->beConstructedWith('foobar');

        $this->centered(6)->string()->shouldBe('foobar');
        $this->centered(10)->string()->shouldBe('  foobar  ');
        $this->centered(10, ' ')->string()->shouldBe('  foobar  ');
        $this->centered(10, '=')->string()->shouldBe('==foobar==');
        $this->centered(11, '=')->string()->shouldBe('==foobar===');
        $this->centered(11, '=', 'left')->string()->shouldBe('==foobar===');
        $this->centered(11, '=', 'right')->string()->shouldBe('===foobar==');

        $this->shouldThrow('UnexpectedValueException')
            ->during('centered', [11, '=', 'foo']);
    }

    public function it_finds_what_comes_before_a_given_substring()
    {
        $this->beConstructedWith('foo bar baz foo bar baz');

        $this->before('baz')->string()->shouldBe('foo bar ');
        $this->before('foo')->string()->shouldBe('');
        $this->before('')->string()->shouldBe($this->string());
        $this->before('not in parent string')->string()->shouldBe('');

        $this->before('foo', 1)->string()->shouldBe('foo bar baz ');
        $this->before('bar', 1)->string()->shouldBe('foo bar baz foo ');
        $this->before('bar', 2)->string()->shouldBe('');
    }

    public function it_finds_what_comes_after_a_given_substring()
    {
        $this->beConstructedWith('foo bar baz foo bar baz');

        $this->after('baz')->string()->shouldBe(' foo bar baz');
        $this->after('foo')->string()->shouldBe(' bar baz foo bar baz');
        $this->after('')->string()->shouldBe($this->string());
        $this->after('not in parent string')->string()->shouldBe('');

        $this->after('baz', 1)->string()->shouldBe('');
        $this->after('bar', 1)->string()->shouldBe(' baz');
        $this->after('bar', 2)->string()->shouldBe('');
    }

    public function it_finds_the_string_between_two_substrings()
    {
        $this->beConstructedWith('foo bar1 baz foo bar2 baz');

        $this->between('foo', 'baz')->string()->shouldBe(' bar1 ');
        $this->between('foo', 'baz', 0)->string()->shouldBe(' bar1 ');
        $this->between('foo', 'baz', 1)->string()->shouldBe(' bar2 ');
        $this->between('foo', 'baz', 2)->string()->shouldBe('');
        $this->between('non-existing', 'baz')->string()->shouldBe('');
        $this->between('foo', 'non-existing')->string()->shouldBe('');
        $this->between('non-existing', 'non-existing')->string()->shouldBe('');
    }

    public function it_can_transform_a_string_via_callback()
    {
        $this->beConstructedWith('foo bar bing baz');

        $this->transform(function ($stringy) {
            return preg_replace('/\s*bing/', '', $stringy->string());
        })->string()->shouldBe('foo bar baz');

        $this->transform(function ($stringy) {
            return 'OTHER';
        })->string()->shouldBe('OTHER');

        $this->transform(function ($stringy) {
            return Stringy::create('THING');
        })->string()->shouldBe('THING');
    }

    public function it_can_slugify_a_string()
    {
        $this->beConstructedWith('some % Ødd_string-that    needs sluGging');

        $this->slug()->string()->shouldBe('some-odd-string-that-needs-slugging');
        $this->slug('_')->string()->shouldBe('some_odd_string_that_needs_slugging');
        $this->slug('_', '--deleted--')->string()->shouldBe('some_--deleted--_odd_string_that_needs_slugging');
    }



    /**
     * TODO:
     * before
     * acter
     * between
     * substring
     * upper
     * lower
     * explode
     * characters
     * append
     * prepend
     * surroundWith
     * includeIn
     * reverse
     * glue
     * limit
     * slug
     * length
     * asciiSafe
     * entityEncoded
     * __toString
     * [array access]
     */
}
