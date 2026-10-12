<?php

return [
    // Berapa hari akses recurring tetap aktif setelah expires_at terlewat
    'grace_days' => 3,

    // Berapa hari sebelum expired, tagihan renewal dibuat
    'renewal_notice_days' => 7,
];
