<?php

return [
    // Berapa hari akses recurring tetap aktif setelah expires_at terlewat
    'grace_days' => 3,

    // Berapa hari sebelum expired, tagihan renewal dibuat
    'renewal_notice_days' => 7,

    // Berapa hari sebelum expired, tagihan reminder dibuat
    'reminder_days' => [7, 3, 1, -1],
];
