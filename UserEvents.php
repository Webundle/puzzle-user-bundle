<?php
namespace Puzzle\UserBundle;

final class UserEvents
{
    const USER_CREATING = 'user.creating';
    const USER_CREATED = 'user.created';
    
    const USER_UPDATING = 'user.updating';
    const USER_UPDATED = 'user.updated';
    
    const USER_ENABLED = 'user.enabled';
    const USER_DISABLED = 'user.disabled';
    
    const USER_LOCKED = 'user.locked';
    const USER_UNLOCKED = 'user.unlocked';
}