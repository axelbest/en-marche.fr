<?php

namespace AppBundle;

final class Events
{
    public const COMMITTEE_CREATED = 'committee_created';
    public const COMMITTEE_UPDATED = 'committee_updated';
    public const EVENT_CREATED = 'event_created';
    public const EVENT_UPDATED = 'event_updated';
    public const EVENT_CANCELLED = 'event_cancelled';
    public const EVENT_REGISTRATION_CREATED = 'event_registration_created';
    public const CITIZEN_ACTION_CREATED = 'citizen_action_created';
    public const CITIZEN_ACTION_UPDATED = 'citizen_action_updated';
    public const CITIZEN_ACTION_CANCELLED = 'citizen_action_cancelled';
    public const CITIZEN_PROJECT_CREATED = 'citizen_project_created';
    public const CITIZEN_PROJECT_UPDATED = 'citizen_project_updated';
    public const CITIZEN_PROJECT_APPROVED = 'citizen_project_approved';
    public const CITIZEN_PROJECT_FOLLOWER_ADDED = 'citizen_project_follower_added';
    public const CITIZEN_PROJECT_COMMENT_CREATED = 'citizen_project_comment_created';

    private function __construct()
    {
    }
}
