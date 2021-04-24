<?php
    declare(strict_types=1);

    /* Not for editing
     * Things you change here can break application for good 
     * Proceed with caution
     */

    return [
        /* 
        * Checking Account for your account where you make debit transactions
        * Credit Card for your credit transactions
        * Wallet Cash for transactions on cash
        * Investment Account for any kind of account where you keep savings (this account is off budget)
        */
        'accountKinds'            => ['checkingAccount', 'creditCard', 'walletCash', 'investmentAccount'],
    ]
?>