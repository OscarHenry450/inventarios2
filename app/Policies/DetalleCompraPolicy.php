<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DetalleCompra;
use Illuminate\Auth\Access\HandlesAuthorization;

class DetalleCompraPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DetalleCompra');
    }

    public function view(AuthUser $authUser, DetalleCompra $detalleCompra): bool
    {
        return $authUser->can('View:DetalleCompra');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DetalleCompra');
    }

    public function update(AuthUser $authUser, DetalleCompra $detalleCompra): bool
    {
        return $authUser->can('Update:DetalleCompra');
    }

    public function delete(AuthUser $authUser, DetalleCompra $detalleCompra): bool
    {
        return $authUser->can('Delete:DetalleCompra');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:DetalleCompra');
    }

    public function restore(AuthUser $authUser, DetalleCompra $detalleCompra): bool
    {
        return $authUser->can('Restore:DetalleCompra');
    }

    public function forceDelete(AuthUser $authUser, DetalleCompra $detalleCompra): bool
    {
        return $authUser->can('ForceDelete:DetalleCompra');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DetalleCompra');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DetalleCompra');
    }

    public function replicate(AuthUser $authUser, DetalleCompra $detalleCompra): bool
    {
        return $authUser->can('Replicate:DetalleCompra');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DetalleCompra');
    }

}