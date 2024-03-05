<?php

// definindo o namespace, referente ao caminho das pastas
namespace App\Repositories\Transactions;

// importações
use Core\UseCase\Intefaces\TransactionDbInterface;
use Illuminate\Support\Facades\DB;

// definindo a classe concreta de tratamento de transações no BD
class TransactionDb implements TransactionDbInterface
{
    // construtor
    public function __construct()
    {
        // inicializando a transação no construtor
        DB::beginTransaction();
    }

    public function commit()
    {
        DB::commit();
    }

    public function rollback()
    {
        DB::rollBack();
    }
}
