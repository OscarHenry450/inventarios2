<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DetalleTransferencia;
use Illuminate\Auth\Access\HandlesAuthorization;

class DetalleTransferenciaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DetalleTransferencia');
    }

    public function view(AuthUser $authUser, DetalleTransferencia $detalleTransferencia): bool
    {
        return $authUser->can('View:DetalleTransferencia');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DetalleTransferencia');
    }

    public function update(AuthUser $authUser, DetalleTransferencia $detalleTransferencia): bool
    {
        return $authUser->can('Update:DetalleTransferencia');
    }

    public function delete(AuthUser $authUser, DetalleTransferencia $detalleTransferencia): bool
    {
        return $authUser->can('Delete:DetalleTransferencia');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:DetalleTransferencia');
    }

    public function restore(AuthUser $authUser, DetalleTransferencia $detalleTransferencia): bool
    {
        return $authUser->can('Restore:DetalleTransferencia');
    }

    public function forceDelete(AuthUser $authUser, DetalleTransferencia $detalleTransferencia): bool
    {
        return $authUser->can('ForceDelete:DetalleTransferencia');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DetalleTransferencia');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DetalleTransferencia');
    }

    public function replicate(AuthUser $authUser, DetalleTransferencia $detalleTransferencia): bool
    {
        return $authUser->can('Replicate:DetalleTransferencia');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DetalleTransferencia');
    }

}