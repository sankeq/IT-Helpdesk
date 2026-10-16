<?php

namespace Tests\Unit;

use App\Services\TicketNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TicketNumberGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_correct_format(): void
    {
        $generator = new TicketNumberGenerator;
        $ticketNumber = $generator->generate();

        $year = now('Asia/Makassar')->year;
        $this->assertEquals("IKI-IT-{$year}-000001", $ticketNumber);
    }

    public function test_it_increments_consecutively(): void
    {
        $generator = new TicketNumberGenerator;
        $year = now('Asia/Makassar')->year;

        $number1 = $generator->generate();
        $number2 = $generator->generate();
        $number3 = $generator->generate();

        $this->assertEquals("IKI-IT-{$year}-000001", $number1);
        $this->assertEquals("IKI-IT-{$year}-000002", $number2);
        $this->assertEquals("IKI-IT-{$year}-000003", $number3);

        $sequence = DB::table('ticket_number_sequences')->where('prefix', "IKI-IT-{$year}")->first();
        $this->assertEquals(3, $sequence->last_number);
    }
}
