<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Inventario;
use Illuminate\Auth\Access\HandlesAuthorization;

class InventarioPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Inventario');
    }

    public function view(AuthUser $authUser, Inventario $inventario): bool
    {
        return $authUser->can('View:Inventario');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Inventario');
    }

    public function update(AuthUser $authUser, Inventario $inventario): bool
    {
        return $authUser->can('Update:Inventario');
    }

    public function delete(AuthUser $authUser, Inventario $inventario): bool
    {
        return $authUser->can('Delete:Inventario');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Inventario');
    }

    public function restore(AuthUser $authUser, Inventario $inventario): bool
    {
        return $authUser->can('Restore:Inventario');
    }

    public function forceDelete(AuthUser $authUser, Inventario $inventario): bool
    {
        return $authUser->can('ForceDelete:Inventario');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Inventario');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Inventario');
    }

    public function replicate(AuthUser $authUser, Inventario $inventario): bool
    {
        return $authUser->can('Replicate:Inventario');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Inventario');
    }

}