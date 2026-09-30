<?php

namespace Tests\Unit;

use App\Support\Money;
use App\Support\Phone;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SupportHelpersTest extends TestCase
{
    #[Test]
    public function money_is_formatted_as_rupiah(): void
    {
        $this->assertSame('Rp 3.500', Money::rupiah(3500));
        $this->assertSame('Rp 1.250.000', Money::rupiah(1250000));
        $this->assertSame('-Rp 2.000', Money::rupiah(-2000));
        $this->assertSame('Rp 0', Money::rupiah(null));
        $this->assertSame('3.500', Money::rupiah(3500, false));
    }

    #[Test]
    public function phone_numbers_are_normalised_to_international_format(): void
    {
        $this->assertSame('6281234567890', Phone::normalize('081234567890'));
        $this->assertSame('6281234567890', Phone::normalize('+62 812-3456-7890'));
        $this->assertSame('6281234567890', Phone::normalize('81234567890'));
        $this->assertNull(Phone::normalize(''));
        $this->assertNull(Phone::normalize(null));
    }

    #[Test]
    public function whatsapp_links_are_built_with_encoded_text(): void
    {
        $this->assertSame('https://wa.me/6281234567890', Phone::whatsappUrl('081234567890'));
        $this->assertSame('https://wa.me/6281234567890?text=Halo%20Nitip', Phone::whatsappUrl('081234567890', 'Halo Nitip'));
        $this->assertNull(Phone::whatsappUrl(null, 'x'));
        $this->assertSame('0812-3456-7890', Phone::pretty('6281234567890'));
    }
}
