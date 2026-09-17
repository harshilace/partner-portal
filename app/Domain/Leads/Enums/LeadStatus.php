<?php

namespace App\Domain\Leads\Enums;

enum LeadStatus: string
{
    case NEW = 'new';
    case CONTACTED = 'contacted';
    case DEMO_SCHEDULED = 'demo_scheduled';
    case DEMO_COMPLETED = 'demo_completed';
    case PROPOSAL_SENT = 'proposal_sent';
    case NEGOTIATION = 'negotiation';
    case PAYMENT_PENDING = 'payment_pending';
    case CONVERTED = 'converted';
    case LOST = 'lost';
    case NOT_INTERESTED = 'not_interested';
}
