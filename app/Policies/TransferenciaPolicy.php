<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Transferencia;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransferenciaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Transferencia');
    }

    public function view(AuthUser $authUser, Transferencia $transferencia): bool
    {
        return $authUser->can('View:Transferencia');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Transferencia');
    }

    public function update(AuthUser $authUser, Transferencia $transferencia): bool
    {
        return $authUser->can('Update:Transferencia');
    }

    public function delete(AuthUser $authUser, Transferencia $transferencia): bool
    {
        return $authUser->can('Delete:Transferencia');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Transferencia');
    }

    public function restore(AuthUser $authUser, Transferencia $transferencia): bool
    {
        return $authUser->can('Restore:Transferencia');
    }

    public function forceDelete(AuthUser $authUser, Transferencia $transferencia): bool
    {
        return $authUser->can('ForceDelete:Transferencia');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Transferencia');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Transferencia');
    }

    public function replicate(AuthUser $authUser, Transferencia $transferencia): bool
    {
        return $authUser->can('Replicate:Transferencia');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Transferencia');
    }

}