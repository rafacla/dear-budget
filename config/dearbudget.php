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
            'checkingAccount'       => '🏦 Checking Account',  
            'walletCash'            => '👛 Cash Wallet', 
            'investmentAccount'     => '📈 Investment Account',
            'creditCard'            => '💳 Credit Card',
            'expenseAccount'        => '📤 Expense Account',
            'incomeAccount'         => '📥 Income Account'
        ],
    ]
?>