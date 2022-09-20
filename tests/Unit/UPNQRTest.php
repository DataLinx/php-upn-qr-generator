<?php

namespace DataLinx\PhpUpnQrGenerator\Tests\Unit;

use DataLinx\PhpUpnQrGenerator\UPNQR;
use Exception;
use PHPUnit\Framework\TestCase;
use Zxing\QrReader;

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
        $this->QR->setPayerName("Janez Novak");
        $this->QR->setPayerStreetAddress("Lepa ulica 33");
        $this->QR->setPayerCity("Koper");
        $this->QR->setAmount(55.586);
        $this->QR->setPaymentDate("2022-06-16");
        $this->QR->setUrgent(false);
        $this->QR->setPaymentPurpose("Predracun 111");
        $this->QR->setPaymentDueDate("2022-06-30");
        $this->QR->setRecipientIban("SI56020360253863406");
        $this->QR->setRecipientReference("SI081236-17-34565");
        $this->QR->setRecipientName("Podjetje d.o.o.");
        $this->QR->setRecipientStreetAddress("Neka ulica 5");
        $this->QR->setRecipientCity("Ljubljana");

        // We must set a value for "purposeCode" and it must not be OTHR, because there appears to be a bug in the OCR library and the test fails. However, the QR generator works fine with any value.
        $this->QR->setPurposeCode("GDSV");
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create build folder if it doesn't exist
        if (!file_exists("build")) {
            mkdir("build");
        }

        // Create an instance of the UPNQR class and set the parameters
        $this->setDefaultQr();

        // Create a second instance of the UNPQR class without setting the parameters
        $this->QRR = new UPNQR();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testGenerateQrCodeException(): void
    {
        // We pass an invalid filename (typo in path)
        $this->expectException("Exception");
        $this->QR->generateQrCode("./buid/qrcode.svg");
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testCheckRequiredParametersException(): void
    {
        // We instantiate the UPNQR object without setting the recipient IBAN number
        $UPNQR = new UPNQR();

        $UPNQR->setPayerIban("SI56020170014356205");
        $UPNQR->setDeposit(true);
        $UPNQR->setWithdraw(false);
        $UPNQR->setPayerReference("SI00225268-32526-222");
        $UPNQR->setPayerName("Janez Novak");
        $UPNQR->setPayerStreetAddress("Lepa ulica 33");
        $UPNQR->setPayerCity("Koper");
        $UPNQR->setAmount(55.586);
        $UPNQR->setPaymentDate("2022-06-16");
        $UPNQR->setUrgent(false);
        $UPNQR->setPurposeCode("COST");
        $UPNQR->setPaymentPurpose("Predračun 111");
        $UPNQR->setPaymentDueDate("2022-06-30");
        $UPNQR->setRecipientReference("SI081236-17-34565");
        $UPNQR->setRecipientName("Podjetje d.o.o.");
        $UPNQR->setRecipientStreetAddress("Neka ulica 5");
        $UPNQR->setRecipientCity("Ljubljana");

        try {
            $UPNQR->checkRequiredParameters();
        } catch (Exception $e) {
            $this->assertEquals("recipientIban is required.", $e->getMessage());
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testFileOutput(): void
    {
        $this->QR->generateQrCode("./build/qrcode.svg");

        $this->assertFileExists("./build/qrcode.svg");
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testGeneratedImageContents(): void
    {
        $this->QR->generateQrCode('./build/qrcode.png');
        $qrcode = new QrReader("./build/qrcode.png");

        // Return decoded text from QR Code
        $text = $qrcode->text();

        $this->assertIsString($text);

        $explodedText = explode("\n", $text);
        $this->assertSame($explodedText[0], UPNQR::LEADING_STRING);
        $this->assertSame($explodedText[1], $this->QR->getPayerIban());
        $this->assertSame($explodedText[2], $this->QR->getDeposit() ? 'X' : '');
        $this->assertSame($explodedText[3], $this->QR->getWithdraw() ? 'X' : '');
        $this->assertSame($explodedText[4], $this->QR->getPayerReference());
        $this->assertSame($explodedText[5], $this->QR->getPayerName());
        $this->assertSame($explodedText[6], $this->QR->getPayerStreetAddress());
        $this->assertSame($explodedText[7], $this->QR->getPayerCity());
        $this->assertSame($explodedText[8], $this->QR->getFormattedAmount());
        $this->assertSame($explodedText[9], $this->QR->formatDate($this->QR->getPaymentDate()));
        $this->assertSame($explodedText[10], $this->QR->getUrgent() ? 'X' : '');
        $this->assertSame($explodedText[11], $this->QR->getPurposeCode() ? strtoupper($this->QR->getPurposeCode()) : UPNQR::DEFAULT_PURPOSE_CODE);
        $this->assertSame($explodedText[12], $this->QR->getPaymentPurpose());
        $this->assertSame($explodedText[13], $this->QR->formatDate($this->QR->getPaymentDueDate()));
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
    public function testPayerIban(): void
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
            ["SI5602017001435620", "Payer IBAN must either be null or have 19 characters with the country code prefix of two characters (alpha-2 ISO standard)."],
            ["5602017001435620", "Payer IBAN must either be null or have 19 characters with the country code prefix of two characters (alpha-2 ISO standard)."],
            ["5456020170014356205", "Payer IBAN must either be null or have 19 characters with the country code prefix of two characters (alpha-2 ISO standard)."],
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
    public function testDeposit(): void
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
    public function testWithdraw(): void
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
    public function testPayerReference(): void
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
            ["SI", "Payer reference must either be null or start with SI or RF and then 2 digits and other digits or characters."],
            ["RF", "Payer reference must either be null or start with SI or RF and then 2 digits and other digits or characters."],
            ["SI9", "Payer reference must either be null or start with SI or RF and then 2 digits and other digits or characters."],
            ["RF9", "Payer reference must either be null or start with SI or RF and then 2 digits and other digits or characters."],
            ["SO99", "Payer reference must either be null or start with SI or RF and then 2 digits and other digits or characters."],
            ["TF99", "Payer reference must either be null or start with SI or RF and then 2 digits and other digits or characters."],
            ["SI9912345678912345678912345", "Payer reference should not have more than 26 characters."],
            ["SI9965465-4156-15615-615", "Payer references that starts with SI should not have more than two dashes."],
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
    public function testPayerName(): void
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
            [str_repeat('a', 34), "Payer name must either be null or not have more than 33 characters."],
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
    public function testPayerStreetAddress(): void
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
            [str_repeat('a', 34), "Payer street address must either be null or not have more than 33 characters."],
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
    public function testPayerCity(): void
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
            [str_repeat('a', 34), "Payer city must either be null or not have more than 33 characters."],
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
    public function testAmountUpnFormat(): void
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
            $this->assertEquals($case[1], $this->QRR->getFormattedAmount());
        }

        $wrongCases = [
            [0, "Amount must either be null or a value between 0 and 1,000,000,000"],
            [-1, "Amount must either be null or a value between 0 and 1,000,000,000"],
            [1000000000, "Amount must either be null or a value between 0 and 1,000,000,000"],
        ];

        foreach ($wrongCases as $case) {
            try {
                $this->QRR->setAmount($case[0]);
            } catch (Exception $e) {
                $this->assertEquals($case[1], $e->getMessage());
            }
        }
    }

    /**
     * @throws Exception
     */
    public function testAmount(): void
    {
        $correctCases = [
            [1, 1],
            [999999999, 999999999],
        ];

        foreach ($correctCases as $case) {
            $this->QRR->setAmount($case[0]);
            $this->assertEquals($case[1], $this->QRR->getAmount());
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testPaymentDateUpnFormat(): void
    {
        $correctCases = [
            ["2022-01-01", "01.01.2022"],
            ["2022-12-12", "12.12.2022"],
            ["2022-06-01", "01.06.2022"],
        ];

        foreach ($correctCases as $case) {
            $this->QRR->setPaymentDate($case[0]);
            $this->assertEquals($case[1], $this->QRR->formatDate($this->QRR->getPaymentDate()));
        }

        $wrongCases = [
            ["202-05-06", "Payment date must either be null or be in the YYYY-MM-DD format."],
            ["2022-05-6", "Payment date must either be null or be in the YYYY-MM-DD format."],
            ["2022-5-06", "Payment date must either be null or be in the YYYY-MM-DD format."],
            ["2022-13-08", "The provided payment date is not a valid date."],
            ["2022-05-32", "The provided payment date is not a valid date."],
            ["1969-12-31", "The provided payment date is not a valid date."],
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
    public function testPaymentDate(): void
    {
        $correctCases = [
            ["1970-01-01", "01.01.1970"],
            ["2022-06-01", "01.06.2022"],
            ["2500-12-12", "12.12.2500"],
        ];

        foreach ($correctCases as $case) {
            $this->QRR->setPaymentDate($case[0]);
            $this->assertEquals($case[1], $this->QRR->formatDate($this->QRR->getPaymentDate()));
        }
    }

    /**
     * @return void
     */
    public function testUrgent(): void
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
    public function testPurposeCode(): void
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
            ["", "Purpose code must be null or have exactly four uppercase characters [A-Z]."],
            [" ", "Purpose code must be null or have exactly four uppercase characters [A-Z]."],
            ["RTF ", "Purpose code must be null or have exactly four uppercase characters [A-Z]."],
            ["RTFDE", "Purpose code must be null or have exactly four uppercase characters [A-Z]."],
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
    public function testPaymentPurpose(): void
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
            [str_repeat("a", 43), "Payment purpose must either be null or not have more than 42 characters."],
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
    public function testPaymentDueDate(): void
    {
        $correctCases = [
            ["2022-01-01", "01.01.2022"],
            ["2022-12-12", "12.12.2022"],
            ["2022-06-01", "01.06.2022"],
        ];

        foreach ($correctCases as $case) {
            $this->QRR->setPaymentDueDate($case[0]);
            $this->assertEquals($case[1], $this->QRR->formatDate($this->QRR->getPaymentDueDate()));
        }

        $wrongCases = [
            ["202-05-06", "Payment due date must either be null or be in the YYYY-MM-DD format."],
            ["2022-05-6", "Payment due date must either be null or be in the YYYY-MM-DD format."],
            ["2022-5-06", "Payment due date must either be null or be in the YYYY-MM-DD format."],
            ["2022-13-08", "The provided payment due date is not a valid date."],
            ["2022-05-32", "The provided payment due date is not a valid date."],
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
    public function testRecipientIban(): void
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
            ["SI5602017001435620", "Recipient IBAN must be 19 characters long with the country code prefix of two characters (alpha-2 ISO standard)."],
            ["5602017001435620", "Recipient IBAN must be 19 characters long with the country code prefix of two characters (alpha-2 ISO standard)."],
            ["545602017001435620545", "Recipient IBAN must be 19 characters long with the country code prefix of two characters (alpha-2 ISO standard)."],
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
    public function testRecipientReference(): void
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
            ["SI9", "Recipient reference must either be null or start with SI or RF and then 2 digits and other digits or characters."],
            ["RF9", "Recipient reference must either be null or start with SI or RF and then 2 digits and other digits or characters."],
            ["SO99", "Recipient reference must either be null or start with SI or RF and then 2 digits and other digits or characters."],
            ["TF99", "Recipient reference must either be null or start with SI or RF and then 2 digits and other digits or characters."],
            ["SI9912345678912345678912345", "Recipient reference should not have more than 26 characters."],
            ["SI9965465-4156-15615-615", "Recipient references that starts with SI should not have more than two dashes."],
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
    public function testRecipientName(): void
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
            [str_repeat("a", 34), "Recipient name must either be null or not have more than 33 characters."],
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
    public function testRecipientStreetAddress(): void
    {
        $correctCases = [
            ["Koprska ulica 55", "Koprska ulica 55"],
            ["   Koprska ulica 55", "Koprska ulica 55"],
            ["Koprska ulica 55    ", "Koprska ulica 55"],
        ];

        foreach ($correctCases as $case) {
            $this->QRR->setRecipientStreetAddress($case[0]);
            $this->assertEquals($case[1], $this->QRR->getRecipientStreetAddress());
        }

        $wrongCases = [
            [str_repeat("a", 34), "Recipient street address must either be null or not have more than 33 characters."],
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
    public function testRecipientCity(): void
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
            [str_repeat("a", 34), "Recipient city should not have more than 33 characters."],
        ];

        foreach ($wrongCases as $case) {
            try {
                $this->QRR->setRecipientCity($case[0]);
            } catch (Exception $e) {
                $this->assertEquals($case[1], $e->getMessage());
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testNullableParameters(): void
    {
        $qr = new UPNQR();

        $this->assertNull($qr->getPayerIban());
        $this->assertIsObject($qr->setPayerIban('SI56020170014356205'));
        $this->assertIsObject($qr->setPayerIban(null));

        $this->assertNull($qr->getDeposit());
        $this->assertIsObject($qr->setDeposit(true));
        $this->assertIsObject($qr->setDeposit(null));

        $this->assertNull($qr->getWithdraw());
        $this->assertIsObject($qr->setWithdraw(true));
        $this->assertIsObject($qr->setWithdraw(null));

        $this->assertNull($qr->getPayerReference());
        $this->assertIsObject($qr->setPayerReference('SI00225268-32526-222'));
        $this->assertIsObject($qr->setPayerReference(null));

        $this->assertNull($qr->getPayerName());
        $this->assertIsObject($qr->setPayerName('Janez Novak'));
        $this->assertIsObject($qr->setPayerName(null));

        $this->assertNull($qr->getPayerStreetAddress());
        $this->assertIsObject($qr->setPayerStreetAddress('Lepa ulica 33'));
        $this->assertIsObject($qr->setPayerStreetAddress(null));

        $this->assertNull($qr->getPayerCity());
        $this->assertIsObject($qr->setPayerCity('Koper'));
        $this->assertIsObject($qr->setPayerCity(null));

        $this->assertNull($qr->getAmount());
        $this->assertIsObject($qr->setAmount('44.24'));
        $this->assertIsObject($qr->setAmount(null));

        $this->assertNull($qr->getPaymentDate());
        $this->assertIsObject($qr->setPaymentDate('2022-06-16'));
        $this->assertIsObject($qr->setPaymentDate(null));

        $this->assertNull($qr->getUrgent());
        $this->assertIsObject($qr->setUrgent(true));
        $this->assertIsObject($qr->setUrgent(null));

        $this->assertNull($qr->getPurposeCode());
        $this->assertIsObject($qr->setPurposeCode('COST'));
        $this->assertIsObject($qr->setPurposeCode(null));

        $this->assertNull($qr->getPaymentDueDate());
        $this->assertIsObject($qr->setPaymentDueDate('2022-06-16'));
        $this->assertIsObject($qr->setPaymentDueDate(null));

        $this->assertNull($qr->getRecipientReference());
        $this->assertIsObject($qr->setRecipientReference('SI081236-17-34565'));
        $this->assertIsObject($qr->setRecipientReference(null));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testMinimal(): void
    {
        if (file_exists("./build/minimalQr.svg")) {
            unlink("./build/minimalQr.svg");
        }

        $qr = new UPNQR();

        $qr->setRecipientIban("SI56020360253863406");
        $qr->setRecipientCity("Ljubljana");

        $qr->generateQrCode("./build/minimalQr.svg");

        $this->assertFileExists("./build/minimalQr.svg");
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testFileOutputs(): void
    {
        $svgFilename = "./build/qrOutputType.svg";
        $pngFilename = "./build/qrOutputType.png";
        $epsFilename = "./build/qrOutputType.eps";

        if (file_exists($svgFilename)) {
            unlink($svgFilename);
        }

        if (file_exists($pngFilename)) {
            unlink($pngFilename);
        }

        if (file_exists($epsFilename)) {
            unlink($epsFilename);
        }

        $qr = new UPNQR();

        $qr->setPayerIban("SI56020170014356205");
        $qr->setPayerReference("SI00225268-32526-222");
        $qr->setPayerName("Janez Novak");
        $qr->setPayerCity("Koper");
        $qr->setAmount(55.586);
        $qr->setPaymentDate("2022-06-16");
        $qr->setPurposeCode("COST");
        $qr->setPaymentPurpose("Predračun 111");
        $qr->setRecipientIban("SI56020360253863406");
        $qr->setRecipientReference("SI081236-17-34565");
        $qr->setRecipientCity("Ljubljana");

        $qr->generateQrCode($svgFilename);
        $this->assertFileExists($svgFilename);

        $qr->generateQrCode($pngFilename);
        $this->assertFileExists($pngFilename);

        $qr->generateQrCode($epsFilename);
        $this->assertFileExists($epsFilename);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testInvalidFileExtension(): void
    {
        $qr = new UPNQR();

        $qr->setRecipientIban("SI56020360253863406");
        $qr->setRecipientCity("Ljubljana");

        $this->expectExceptionMessage("Beacon QR code threw an exception: Please provide a valid path with a supported extension (.png, .svg or .eps).");

        // we set an invalid file extension (.sv instead of .svg)
        $qr->generateQrCode("./build/invalidQr.sv");
    }
}
