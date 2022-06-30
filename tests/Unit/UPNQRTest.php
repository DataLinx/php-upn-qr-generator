<?php

namespace DataLinx\PhpUpnQrGenerator\Tests\Unit;

use DataLinx\PhpUpnQrGenerator\UPNQR;
use Exception;
use PHPUnit\Framework\TestCase;
use Zxing\QrReader;

include "vendor/autoload.php";


class UPNQRTest extends TestCase
{
    private UPNQR $QR;
    private UPNQR $QRR;

    /**
     * @return void
     * @throws Exception
     */
    public function setDefaultQr(): void
    {
        $this->QR = new UPNQR();

        $this->QR->setPayerIban("SI56020170014356205");
        $this->QR->setDeposit(true);
        $this->QR->setWithdraw(false);
        $this->QR->setPayerReference("SI00225268-32526-222");
        $this->QR->setPayerName("Omer d.o.o.");
        $this->QR->setPayerStreetAddress("Lepa ulica 33");
        $this->QR->setPayerCity("Izola");
        $this->QR->setAmount(55.586);
        $this->QR->setPaymentDate("2022-06-16");
        $this->QR->setUrgent(false);
        $this->QR->setPurposeCode("COST");
        $this->QR->setPaymentPurpose("Predračun 111");
        $this->QR->setPaymentDueDate("2022-06-30");
        $this->QR->setRecipientIban("SI56020360253863406");
        $this->QR->setRecipientReference("SI081236-17-34565");
        $this->QR->setRecipientName("Podjetje d.o.o.");
        $this->QR->setRecipientStreetAddress("Neka ulica 5");
        $this->QR->setRecipientCity("Ljubljana");
    }

    protected function setUp(): void
    {
        parent::setUp();

        // create an instance of the UPNQR class and set the parameters
        $this->setDefaultQr();

        // create a second instance of the UNPQR class without setting the parameters
        $this->QRR = new UPNQR();
    }

    public function testValidation()
    {
        $this->assertEquals("SI56020170014356205", $this->QR->getPayerIban());
        $this->assertEquals("X", $this->QR->getDeposit() ? 'X' : '');
        $this->assertEquals("", $this->QR->getWithdraw() ? 'X' : '');
        $this->assertEquals("SI00225268-32526-222", $this->QR->getPayerReference());
        $this->assertEquals("Omer d.o.o.", $this->QR->getPayerName());
        $this->assertEquals("Lepa ulica 33", $this->QR->getPayerStreetAddress());
        $this->assertEquals("Izola", $this->QR->getPayerCity());
        $this->assertEquals("00000005559", $this->QR->getAmount(true));
        $this->assertEquals("16.06.2022", $this->QR->getPaymentDate(true));
        $this->assertEquals("", $this->QR->getUrgent() ? 'X' : '');
        $this->assertEquals("COST", $this->QR->getPurposeCode() ? strtoupper($this->QR->getPurposeCode()) : "OTHR");
        $this->assertEquals("Predračun 111", $this->QR->getPaymentPurpose());
        $this->assertEquals("30.06.2022", $this->QR->getPaymentDueDate(true));
        $this->assertEquals("SI56020360253863406", $this->QR->getRecipientIban());
        $this->assertEquals("SI081236-17-34565", $this->QR->getRecipientReference());
        $this->assertEquals("Podjetje d.o.o.", $this->QR->getRecipientName());
        $this->assertEquals("Neka ulica 5", $this->QR->getRecipientStreetAddress());
        $this->assertEquals("Ljubljana", $this->QR->getRecipientCity());
    }

    public function testFileOutput()
    {
        try {
            $this->QR->generateQrCode("./build/qrcode.svg");
        } catch (Exception $e) {
            throw new Exception("Error serializing QR contents or generating QR code image. " . $e->getMessage());
        }
        $this->assertFileExists("./build/qrcode.svg");
    }

    public function testGeneratedImageContents()
    {
        $qrcode = new QrReader("./build/qrcode.svg");
        $text = $qrcode->text(); //return decoded text from QR Code

        $explodedText = explode("\n", $text);
        $this->assertSame($explodedText[0], UPNQR::VODILNI_SLOG);
        $this->assertSame($explodedText[1], $this->QR->getPayerIban());
        $this->assertSame($explodedText[2], $this->QR->getDeposit() ? 'X' : '');
        $this->assertSame($explodedText[3], $this->QR->getWithdraw() ? 'X' : '');
        $this->assertSame($explodedText[4], $this->QR->getPayerReference());
        $this->assertSame($explodedText[5], $this->QR->getPayerName());
        $this->assertSame($explodedText[6], $this->QR->getPayerStreetAddress());
        $this->assertSame($explodedText[7], $this->QR->getPayerCity());
        $this->assertSame($explodedText[8], $this->QR->getAmount(true));
        $this->assertSame($explodedText[9], $this->QR->getPaymentDate(true));
        $this->assertSame($explodedText[10], $this->QR->getUrgent() ? 'X' : '');
        $this->assertSame($explodedText[11], $this->QR->getPurposeCode());
        //$this->assertSame($explodedText[12],"Predračun 111");
        $this->assertSame($explodedText[13], $this->QR->getPaymentDueDate(true));
        $this->assertSame($explodedText[14], $this->QR->getRecipientIban());
        $this->assertSame($explodedText[15], $this->QR->getRecipientReference());
        $this->assertSame($explodedText[16], $this->QR->getRecipientName());
        $this->assertSame($explodedText[17], $this->QR->getRecipientStreetAddress());
        $this->assertSame($explodedText[18], $this->QR->getRecipientCity());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testPayerIban()
    {
        $correctCases = [
            ["SI56020170014356205", "SI56020170014356205"],
            ["SI56 0201 7001 4356 205", "SI56020170014356205"],
            ["    SI56020170014356208", "SI56020170014356208"],
            ["SI56020170014356209   ", "SI56020170014356209"],
            ["SI 56    020170014356201   ", "SI56020170014356201"],
        ];

        foreach ($correctCases as $case) {
            $this->QRR->setPayerIban($case[0]);
            $this->assertEquals($case[1], $this->QRR->getPayerIban());
        }

        $wrongCases = [
            ["SI5602017001435620", "ibanPlacnika must be 19 characters long with the country code prefix of two characters (alpha-2 ISO standard)."],
            ["5602017001435620", "ibanPlacnika must be 19 characters long with the country code prefix of two characters (alpha-2 ISO standard)."],
            ["5456020170014356205", "ibanPlacnika must be 19 characters long with the country code prefix of two characters (alpha-2 ISO standard)."],
        ];

        foreach ($wrongCases as $case) {
            try {
                $this->QRR->setPayerIban($case[0]);
            } catch (Exception $e) {
                $this->assertEquals($case[1], $e->getMessage());
            }
        }
    }

    /**
     * @return void
     */
    public function testDeposit()
    {
        $correctCases = [
            [true, "X"],
            [false, ""],
        ];
        foreach ($correctCases as $case) {
            $this->QRR->setDeposit($case[0]);
            $this->assertEquals($case[1], $this->QRR->getDeposit() ? 'X' : '');
        }
    }

    /**
     * @return void
     */
    public function testWithdraw()
    {
        $correctCases = [
            [true, "X"],
            [false, ""],
        ];
        foreach ($correctCases as $case) {
            $this->QRR->setWithdraw($case[0]);
            $this->assertEquals($case[1], $this->QRR->getWithdraw() ? 'X' : '');
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testPayerReference()
    {
        $correctCases = [
            ["SI99123456789", "SI99123456789"],
            ["RF99123456789", "RF99123456789"],
            ["SI99123456789   ", "SI99123456789"],
            ["  RF99123456789     ", "RF99123456789"],
            ["  RF99123456789     ", "RF99123456789"],
            ["SI00 ", "SI00"],
            ["RF99 ", "RF99"],
        ];

        foreach ($correctCases as $case) {
            $this->QRR->setPayerReference($case[0]);
            $this->assertEquals($case[1], $this->QRR->getPayerReference());
        }

        $wrongCases = [
            ["SI", "referencaPlacnika must start with SI or RF and then 2 digits and other digits or characters."],
            ["RF", "referencaPlacnika must start with SI or RF and then 2 digits and other digits or characters."],
            ["SI9", "referencaPlacnika must start with SI or RF and then 2 digits and other digits or characters."],
            ["RF9", "referencaPlacnika must start with SI or RF and then 2 digits and other digits or characters."],
            ["SO99", "referencaPlacnika must start with SI or RF and then 2 digits and other digits or characters."],
            ["TF99", "referencaPlacnika must start with SI or RF and then 2 digits and other digits or characters."],
            ["SI9912345678912345678912345", "referencaPlacnika should not have more than 26 characters."],
            ["SI9965465-4156-15615-615", "referencaPlacnika that starts with SI should not have more than two dashes."],
        ];

        foreach ($wrongCases as $case) {
            try {
                $this->QRR->setPayerReference($case[0]);
            } catch (Exception $e) {
                $this->assertEquals($case[1], $e->getMessage());
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testPayerName()
    {
        $correctCases = [
            ["Mark", "Mark"],
            ["  Mark", "Mark"],
            ["Mark  ", "Mark"],
            ["Ma rk  ", "Ma rk"],
        ];

        foreach ($correctCases as $case) {
            $this->QRR->setPayerName($case[0]);
            $this->assertEquals($case[1], $this->QRR->getPayerName());
        }

        $wrongCases = [
            [str_repeat('a', 34), "imePlacnika should not have more than 33 characters."],
        ];

        foreach ($wrongCases as $case) {
            try {
                $this->QRR->setPayerName($case[0]);
            } catch (Exception $e) {
                $this->assertEquals($case[1], $e->getMessage());
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testPayerStreetAddress()
    {
        $correctCases = [
            ["Koprska ulica 55", "Koprska ulica 55"],
            ["   Koprska ulica 55", "Koprska ulica 55"],
            ["Koprska ulica 55    ", "Koprska ulica 55"],
        ];

        foreach ($correctCases as $case) {
            $this->QRR->setPayerStreetAddress($case[0]);
            $this->assertEquals($case[1], $this->QRR->getPayerStreetAddress());
        }

        $wrongCases = [
            [str_repeat('a', 34), "ulicaPlacnika should not have more than 33 characters."],
        ];

        foreach ($wrongCases as $case) {
            try {
                $this->QRR->setPayerStreetAddress($case[0]);
            } catch (Exception $e) {
                $this->assertEquals($case[1], $e->getMessage());
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testPayerCity()
    {
        $correctCases = [
            ["Koper", "Koper"],
            ["  Koper ", "Koper"],
            ["Koper   ", "Koper"],
        ];

        foreach ($correctCases as $case) {
            $this->QRR->setPayerCity($case[0]);
            $this->assertEquals($case[1], $this->QRR->getPayerCity());
        }

        $wrongCases = [
            [str_repeat('a', 34), "krajPlacnika should not have more than 33 characters."],
        ];

        foreach ($wrongCases as $case) {
            try {
                $this->QRR->setPayerCity($case[0]);
            } catch (Exception $e) {
                $this->assertEquals($case[1], $e->getMessage());
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testAmountUpnFormat()
    {
        $correctCases = [
            [1, "00000000100"],
            [55, "00000005500"],
            [11.55, "00000001155"],
            [0.55, "00000000055"],
            [1155, "00000115500"],
            [999999999, "99999999900"],
        ];

        foreach ($correctCases as $case) {
            $this->QRR->setAmount($case[0]);
            $this->assertEquals($case[1], $this->QRR->getAmount(true));
        }

        $wrongCases = [
            [0, "znesek should be more than 0 and less than 1000000000"],
            [-1, "znesek should be more than 0 and less than 1000000000"],
            [1000000000, "znesek should be more than 0 and less than 1000000000"],
        ];

        foreach ($wrongCases as $case) {
            try {
                $this->QRR->setAmount($case[0]);
            } catch (Exception $e) {
                $this->assertEquals($case[1], $e->getMessage());
            }
        }
    }

    public function testAmount()
    {
        $correctCases = [
            [1, 1],
            [999999999, 999999999],
        ];

        foreach ($correctCases as $case) {
            $this->QRR->setAmount($case[0]);
            $this->assertEquals($case[1], $this->QRR->getAmount(false));
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testPaymentDateUpnFormat()
    {
        $correctCases = [
            ["2022-01-01", "01.01.2022"],
            ["2022-12-12", "12.12.2022"],
            ["2022-06-01", "01.06.2022"],
        ];

        foreach ($correctCases as $case) {
            $this->QRR->setPaymentDate($case[0]);
            $this->assertEquals($case[1], $this->QRR->getPaymentDate(true));
        }

        $wrongCases = [
            ["202-05-06", "datumPlacila should be in the YYYY-MM-DD format."],
            ["2022-05-6", "datumPlacila should be in the YYYY-MM-DD format."],
            ["2022-5-06", "datumPlacila should be in the YYYY-MM-DD format."],
            ["2022-13-08", "The provided datumPlacila is not a valid date."],
            ["2022-05-32", "The provided datumPlacila is not a valid date."],
            ["1969-12-31", "The provided datumPlacila is not a valid date."],
        ];

        foreach ($wrongCases as $case) {
            try {
                $this->QRR->setPaymentDate($case[0]);
            } catch (Exception $e) {
                $this->assertEquals($case[1], $e->getMessage());
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testPaymentDate()
    {
        $correctCases = [
            ["1970-01-01", "1970-01-01"],
            ["2022-06-01", "2022-06-01"],
            ["2500-12-12", "2500-12-12"],
        ];

        foreach ($correctCases as $case) {
            $this->QRR->setPaymentDate($case[0]);
            $this->assertEquals($case[1], $this->QRR->getPaymentDate(false));
        }
    }

    /**
     * @return void
     */
    public function testUrgent()
    {
        $correctCases = [
            [true, "X"],
            [false, ""],
        ];
        foreach ($correctCases as $case) {
            $this->QRR->setUrgent($case[0]);
            $this->assertEquals($case[1], $this->QRR->getUrgent() ? 'X' : '');
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testPurposeCode()
    {
        $correctCases = [
            ["COST", "COST"],
            ["COMM  ", "COMM"],
            ["  COMM  ", "COMM"],
        ];

        foreach ($correctCases as $case) {
            $this->QRR->setPurposeCode($case[0]);
            $this->assertEquals($case[1], $this->QRR->getPurposeCode());
        }

        $wrongCases = [
            ["", "kodaNamena must have exactly four uppercase characters [A-Z]."],
            [" ", "kodaNamena must have exactly four uppercase characters [A-Z]."],
            ["RTF ", "kodaNamena must have exactly four uppercase characters [A-Z]."],
            ["RTFDE", "kodaNamena must have exactly four uppercase characters [A-Z]."],
        ];

        foreach ($wrongCases as $case) {
            try {
                $this->QRR->setPurposeCode($case[0]);
            } catch (Exception $e) {
                $this->assertEquals($case[1], $e->getMessage());
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testPaymentPurpose()
    {
        $correctCases = [
            ["Prenos sredstev", "Prenos sredstev"],
            ["Prenos sredstev   ", "Prenos sredstev"],
            ["   Prenos sredstev", "Prenos sredstev"],
            [str_repeat("a", 42), str_repeat("a", 42)],
        ];

        foreach ($correctCases as $case) {
            $this->QRR->setPaymentPurpose($case[0]);
            $this->assertEquals($case[1], $this->QRR->getPaymentPurpose());
        }

        $wrongCases = [
            [str_repeat("a", 43), "namenPlacila should not have more than 42 characters."],
        ];

        foreach ($wrongCases as $case) {
            try {
                $this->QRR->setPaymentPurpose($case[0]);
            } catch (Exception $e) {
                $this->assertEquals($case[1], $e->getMessage());
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testPaymentDueDateUpnFormat()
    {
        $correctCases = [
            ["2022-01-01", "01.01.2022"],
            ["2022-12-12", "12.12.2022"],
            ["2022-06-01", "01.06.2022"],
        ];

        foreach ($correctCases as $case) {
            $this->QRR->setPaymentDueDate($case[0]);
            $this->assertEquals($case[1], $this->QRR->getPaymentDueDate(true));
        }

        $wrongCases = [
            ["202-05-06", "rokPlacila should be in the YYYY-MM-DD format."],
            ["2022-05-6", "rokPlacila should be in the YYYY-MM-DD format."],
            ["2022-5-06", "rokPlacila should be in the YYYY-MM-DD format."],
            ["2022-13-08", "The provided rokPlacila is not a valid date."],
            ["2022-05-32", "The provided rokPlacila is not a valid date."],
        ];

        foreach ($wrongCases as $case) {
            try {
                $this->QRR->setPaymentDueDate($case[0]);
            } catch (Exception $e) {
                $this->assertEquals($case[1], $e->getMessage());
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testPaymentDueDate()
    {
        $correctCases = [
            ["1970-01-01", "1970-01-01"],
            ["2022-06-01", "2022-06-01"],
            ["2500-12-12", "2500-12-12"],
        ];

        foreach ($correctCases as $case) {
            $this->QRR->setPaymentDueDate($case[0]);
            $this->assertEquals($case[1], $this->QRR->getPaymentDueDate(false));
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testRecipientIban()
    {
        $correctCases = [
            ["SI56020170014356205", "SI56020170014356205"],
            ["SI56 0201 7001 4356 205", "SI56020170014356205"],
            ["    SI56020170014356208", "SI56020170014356208"],
            ["SI56020170014356209   ", "SI56020170014356209"],
            ["SI 56    020170014356201   ", "SI56020170014356201"],
        ];

        foreach ($correctCases as $case) {
            $this->QRR->setRecipientIban($case[0]);
            $this->assertEquals($case[1], $this->QRR->getRecipientIban());
        }

        $wrongCases = [
            ["SI5602017001435620", "ibanPrejemnika must be 19 characters long with the country code prefix of two characters (alpha-2 ISO standard)."],
            ["5602017001435620", "ibanPrejemnika must be 19 characters long with the country code prefix of two characters (alpha-2 ISO standard)."],
            ["545602017001435620545", "ibanPrejemnika must be 19 characters long with the country code prefix of two characters (alpha-2 ISO standard)."],
        ];

        foreach ($wrongCases as $case) {
            try {
                $this->QRR->setRecipientIban($case[0]);
            } catch (Exception $e) {
                $this->assertEquals($case[1], $e->getMessage());
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testRecipientReference()
    {
        $correctCases = [
            ["SI99123456789", "SI99123456789"],
            ["RF99123456789", "RF99123456789"],
            ["SI99123456789   ", "SI99123456789"],
            ["  RF99123456789     ", "RF99123456789"],
            ["  RF99123456789     ", "RF99123456789"],
            ["SI00 ", "SI00"],
            ["RF99 ", "RF99"],
        ];

        foreach ($correctCases as $case) {
            $this->QRR->setRecipientReference($case[0]);
            $this->assertEquals($case[1], $this->QRR->getRecipientReference());
        }

        $wrongCases = [
            ["SI9", "referencaPrejemnika must start with SI or RF and then 2 digits and other digits or characters."],
            ["RF9", "referencaPrejemnika must start with SI or RF and then 2 digits and other digits or characters."],
            ["SO99", "referencaPrejemnika must start with SI or RF and then 2 digits and other digits or characters."],
            ["TF99", "referencaPrejemnika must start with SI or RF and then 2 digits and other digits or characters."],
            ["SI9912345678912345678912345", "referencaPrejemnika should not have more than 26 characters."],
            ["SI9965465-4156-15615-615", "referencaPrejemnika that starts with SI should not have more than two dashes."],
        ];

        foreach ($wrongCases as $case) {
            try {
                $this->QRR->setRecipientReference($case[0]);
            } catch (Exception $e) {
                $this->assertEquals($case[1], $e->getMessage());
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testRecipientName()
    {
        $correctCases = [
            ["Mark", "Mark"],
            ["  Mark", "Mark"],
            ["Mark  ", "Mark"],
            ["Ma rk  ", "Ma rk"],
        ];

        foreach ($correctCases as $case) {
            $this->QRR->setRecipientName($case[0]);
            $this->assertEquals($case[1], $this->QRR->getRecipientName());
        }

        $wrongCases = [
            [str_repeat("a", 34), "imePrejemnika should not have more than 33 characters."],
        ];

        foreach ($wrongCases as $case) {
            try {
                $this->QRR->setRecipientName($case[0]);
            } catch (Exception $e) {
                $this->assertEquals($case[1], $e->getMessage());
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testRecipientStreetAddress()
    {
        $correctCases = [
            ["Koperska ulica 55", "Koperska ulica 55"],
            ["   Koperska ulica 55", "Koperska ulica 55"],
            ["Koperska ulica 55    ", "Koperska ulica 55"],
        ];

        foreach ($correctCases as $case) {
            $this->QRR->setRecipientStreetAddress($case[0]);
            $this->assertEquals($case[1], $this->QRR->getRecipientStreetAddress());
        }

        $wrongCases = [
            [str_repeat("a", 34), "ulicaPrejemnika should not have more than 33 characters."],
        ];

        foreach ($wrongCases as $case) {
            try {
                $this->QRR->setRecipientStreetAddress($case[0]);
            } catch (Exception $e) {
                $this->assertEquals($case[1], $e->getMessage());
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testRecipientCity()
    {
        $correctCases = [
            ["Koper", "Koper"],
            ["  Koper ", "Koper"],
            ["Koper   ", "Koper"],
        ];

        foreach ($correctCases as $case) {
            $this->QRR->setRecipientCity($case[0]);
            $this->assertEquals($case[1], $this->QRR->getRecipientCity());
        }

        $wrongCases = [
            [str_repeat("a", 34), "krajPrejemnika should not have more than 33 characters."],
        ];

        foreach ($wrongCases as $case) {
            try {
                $this->QRR->setRecipientCity($case[0]);
            } catch (Exception $e) {
                $this->assertEquals($case[1], $e->getMessage());
            }
        }
    }
}
