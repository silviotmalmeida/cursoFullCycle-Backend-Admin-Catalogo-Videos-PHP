<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\UseCase\Intefaces;

// definindo a interface de tratamento de transações no BD
interface TransactionDbInterface
{
    public function commit();

    public function rollback();
}
