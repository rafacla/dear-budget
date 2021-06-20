<?php
    declare(strict_types=1);

    /* Not for editing
     * Things you change here can break application for good
     * Proceed with caution
     */

    return [
        'availableLanguages'    => [
            'en'    =>  'English',
            'pt_BR' =>  'Brazilian Portuguese',
        ],
        /*
        * Checking Account for your account where you make debit transactions
        * Credit Card for your credit transactions
        * Wallet Cash for transactions on cash
        * Investment Account for any kind of account where you keep savings (this account is off budget)
        * Expense Account for debits (accounts that receive money from withdrawals/payments like 3rd parts)
        * Income Account for credits (accounts that pay/give you money off budget like 3rd parts)
        */
        'accountRoles'            => [
            'checkingAccount'       => ['name' => '🏦 Checking Account', 'icon' => '🏦', 'budget' => 'on'],
            'walletCash'            => ['name' => '👛 Cash Wallet', 'icon' => '👛', 'budget' => 'on'],
            'investmentAccount'     => ['name' => '📈 Investment Account', 'icon' => '📈', 'budget' => 'investment'],
            'creditCard'            => ['name' => '💳 Credit Card', 'icon' => '💳', 'budget' => 'on'],
            'expenseAccount'        => ['name' => '📤 Expense Account', 'icon' => '📤', 'budget' => 'off'],
            'incomeAccount'         => ['name' => '📥 Income Account', 'icon' => '📥', 'budget' => 'off'],
        ],
        /*
        * Transaction types
        *   transfer: transfer values from an asset account to another
        *   expense: transfer value from an asset account (credit) to an expense account (debit)
        *   income: transfer falue from an income account (credit) to an asset account (debit)
        *   initialBalance: set transaction as the opening balance
        */
        'transactionTypes'          => [
            0     =>  ['type' => 'transfer'],
            1     =>  ['type' => 'expense'],
            2     =>  ['type' => 'income'],
            3     =>  ['type' => 'initialBalance']
        ],
    ]
?>
