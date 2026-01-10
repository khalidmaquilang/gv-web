<?php

declare(strict_types=1);

namespace App\Features\User\Concerns;

use Bavix\Wallet\Traits\HasWalletFloat;
use Bavix\Wallet\Traits\HasWallets;

trait GvCoinConcernTrait
{
    use HasWalletFloat;
    use HasWallets;

    public function getGvCoins(): float
    {
        if (! $this->hasWallet('gv-coins')) {
            $wallet = $this->createWallet([
                'name' => 'Gv Coins',
                'slug' => 'gv-coins',
            ]);

            return $wallet->balanceFloatNum;
        }

        $wallet = $this->getWallet('gv-coins');

        return $wallet->balanceFloatNum;
    }
}
