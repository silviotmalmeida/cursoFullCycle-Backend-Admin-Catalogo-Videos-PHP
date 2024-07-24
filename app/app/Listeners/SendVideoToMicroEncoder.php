<?php

// definindo o namespace, referente ao caminho das pastas
namespace App\Listeners;

//importações
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

// classe listener para realizar ações após ocorrência do VideoCreatedEvent
class SendVideoToMicroEncoder
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        //ações a serem realizadas
        Log::info($event->getPayload());
    }
}
