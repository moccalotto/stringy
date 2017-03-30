<?php

declare(strict_types=1);

/**
 * Stringy test package.
 *
 * @codingStandardsIgnoreFile
 */

namespace spec\Moccalotto\Stringy;

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
     * Setup.
     */
    public function let()
    {
        setlocale(LC_ALL, 'en_US.UTF-8');
        $this->toggleEncoding('UTF-8');
    }

    public function letGo()
    {
        $this->toggleEncoding(null);
    }

    public function testString($encodedAs = 'UTF-8')
    {
        return mb_convert_encoding(static::UTF_8_TEST_STRING, $encodedAs, 'UTF-8');
    }

    public function it_is_initializable_with_an_empty_string()
    {
        $this->beConstructedWith('');
        $this->shouldHaveType(Stringy::class);
    }

    public function it_detects_bad_strings_during_instantiation()
    {
        $this->beConstructedWith(
            $this->testString('UTF-32')
        );

        $this->shouldThrow(EncodingException::class)->duringInstantiation();
    }

    public function it_can_be_created_from_utf_32()
    {
        $this->beConstructedWith(
            $this->testString('UTF-32'),
            'UTF-32'
        );

        $this->string()->shouldBe(static::UTF_8_TEST_STRING);
    }

    public function it_has_a_static_constructor()
    {
        $this->beConstructedThrough('create', ['foo']);
        $this->string()->shouldBe('foo');
    }

    public function it_contains_a_string()
    {
        $this->beConstructedWith($this->testString());
        $this->string('UTF-8')->shouldBe($this->testString());
    }

    public function it_has_sane_default_parameters_in_constructor()
    {
        $this->beConstructedWith();
        $this->string()->shouldBe('');
    }

    public function it_can_be_constructed_with_strings_of_non_native_encoding()
    {
        $this->beConstructedWith($this->testString('UTF-32'), 'UTF-32');

        $this->string('UTF-8')->shouldBe($this->testString());
    }

    public function it_can_convert_encoding()
    {
        $this->beConstructedWith($this->testString());

        $this->string('UTF-32')->shouldBe(
            $this->testString('UTF-32')
        );
    }

    public function it_can_compare_similarity()
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

    public function it_can_be_truncated()
    {
        $this->beConstructedWith('test string');
        $this->limit(0)->shouldHaveType(Stringy::class);
        $this->limit(4)->string()->shouldBe('test');
    }

    public function it_can_be_shortened_for_human_readability()
    {
        $this->beConstructedWith('test string of doom');

        $this->shorten(100)->shouldHaveType(Stringy::class);

        $this->shorten(12)->string()->shouldBe('test string…');
        $this->shorten(13)->string()->shouldBe('test string …');
        $this->shorten(13, ' ')->string()->shouldBe('test string…');
        $this->shorten(15, ' ')->string()->shouldBe('test string…');
        $this->shorten(16, ' ')->string()->shouldBe('test string of…');
        $this->shorten(16, ' ', '...')->string()->shouldBe('test string...');
        $this->shorten(18, ' ', '...')->string()->shouldBe('test string of...');
    }

    public function it_can_detect_the_precense_of_substrings()
    {
        $this->beConstructedWith('Foo 1/Foo 2/Foo 3-Foo 4\Foo 5\Foo 6');

        $this->contains('Foo 1/Foo 2/Foo 3-Foo 4\Foo 5\Foo 6')->shouldBe(true);
        $this->contains('Foo')->shouldBe(true);
        $this->contains(str('Foo'))->shouldBe(true);
        $this->contains('Bar')->shouldBe(false);
        $this->contains('')->shouldBe(true);
    }

    public function it_can_locate_the_position_of_substrings()
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

        $this->positionOfLast('Foo')->shouldBe(30);
    }

    public function it_can_be_left_trimmed()
    {
        $this->beConstructedWith('/some regex/');
        $this->leftTrim('/')->string()->shouldBe('some regex/');
        $this->leftTrim('/some')->string()->shouldBe(' regex/');

        $this->leftTrim('some')->string()->shouldBe('/some regex/');
        $this->leftTrim('regex/')->string()->shouldBe('/some regex/');

        $this->leftTrimAll(['/'])->string()->shouldBe('some regex/');
        $this->leftTrimAll(['/', 'some', ' '])->string()->shouldBe('regex/');
        $this->leftTrimAll(['/', 'some', ' ', 'foo', 'bar', 'baz'])->string()->shouldBe('regex/');
    }

    public function it_can_be_right_trimmed()
    {
        $this->beConstructedWith('/some regex/');
        $this->rightTrim('/')->string()->shouldBe('/some regex');
        $this->rightTrim('regex/')->string()->shouldBe('/some ');

        $this->rightTrim('regex')->string()->shouldBe('/some regex/');
        $this->rightTrim('some')->string()->shouldBe('/some regex/');

        $this->rightTrimAll(['/'])->string()->shouldBe('/some regex');
        $this->rightTrimAll(['/', 'regex', ' '])->string()->shouldBe('/some');
        $this->rightTrimAll(['/', 'regex', ' ', 'foo', 'bar', 'baz'])->string()->shouldBe('/some');
    }

    public function it_can_be_repeated()
    {
        $this->beConstructedWith('foo');

        $this->repeat(3)->string()->shouldBe('foofoofoo');
        $this->repeat(0)->string()->shouldBe('');
    }

    public function it_can_detect_if_it_starts_with_a_given_string()
    {
        $this->beConstructedWith('foo bar baz');

        $this->startsWith('foo')->shouldBe(true);
        $this->startsWith('foo bar baz')->shouldBe(true);

        $this->startsWith('bar')->shouldBe(false);
        $this->startsWith('foo baz')->shouldBe(false);
    }

    public function it_can_detect_if_it_ends_with_a_given_string()
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

        // should throw exception if not enough sprintf args
        $this->shouldThrow(StringyException::class)->during('format', [[]]);
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

    public function it_can_be_reduced_to_the_content_before_a_given_substring()
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

    public function it_can_be_reduced_to_the_content_after_a_given_substring()
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

    public function it_can_be_reduced_to_the_content_between_two_substrings()
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

    public function it_can_be_sluggified()
    {
        $this->beConstructedWith('some % Ødd_string-that    needsSlugging');

        $this->slug()->string()->shouldBe('some-odd-string-that-needs-slugging');
        $this->slug('_')->string()->shouldBe('some_odd_string_that_needs_slugging');
        $this->slug('_', '--deleted--')->string()->shouldBe('some_--deleted--_odd_string_that_needs_slugging');
    }

    public function it_can_select_a_substring_in_the_same_way_as_phps_substr_method()
    {
        $this->beConstructedWith('foo bar baz');

        $this->substring(0, 11)->string()->shouldBe('foo bar baz');
        $this->substring(0)->string()->shouldBe('foo bar baz');

        $this->substring(0, 7)->string()->shouldBe('foo bar');

        $this->substring(0, -4)->string()->shouldBe('foo bar');
        $this->substring(-3, null)->string()->shouldBe('baz');
        $this->substring(-3)->string()->shouldBe('baz');

        $this->substring(-7, 3)->string()->shouldBe('bar');

        $this->substring(-7, -1)->string()->shouldBe('bar ba');
        $this->substring(-7, -4)->string()->shouldBe('bar');
        $this->substring(-7, -7)->string()->shouldBe('');

        $this->shouldThrow('TypeError')->during('substring', [null]);
    }

    public function it_can_select_individual_characters_via_array_access()
    {
        $this->beConstructedWith('foo bar baz');

        $this[0]->string()->shouldBe('f');
        $this[1]->string()->shouldBe('o');
        $this[2]->string()->shouldBe('o');

        $this[-3]->string()->shouldBe('b');
        $this[-2]->string()->shouldBe('a');
        $this[-1]->string()->shouldBe('z');

        $this->shouldThrow('OutOfRangeException')->during('offsetGet', [100]);
        $this->shouldThrow('InvalidArgumentException')->during('offsetGet', ['one hundred']);
    }

    public function it_can_replace_substring()
    {
        $this->beConstructedWith('foo bar baz');

        $this->replace('bar', 'bing')->string()->shouldBe('foo bing baz');

        $this->replaceMany([
            'foo' => 'food',
            'baz' => 'with booze',
        ])->string()->shouldBe('food bar with booze');

        $this->replaceMany([
            'o' => 'x',
            'x' => '',
            ' ' => 'o',
        ])->string()->shouldBe('fxxobarobaz');
    }

    public function it_can_be_uppercased()
    {
        $this->beConstructedWith('foo');

        $this->upper()->string()->shouldBe('FOO');

        // don't uppercase an already uppercased string.
        $this->upper()->upper()->string()->shouldBe($this->upper()->string());

        // handle a more complex string.
        $this->replace('foo', 'æøåü€$ÿ123fƒç')->upper()->string()->shouldBe('ÆØÅÜ€$Ÿ123FƑÇ');
    }

    public function it_can_be_lowercased()
    {
        $this->beConstructedWith('FOO');

        $this->lower()->string()->shouldBe('foo');

        // don't lowercase an already lowercased string.
        $this->lower()->lower()->string()->shouldBe($this->lower()->string());

        // handle a more complex string.
        $this->replace('FOO', 'ÆØÅÜ€$Ÿ123FƑÇ')->lower()->string()->shouldBe('æøåü€$ÿ123fƒç');
    }

    public function it_can_be_exploded_into_an_array()
    {
        $this->beConstructedWith('foo');

        $result = $this->replace('foo', 'a,b,c')->explode(',');

        $result->shouldBeArray();
        foreach (['a', 'b', 'c'] as $index => $str) {
            $result[$index]->shouldHaveType(Stringy::class);
            $result[$index]->string()->shouldBe($str);
        }

        $result = $this->replace('foo', 'one word another word')->explode(' ');

        $result->shouldBeArray();

        foreach (['one', 'word', 'another', 'word'] as $index => $str) {
            $result[$index]->shouldHaveType(Stringy::class);
            $result[$index]->string()->shouldBe($str);
        }

        $result = $this->replace('foo', 'foo')->explode('foo');

        $result->shouldBeArray();
        foreach (['', ''] as $index => $str) {
            $result[$index]->shouldHaveType(Stringy::class);
            $result[$index]->string()->shouldBe($str);
        }
    }

    public function it_can_become_an_array_of_characters()
    {
        $this->beConstructedWith('fooæøåπ');

        $result = $this->characters();

        $result->shouldBeArray();

        foreach (['f', 'o', 'o', 'æ', 'ø', 'å', 'π'] as $index => $char) {
            $result[$index]->shouldHaveType(Stringy::class);
            $result[$index]->string()->shouldBe($char);
        }
    }

    public function it_can_append_a_substring()
    {
        $this->beConstructedWith('foo');

        $this->append('')->string()->shouldBe('foo');
        $this->append(' ')->append('bar')->string()->shouldBe('foo bar');
        $this->append(' ')->append(Stringy::create('bar'))->string()->shouldBe('foo bar');
    }

    public function it_can_prepend_a_substring()
    {
        $this->beConstructedWith('foo');

        $this->prepend('')->string()->shouldBe('foo');
        $this->prepend(' ')->prepend('bar')->string()->shouldBe('bar foo');
        $this->prepend(' ')->prepend(Stringy::create('bar'))->string()->shouldBe('bar foo');
    }

    public function it_can_be_surrounded_by_two_other_strings()
    {
        $this->beConstructedWith('bar');

        $this->surroundWith('')->string()->shouldBe('bar');
        $this->surroundWith('::')->string()->shouldBe('::bar::');
        $this->surroundWith(Stringy::create('::'))->string()->shouldBe('::bar::');
        $this->surroundWith(' ')->surroundWith('foo', 'baz')->string()->shouldBe('foo bar baz');
    }

    public function it_can_detect_the_shortest_cycle_in_a_string()
    {
        $this->beConstructedWith('bar');

        $this->cycle()->string()->shouldBe('');

        $this->replace('bar', 'something barbar')
            ->cycle()->string()->shouldBe('bar');

        $this->replace('bar', '123123123')
            ->cycle()->string()->shouldBe('123');

        $this->replace('bar', 'bar foofoofoo')
            ->cycle()->string()->shouldBe('foo');

        $this->replace('bar', 'something foo bar baz foo bar baz fo')
            ->cycle()->string()->shouldBe(' foo bar baz');

        $this->replace('bar', 'something foo bar baz foo bar baz else')
            ->cycle()->string()->shouldBe('');

        $this->replace('bar', 'something foo bar baz foo bar baz bar')
            ->cycle()->string()->shouldBe('');
    }

    public function it_can_be_included_in_another_template_string()
    {
        $this->beConstructedWith('foo');

        $this->includeIn('')->string()->shouldBe('');
        $this->includeIn('%s')->string()->shouldBe('foo');
        $this->includeIn('%s bar baz')->string()->shouldBe('foo bar baz');
        $this->includeIn('%s %s baz', ['bar'])->string()->shouldBe('foo bar baz');
        $this->includeIn('%s %s %s', ['bar', 'baz'])->string()->shouldBe('foo bar baz');
    }

    public function it_can_be_reversed()
    {
        $this->beConstructedWith('foo');

        $this->reverse()->string()->shouldBe('oof');
        $this->replace('foo', '')->reverse()->string()->shouldBe('');
    }

    public function it_can_be_used_as_glue_to_implode_an_array()
    {
        $this->beConstructedWith(' ');

        $this->glue(['foo', 'bar', 'baz'])->string()->shouldBe('foo bar baz');

        $this->glue([
            Stringy::create('foo', 'UTF-8'),
            Stringy::create('bar', 'UTF-8'),
            Stringy::create('baz', 'UTF-8'),
        ])->string()->shouldBe('foo bar baz');
    }

    public function it_can_return_its_length()
    {
        $this->beConstructedWith('foo');

        $this->length()->shouldBe(3);

        $this->replace('foo', $this->testString())
            ->length()
            ->shouldBe(mb_strlen($this->testString(), 'UTF-8'));
    }

    public function it_can_remove_a_substring()
    {
        $this->beConstructedWith('foo bar bing baz');

        $this->remove('bing ')->string()->shouldBe('foo bar baz');

        $this->remove('o')->string()->shouldBe('f bar bing baz');
        $this->remove('a')->string()->shouldBe('foo br bing bz');
        $this->remove('b')->string()->shouldBe('foo ar ing az');
        $this->remove(' ')->string()->shouldBe('foobarbingbaz');

        $this->removeMany(['a', 'o', 'b', ' '])->string()->shouldBe('fringz');
    }

    public function it_can_make_a_string_safe_for_ascii()
    {
        $this->beConstructedWith('foo');

        $this->asciiSafe()->string()->shouldBe('foo');

        $this->replace('foo', '金')->asciiSafe()->string()->shouldBe('Jin ');
        $this->replace('foo', 'u')->asciiSafe()->string()->shouldBe('u');
        $this->replace('foo', 'æ')->asciiSafe()->string()->shouldBe('ae');
        $this->replace('foo', 'ø')->asciiSafe()->string()->shouldBe('o');
        $this->replace('foo', 'π')->asciiSafe()->string()->shouldBe('p');
        $this->replace('foo', '€')->asciiSafe()->string()->shouldBe('EUR');
    }

    public function it_can_be_encoded_into_html_entities()
    {
        $this->beConstructedWith('foo & bar & baz');

        $this->entityEncoded()->string()->shouldBe('foo &amp; bar &amp; baz');
    }

    public function it_has_a_toString_method()
    {
        $this->beConstructedWith('foo');

        $this->__toString()->shouldBe($this->string());
    }

    public function it_has_the_set_state_constructor()
    {
        $this->beConstructedThrough('__set_state', [
            ['string' => $this->testString()],
        ]);

        $this->string()->shouldBe($this->testString());
    }
}
