<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AITokensGenerated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $sessionId;
    public string $type; // 'suggestion', 'chat', 'follow_up'
    public array $tokens;
    public bool $isComplete;

    public function __construct(string $sessionId, string $type, array $tokens, bool $isComplete = false)
    {
        $this->sessionId = $sessionId;
        $this->type = $type;
        $this->tokens = $tokens;
        $this->isComplete = $isComplete;
    }
}
