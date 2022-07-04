<?php

namespace DataLinx\PhpUpnQrGenerator;

use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Exception;

class UPNQR
{
    public const VODILNI_SLOG = "UPNQR";
    public const DEFAULT_PURPOSE_CODE = "OTHR";

    protected string $payerIban;
    protected bool $deposit;
    protected bool $withdraw;
    protected string $payerReference;
    protected string $payerName;
    protected string $payerStreetAddress;
    protected string $payerCity;
    protected ?float $amount;
    protected string $paymentDate;
    protected bool $urgent;
    protected ?string $purposeCode;
    protected string $paymentPurpose;
    protected string $paymentDueDate;
    protected string $recipientIban;
    protected string $recipientReference;
    protected string $recipientName;
    protected string $recipientStreetAddress;
    protected string $recipientCity;

    /**
     * Serialize UPN contents
     * @return string
     * @throws Exception
     */
    public function serializeContents(): string
    {
        // Check if all required parameters are set
        $this->checkRequiredParameters();

        $qrDelim = "\n";

        $qrContentStr = implode($qrDelim, [
                self::VODILNI_SLOG,
                $this->getPayerIban(),
                $this->getDeposit() ? 'X' : '',
                $this->getWithdraw() ? 'X' : '',
                $this->getPayerReference(),
                $this->getPayerName(),
                $this->getPayerStreetAddress(),
                $this->getPayerCity(),
                $this->getFormattedAmount(),
                $this->formatDate($this->getPaymentDate()),
                $this->getUrgent() ? 'X' : '',
                $this->getPurposeCode() ? strtoupper($this->getPurposeCode()) : self::DEFAULT_PURPOSE_CODE,
                $this->getPaymentPurpose(),
                $this->formatDate($this->getPaymentDueDate()),
                $this->getRecipientIban(),
                $this->getRecipientReference() ?: "SI99",
                $this->getRecipientName(),
                $this->getRecipientStreetAddress(),
                $this->getRecipientCity(),
            ]) . $qrDelim;

        // Checksum check. Max characters is 411.
        $checksum = mb_strlen($qrContentStr);

        $qrContentStr .= sprintf('%03d', $checksum);

        return $qrContentStr;
    }

    /**
     * Generate QR code based on object data. You can define the filetype by providing the file extension (.png and .svg are suppoerted)
     * @param string $filename target file name
     * @return void
     * @throws Exception
     */
    public function generateQrCode(string $filename)
    {
        try {
            $renderer = new ImageRenderer(
                new RendererStyle(400),
                new ImagickImageBackEnd()
            );
            $writer = new Writer($renderer);
            $writer->writeFile($this->serializeContents(), $filename, "ISO-8859-2");
        } catch (Exception $exception) {
            throw new Exception("Beacon QR code threw an exception: " . $exception->getMessage());
        }
    }

    /**
     * Check if all the required parameters are set
     * @throws Exception
     */
    public function checkRequiredParameters()
    {
        $params = [
            'paymentPurpose',
            'recipientIban',
            'recipientName',
            'recipientStreetAddress',
            'recipientCity',
        ];

        foreach ($params as $param) {
            if (!isset($this->{$param})) {
                throw new Exception("$param is required.");
            }
        }
    }

    /**
     * @return string
     */
    public function getPayerIban(): string
    {
        return $this->payerIban;
    }

    /**
     * Payer IBAN account number written with 19 characters (example: SI56020170014356205)
     * (sln. IBAN plačnika)
     * @param string $payerIban
     * @return void
     * @throws Exception
     */
    public function setPayerIban(string $payerIban): void
    {
        $payerIban = trim(str_replace(' ', '', $payerIban));
        if (!preg_match('/^[a-z]{2}\d{17}$/i', $payerIban)) {
            throw new Exception("Payer IBAN must be 19 characters long with the country code prefix of two characters (alpha-2 ISO standard).");
        }
        $this->payerIban = $payerIban;
    }

    /**
     * @return bool
     */
    public function getDeposit(): bool
    {
        return $this->deposit;
    }

    /**
     * Set order deposit state
     * (sln. polog)
     * @param bool $deposit
     * @return void
     */
    public function setDeposit(bool $deposit): void
    {
        $this->deposit = $deposit;
    }

    /**
     * @return bool
     */
    public function getWithdraw(): bool
    {
        return $this->withdraw;
    }

    /**
     * Set order withdrawal state
     * (sln. dvig)
     * @param bool $withdraw
     * @return void
     */
    public function setWithdraw(bool $withdraw): void
    {
        $this->withdraw = $withdraw;
    }

    /**
     * @return string
     */
    public function getPayerReference(): string
    {
        return $this->payerReference;
    }

    /**
     * Payer reference number (example: SI00225268-32526-222)
     * (sln. referenca plačnika)
     * @param string $payerReference
     * @return void
     * @throws Exception
     */
    public function setPayerReference(string $payerReference): void
    {
        $payerReference = trim($payerReference);
        if (!preg_match('/^(SI|RF)\d{2}/', $payerReference)) {
            throw new Exception("Payer reference must start with SI or RF and then 2 digits and other digits or characters.");
        }
        if (mb_strlen($payerReference) > 26) {
            throw new Exception("Payer reference should not have more than 26 characters.");
        }

        // Source: http://www.firmar.si/index.jsp?pg=nasveti-clanki/upn/referenca-si-in-rf-za-univerzalni-placilni-nalog-upn
        if (preg_match('/^SI/', $payerReference) && substr_count($payerReference, '-') > 2) {
            throw new Exception("Payer references that starts with SI should not have more than two dashes.");
        }
        $this->payerReference = $payerReference;
    }

    /**
     * @return string
     */
    public function getPayerName(): string
    {
        return $this->payerName;
    }

    /**
     * Payer name/title
     * (sln. ime plačnika)
     * @param string $payerName
     * @return void
     * @throws Exception
     */
    public function setPayerName(string $payerName): void
    {
        $payerName = trim($payerName);
        if (mb_strlen($payerName) > 33) {
            throw new Exception("Payer name should not have more than 33 characters.");
        }
        $this->payerName = $payerName;
    }

    /**
     * @return string
     */
    public function getPayerStreetAddress(): string
    {
        return $this->payerStreetAddress;
    }

    /**
     * Payer street name and number
     * (sln. ulica in št. plačnika)
     * @param string $payerStreetAddress
     * @return void
     * @throws Exception
     */
    public function setPayerStreetAddress(string $payerStreetAddress): void
    {
        $payerStreetAddress = trim($payerStreetAddress);
        if (mb_strlen($payerStreetAddress) > 33) {
            throw new Exception("Payer street address should not have more than 33 characters.");
        }
        $this->payerStreetAddress = $payerStreetAddress;
    }

    /**
     * @return string
     */
    public function getPayerCity(): string
    {
        return $this->payerCity;
    }

    /**
     * Payer city/location name
     * (sln. kraj plačnika)
     * @param string $payerCity
     * @return void
     * @throws Exception
     */
    public function setPayerCity(string $payerCity): void
    {
        $payerCity = trim($payerCity);
        if (mb_strlen($payerCity) > 33) {
            throw new Exception("Payer city should not have more than 33 characters.");
        }
        $this->payerCity = $payerCity;
    }

    /**
     * @return float|null
     */
    public function getAmount(): ?float
    {
        return $this->amount ?? null;
    }

    /**
     * Returns amount in QR UPN required format. Example: 150.555 will be 00000015056
     * @return string
     */
    public function getFormattedAmount(): string
    {
        return str_pad(number_format($this->amount, 2, "", ""), 11, 0, STR_PAD_LEFT);
    }

    /**
     * Payment amount
     * (sln. znesek)
     * @param float|null $amount
     * @return $this
     * @throws Exception
     */
    public function setAmount(?float $amount): self
    {
        if ($amount !== null and ($amount <= 0 or $amount > 999999999)) {
            throw new Exception("Amount must either be null or a value between 0 and 1,000,000,000");
        }

        $this->amount = $amount;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentDate(): string
    {
        return $this->paymentDate;
    }

    /**
     * Payment date (example. 2022-06-16)
     * (sln. datum plačila)
     * @param string $paymentDate
     * @return void
     * @throws Exception
     */
    public function setPaymentDate(string $paymentDate): void
    {
        $paymentDate = trim($paymentDate);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $paymentDate)) {
            throw new Exception("Payment date should be in the YYYY-MM-DD format.");
        }
        if (!strtotime($paymentDate)) {
            throw new Exception("The provided payment date is not a valid date.");
        }
        $this->paymentDate = $paymentDate;
    }

    /**
     * @return bool
     */
    public function getUrgent(): bool
    {
        return $this->urgent;
    }

    /**
     * Set if order is urgent
     * (sln. nujno)
     * @param bool $urgent
     * @return void
     */
    public function setUrgent(bool $urgent): void
    {
        $this->urgent = $urgent;
    }

    /**
     * @return string|null
     */
    public function getPurposeCode(): ?string
    {
        return $this->purposeCode ?? null;
    }

    /**
     * Order purpose code (example: COST)
     * (sln. koda namena)
     * @param string|null $purposeCode 4-letter payment code in uppercase
     * @return $this
     * @throws Exception
     */
    public function setPurposeCode(?string $purposeCode): self
    {
        $purposeCode = trim($purposeCode);
        if ($purposeCode != null and !preg_match('/^[A-Z]{4}$/', $purposeCode)) {
            throw new Exception("Purpose code must be null or have exactly four uppercase characters [A-Z].");
        }

        $this->purposeCode = $purposeCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentPurpose(): string
    {
        return $this->paymentPurpose;
    }

    /**
     * Payment purpose text
     * (sln. namen plačila)
     * @param string $paymentPurpose
     * @return void
     * @throws Exception
     */
    public function setPaymentPurpose(string $paymentPurpose): void
    {
        $paymentPurpose = trim($paymentPurpose);
        if (mb_strlen($paymentPurpose) > 42) {
            throw new Exception("Payment purpose should not have more than 42 characters.");
        }
        $this->paymentPurpose = $paymentPurpose;
    }

    /**
     * @return string
     */
    public function getPaymentDueDate(): string
    {
        return $this->paymentDueDate;
    }

    /**
     * Payment due date (example: 2022-09-05)
     * (sln. rok plačila)
     * @param string $paymentDueDate
     * @return void
     * @throws Exception
     */
    public function setPaymentDueDate(string $paymentDueDate): void
    {
        $paymentDueDate = trim($paymentDueDate);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $paymentDueDate)) {
            throw new Exception("Payment due date should be in the YYYY-MM-DD format.");
        }
        if (!strtotime($paymentDueDate)) {
            throw new Exception("The provided payment due date is not a valid date.");
        }
        $this->paymentDueDate = $paymentDueDate;
    }

    /**
     * @return string
     */
    public function getRecipientIban(): string
    {
        return $this->recipientIban;
    }

    /**
     * Recipient/payee IBAN account number written with 19 characters (example: SI56020170014356205)
     * (sln. IBAN prejemnika)
     * @param string $recipientIban
     * @return void
     * @throws Exception
     */
    public function setRecipientIban(string $recipientIban): void
    {
        $recipientIban = trim(str_replace(' ', '', $recipientIban));
        if (!preg_match('/^[a-z]{2}\d{17}$/i', $recipientIban)) {
            throw new Exception("Recipient IBAN must be 19 characters long with the country code prefix of two characters (alpha-2 ISO standard).");
        }
        $this->recipientIban = $recipientIban;
    }

    /**
     * @return string
     */
    public function getRecipientReference(): string
    {
        return $this->recipientReference;
    }

    /**
     * Recipient/payee reference number (example: SI00225268-32526-222)
     * (sln. referenca prejemnika)
     * @param string $recipientReference
     * @return void
     * @throws Exception
     */
    public function setRecipientReference(string $recipientReference): void
    {
        $recipientReference = trim($recipientReference);
        if (!preg_match('/^(SI|RF)\d{2}/', $recipientReference)) {
            throw new Exception("Recipient reference must start with SI or RF and then 2 digits and other digits or characters.");
        }
        if (mb_strlen($recipientReference) > 26) {
            throw new Exception("Recipient reference should not have more than 26 characters.");
        }
        if (preg_match('/^SI/', $recipientReference) && substr_count($recipientReference, '-') > 2) {
            throw new Exception("Recipient references that starts with SI should not have more than two dashes.");
        }
        $this->recipientReference = $recipientReference;
    }

    /**
     * @return string
     */
    public function getRecipientName(): string
    {
        return $this->recipientName;
    }

    /**
     * Recipient/payee name/title
     * (sln. ime prejemnika)
     * @param string $recipientName
     * @return void
     * @throws Exception
     */
    public function setRecipientName(string $recipientName): void
    {
        $recipientName = trim($recipientName);
        if (mb_strlen($recipientName) > 33) {
            throw new Exception("Recipient name should not have more than 33 characters.");
        }
        $this->recipientName = $recipientName;
    }

    /**
     * @return string
     */
    public function getRecipientStreetAddress(): string
    {
        return $this->recipientStreetAddress;
    }

    /**
     * Recipient/payee street name and number
     * (sln. ulica in št. prejemnika)
     * @param string $recipientStreetAddress
     * @return void
     * @throws Exception
     */
    public function setRecipientStreetAddress(string $recipientStreetAddress): void
    {
        $recipientStreetAddress = trim($recipientStreetAddress);
        if (mb_strlen($recipientStreetAddress) > 33) {
            throw new Exception("Recipient street address should not have more than 33 characters.");
        }
        $this->recipientStreetAddress = $recipientStreetAddress;
    }

    /**
     * @return string
     */
    public function getRecipientCity(): string
    {
        return $this->recipientCity;
    }

    /**
     * Recipient/payee city/location name
     * (sln. kraj prejemnika)
     * @param string $recipientCity
     * @return void
     * @throws Exception
     */
    public function setRecipientCity(string $recipientCity): void
    {
        $recipientCity = trim($recipientCity);
        if (mb_strlen($recipientCity) > 33) {
            throw new Exception("Recipient city should not have more than 33 characters.");
        }
        $this->recipientCity = $recipientCity;
    }

    /**
     * Format date from 'Y-m-d' to 'd.m.Y'
     * @param string $date
     * @return string
     */
    public function formatDate(string $date): string
    {
        return date('d.m.Y', strtotime($date));
    }
}
