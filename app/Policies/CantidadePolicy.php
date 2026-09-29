<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Cantidade;
use Illuminate\Auth\Access\HandlesAuthorization;

class CantidadePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Cantidade');
    }

    public function view(AuthUser $authUser, Cantidade $cantidade): bool
    {
        return $authUser->can('View:Cantidade');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Cantidade');
    }

    public function update(AuthUser $authUser, Cantidade $cantidade): bool
    {
        return $authUser->can('Update:Cantidade');
    }

    public function delete(AuthUser $authUser, Cantidade $cantidade): bool
    {
        return $authUser->can('Delete:Cantidade');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Cantidade');
    }

    public function restore(AuthUser $authUser, Cantidade $cantidade): bool
    {
        return $authUser->can('Restore:Cantidade');
    }

    public function forceDelete(AuthUser $authUser, Cantidade $cantidade): bool
    {
        return $authUser->can('ForceDelete:Cantidade');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Cantidade');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Cantidade');
    }

    public function replicate(AuthUser $authUser, Cantidade $cantidade): bool
    {
        return $authUser->can('Replicate:Cantidade');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Cantidade');
    }

}