<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DetalleVenta;
use Illuminate\Auth\Access\HandlesAuthorization;

class DetalleVentaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DetalleVenta');
    }

    public function view(AuthUser $authUser, DetalleVenta $detalleVenta): bool
    {
        return $authUser->can('View:DetalleVenta');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DetalleVenta');
    }

    public function update(AuthUser $authUser, DetalleVenta $detalleVenta): bool
    {
        return $authUser->can('Update:DetalleVenta');
    }

    public function delete(AuthUser $authUser, DetalleVenta $detalleVenta): bool
    {
        return $authUser->can('Delete:DetalleVenta');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:DetalleVenta');
    }

    public function restore(AuthUser $authUser, DetalleVenta $detalleVenta): bool
    {
        return $authUser->can('Restore:DetalleVenta');
    }

    public function forceDelete(AuthUser $authUser, DetalleVenta $detalleVenta): bool
    {
        return $authUser->can('ForceDelete:DetalleVenta');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DetalleVenta');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DetalleVenta');
    }

    public function replicate(AuthUser $authUser, DetalleVenta $detalleVenta): bool
    {
        return $authUser->can('Replicate:DetalleVenta');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DetalleVenta');
    }

}