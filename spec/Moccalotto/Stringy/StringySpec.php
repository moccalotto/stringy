<?php

namespace spec\Moccalotto\Stringy;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class StringySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Moccalotto\Stringy\Stringy');
    }

    function it_can_make_a_string_shorter()
    {
        $this->beConstructedWith('test string');
        $this->limit(4)->shouldHaveType('Moccalotto\Stringy\Stringy');
        $this->limit(4)->string()->shouldBe('test');
    }
}
