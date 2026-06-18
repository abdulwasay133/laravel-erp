<?php

namespace App\Services\POS;

use App\Models\POSTransaction;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;

class POSReceiptService
{
    public function generate(POSTransaction $transaction): POSTransaction
    {
        return $transaction->load(['items', 'payments', 'customer', 'session.user']);
    }

    public function getThermalData(POSTransaction $transaction): array
    {
        $transaction->load(['items', 'payments', 'customer', 'session.user']);

        $companyName    = Setting::getValue('company_name', 'Your Store');
        $companyAddress = Setting::getValue('company_address', '');
        $companyPhone   = Setting::getValue('company_phone', '');
        $companyEmail   = Setting::getValue('company_email', '');
        $footer         = Setting::getValue('pos_receipt_footer', 'Thank you for your purchase!');
        $width          = (int) Setting::getValue('pos_thermal_width', 48);

        $lines = [];
        $lines[] = '';
        $lines[] = $this->center(strtoupper($companyName), $width);
        $lines[] = '';

        if ($companyAddress) {
            foreach (explode("\n", $companyAddress) as $addrLine) {
                $lines[] = $this->center(trim($addrLine), $width);
            }
        }
        if ($companyPhone) {
            $lines[] = $this->center('Tel: ' . $companyPhone, $width);
        }
        if ($companyEmail) {
            $lines[] = $this->center($companyEmail, $width);
        }

        $lines[] = '';
        $lines[] = str_repeat('=', $width);

        $receiptLabel = Setting::getValue('pos_receipt_label', 'SALE INVOICE');
        $lines[] = $this->center($receiptLabel, $width);
        $lines[] = str_repeat('=', $width);
        $lines[] = '';

        $lines[] = str_pad('Receipt:', 14) . $transaction->receipt_no;
        $lines[] = str_pad('Date:', 14) . $transaction->transaction_at->format('d M Y h:i A');
        $lines[] = str_pad('Cashier:', 14) . ($transaction->session->user->name ?? '-');

        $customerName = $transaction->customer
            ? trim($transaction->customer->first_name . ' ' . $transaction->customer->last_name)
            : ($transaction->customer_name ?? 'Walk-in');
        $customerPhone = $transaction->customer?->phone ?? $transaction->customer_phone ?? '';
        $lines[] = str_pad('Customer:', 14) . $customerName;
        if ($customerPhone) {
            $lines[] = str_pad('Phone:', 14) . $customerPhone;
        }

        $lines[] = '';
        $lines[] = str_repeat('-', $width);
        $lines[] = str_pad('Item', $width - 22, ' ', STR_PAD_RIGHT) . '  Qty    Price    Total';
        $lines[] = str_repeat('-', $width);

        foreach ($transaction->items as $item) {
            $name = mb_substr($item->product_name, 0, $width - 26);
            $qty  = number_format($item->quantity, 0);
            $price = number_format($item->unit_price, 0);
            $total = number_format($item->line_total, 0);
            $lines[] = str_pad($name, $width - 22, ' ', STR_PAD_RIGHT)
                     . '  ' . str_pad($qty, 3, ' ', STR_PAD_LEFT)
                     . '  ' . str_pad($price, 6, ' ', STR_PAD_LEFT)
                     . '  ' . str_pad($total, 6, ' ', STR_PAD_LEFT);
        }

        $lines[] = str_repeat('-', $width);
        $lines[] = str_pad('Subtotal:', $width - 14, ' ', STR_PAD_LEFT)
                 . str_pad(number_format($transaction->subtotal, 0), 14, ' ', STR_PAD_LEFT);

        if ((float) $transaction->discount_amount > 0) {
            $lines[] = str_pad('Discount:', $width - 14, ' ', STR_PAD_LEFT)
                     . str_pad('- ' . number_format($transaction->discount_amount, 0), 14, ' ', STR_PAD_LEFT);
        }

        $lines[] = str_repeat('=', $width);
        $lines[] = str_pad('GRAND TOTAL:', $width - 14, ' ', STR_PAD_LEFT)
                 . str_pad('Rs. ' . number_format($transaction->grand_total, 0), 14, ' ', STR_PAD_LEFT);
        $lines[] = str_repeat('=', $width);

        $lines[] = str_pad('Tendered:', $width - 14, ' ', STR_PAD_LEFT)
                 . str_pad(number_format($transaction->tendered_amount, 0), 14, ' ', STR_PAD_LEFT);
        $lines[] = str_pad('Change:', $width - 14, ' ', STR_PAD_LEFT)
                 . str_pad(number_format($transaction->change_amount, 0), 14, ' ', STR_PAD_LEFT);

        if ($transaction->payments->count()) {
            foreach ($transaction->payments as $payment) {
                $lines[] = str_pad('Paid via ' . ucfirst($payment->method) . ':', $width - 14, ' ', STR_PAD_LEFT)
                         . str_pad(number_format($payment->amount, 0), 14, ' ', STR_PAD_LEFT);
            }
        }

        $lines[] = '';
        $lines[] = str_repeat('-', $width);

        foreach (explode("\n", $footer) as $footerLine) {
            $trimmed = trim($footerLine);
            if ($trimmed) {
                $lines[] = $this->center($trimmed, $width);
            }
        }

        $lines[] = '';
        $lines[] = str_repeat('-', $width);
        $lines[] = '';

        // Generate QR code SVG
        $qrData = $companyName . "\n"
                . 'Receipt: ' . $transaction->receipt_no . "\n"
                . 'Date: ' . $transaction->transaction_at->format('d M Y h:i A') . "\n"
                . 'Total: Rs. ' . number_format($transaction->grand_total, 2);
        $qrSvg = $this->generateQrSvg($qrData);

        return [
            'raw'        => implode("\n", $lines),
            'lines'      => $lines,
            'width'      => $width,
            'qr_svg'     => $qrSvg,
            'company'    => $companyName,
            'customer'   => $customerName,
            'receipt_no' => $transaction->receipt_no,
            'date'       => $transaction->transaction_at->format('d M Y h:i A'),
        ];
    }

    public function generatePdf(POSTransaction $transaction): \Barryvdh\DomPDF\PDF
    {
        $transaction->load(['items', 'payments', 'customer', 'session.user']);
        $settings = [
            'company_name'    => Setting::getValue('company_name', 'Store'),
            'company_address' => Setting::getValue('company_address', ''),
            'company_phone'   => Setting::getValue('company_phone', ''),
            'company_email'   => Setting::getValue('company_email', ''),
        ];

        return Pdf::loadView('pos.receipt-pdf', compact('transaction', 'settings'));
    }

    public function generateQrSvg(string $data): string
    {
        try {
            $qrCode = new QrCode(
                data: $data,
                encoding: new Encoding('UTF-8'),
                size: 160
            );
            $writer = new SvgWriter();
            $result = $writer->write($qrCode);
            return $result->getString();
        } catch (\Exception $e) {
            return '';
        }
    }

    private function center(string $text, int $width): string
    {
        $len = mb_strlen($text);
        if ($len >= $width) return $text;
        $padding = intdiv($width - $len, 2);
        return str_repeat(' ', $padding) . $text;
    }
}
