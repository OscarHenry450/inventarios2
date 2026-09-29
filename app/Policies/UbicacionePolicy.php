<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Ubicacione;
use Illuminate\Auth\Access\HandlesAuthorization;

class UbicacionePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Ubicacione');
    }

    public function view(AuthUser $authUser, Ubicacione $ubicacione): bool
    {
        return $authUser->can('View:Ubicacione');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Ubicacione');
    }

    public function update(AuthUser $authUser, Ubicacione $ubicacione): bool
    {
        return $authUser->can('Update:Ubicacione');
    }

    public function delete(AuthUser $authUser, Ubicacione $ubicacione): bool
    {
        return $authUser->can('Delete:Ubicacione');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Ubicacione');
    }

    public function restore(AuthUser $authUser, Ubicacione $ubicacione): bool
    {
        return $authUser->can('Restore:Ubicacione');
    }

    public function forceDelete(AuthUser $authUser, Ubicacione $ubicacione): bool
    {
        return $authUser->can('ForceDelete:Ubicacione');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Ubicacione');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Ubicacione');
    }

    public function replicate(AuthUser $authUser, Ubicacione $ubicacione): bool
    {
        return $authUser->can('Replicate:Ubicacione');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Ubicacione');
    }

}