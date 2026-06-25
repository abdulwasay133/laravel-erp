<?php

namespace App\Services;

trait HandlesAccounting
{
    protected AccountingService $accounting;

    protected function initAccounting(): void
    {
        $this->accounting = app(AccountingService::class);
    }

    protected function postSaleAccounting(int $saleId, float $totalAmount, bool $hasInventory = true, ?float $cogs = null): void
    {
        $this->initAccounting();
        $this->accounting->postSaleInvoice($saleId, $totalAmount, $hasInventory, $cogs);
    }

    protected function postSalePaymentAccounting(int $saleId, float $amount, string $paymentMethod = 'cash'): void
    {
        $this->initAccounting();
        $this->accounting->postSalePayment($saleId, $amount, $paymentMethod);
    }

    protected function postPurchaseAccounting(int $purchaseId, float $totalAmount, bool $isInventory = true): void
    {
        $this->initAccounting();
        $this->accounting->postPurchaseInvoice($purchaseId, $totalAmount, $isInventory);
    }

    protected function postSupplierPaymentAccounting(int $supplierPaymentId, float $amount, string $paymentMethod = 'cash'): void
    {
        $this->initAccounting();
        $this->accounting->postSupplierPayment($supplierPaymentId, $amount, $paymentMethod);
    }

    protected function postExpenseAccounting(int $expenseId, float $amount, int $expenseAccountId, string $paymentMethod = 'cash'): void
    {
        $this->initAccounting();
        $this->accounting->postExpense($expenseId, $amount, $expenseAccountId, $paymentMethod);
    }

    protected function postSalaryAccounting(int $salaryPaymentId, float $totalSalary): void
    {
        $this->initAccounting();
        $this->accounting->postSalaryExpense($salaryPaymentId, $totalSalary);
    }

    protected function postCustomerPaymentAccounting(int $customerPaymentId, float $amount, string $paymentMethod = 'cash', string $paymentType = 'credit'): void
    {
        $this->initAccounting();
        $this->accounting->postCustomerPayment($customerPaymentId, $amount, $paymentMethod, $paymentType);
    }

    protected function postWasteAccounting(int $wasteId, float $totalCost, string $wasteDate): void
    {
        $this->initAccounting();
        $this->accounting->postWaste($wasteId, $totalCost, $wasteDate);
    }

    protected function reverseAccounting(string $referenceType, int $referenceId, string $reason = 'Reversed'): void
    {
        $this->initAccounting();
        $this->accounting->reverseJournalEntry($referenceType, $referenceId, $reason);
    }
}
