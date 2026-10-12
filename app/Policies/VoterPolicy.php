<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Voter;
use Illuminate\Auth\Access\HandlesAuthorization;

class VoterPolicy
{
    use HandlesAuthorization;

    /**
     * Hak akses Super Admin / Panitia / Auditor untuk melihat DPT
     */
    public function viewAnyAdmin(?User $user): bool
    {
        return $user && in_array($user->role, ['super_admin', 'panitia', 'auditor']);
    }

    /**
     * Anti-IDOR: Pemilih HANYA boleh melihat profil / hak suaranya sendiri.
     * Admin/Panitia/Auditor berwenang melihat data pemilih.
     */
    public function view($user, Voter $targetVoter): bool
    {
        if ($user instanceof User) {
            return in_array($user->role, ['super_admin', 'panitia', 'auditor', 'helpdesk']);
        }

        if ($user instanceof Voter) {
            return (int) $user->id === (int) $targetVoter->id;
        }

        return false;
    }

    /**
     * Anti-IDOR: Pemilih HANYA boleh memperbarui datanya sendiri.
     * Admin/Panitia/Helpdesk berwenang mengubah status/reset data pemilih.
     */
    public function update($user, Voter $targetVoter): bool
    {
        if ($user instanceof User) {
            return in_array($user->role, ['super_admin', 'panitia', 'helpdesk']);
        }

        if ($user instanceof Voter) {
            return (int) $user->id === (int) $targetVoter->id;
        }

        return false;
    }

    /**
     * Anti-IDOR: Pemilih HANYA boleh memilih atas nama akunnya sendiri yang aktif.
     */
    public function vote($user, Voter $targetVoter): bool
    {
        if ($user instanceof Voter) {
            return (int) $user->id === (int) $targetVoter->id && (bool) $user->is_active;
        }

        return false;
    }
}
