<?php

namespace App\Services;

use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Notifications\KaryawanNotifikasi;

class NotificationService
{
    public function ApprovalKaryawanBaru($dtCalon)
    {

        $projects = explode(',', $dtCalon['project']); // misalnya "8,9,10" → [8,9,10]

        if (in_array(1, $projects)) {
            // Project 1 → HR PUSAT
            $users = User::role('HR PUSAT')
                ->permission('approve-karyawan-in')
                ->get();
        } else {
            // Selain project 1 → HR TENAGAKERJA
            $users = User::role('HR TENAGAKERJA')
                ->permission('approve-karyawan-in')
                ->get();
        }

        Notification::send($users, new KaryawanNotifikasi($dtCalon));

        return true;
    }
}
